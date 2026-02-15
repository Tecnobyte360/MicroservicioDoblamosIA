<?php

namespace App\Http\Controllers\Api\SAPDoblamos;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class InventarioDisponibleController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {

            $inventario = $this->consultarInventarioSAP();

            return response()->json([
                'ok'      => true,
                'service' => 'MicroServicioDoblamosIA',
                'mode'    => 'all',
                'total'   => $inventario->count(),
                'data'    => $inventario->values(),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function query(Request $request): JsonResponse
    {
        try {

            $inventario = $this->consultarInventarioSAPConQuery($request);
            $sentQuery  = $this->buildODataQueryParams($request);

            return response()->json([
                'ok'      => true,
                'service' => 'MicroServicioDoblamosIA',
                'mode'    => $request->query('q') ? 'search' : 'filtered_query',
                'query'   => $sentQuery,
                'total'   => $inventario->count(),
                'data'    => $inventario->values(),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * SIN FILTROS
     */
    private function consultarInventarioSAP()
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        $response = $client->get(
            $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA',
            [
                'headers' => [
                    'Cookie' => $cookie,
                    'Accept' => 'application/json'
                ]
            ]
        );

        $data = json_decode($response->getBody(), true);

        return collect($data['value']);
    }

    /**
     * CON FILTROS
     */
    private function consultarInventarioSAPConQuery(Request $request)
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        $odataParams = $this->buildODataQueryParams($request);

        $queryString = http_build_query($odataParams, '', '&', PHP_QUERY_RFC3986);

        $url = $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA' . ($queryString ? '?' . $queryString : '');

        $response = $client->get($url, [
            'headers' => [
                'Cookie' => $cookie,
                'Accept' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return collect($data['value']);
    }

    /**
     * LOGIN SAP
     */
    private function loginAndBuildClient(): array
    {
        $sapBaseUrl   = rtrim(env('SAP_SL_BASE_URL'), '/');
        $sapCompanyDB = env('SAP_SL_COMPANY_DB');
        $sapUsername  = env('SAP_SL_USERNAME');
        $sapPassword  = env('SAP_SL_PASSWORD');

        $client = new Client([
            'timeout' => 30,
            'verify'  => false
        ]);

        $loginResponse = $client->post($sapBaseUrl . '/Login', [
            'json' => [
                'CompanyDB' => $sapCompanyDB,
                'UserName'  => $sapUsername,
                'Password'  => $sapPassword
            ]
        ]);

        $loginData = json_decode($loginResponse->getBody(), true);

        $cookie = 'B1SESSION=' . $loginData['SessionId'] . '; ROUTEID=.node1';

        return [$client, $sapBaseUrl, $cookie];
    }

    /**
     * 🔥 QUERY INTELIGENTE + FILTROS POR CAMPOS
     *
     * Soporta:
     * - q=texto libre
     * - Campo=valor (exacto)    ej: GrupoDOB=04
     * - Campo_like=valor        ej: ItemCode_like=LAM
     * - Campo_min / Campo_max   ej: PrecioVenta_min=100000&PrecioVenta_max=300000
     *
     * Campos permitidos:
     * ItemCode, DescripcionArticulo, GrupoDOB, StockTotal, PesoUnitario,
     * PrecioVenta, PVP, Especial, Minorista, Mayorista, id__
     */
    private function buildODataQueryParams(Request $request): array
    {
        $params = [];

        // Si mandan $filter manual, se respeta
        if ($request->query('$filter')) {
            $params['$filter'] = $request->query('$filter');
            return $params;
        }

        $filters = [];

        // 1) 🔎 Búsqueda libre por q
        $q = $this->normalizeSearchText((string) $request->query('q', ''));

        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q);
            $parts  = [];

            foreach ($tokens as $token) {
                if (strlen($token) < 2) continue;

                $token = $this->singularizeToken($token);
                $tokenSafe = str_replace("'", "''", $token);

                $parts[] = "(
                    contains(DescripcionArticulo,'{$tokenSafe}')
                    or contains(ItemCode,'{$tokenSafe}')
                    or contains(GrupoDOB,'{$tokenSafe}')
                )";
            }

            if (!empty($parts)) {
                $filters[] = implode(' and ', $parts);
            }
        }

        // 2) 🎯 Filtros por campos (exacto / like / min / max)
        $allowedFields = [
            'ItemCode'            => 'string',
            'DescripcionArticulo' => 'string',
            'GrupoDOB'            => 'string',
            'StockTotal'          => 'number',
            'PesoUnitario'        => 'number',
            'PrecioVenta'         => 'number',
            'PVP'                 => 'number',
            'Especial'            => 'number',
            'Minorista'           => 'number',
            'Mayorista'           => 'number',
            'id__'                => 'number',
        ];

        foreach ($allowedFields as $field => $type) {

            // Exacto: Campo=valor
            $val = $request->query($field);
            if ($val !== null && $val !== '') {
                if ($type === 'number') {
                    $filters[] = "{$field} eq " . (float) $val;
                } else {
                    $v = str_replace("'", "''", (string) $val);
                    $filters[] = "{$field} eq '{$v}'";
                }
            }

            // Like: Campo_like=valor
            $valLike = $request->query($field . '_like');
            if ($valLike !== null && $valLike !== '') {
                $v = $this->normalizeSearchText((string) $valLike); // normaliza igual que q
                $v = str_replace("'", "''", $v);
                $filters[] = "contains({$field},'{$v}')";
            }

            // Min: Campo_min
            $valMin = $request->query($field . '_min');
            if ($valMin !== null && $valMin !== '') {
                $filters[] = "{$field} ge " . (float) $valMin;
            }

            // Max: Campo_max
            $valMax = $request->query($field . '_max');
            if ($valMax !== null && $valMax !== '') {
                $filters[] = "{$field} le " . (float) $valMax;
            }
        }

        if (!empty($filters)) {
            $params['$filter'] = implode(' and ', $filters);
        }

        return $params;
    }

    /**
     * 🔥 NORMALIZA TEXTO (tildes, mayúsculas, símbolos)
     */
    private function normalizeSearchText(string $text): string
    {
        $text = trim($text);
        if ($text === '') return '';

        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = mb_strtoupper($text, 'UTF-8');
        $text = preg_replace('/[^A-Z0-9]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * 🔥 PLURAL SIMPLE (LAMINAS -> LAMINA)
     */
    private function singularizeToken(string $token): string
    {
        if (strlen($token) > 3 && substr($token, -1) === 'S') {
            return substr($token, 0, -1);
        }
        return $token;
    }
}
