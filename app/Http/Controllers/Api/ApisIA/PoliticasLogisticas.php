<?php

namespace App\Http\Controllers\Api\ApisIA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PoliticasLogisticas extends Controller
{
    /**
     * CONTEXTO ÚNICO DE POLÍTICAS LOGÍSTICAS (ZONAS)
     * Este texto se inyecta completo a la IA.
     */
    private function contexto(): string
    {
        return trim(<<<TEXT
POLÍTICAS LOGÍSTICAS OFICIALES DOBLAMOS S.A.S. (USO EXCLUSIVO PARA RESPUESTAS AL CLIENTE)

Regla base obligatoria:
Toda disponibilidad, inventario, bodega de despacho, precios, mínimos, costos de flete y tiempos exactos se confirman únicamente consultando SAP a través de la API de almacenes y logística. Está prohibido inventar, suponer o prometer información no validada.

ZONAS DE ATENCIÓN Y COBERTURA

ÁREA METROPOLITANA
Incluye las siguientes zonas y municipios:
- Comunas nororiental, noroccidental, Villahermosa, Aranjuez, Manrique, Populares, Santa Cruz
- Copacabana, Bello, Girardota, Castilla, 12 de Octubre
- Caldas, Barbosa
- Corregimientos de San Cristóbal y San Antonio de Prado

Condiciones logísticas Área Metropolitana:
- Material de 6 a 9 metros y/o servicios:
  Peso mínimo: 300 kg
  Valor mínimo facturado: $1.200.000 antes de IVA
  Si no cumple mínimos: costo de flete $120.000 antes de IVA

- Material de 12 metros:
  Peso mínimo: 1.5 toneladas
  Valor mínimo facturado: $6.000.000 antes de IVA
  Si no cumple mínimos: costo de flete $250.000 antes de IVA

- El material estándar para el área metropolitana se despacha preferiblemente desde la Bodega Copacabana (Bodega 15). Si no hay disponibilidad, se despacha desde la Bodega de la 33 (Bodega 12).

ORIENTE ANTIOQUEÑO CERCANO
Incluye los siguientes municipios:
- Guarne, Rionegro, Marinilla, Santuario, San Vicente
- El Peñol, Guatapé
- El Retiro, La Ceja, Carmen de Viboral
- Palmas, Llano Grande, Escobero

Condiciones logísticas Oriente Cercano:
- Material de 6 a 9 metros y/o servicios:
  Peso mínimo: 300 kg
  Valor mínimo facturado: $1.200.000 antes de IVA
  Si no cumple mínimos: costo de flete $250.000 antes de IVA

- Material de 12 metros:
  Peso mínimo: 1.5 toneladas
  Valor mínimo facturado: $6.000.000 antes de IVA
  Si no cumple mínimos: costo de flete $250.000 antes de IVA

- Para despachos al Oriente Cercano, la bodega preferente es Rionegro (Bodega 08).

REGLAS OPERATIVAS GENERALES:
- Siempre se toma en cuenta la mayor longitud del material dentro del pedido.
- El asesor debe consultar con el cliente el tipo de vehículo permitido para descargue en obra.
- Si el pedido incluye material de diferentes bodegas, logística coordina la entrega.
- Para zonas con restricciones, se debe validar programación con logística.
- Las entregas se realizan solo en zonas permitidas para cargue y descargue.
- Para artículos con peso superior a 80 kg, el descargue lo asume el cliente.
- El tiempo máximo de espera del vehículo para iniciar descargue es de 30 minutos.

COMPORTAMIENTO OBLIGATORIO DE LA IA:
- Nunca inventar precios, fletes, tiempos ni bodegas.
- Si falta información: pedir el dato exacto.
- Si se inicia validación SAP: responder obligatoriamente con resultado o aviso de espera.
- Nunca dejar al cliente sin respuesta después de decir “ya lo valido”.

FIN DE POLÍTICAS LOGÍSTICAS
TEXT);
    }

    /**
     * GET /api/ia/politicasVentas
     * Devuelve el contexto completo para la IA
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
