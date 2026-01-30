<?php

namespace App\Http\Controllers\Api\ApisIA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PoliticasLogisticas extends Controller
{
    /**
     * CONTEXTO ÚNICO DE POLÍTICAS LOGÍSTICAS
     * Texto oficial inyectado a la IA
     */
    private function contexto(): string
    {
        return <<<TEXT
POLÍTICAS LOGÍSTICAS OFICIALES  
DOBLAMOS S.A.S.  
(USO EXCLUSIVO PARA RESPUESTAS AL CLIENTE)

────────────────────────────────────────

REGLA BASE OBLIGATORIA

Toda disponibilidad, inventario, bodega de despacho, precios, mínimos,
costos de flete y tiempos exactos se confirman únicamente consultando
SAP a través de la API de almacenes y logística.

❌ Está prohibido inventar, suponer o prometer información no validada.

────────────────────────────────────────

ZONAS DE ATENCIÓN Y COBERTURA

========================================
ÁREA METROPOLITANA
========================================

Incluye:
• Comunas nororiental, noroccidental, Villahermosa  
• Aranjuez, Manrique, Populares, Santa Cruz  
• Copacabana, Bello, Girardota  
• Castilla, 12 de Octubre  
• Caldas, Barbosa  
• Corregimientos: San Cristóbal y San Antonio de Prado  

----------------------------------------
Condiciones logísticas
----------------------------------------

▶ Material de 6 a 9 metros y/o servicios  
• Peso mínimo: 300 kg  
• Valor mínimo: $1.200.000 antes de IVA  
• Si no cumple mínimos:  
  → Flete: $120.000 antes de IVA  

▶ Material de 12 metros  
• Peso mínimo: 1.5 toneladas  
• Valor mínimo: $6.000.000 antes de IVA  
• Si no cumple mínimos:  
  → Flete: $250.000 antes de IVA  

📦 Bodega preferente:
• Copacabana – Bodega 15  
• Alterna: Bodega de la 33 – Bodega 12  

========================================
ORIENTE ANTIOQUEÑO CERCANO
========================================

Incluye:
• Guarne, Rionegro, Marinilla  
• Santuario, San Vicente  
• El Peñol, Guatapé  
• El Retiro, La Ceja, Carmen de Viboral  
• Palmas, Llano Grande, Escobero  

----------------------------------------
Condiciones logísticas
----------------------------------------

▶ Material de 6 a 9 metros y/o servicios  
• Peso mínimo: 300 kg  
• Valor mínimo: $1.200.000 antes de IVA  
• Si no cumple mínimos:  
  → Flete: $250.000 antes de IVA  

▶ Material de 12 metros  
• Peso mínimo: 1.5 toneladas  
• Valor mínimo: $6.000.000 antes de IVA  
• Si no cumple mínimos:  
  → Flete: $250.000 antes de IVA  

📦 Bodega preferente:
• Rionegro – Bodega 08  

────────────────────────────────────────

REGLAS OPERATIVAS GENERALES

• Siempre se toma la mayor longitud del material del pedido  
• Confirmar con el cliente el tipo de vehículo permitido para descargue  
• Si el pedido incluye varias bodegas, logística coordina la entrega  
• Zonas con restricciones requieren validación previa  
• Entregas solo en zonas autorizadas  
• Artículos con peso superior a 80 kg: descargue a cargo del cliente  
• Tiempo máximo de espera del vehículo para descargue: 30 minutos  

────────────────────────────────────────

COMPORTAMIENTO OBLIGATORIO DE LA IA

✔ Nunca inventar precios, fletes, tiempos ni bodegas  
✔ Si falta información, solicitar el dato exacto  
✔ Si se inicia validación SAP, responder con resultado o aviso de espera  
✔ Nunca dejar al cliente sin respuesta tras decir “ya lo valido”

────────────────────────────────────────

FIN DE POLÍTICAS LOGÍSTICAS
TEXT;
    }

    /**
     * GET /api/ia/politicasVentas
     * Devuelve el texto limpio y legible
     */
    public function query(Request $request)
    {
        return response($this->contexto(), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
