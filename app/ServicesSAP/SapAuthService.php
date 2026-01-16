<?php

namespace App\ServicesSAP;

use App\Models\Seguridad\ConfiguracionSAP;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SapAuthService
{
    public static function autenticar(): void
    {
        try {
            // Cargar configuración desde la base de datos
            $config = ConfiguracionSAP::first();

            if (!$config) {
                throw new \Exception('No se encontró configuración SAP en la base de datos');
            }

            $client = new Client([
                'base_uri' => $config->base_url,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $response = $client->post('Login', [
                'json' => [
                    'CompanyDB' => $config->company_db,
                    'UserName'  => $config->username,
                    'Password'  => $config->password,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['SessionId'])) {
                Log::error('Error de autenticación: respuesta sin SessionId', ['response' => $data]);
                throw new \Exception('Autenticación fallida: no se recibió SessionId');
            }

            session([
                'sap_cookie' => 'B1SESSION=' . $data['SessionId'] . '; ROUTEID=' . $config->route_id,
            ]);

            Log::info('SAP autenticado exitosamente', ['session_id' => $data['SessionId']]);

        } catch (RequestException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No response body';
            $status = $response ? $response->getStatusCode() : 'No status code';

            Log::error('Error de autenticación SAP', [
                'status' => $status,
                'body' => $body,
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('Error al autenticar con SAP: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::critical('Fallo inesperado en autenticación SAP', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
