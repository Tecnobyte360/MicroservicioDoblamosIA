<?php

namespace App\Http\Controllers\Api\ApisIA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PoliticasLogisticas extends Controller
{
    /**
     * CONTEXTO ÚNICO (lo que se le pasa a la IA completo)
     */
    private function contexto(): string
    {
        return trim(<<<TEXT
CONTEXTO LOGÍSTICO OFICIAL DOBLAMOS S.A.S. (USO EXCLUSIVO PARA RESPUESTAS AL CLIENTE)

Regla principal: toda disponibilidad, inventario por bodega, precios, opciones equivalentes, sedes exactas y tiempos exactos se confirman consultando SAP a través de la API de almacenes. Está prohibido inventar o suponer datos. Si no hay respuesta inmediata de SAP, se debe informar que se está validando y luego confirmar con datos reales.

Modalidades:
1) Recogida: el cliente puede recoger el material en la sede acordada. La recogida se coordina únicamente después de confirmar disponibilidad en SAP y de confirmar alistamiento por bodega/operación. Se solicita nombre y documento de la persona que recoge (si aplica) y se confirma hora o rango de recogida según programación.
2) Entrega: se coordina transporte a la dirección indicada por el cliente. Para entrega se solicita ciudad, dirección exacta y horario de recepción. El costo del flete no se inventa; se confirma según ruta/transportador y condiciones del pedido.

Tiempos (orientativos, no exactos):
- Recogida: el alistamiento puede ser el mismo día o el siguiente día hábil, dependiendo de disponibilidad SAP, corte/transformación y carga operativa.
- Entrega: normalmente entre 1 y 3 días hábiles, dependiendo de ciudad, cupos de transporte, tipo de material y si requiere transformación.
- Transformación (corte/doblez/perforado): el tiempo depende de complejidad y capacidad; siempre se confirma antes de prometer.

Transformación y servicios:
- Se ofrece corte, doblez y perforado según factibilidad técnica y capacidad. Para transformación se deben pedir medidas finales, tolerancias y si aplica plano/observaciones.
- Para materiales largos (6m, 12m u otros), puede requerirse coordinación especial por transporte y maniobra de cargue/descargue.

Información mínima para cotizar correctamente:
- Producto y especificación (tipo de material, referencia, espesor/calibre, medidas o largo/formato).
- Cantidad y unidad (unidades, kilos, metros, toneladas).
- Ciudad/destino y modalidad (entrega o recoge).
- Si es entrega: dirección y horario de recepción.
- Si requiere transformación: tipo de proceso y medidas finales.

Mensajes aprobados (para WhatsApp):
- Para validar: "Listo, lo valido en SAP y te confirmo disponibilidad y precio."
- Si falta dato clave: "Perfecto. Para validarlo en SAP, ¿me confirmas [dato faltante]?"
- Si SAP tarda: "Ya estoy validando en SAP. Apenas me cargue la información te confirmo."
- Si SAP no responde: "SAP está tardando en responder. Para no dejarte esperando, lo valido con un asesor y te confirmo apenas tenga el dato. ¿Es entrega o recoges y en qué ciudad?"

Regla anti-loop:
- No repetir preguntas sobre datos que el cliente ya dio.
- Si se inició consulta a SAP (se dijo “ya lo valido”), se debe responder sí o sí con resultado o con fallback controlado (nunca dejar al cliente sin respuesta).

FIN DEL CONTEXTO LOGÍSTICO OFICIAL
TEXT);
    }

    /**
     * GET /api/ia/politicasVentas
     * Devuelve el contexto completo para inyectarlo al prompt.
     */
    public function query(Request $request)
    {
        return response()->json([
            "ok" => true,
            "contexto" => $this->contexto(),
            "version" => "1.0.0",
            "updated_at" => now()->toIso8601String(),
        ]);
    }
}
