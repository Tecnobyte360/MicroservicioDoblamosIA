<?php

namespace App\Livewire\Proveedores;

use App\Models\Proveedores\EvaluacionProveedor;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\ServicesSAP\SapPurchaseOrdersService;
use App\ServicesSAP\SapEvaluacionProveedoresService;
use GuzzleHttp\Exception\RequestException;
use Masmerise\Toaster\PendingToast;

class EvaluacionProveedores extends Component
{
    public $fecha_evaluacion, $centro_costo_id, $proveedor_id = '';
    public $cuatrimestre = '', $cuatrimestres = [];

    public $fecha_inicio, $fecha_fin;
    public $observaciones = '';
    public $ordenesSeleccionadas = [];
    public $ordenesSAP;
    public $ordenesCompra;
    public $cargando = false;
    public $centrosCostos;
    public $proveedores;
    public $proveedoresFiltrados;
    public $evaluador;
    public $criterios = [];
    public $consultandoSAP = false;
    public function mount()
    {
        $this->fecha_evaluacion = now()->toDateString();

        $this->ordenesSAP = collect();
        $this->ordenesCompra = collect();
        $this->proveedores = collect();
        $this->centrosCostos = collect();
        $this->proveedoresFiltrados = collect();
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->evaluador = Auth::user()?->name ?? 'Desconocido';
        $this->generarCuatrimestres();
    }

    public function rules()
    {
        return [
            'fecha_evaluacion' => 'required|date',
            'centro_costo_id' => 'required|string',
            'proveedor_id' => 'required|string',
            'cuatrimestre' => 'required|string',
            'criterios.cumplimiento_entrega' => 'required|integer|min:1|max:5',
            'criterios.calidad_servicio' => 'required|integer|min:1|max:5',
            'criterios.garantia_soporte' => 'required|integer|min:1|max:5',
            'criterios.precio' => 'required|integer|min:1|max:5',
            'criterios.capacidad_respuesta' => 'required|integer|min:1|max:5',
            'observaciones' => 'required|string|min:3|max:200',
            'ordenesSeleccionadas' => 'required|array|min:1',
        ];
    }

    public function generarCuatrimestres()
    {
        $añoActual = now()->year;
        foreach ([$añoActual - 1, $añoActual, $añoActual + 1] as $año) {
            for ($i = 1; $i <= 3; $i++) {
                $this->cuatrimestres[] = "Q{$i}-{$año}";
            }
        }
    }

    public function updatedFechaFin()
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            $this->consultarOrdenesSAP();
        }
    }

    public function updatedCuatrimestre()
    {
        if ($this->cuatrimestre) {
            $this->asignarFechasDesdeCuatrimestre($this->cuatrimestre);

            if ($this->fecha_inicio && $this->fecha_fin) {
                $this->consultarOrdenesSAP();
            }
        }
    }


    public function asignarFechasDesdeCuatrimestre($cuatrimestre)
    {
        [$q, $año] = explode('-', $cuatrimestre);
        $fechas = match ($q) {
            'Q1' => ["$año-01-01", "$año-04-30"],
            'Q2' => ["$año-05-01", "$año-08-31"],
            'Q3' => ["$año-09-01", "$año-12-31"],
            'Q4' => ["$año-10-01", "$año-12-31"],
            default => [null, null],
        };
        [$this->fecha_inicio, $this->fecha_fin] = $fechas;
    }

    public function consultarOrdenesSAP()
    {
        // Previene múltiples ejecuciones simultáneas
        if ($this->consultandoSAP) return;

        $this->consultandoSAP = true;
        $this->cargando = true;

        try {
            $sapService = app(SapPurchaseOrdersService::class);
            $ordenesSAP = $sapService->consultarOrdenes($this->fecha_inicio, $this->fecha_fin);

            $this->ordenesSAP = collect($ordenesSAP)->map(fn($orden, $index) => (object) [
                'id' => $index + 1,
                'doc_entry' => $orden['DocEntry'],
                'doc_num' => $orden['DocNum'],
                'numero_orden_compra_sap' => $orden['DocNum'],
                'card_code' => $orden['CardCode'],
                'card_name' => $orden['CardName'],
                'monto_total' => $orden['DocTotal'],
                'centro_costo_auto' => $orden['U_CCOSTOS_AUTO'] ?? null,
                'comentarios' => $orden['Comments'] ?? '',
            ]);


            if ($this->ordenesSAP->isEmpty()) {
                \Masmerise\Toaster\PendingToast::create()
                    ->error()
                    ->message('No se encontraron órdenes en SAP para el rango seleccionado.')
                    ->duration(8000);
            }

            // Procesar proveedores
            $this->proveedores = $this->ordenesSAP
                ->map(fn($orden) => [
                    'codigo' => $orden->card_code,
                    'nombre' => $orden->card_name,
                    'centro' => $orden->centro_costo_auto,
                ])
                ->unique(fn($p) => $p['codigo'] . $p['centro'])
                ->sortBy('nombre')
                ->values();

            // Procesar centros de costo
            $this->centrosCostos = $this->ordenesSAP
                ->map(fn($orden) => ['codigo' => $orden->centro_costo_auto])
                ->filter(fn($c) => !empty($c['codigo']))
                ->unique('codigo')
                ->sortBy('codigo')
                ->values();

            // Reset
            $this->proveedor_id = '';
            $this->ordenesCompra = collect();
            $this->proveedoresFiltrados = collect();
        } catch (\Throwable $e) {
            $this->ordenesSAP = collect();
            $this->proveedores = collect();
            $this->centrosCostos = collect();

            \Illuminate\Support\Facades\Log::error('Error al consultar órdenes de SAP', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            \Masmerise\Toaster\PendingToast::create()
                ->error()
                ->message('Error al consultar SAP: ' . $e->getMessage())
                ->duration(8000);
        }

        $this->cargando = false;
        $this->consultandoSAP = false;
    }



    public function updatedCentroCostoId()
    {
        $this->proveedor_id = '';
        $this->ordenesCompra = collect();

        $this->proveedoresFiltrados = $this->proveedores
            ->filter(fn($prov) => $prov['centro'] === $this->centro_costo_id)
            ->values();
    }

    public function updatedProveedorId()
    {
        $this->ordenesCompra = $this->ordenesSAP
            ->filter(
                fn($orden) =>
                $orden->card_code === $this->proveedor_id &&
                    $orden->centro_costo_auto === $this->centro_costo_id
            )
            ->values();


        $this->ordenesSeleccionadas = $this->ordenesCompra->pluck('id')->toArray();
    }


     public function enviarEvaluacionASAP()
    {
        $this->validate();
        $this->cargando = true;

        try {
            $cookie = session('sap_cookie');
            if (! $cookie) {
                throw new \Exception('No hay sesión activa con SAP.');
            }

            $evaluadorId     = Auth::id();
            $evaluadorNombre = Auth::user()?->name ?? 'Desconocido';
            // Obtener cuatrimestre y año
            [$q, $año] = explode('-', $this->cuatrimestre);
            $cuatrimestre = (int) ltrim($q, 'Q');

            // 1. Guardar encabezado en la tabla local evaluacion_proveedors
            $evaluacion = EvaluacionProveedor::create([
                'cuatrimestre'         => $this->cuatrimestre,
                'fecha_evaluacion'     => $this->fecha_evaluacion ?? now()->toDateString(),
                'centro_costo_id'      => $this->centro_costo_id,
                'proveedor_id'         => $this->proveedor_id,
                'evaluador_id'         => $evaluadorId,
                'cumplimiento_entrega' => (int)($this->criterios['cumplimiento_entrega'] ?? 0),
                'calidad_servicio'     => (int)($this->criterios['calidad_servicio'] ?? 0),
                'garantia_soporte'     => (int)($this->criterios['garantia_soporte'] ?? 0),
                'precio'               => (int)($this->criterios['precio'] ?? 0),
                'capacidad_respuesta'  => (int)($this->criterios['capacidad_respuesta'] ?? 0),
                'observaciones'        => $this->observaciones,
            ]);

            // 2. Enviar encabezado a SAP (tabla U_PEX_EVALUACIONPROVE)
            $sapCode = (string)$evaluacion->id;
            $payload = [
                "Code"                  => $sapCode,
                "Name"                  => $sapCode,
                "U_PEX_FechaEvaluacion" => $evaluacion->fecha_evaluacion,
                "U_PEX_Periodo"         => (int)$año,
                "U_PEX_Cuatrimestre"    => $cuatrimestre,
                "U_PEX_RangoIni"        => $this->fecha_inicio,
                "U_PEX_RangoFin"        => $this->fecha_fin,
                "U_PEX_CentroCosto"     => $this->centro_costo_id,
                "U_PEX_Proveedor"       => $this->proveedor_id,
                "U_PEX_Entrega"         => $evaluacion->cumplimiento_entrega,
                "U_PEX_Calidad"         => $evaluacion->calidad_servicio,
                "U_PEX_Garantia"        => $evaluacion->garantia_soporte,
                "U_PEX_Precio"          => $evaluacion->precio,
                "U_PEX_Capacidad"       => $evaluacion->capacidad_respuesta,
                "U_PEX_Observaciones"   => $evaluacion->observaciones,
                "U_PEX_Evaluador"       => $evaluadorNombre,
            ];

            $sapService      = app(SapEvaluacionProveedoresService::class);
            $sapOrdenService = app(SapPurchaseOrdersService::class);

            Log::info('📤 Enviando evaluación general a SAP', ['payload' => $payload]);
            $sapService->enviar($payload, $cookie);

            // 3. Enviar cada detalle: aquí usamos evaluacion_id como “Code”
            foreach ($this->ordenesSeleccionadas as $ordenId) {
                // 3.1) Guardar detalle local en evaluacion_orden_compras
                $detalle = \App\Models\EvaluacionProveedor\EvaluacionOrdenCompra::create([
                    'evaluacion_id'   => $evaluacion->id,
                    'orden_compra_id' => $ordenId,
                ]);

                // 3.2) Buscar datos de la orden en la colección de SAP
                $orden = $this->ordenesSAP->firstWhere('id', $ordenId);
                if (! $orden) {
                    Log::warning("⚠️ Orden con ID $ordenId no encontrada en ordenesSAP.");
                    continue;
                }

                // 3.3) Armar payload para detalle usando evaluacion_id como Code
                $detalleCode = $sapCode . '-' . $orden->doc_entry;
              $detallePayload = [
                    "Code"                => $detalleCode,         // ✅ Único para cada detalle
                    "Name"                => $detalleCode,
                    "U_PEX_CodeEP"        => $sapCode,             // 🔗 FK hacia encabezado en SAP
                    "U_PEX_OCEvaluacion"  => $orden->doc_entry,
                    "U_PEX_OCEvaluacionIn"=> $orden->doc_num,
                    "U_PEX_ResumenCompra" => $orden->comentarios ?? 'Sin comentarios',
                    "U_PEX_TotalCompra"   => (float)$orden->monto_total ?? 0.0,
                ];

                Log::info('📦 Enviando detalle a SAP', ['detalle' => $detallePayload]);
                $sapService->enviarDetalle($detallePayload, $cookie);

                // 3.4) Marcar la orden (en SAP) como evaluada
                try {
                    Log::debug("🔄 PATCH SAP para DocEntry {$orden->doc_entry}");
                    $sapOrdenService->marcarOrdenComoEvaluada((int)$orden->doc_entry);
                    Log::info("✔️ Orden SAP DocEntry {$orden->doc_entry} marcada como evaluada.");
                    PendingToast::create()
                        ->success()
                        ->message("Orden {$orden->doc_num} actualizada como evaluada.")
                        ->duration(5000);
                }
                catch (\Throwable $ex) {
                    Log::error("❌ Error al marcar como evaluada la orden {$orden->doc_entry}", [
                        'error' => $ex->getMessage(),
                        'trace' => $ex->getTraceAsString(),
                    ]);
                    PendingToast::create()
                        ->error()
                        ->message("Error al actualizar orden {$orden->doc_num}.")
                        ->duration(9000);
                }
            }

            PendingToast::create()
                ->success()
                ->message('✅ Evaluación enviada a SAP con éxito y órdenes actualizadas.')
                ->duration(9000);

            $this->resetFormulario();
            $this->dispatch('resetCamposBusqueda');
        }
        catch (\Exception $e) {
            Log::error('💥 Error al guardar/enviar evaluación a SAP', [
                'message' => $e->getMessage(),
                'payload' => $payload ?? [],
            ]);
            PendingToast::create()
                ->error()
                ->message('Error: ' . $e->getMessage())
                ->duration(9000);
        }

        $this->cargando = false;
    }



   public function resetFormulario()
{
    $this->fecha_evaluacion = now()->toDateString();
    $this->centro_costo_id = '';
    $this->proveedor_id = '';
    $this->cuatrimestre = '';
    $this->fecha_inicio = null;
    $this->fecha_fin = null;
    $this->observaciones = '';
    $this->criterios = [];
    $this->ordenesSeleccionadas = [];
    $this->ordenesCompra = collect();
    $this->proveedoresFiltrados = collect();
    $this->ordenesSAP = collect();
    $this->centrosCostos = collect();
    $this->proveedores = collect();
}



    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


    public function render()
    {
        return view('livewire.proveedores.evaluacion-proveedores');
    }
}
