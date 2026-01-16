<?php

namespace App\ServicesSAP;

use App\Models\Seguridad\ConfiguracionSAP;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SapEvaluacionProveedoresService
{
    /**
     * Envia la evaluación del proveedor a SAP
     *
     * @param array $payload
     * @param string $cookie
     * @return void
     * @throws \Exception
     */
    public function enviar(array $payload, string $cookie): void
    {
        $config = ConfiguracionSAP::first();

        if (!$config) {
            throw new \Exception('No se encontró configuración SAP en la base de datos');
        }

        try {
            $client = new Client([
                'base_uri' => $config->base_url,
                'verify' => false,
                'timeout' => 15,
            ]);

            $response = $client->post('U_PEX_EVALUACIONPROVE', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Cookie' => $cookie,
                ],
                'json' => $payload,
            ]);

            if (!in_array($response->getStatusCode(), [200, 201])) {
                throw new \Exception("SAP respondió con código HTTP {$response->getStatusCode()}");
            }

        } catch (RequestException $e) {
            $mensaje = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            Log::error('❌ Error HTTP al enviar evaluación a SAP', [
                'payload' => $payload,
                'error' => $mensaje,
            ]);

            throw new \Exception("Error al enviar a SAP: $mensaje");
        } catch (\Exception $e) {
            Log::error('❌ Excepción general al enviar evaluación a SAP', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Envia el detalle de la evaluación del proveedor
     *
     * @param array $detalle
     * @param string $cookie
     * @return void
     * @throws \Exception
     */
    public function enviarDetalle(array $detalle, string $cookie): void
    {
        $config = ConfiguracionSAP::first();

        if (!$config) {
            throw new \Exception('No se encontró configuración SAP en la base de datos');
        }

        try {
            $client = new Client([
                'base_uri' => $config->base_url,
                'verify' => false,
                'timeout' => 15,
            ]);

            $response = $client->post('U_PEX_DETALLEEVAPROV', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Cookie' => $cookie,
                ],
                'json' => $detalle,
            ]);

            if (!in_array($response->getStatusCode(), [200, 201])) {
                throw new \Exception("SAP respondió con código HTTP {$response->getStatusCode()}");
            }

        } catch (RequestException $e) {
            $mensaje = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            Log::error('❌ Error HTTP al enviar DETALLE evaluación a SAP', [
                'detalle' => $detalle,
                'error' => $mensaje,
            ]);

            throw new \Exception("Error al enviar detalle a SAP: $mensaje");
        } catch (\Exception $e) {
            Log::error('❌ Excepción general al enviar DETALLE evaluación a SAP', [
                'detalle' => $detalle,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
