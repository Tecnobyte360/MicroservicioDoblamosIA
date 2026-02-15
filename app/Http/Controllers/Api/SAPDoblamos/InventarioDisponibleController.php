<?php

namespace App\Http\Controllers\Api\SAPDoblamos;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
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
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * SIN FILTROS
     */
    public function consultarInventarioSAP()
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        $response = $client->get(
            $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA',
            [
                'headers' => [
                    'Cookie'       => $cookie,
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        $data = json_decode((string) $response->getBody(), true);

        return collect($data['value']);
    }

    /**
     * CON QUERY ODATA + q inteligente
     */
    public function consultarInventarioSAPConQuery(Request $request)
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        $odataParams = $this->buildODataQueryParams($request);
        $queryString = http_build_query($odataParams, '', '&', PHP_QUERY_RFC3986);

        $url = $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA' . ($queryString ? '?' . $queryString : '');

        $response = $client->get($url, [
            'headers' => [
                'Cookie' => $cookie,
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

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
            'verify'  => false,
        ]);

        $loginResponse = $client->post($sapBaseUrl . '/Login', [
            'json' => [
                'CompanyDB' => $sapCompanyDB,
                'UserName'  => $sapUsername,
                'Password'  => $sapPassword,
            ],
        ]);

        $loginData = json_decode((string) $loginResponse->getBody(), true);

        $cookie = 'B1SESSION=' . $loginData['SessionId'] . '; ROUTEID=.node1';

        return [$client, $sapBaseUrl, $cookie];
    }

    /**
     * 🔥 CONSTRUCCIÓN DEL QUERY INTELIGENTE
     */
    private function buildODataQueryParams(Request $request): array
    {
        $params = [];

        // SI ENVÍAN $filter manual, se respeta
        if ($request->query('$filter')) {
            $params['$filter'] = $request->query('$filter');
        }

        // 🔥 BÚSQUEDA INTELIGENTE POR PALABRAS
        $q = $this->normalizeSearchText((string) $request->query('q', ''));

        if ($q !== '' && empty($params['$filter'])) {

            $tokens = preg_split('/\s+/', $q);

            $parts = [];

            foreach ($tokens as $token) {

                $token = $this->singularizeToken($token);

                $tokenSafe = str_replace("'", "''", $token);

                $parts[] = "(contains(DescripcionArticulo,'{$tokenSafe}') or contains(ItemCode,'{$tokenSafe}'))";
            }

            if (!empty($parts)) {
                $params['$filter'] = implode(' and ', $parts);
            }
        }

        return $params;
    }

    /**
     * 🔥 NORMALIZA TEXTO (lámina -> LAMINA)
     */
    private function normalizeSearchText(string $text): string
    {
        $text = trim($text);

        if ($text === '') return '';

        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = mb_strtoupper($text, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * 🔥 PLURAL SIMPLE (LAMINAS -> LAMINA)
     */
    private function singularizeToken(string $token): string
    {
        if (mb_strlen($token) > 3 && mb_substr($token, -1) === 'S') {
            return mb_substr($token, 0, -1);
        }

        return $token;
    }
}
