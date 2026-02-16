¿<?php

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
            // 1) Construir intentos (filtros) en cascada
            $attempts = $this->buildODataAttempts($request);

            // 2) Ejecutar intentos hasta que alguno tenga resultados
            $result = null;
            $used   = null;

            foreach ($attempts as $idx => $odataParams) {
                $data = $this->consultarInventarioSAPConQuery($odataParams);

                if ($data->count() > 0) {
                    $result = $data;
                    $used   = [
                        'attempt' => $idx + 1,
                        'strategy'=> $odataParams['_strategy'] ?? 'unknown',
                        '$filter' => $odataParams['$filter'] ?? null,
                    ];
                    break;
                }
            }

            // 3) Si por alguna razón TODO da 0, igual responde algo (último recurso)
            if ($result === null) {
                $fallback = $this->consultarInventarioSAPConQuery([
                    '$top'    => '50',
                    '$orderby'=> 'StockTotal desc',
                    '$filter' => 'StockTotal ge 1'
                ]);

                $result = $fallback;
                $used = [
                    'attempt'  => 'fallback',
                    'strategy' => 'stock_desc',
                    '$filter'  => 'StockTotal ge 1',
                ];
            }

            return response()->json([
                'ok'      => true,
                'service' => 'MicroServicioDoblamosIA',
                'mode'    => $request->query('q') ? 'search' : 'filtered_query',
                'used'    => $used,
                'debug'   => [
                    'raw_q'        => $request->query('q'),
                    'normalized_q' => $this->normalizeSearchText((string)$request->query('q','')),
                ],
                'total'   => $result->count(),
                'data'    => $result->values(),
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
     * CON FILTROS (recibe array odata)
     */
    private function consultarInventarioSAPConQuery(array $odataParams)
    {
        [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

        // quitar meta internos
        unset($odataParams['_strategy']);

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
     * 🔥 Construye varios intentos de búsqueda para GARANTIZAR respuesta
     */
    private function buildODataAttempts(Request $request): array
    {
        // Si mandan $filter manual, lo respetas (1 solo intento)
        if ($request->query('$filter')) {
            return [[
                '_strategy' => 'manual_filter',
                '$filter'   => (string)$request->query('$filter'),
                '$top'      => '200',
            ]];
        }

        $rawQ = (string)$request->query('q','');
        $q    = $this->normalizeSearchText($rawQ);

        $tokens = $this->extractTokens($q);

        // Si no hay tokens, devolvemos top con stock
        if (empty($tokens)) {
            return [[
                '_strategy' => 'no_q_stock_desc',
                '$filter'   => 'StockTotal ge 1',
                '$orderby'  => 'StockTotal desc',
                '$top'      => '50',
            ]];
        }

        // Construimos partes contains
        $parts = array_map(function($token){
            $token = $this->singularizeToken($token);
            $tokenSafe = str_replace("'", "''", $token);
            return "(contains(DescripcionArticulo,'{$tokenSafe}') or contains(ItemCode,'{$tokenSafe}') or contains(GrupoDOB,'{$tokenSafe}'))";
        }, $tokens);

        // Tokens “fuertes”: normas y patrones (NTC, ASTM, GR, A500, A36, etc.)
        $strong = array_values(array_filter($tokens, function($t){
            return preg_match('/^(NTC|ASTM|GR|A\d{2,4}|A500|A36|A572|ISO|DIN)\b/', $t)
                || preg_match('/^\d{3,4}X\d{2,4}$/', $t) // 1200X6000
                || preg_match('/^\d+(MM|M)$/', $t)       // 6M, 4MM
                || preg_match('/^\d+$/', $t);            // 2289, 4526
        }));

        $attempts = [];

        // 1) Estricto (AND) con todos
        $attempts[] = [
            '_strategy' => 'and_all',
            '$filter'   => implode(' and ', $parts),
            '$top'      => '200',
        ];

        // 2) Medio (OR) con todos (más recall)
        $attempts[] = [
            '_strategy' => 'or_all',
            '$filter'   => implode(' or ', $parts),
            '$top'      => '200',
        ];

        // 3) Suave: solo 3 tokens más largos (tienden a ser los más informativos)
        $topTokens = $tokens;
        usort($topTokens, fn($a,$b) => strlen($b) <=> strlen($a));
        $topTokens = array_slice($topTokens, 0, 3);

        $parts3 = array_map(function($token){
            $token = $this->singularizeToken($token);
            $tokenSafe = str_replace("'", "''", $token);
            return "(contains(DescripcionArticulo,'{$tokenSafe}') or contains(ItemCode,'{$tokenSafe}') or contains(GrupoDOB,'{$tokenSafe}'))";
        }, $topTokens);

        $attempts[] = [
            '_strategy' => 'and_top3',
            '$filter'   => implode(' and ', $parts3),
            '$top'      => '200',
        ];

        // 4) Solo fuertes (si existen)
        if (!empty($strong)) {
            $strongParts = array_map(function($token){
                $token = $this->singularizeToken($token);
                $tokenSafe = str_replace("'", "''", $token);
                return "(contains(DescripcionArticulo,'{$tokenSafe}') or contains(ItemCode,'{$tokenSafe}') or contains(GrupoDOB,'{$tokenSafe}'))";
            }, $strong);

            $attempts[] = [
                '_strategy' => 'and_strong_only',
                '$filter'   => implode(' and ', $strongParts),
                '$top'      => '200',
            ];
        }

        // 5) Último intento: devolver algo útil con stock y ordenado
        $attempts[] = [
            '_strategy' => 'stock_desc_fallback',
            '$filter'   => 'StockTotal ge 1',
            '$orderby'  => 'StockTotal desc',
            '$top'      => '50',
        ];

        return $this->applyFieldFilters($request, $attempts);
    }

    /**
     * Extrae tokens limpios + stopwords + limita cantidad
     */
    private function extractTokens(string $q): array
    {
        $tokens = preg_split('/\s+/', $q) ?: [];
        $tokens = array_values(array_filter($tokens, fn($t) => strlen($t) >= 2));

        $stop = [
            'PERO','ES','QUE','VALE','PRECIO','CUANTO','CUÁNTO','CUESTA','VALOR',
            'NECESITO','QUIERO','ME','REGALA','UNA','UN','LA','EL','LOS','LAS',
            'DE','DEL','POR','PARA','CON','SIN','A','AL','EN','Y','O'
        ];
        $tokens = array_values(array_filter($tokens, fn($t) => !in_array($t, $stop, true)));

        // limitar para no crear filtros enormes
        return array_slice($tokens, 0, 8);
    }

    /**
     * Aplica filtros por campos (min/max/like/exacto) a TODOS los intentos
     */
    private function applyFieldFilters(Request $request, array $attempts): array
    {
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

        $extraFilters = [];

        foreach ($allowedFields as $field => $type) {

            $val = $request->query($field);
            if ($val !== null && $val !== '') {
                if ($type === 'number') $extraFilters[] = "{$field} eq " . (float)$val;
                else {
                    $v = str_replace("'", "''", (string)$val);
                    $extraFilters[] = "{$field} eq '{$v}'";
                }
            }

            $valLike = $request->query($field . '_like');
            if ($valLike !== null && $valLike !== '') {
                $v = $this->normalizeSearchText((string)$valLike);
                $v = str_replace("'", "''", $v);
                $extraFilters[] = "contains({$field},'{$v}')";
            }

            $valMin = $request->query($field . '_min');
            if ($valMin !== null && $valMin !== '') {
                $extraFilters[] = "{$field} ge " . (float)$valMin;
            }

            $valMax = $request->query($field . '_max');
            if ($valMax !== null && $valMax !== '') {
                $extraFilters[] = "{$field} le " . (float)$valMax;
            }
        }

        if (empty($extraFilters)) return $attempts;

        // añadir filtros extra a cada intento
        foreach ($attempts as &$a) {
            if (!empty($a['$filter'])) {
                $a['$filter'] = '(' . $a['$filter'] . ') and ' . implode(' and ', $extraFilters);
            } else {
                $a['$filter'] = implode(' and ', $extraFilters);
            }
        }

        return $attempts;
    }

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

    private function singularizeToken(string $token): string
    {
        if (strlen($token) > 3 && substr($token, -1) === 'S') {
            return substr($token, 0, -1);
        }
        return $token;
    }
}
