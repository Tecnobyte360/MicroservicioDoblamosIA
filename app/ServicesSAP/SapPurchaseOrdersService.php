<?php

namespace App\ServicesSAP;

use App\Models\Seguridad\ConfiguracionSAP;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\ServicesSAP\SapAuthService;
use Illuminate\Support\Facades\Log;

class SapPurchaseOrdersService
{
    public function consultarOrdenes(string $fechaInicio, string $fechaFin): array
    {
        if (!session('sap_cookie')) {
            Log::info('No hay cookie SAP activa, autenticando...');
            SapAuthService::autenticar();
        }

        $config = ConfiguracionSAP::first();
        if (!$config) {
            throw new \Exception('No se encontró configuración SAP en la base de datos');
        }

        $client = new Client([
            'base_uri' => $config->base_url,
            'verify' => false,
        ]);

        $urlBase = "PurchaseOrders?"
            . "\$filter=DocDate ge {$fechaInicio} and DocDate le {$fechaFin} "
            . "and DocumentStatus eq 'bost_Close' and Cancelled eq 'tNO' and (U_PEX_OCEvaluada eq null or U_PEX_OCEvaluada eq 'NO')"
            . "&\$select=DocNum,DocEntry,CardCode,CardName,DocTotal,U_CCOSTOS_AUTO,Comments"
            . "&\$orderby=DocDate desc";



        $url = $urlBase;
        $ordenes = [];

        do {
            try {
                $response = $client->get($url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Cookie' => session('sap_cookie'),
                    ]
                ]);

                Log::debug('Respuesta exitosa desde SAP', [
                    'http_status' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                ]);
            } catch (RequestException $e) {
                $status = optional($e->getResponse())->getStatusCode();
                $body = optional($e->getResponse())->getBody()?->getContents();

                Log::error('Error al consultar órdenes en SAP', [
                    'url' => $url,
                    'status_code' => $status,
                    'error_message' => $e->getMessage(),
                    'response_body' => $body,
                ]);

                if ($status === 401) {
                    Log::warning('Sesión expirada, intentando reautenticar...');
                    SapAuthService::autenticar();

                    try {
                        $response = $client->get($url, [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'Cookie' => session('sap_cookie'),
                            ]
                        ]);

                        Log::info('Reintento exitoso tras autenticación SAP.');
                    } catch (\Throwable $retryError) {
                        Log::critical('Fallo reintento tras autenticación SAP', [
                            'mensaje' => $retryError->getMessage(),
                            'trace' => $retryError->getTraceAsString()
                        ]);
                        throw $retryError;
                    }
                } else {
                    throw $e;
                }
            } catch (\Throwable $e) {
                Log::critical('Error inesperado al consultar SAP', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            $data = json_decode($response->getBody(), true);

            if (!isset($data['value'])) {
                Log::warning('Respuesta SAP sin campo "value"', [
                    'raw_body' => $response->getBody()->getContents()
                ]);
                break;
            }

            $ordenesObtenidas = count($data['value']);
            Log::info("Órdenes obtenidas en esta página: {$ordenesObtenidas}");

            $ordenes = array_merge($ordenes, $data['value']);
            $url = $data['@odata.nextLink'] ?? null;

            if ($url) {
                Log::debug('SAP retornó @odata.nextLink, continuará con siguiente página.', [
                    'next_link' => $url
                ]);
            }
        } while ($url);

        Log::info('Consulta SAP finalizada', [
            'total_ordenes' => count($ordenes),
        ]);

        return $ordenes;
    }

    public function marcarOrdenComoEvaluada(int $docEntry): void
{
    $config = ConfiguracionSAP::first();
    if (!$config) {
        throw new \Exception('No se encontró configuración SAP en la base de datos');
    }

    if (!session('sap_cookie')) {
        SapAuthService::autenticar();
    }

    try {
        $client = new Client([
            'base_uri' => $config->base_url,
            'verify' => false,
        ]);

        $payload = [
            'U_PEX_OCEvaluada' => 'SI',
        ];

        Log::info("📤 Enviando PATCH para marcar OC como evaluada", [
            'url' => "PurchaseOrders({$docEntry})",
            'payload' => $payload
        ]);

        $response = $client->patch("PurchaseOrders({$docEntry})", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Cookie' => session('sap_cookie'),
            ],
            'json' => $payload,
        ]);

        if (!in_array($response->getStatusCode(), [200, 204])) {
            throw new \Exception("Error al actualizar orden {$docEntry} en SAP. Código: " . $response->getStatusCode());
        }

        Log::info("✅ Orden de compra {$docEntry} marcada como evaluada en SAP.");
    } catch (RequestException $e) {
        Log::error("❌ Error en PATCH orden {$docEntry}", [
            'status' => optional($e->getResponse())->getStatusCode(),
            'body' => optional($e->getResponse())->getBody()?->getContents(),
            'exception' => $e->getMessage(),
        ]);
    }
}

}
