<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WolkvoxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Capturar lo que manda Wolkvox (puede venir por query o body)
        $telefono   = $request->input('telefono') ?? $request->query('telefono');
        $idCustomer = $request->input('id_customer') ?? $request->query('id_customer');
        $idQueue    = $request->input('id_queue') ?? $request->query('id_queue');
        $idAgent    = $request->input('id_agent') ?? $request->query('id_agent');

        Log::info('WVX webhook recibido', [
            'telefono' => $telefono,
            'id_customer' => $idCustomer,
            'id_queue' => $idQueue,
            'id_agent' => $idAgent,
            'payload' => $request->all(),
        ]);

        if (!$telefono) {
            return response()->json(['ok' => false, 'msg' => 'Falta telefono'], 422);
        }

        // 2) Buscar el conn_id más reciente para ese teléfono (hoy)
        $now = now('America/Bogota');
        $dateIni = $now->copy()->startOfDay()->format('YmdHis');
        $dateEnd = $now->copy()->endOfDay()->format('YmdHis');

        $token = config('services.wolkvox.token'); // lo guardas en .env

        $baseUrl = 'https://wv0100.wolkvox.com/api/v2/reports_manager.php';

        // chat_1: lista de chats del rango
        $list = Http::withHeaders([
            'wolkvox-token' => $token,
            'Accept' => 'application/json',
        ])->get($baseUrl, [
            'api' => 'chat_1',
            'date_ini' => $dateIni,
            'date_end' => $dateEnd,
        ])->json();

        if (($list['code'] ?? null) !== '200') {
            Log::error('WVX chat_1 error', ['resp' => $list]);
            return response()->json(['ok' => false, 'msg' => 'Error consultando chat_1', 'resp' => $list], 500);
        }

        // Filtrar por customer_phone o user_id (depende del dataset)
        $rows = $list['data'] ?? [];
        $match = collect($rows)->first(function ($row) use ($telefono) {
            $p1 = $row['customer_phone'] ?? null;
            $p2 = $row['user_id'] ?? null;
            return $p1 === $telefono || $p2 === $telefono;
        });

        if (!$match) {
            return response()->json([
                'ok' => true,
                'msg' => 'No encontré conn_id para ese teléfono en el rango de hoy (a veces tarda segundos). Reintenta.',
                'telefono' => $telefono,
                'date_ini' => $dateIni,
                'date_end' => $dateEnd,
            ]);
        }

        $connId = $match['conn_id'];

        // 3) chat_2: traer conversación completa del conn_id
        $detail = Http::withHeaders([
            'wolkvox-token' => $token,
            'Accept' => 'application/json',
        ])->get($baseUrl, [
            'api' => 'chat_2',
            'conn_id' => $connId,
            'date_ini' => $dateIni,
            'date_end' => $dateEnd,
        ])->json();

        if (($detail['code'] ?? null) !== '200') {
            Log::error('WVX chat_2 error', ['resp' => $detail]);
            return response()->json(['ok' => false, 'msg' => 'Error consultando chat_2', 'resp' => $detail], 500);
        }

        // 4) Sacar el último mensaje del cliente (lo que entró)
        $conv = $detail['data'][0]['conversation'] ?? [];
        $lastCustomerMsg = collect($conv)
            ->reverse()
            ->first(fn($m) => ($m['from'] ?? '') === 'CUSTOMER');

        // Aquí es donde llamas tu IA (OpenAI) y generas respuesta
        // $respuestaIA = app(ServicioIA::class)->responder($lastCustomerMsg['message'] ?? '');

        return response()->json([
            'ok' => true,
            'telefono' => $telefono,
            'conn_id' => $connId,
            'ultimo_cliente' => $lastCustomerMsg,
            'conversation_count' => count($conv),
            // 'respuesta_ia' => $respuestaIA,
        ]);
    }
}
