<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Afiliado;
use App\Models\BeneficiarioExterno;
use App\Models\Prestacion;
use App\Models\Prestador;
use App\Models\TipoComprobante;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $canFilterAll = $user->hasAnyRole(['Administrador', 'Supervisor']);

        $query = Orden::with([
            'tipoComprobante',
            'beneficiario:id,certificado,apellido,nombre',
            'titular:id,certificado,apellido,nombre',
            'sucursal:id,nombre',
            'prestador:id,apellido,nombre'
        ]);

        // Control de acceso por sucursal
        if (!$canFilterAll && $user->sucursal_id) {
            // Usuarios normales solo ven órdenes de su sucursal
            $query->where('sucursal_id', $user->sucursal_id);
        }

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_orden', 'like', "%{$search}%")
                  ->orWhereHas('beneficiario', function ($q) use ($search) {
                      $q->where('apellido', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('certificado', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro de sucursal (solo para admin/supervisor)
        if ($canFilterAll && $request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fec_ord', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fec_ord', '<=', $request->fecha_hasta);
        }

        $ordenes = $query->orderBy('fec_ord', 'desc')
            ->orderBy('numero_orden', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Obtener sucursales (solo para admin/supervisor)
        $sucursales = $canFilterAll
            ? Sucursal::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre'])
            : [];

        return Inertia::render('Ordenes/Index', [
            'ordenes' => $ordenes,
            'filters' => $request->only(['search', 'estado', 'sucursal_id', 'fecha_desde', 'fecha_hasta']),
            'sucursales' => $sucursales,
            'canFilterAll' => $canFilterAll,
            'userSucursalId' => $user->sucursal_id ?? null,
        ]);
    }

    public function create()
    {
        $tiposComprobante = TipoComprobante::where('estado', 'activo')->get();
        $prestadores = Prestador::activos()
            ->select('id', 'apellido', 'nombre')
            ->orderBy('apellido')
            ->get();

        return Inertia::render('Ordenes/Create', [
            'tiposComprobante' => $tiposComprobante,
            'prestadores' => $prestadores,
            'sucursalId' => auth()->user()->sucursal_id ?? 1,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_comprobante_id' => 'required|exists:tipo_comprobante,id',
            'titular_id' => 'required|exists:afiliados,id',
            'beneficiario_id' => 'required|exists:afiliados,id',
            'prestador_id' => 'nullable|exists:prestadores,id',
            'observacion' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.prestacion_id' => 'required|exists:prestaciones,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.importe_uni' => 'required|numeric|min:0',
            'detalles.*.importe_total' => 'required|numeric|min:0',
            'detalles.*.total_afiliado' => 'required|numeric|min:0',
            'detalles.*.total_prestador' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $sucursalId = auth()->user()->sucursal_id ?? 1;
            $numeroOrden = Orden::siguienteNumero($sucursalId);

            // Obtener tipo comprobante y sucursal para generar número completo
            $tipoComprobante = TipoComprobante::find($validated['tipo_comprobante_id']);
            $sucursal = \App\Models\Sucursal::find($sucursalId);

            $numeroOrdenCompleto = sprintf(
                '%s-%s-%s',
                $tipoComprobante->codigo,
                str_pad($sucursal->codigo, 4, '0', STR_PAD_LEFT),
                str_pad($numeroOrden, 8, '0', STR_PAD_LEFT)
            );

            // Calcular totales
            $totalAfiliado = collect($validated['detalles'])->sum('total_afiliado');
            $totalPrestador = collect($validated['detalles'])->sum('total_prestador');
            $total = collect($validated['detalles'])->sum('importe_total');

            $orden = Orden::create([
                'numero_orden' => $numeroOrden,
                'numero_orden_completo' => $numeroOrdenCompleto,
                'fec_ord' => now(),
                'tipo_comprobante_id' => $validated['tipo_comprobante_id'],
                'prestador_id' => $validated['prestador_id'],
                'titular_id' => $validated['titular_id'],
                'beneficiario_id' => $validated['beneficiario_id'],
                'observacion' => $validated['observacion'],
                'total' => $total,
                'total_prestador' => $totalPrestador,
                'total_afiliado' => $totalAfiliado,
                'usu_alta' => auth()->user()->name ?? 'Sistema',
                'sucursal_id' => $sucursalId,
                'estado' => 1, // Pendiente
            ]);

            // Crear detalles
            foreach ($validated['detalles'] as $detalle) {
                DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'prestacion_id' => $detalle['prestacion_id'],
                    'cantidad' => $detalle['cantidad'],
                    'importe_uni' => $detalle['importe_uni'],
                    'importe_total' => $detalle['importe_total'],
                    'neto_gravado' => $detalle['neto_gravado'] ?? 0,
                    'iva' => $detalle['iva'] ?? 0,
                    'total_afiliado' => $detalle['total_afiliado'],
                    'total_prestador' => $detalle['total_prestador'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('ordenes.show', $orden)
                ->with('success', 'Orden creada exitosamente')
                ->with('orden_numero', $numeroOrdenCompleto);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la orden: ' . $e->getMessage()]);
        }
    }

    public function show(Orden $orden)
    {
        $orden->load([
            'tipoComprobante',
            'prestador',
            'titular',
            'beneficiario.plan',
            'sucursal',
            'detalles.prestacion.rubro'
        ]);

        return Inertia::render('Ordenes/Show', [
            'orden' => $orden,
        ]);
    }

    public function destroy(Request $request, Orden $orden)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Si la orden está PENDIENTE (estado 1) → Anular directamente
            if ($orden->estado === 1) {
                $orden->update([
                    'estado' => 3, // Anulada
                    'fec_anu' => now(),
                    'usu_anu' => auth()->user()->name ?? 'Sistema',
                    'motivo_anulacion' => $request->motivo_anulacion,
                ]);

                DB::commit();
                return redirect()->route('ordenes.index')
                    ->with('success', 'Orden anulada exitosamente');
            }

            // Si la orden está PAGADA (estado 2) → Crear Nota de Crédito
            if ($orden->estado === 2) {
                // Buscar el tipo de comprobante "Nota de Crédito"
                $tipoNC = DB::table('tipo_comprobante')->where('codigo', 'NC')->first();

                if (!$tipoNC) {
                    throw new \Exception('No se encontró el tipo de comprobante Nota de Crédito');
                }

                // Generar número de orden y número completo para NC
                $numeroOrdenNC = Orden::obtenerSiguienteNumero($orden->sucursal_id);
                $sucursal = \App\Models\Sucursal::find($orden->sucursal_id);

                $numeroOrdenCompletoNC = sprintf(
                    '%s-%s-%s',
                    $tipoNC->codigo,
                    str_pad($sucursal->codigo, 4, '0', STR_PAD_LEFT),
                    str_pad($numeroOrdenNC, 8, '0', STR_PAD_LEFT)
                );

                // Crear nueva orden tipo Nota de Crédito
                $notaCredito = Orden::create([
                    'numero_orden' => $numeroOrdenNC,
                    'numero_orden_completo' => $numeroOrdenCompletoNC,
                    'tipo_comprobante_id' => $tipoNC->id,
                    'sucursal_id' => $orden->sucursal_id,
                    'titular_id' => $orden->titular_id,
                    'beneficiario_id' => $orden->beneficiario_id,
                    'prestador_id' => $orden->prestador_id,
                    'total' => $orden->total,
                    'estado' => 1, // Pendiente
                    'fec_ord' => now(),
                    'usu_alta' => auth()->user()->name ?? 'Sistema',
                    'observacion' => 'Nota de Crédito por anulación de Orden N° ' . $orden->numero_orden_completo . '. Motivo: ' . $request->motivo_anulacion,
                    'motivo_anulacion' => $request->motivo_anulacion,
                ]);

                // Copiar los detalles de la orden original
                foreach ($orden->detalles as $detalle) {
                    $notaCredito->detalles()->create([
                        'prestacion_id' => $detalle->prestacion_id,
                        'cantidad' => $detalle->cantidad,
                        'importe_uni' => $detalle->importe_uni,
                        'importe_total' => $detalle->importe_total,
                        'neto_gravado' => $detalle->neto_gravado,
                        'iva' => $detalle->iva,
                        'total_afiliado' => $detalle->total_afiliado,
                        'total_prestador' => $detalle->total_prestador,
                    ]);
                }

                // Marcar la orden original como anulada
                $orden->update([
                    'estado' => 3, // Anulada
                    'fec_anu' => now(),
                    'usu_anu' => auth()->user()->name ?? 'Sistema',
                    'motivo_anulacion' => $request->motivo_anulacion,
                ]);

                DB::commit();
                return redirect()->route('ordenes.show', $notaCredito->id)
                    ->with('success', 'Nota de Crédito generada exitosamente');
            }

            // Si la orden ya está anulada
            return back()->withErrors(['error' => 'Esta orden ya está anulada']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    // API: Buscar afiliados por certificado (desde MSSQL)
    public function buscarAfiliados(Request $request)
    {
        $request->validate([
            'certificado' => 'required|string',
        ]);

        try {
            \Log::info('Buscando certificado: ' . $request->certificado);

            // Buscar en la base de datos externa (MSSQL)
            $beneficiarios = BeneficiarioExterno::buscarPorCertificado($request->certificado);

            \Log::info('Beneficiarios encontrados: ' . $beneficiarios->count());

            if ($beneficiarios->isEmpty()) {
                return response()->json([
                    'error' => 'No se encontraron beneficiarios con ese certificado'
                ], 404);
            }

            // Formatear datos para el frontend y sincronizar con tabla local
            $resultado = $beneficiarios->map(function ($beneficiario) {
                // Debug: ver todos los atributos
                \Log::info('Atributos del beneficiario:', $beneficiario->getAttributes());

                // Sincronizar con tabla local afiliados (crear/actualizar)
                $afiliadoLocal = Afiliado::updateOrCreate(
                    ['dni' => $beneficiario->Dni], // Buscar por DNI
                    [
                        'certificado' => $beneficiario->IdTitularCF,
                        'apellido' => $beneficiario->Apellido,
                        'nombre' => $beneficiario->Nombre,
                        'vinculo' => (int) $beneficiario->IdVinculoCf, // Vínculo desde MSSQL
                        'plan_id' => 1, // TODO: mapear plan real
                        'tiene_cobertura' => true, // Por defecto todos tienen cobertura
                        'estado' => 'activo',
                    ]
                );

                return [
                    'id' => $afiliadoLocal->id, // Usar ID local sincronizado
                    'certificado' => $beneficiario->IdTitularCF,
                    'apellido' => $beneficiario->Apellido,
                    'nombre' => $beneficiario->Nombre,
                    'dni' => $beneficiario->Dni,
                    'vinculo' => (int) $beneficiario->IdVinculoCf,
                    'vinculo_texto' => $beneficiario->vinculo_texto,
                    'nombre_completo' => $beneficiario->nombre_completo,
                    'plan_id' => 1, // TODO: Obtener plan real cuando esté configurado
                    'plan' => [
                        'id' => 1,
                        'nombre' => 'Plan por Defecto' // TODO: Reemplazar con plan real
                    ],
                ];
            });

            \Log::info('Resultado a enviar: ', $resultado->toArray());

            return response()->json($resultado);

        } catch (\Exception $e) {
            \Log::error('Error en buscarAfiliados: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'Error al conectar con la base de datos de beneficiarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Obtener precio de prestación según cobertura del afiliado
    public function obtenerPrecioPrestacion(Request $request)
    {
        $request->validate([
            'prestacion_id' => 'required|exists:prestaciones,id',
            'afiliado_id' => 'required|exists:afiliados,id',
        ]);

        $afiliado = Afiliado::findOrFail($request->afiliado_id);
        $prestacion = Prestacion::findOrFail($request->prestacion_id);

        // Determinar precio según cobertura
        if ($afiliado->tiene_cobertura) {
            // Afiliado CON cobertura → usa precio_afiliado
            $precioUnitario = $prestacion->precio_afiliado ?? 0;
        } else {
            // Afiliado SIN cobertura (particular) → usa precio_general
            $precioUnitario = $prestacion->precio_general ?? 0;
        }

        return response()->json([
            'importe_uni' => $precioUnitario,
            'total_afiliado' => $precioUnitario,
            'total_prestador' => 0,
            'neto_gravado' => 0,
            'iva' => 0,
        ]);
    }

    // Cambiar estado de orden
    public function cambiarEstado(Request $request, Orden $orden)
    {
        $request->validate([
            'estado' => 'required|in:1,2,3',
        ]);

        try {
            $nuevoEstado = (int) $request->estado;

            if ($nuevoEstado === 3) { // Anular
                if ($orden->estado !== 1) {
                    return back()->withErrors(['error' => 'Solo se pueden anular órdenes pendientes']);
                }

                $orden->update([
                    'estado' => 3,
                    'fec_anu' => now(),
                    'usu_anu' => auth()->user()->name ?? 'Sistema',
                ]);
            } else {
                $orden->update(['estado' => $nuevoEstado]);
            }

            return back()->with('success', 'Estado actualizado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cambiar estado: ' . $e->getMessage()]);
        }
    }

    // Exportar orden a PDF
    public function exportarPdf(Orden $orden)
    {
        $orden->load([
            'tipoComprobante',
            'prestador',
            'beneficiario.plan',
            'sucursal',
            'detalles.prestacion.rubro'
        ]);

        $sucursal = $orden->sucursal;

        $pdf = \PDF::loadView('pdf.orden', [
            'orden' => $orden,
            'sucursal' => $sucursal,
            'fecha' => now()->format('d/m/Y H:i')
        ])->setPaper('a5', 'landscape');

        return $pdf->download('orden-' . $orden->numero_orden . '.pdf');
    }
}
