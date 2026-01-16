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
    /**
     * GET /api/sap/inventario-disponible
     * Sin filtros: retorna todo.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $inventario = $this->consultarInventarioSAP();

            return response()->json([
                'ok'     => true,
                'service'=> 'MicroServicioDoblamosIA',
                'mode'   => 'all',
                'total'  => $inventario->count(),
                'data'   => $inventario,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'service' => 'MicroServicioDoblamosIA',
                'message' => 'Error consultando inventario en SAP',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/sap/inventario-disponible/query
     * Con filtros: reenvía parámetros OData permitidos a SAP.
     *
     * Ej:
     * /api/sap/inventario-disponible/query?$filter=WhsCode eq '01'&$top=100
     */
    public function query(Request $request): JsonResponse
    {
        try {
            $inventario = $this->consultarInventarioSAPConQuery($request);
            $sentQuery  = $this->buildODataQueryParams($request);

            return response()->json([
                'ok'      => true,
                'service' => 'MicroServicioDoblamosIA',
                'mode'    => 'filtered_query',
                'query'   => $sentQuery,
                'total'   => $inventario->count(),
                'data'    => $inventario,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'ok'      => false,
                'service' => 'MicroServicioDoblamosIA',
                'message' => 'Error consultando inventario filtrado en SAP',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta INVENTARIO_DISPONIBLE_IA desde SAP Service Layer (sin filtros)
     */
    public function consultarInventarioSAP()
    {
        try {
            [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

            try {
                $response = $client->get(
                    $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA',
                    [
                        'headers' => [
                            'Cookie'       => $cookie,
                            'Accept'       => 'application/json',
                            'Content-Type' => 'application/json',
                            'Expect'       => '',
                        ],
                    ]
                );
            } catch (RequestException $e) {
                $sapError = $this->parseSapError($e);
                throw new \Exception('Error consultando INVENTARIO_DISPONIBLE_IA: ' . $sapError);
            }

            $data = json_decode((string) $response->getBody(), true);

            if (!isset($data['value'])) {
                throw new \Exception('Respuesta inesperada de SAP (sin propiedad value).');
            }

            return collect($data['value']);

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Consulta INVENTARIO_DISPONIBLE_IA desde SAP Service Layer (con filtros OData)
     */
    public function consultarInventarioSAPConQuery(Request $request)
    {
        try {
            [$client, $sapBaseUrl, $cookie] = $this->loginAndBuildClient();

            $odataParams = $this->buildODataQueryParams($request);
            $queryString = http_build_query($odataParams, '', '&', PHP_QUERY_RFC3986);

            $url = $sapBaseUrl . '/sml.svc/INVENTARIO_DISPONIBLE_IA';
            if (!empty($queryString)) {
                $url .= '?' . $queryString;
            }

            try {
                $response = $client->get($url, [
                    'headers' => [
                        'Cookie'       => $cookie,
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                        'Expect'       => '',
                    ],
                ]);
            } catch (RequestException $e) {
                $sapError = $this->parseSapError($e);
                throw new \Exception('Error consultando INVENTARIO_DISPONIBLE_IA (query): ' . $sapError);
            }

            $data = json_decode((string) $response->getBody(), true);

            if (!isset($data['value'])) {
                throw new \Exception('Respuesta inesperada de SAP (sin propiedad value).');
            }

            return collect($data['value']);

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Login a SAP y retorna:
     * [Client $client, string $sapBaseUrl, string $cookieHeader]
     */
    private function loginAndBuildClient(): array
    {
        try {
            $sapBaseUrl   = rtrim(env('SAP_SL_BASE_URL'), '/');
            $sapCompanyDB = env('SAP_SL_COMPANY_DB');
            $sapUsername  = env('SAP_SL_USERNAME');
            $sapPassword  = env('SAP_SL_PASSWORD');

            $timeout   = (int) env('SAP_SL_TIMEOUT', 30);
            $verifySsl = filter_var(env('SAP_SL_VERIFY_SSL', false), FILTER_VALIDATE_BOOL);

            if (!$sapBaseUrl || !$sapCompanyDB || !$sapUsername || !$sapPassword) {
                throw new \Exception('Variables de entorno SAP_SL_* incompletas.');
            }

            $client = new Client([
                'timeout' => $timeout,
                'verify'  => $verifySsl,
            ]);

            // LOGIN
            try {
                $loginResponse = $client->post($sapBaseUrl . '/Login', [
                    'json' => [
                        'CompanyDB' => $sapCompanyDB,
                        'UserName'  => $sapUsername,
                        'Password'  => $sapPassword,
                    ],
                ]);
            } catch (RequestException $e) {
                $sapError = $this->parseSapError($e);
                throw new \Exception('Error en login SAP: ' . $sapError);
            }

            $loginData = json_decode((string) $loginResponse->getBody(), true);

            if (!isset($loginData['SessionId'])) {
                throw new \Exception('SAP no devolvió SessionId en el login.');
            }

            $sessionId = $loginData['SessionId'];

            // ⚠️ ROUTEID fijo (si falla intermitente, lo hacemos dinámico desde Set-Cookie)
            $cookie = 'B1SESSION=' . $sessionId . '; ROUTEID=.node1';

            return [$client, $sapBaseUrl, $cookie];

        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Permite solo parámetros OData típicos.
     * Ejemplos: $select, $filter, $orderby, $top, $skip, $count, $expand...
     */
    private function buildODataQueryParams(Request $request): array
    {
        $allowed = [
            '$select',
            '$filter',
            '$orderby',
            '$top',
            '$skip',
            '$count',
            '$expand',
            '$apply',
            '$search',
        ];

        $params = [];
        foreach ($allowed as $key) {
            $val = $request->query($key);
            if ($val !== null && $val !== '') {
                $params[$key] = $val;
            }
        }

        // Normalizar $count
        if (isset($params['$count'])) {
            $v = strtolower((string) $params['$count']);
            $params['$count'] = in_array($v, ['1', 'true', 'yes'], true) ? 'true' : 'false';
        }

        // Limitar $top para evitar cargas excesivas (ajusta si quieres)
        if (isset($params['$top'])) {
            $top = max(1, (int) $params['$top']);
            $params['$top'] = (string) min($top, 5000);
        }

        // Normalizar $skip
        if (isset($params['$skip'])) {
            $params['$skip'] = (string) max(0, (int) $params['$skip']);
        }

        return $params;
    }

    /**
     * Extrae error real desde SAP Service Layer
     */
    private function parseSapError(RequestException $e): string
    {
        if ($e->hasResponse()) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);

            if (isset($body['error']['message']['value'])) {
                return $body['error']['message']['value'];
            }

            return (string) $e->getResponse()->getBody();
        }

        return $e->getMessage();
    }
}
