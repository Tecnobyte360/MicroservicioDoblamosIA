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
            $odataParams = $this->buildODataQueryParams($request); // <-- construye filter limpio
            $inventario  = $this->consultarInventarioSAPConQuery($odataParams);

            return response()->json([
                'ok'      => true,
                'service' => 'MicroServicioDoblamosIA',
                'mode'    => $request->query('q') ? 'search' : 'filtered_query',
                'query'   => [
                    // 👇 esto es SOLO para debug legible (sin +)
                    'raw_q'        => $request->query('q'),
                    'normalized_q' => $odataParams['_debug_normalized_q'] ?? null,
                    'tokens'       => $odataParams['_debug_tokens'] ?? [],
                    '$filter'      => $odataParams['$filter'] ?? null,
                ],
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

        return collect($data['value'] ?? []);
    }

    /**
     * CON FILTROS (recibe ya el array OData params listo)
     */
    private function consultarInventarioSAPConQuery(array $odataParams)
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        // ⚠️ sacar debug internos para no enviarlos a SAP
        unset($odataParams['_debug_normalized_q'], $odataParams['_debug_tokens']);

        $queryString = http_build_query($odataParams, '', '&', PHP_QUERY_RFC3986);

        $url = $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA' . ($queryString ? '?' . $queryString : '');

        $response = $client->get($url, [
            'headers' => [
                'Cookie' => $cookie,
                'Accept' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        return collect($data['value'] ?? []);
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

        $cookie = 'B1SESSION=' . ($loginData['SessionId'] ?? '') . '; ROUTEID=.node1';

        return [$client, $sapBaseUrl, $cookie];
    }

    /**
     * 🔥 QUERY INTELIGENTE + FILTROS POR CAMPOS
     */
    private function buildODataQueryParams(Request $request): array
    {
        $params  = [];
        $filters = [];

        // Si mandan $filter manual, se respeta
        if ($request->query('$filter')) {
            $params['$filter'] = (string) $request->query('$filter');
            return $params;
        }

        // ==========================================================
        // 1) 🔎 Búsqueda libre por q (limpia + stopwords + join AND/OR)
        // ==========================================================
        $rawQ = (string) $request->query('q', '');

        // ✅ Extra blindaje: por si llega q con + sin decodificar
        $rawQ = str_replace('+', ' ', $rawQ);

        $q = $this->normalizeSearchText($rawQ);

        $tokens = [];
        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q) ?: [];
            $tokens = array_values(array_filter($tokens, fn($t) => strlen($t) >= 2));

            // Stopwords (para que no dañen el match)
            $stop = [
                'PERO','ES','QUE','VALE','PRECIO','CUANTO','CUÁNTO','CUESTA','VALOR',
                'NECESITO','QUIERO','ME','REGALA','UNA','UN','LA','EL','LOS','LAS',
                'DE','DEL','POR','PARA','CON','SIN','A','AL','EN','Y','O'
            ];
            $tokens = array_values(array_filter($tokens, fn($t) => !in_array($t, $stop, true)));

            // Limitar tokens para no armar filtros gigantes (ajusta si quieres)
            $tokens = array_slice($tokens, 0, 8);

            $parts = [];
            foreach ($tokens as $token) {
                $token = $this->singularizeToken($token);
                $tokenSafe = str_replace("'", "''", $token);

                $parts[] = "(contains(DescripcionArticulo,'{$tokenSafe}') or contains(ItemCode,'{$tokenSafe}') or contains(GrupoDOB,'{$tokenSafe}'))";
            }

            if (!empty($parts)) {
                // ✅ si son muchas palabras: OR (mejor recall)
                // ✅ si son pocas: AND (más precisión)
                $join = (count($parts) > 4) ? ' or ' : ' and ';
                $filters[] = implode($join, $parts);
            }
        }

        // ==========================================================
        // 2) 🎯 Filtros por campos (exacto / like / min / max)
        // ==========================================================
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
                // ✅ aquí también quedas cubierto porque normaliza quita + si apareciera
                $v = $this->normalizeSearchText((string) $valLike);
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

        // Debug legible (NO se envía a SAP, lo quitamos antes)
        $params['_debug_normalized_q'] = $q;
        $params['_debug_tokens']       = $tokens;

        return $params;
    }

    /**
     * 🔥 NORMALIZA TEXTO (tildes, mayúsculas, símbolos)
     * ✅ Incluye blindaje para reemplazar + por espacio.
     */
    private function normalizeSearchText(string $text): string
    {
        $text = trim($text);
        if ($text === '') return '';

        // ✅ por si llega + literal desde alguna capa externa
        $text = str_replace('+', ' ', $text);

        // Quitar tildes / caracteres raros
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;

        // Mayúsculas
        $text = mb_strtoupper($text, 'UTF-8');

        // Solo letras y números
        $text = preg_replace('/[^A-Z0-9]+/', ' ', $text);

        // Espacios duplicados
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
