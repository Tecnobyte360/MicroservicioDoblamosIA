<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WolkvoxWebhookController extends Controller
{
 public function handle(Request $request)
    {
        // 1) Captura todo el payload
        $payload = $request->all();

        // 2) Log para depurar (queda en storage/logs/laravel.log)
        Log::info('WVX_WEBHOOK_IN', [
            'ip'      => $request->ip(),
            'headers' => $this->safeHeaders($request),
            'body'    => $payload,
        ]);

        // 3) Responder rápido (Wolkvox normalmente espera 200)
        return response()->json([
            'ok' => true,
            'msg' => 'Webhook received',
        ], 200);
    }

    /**
     * Evita loggear headers sensibles (authorization, tokens, etc.)
     */
    private function safeHeaders(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            $lower = strtolower($key);
            if (in_array($lower, ['authorization', 'x-api-key', 'wolkvox-token', 'token'])) {
                $headers[$key] = ['***'];
            } else {
                $headers[$key] = $values;
            }
        }
        return $headers;
    }
}
