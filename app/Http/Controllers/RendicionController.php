<?php

namespace App\Http\Controllers;

use App\Models\Rendicion;
use App\Models\DetalleRendicion;
use App\Models\Orden;
use App\Models\Prestador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RendicionController extends Controller
{
    public function index(Request $request)
    {
        $query = Rendicion::with([
            'prestador:id,apellido,nombre',
            'sucursal:id,nombre',
        ]);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_rendicion_completo', 'like', "%{$search}%")
                  ->orWhereHas('prestador', function ($q) use ($search) {
                      $q->where('apellido', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        // Si el usuario no es administrador, solo ver rendiciones de su sucursal
        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $query->where('sucursal_id', $user->sucursal_id);
        }

        $rendiciones = $query->orderBy('fecha_inicio', 'desc')
            ->orderBy('numero_rendicion', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Rendiciones/Index', [
            'rendiciones' => $rendiciones,
            'filters' => $request->only(['search', 'estado', 'sucursal_id', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id ?? 1;

        // Obtener todos los prestadores activos
        // Un prestador puede trabajar en múltiples sucursales
        $prestadores = Prestador::activos()
            ->select('id', 'apellido', 'nombre')
            ->orderBy('apellido')
            ->get();

        return Inertia::render('Rendiciones/Create', [
            'prestadores' => $prestadores,
            'sucursalId' => $sucursalId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prestador_id' => 'required|exists:prestadores,id',
            'observacion' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $sucursalId = $user->sucursal_id ?? 1;

            // Generar número de rendición
            $numeroRendicion = Rendicion::siguienteNumero($sucursalId);
            $sucursal = \App\Models\Sucursal::find($sucursalId);

            $numeroRendicionCompleto = sprintf(
                'REN-%s-%s',
                str_pad($sucursal->codigo, 4, '0', STR_PAD_LEFT),
                str_pad($numeroRendicion, 8, '0', STR_PAD_LEFT)
            );

            $rendicion = Rendicion::create([
                'numero_rendicion' => $numeroRendicion,
                'numero_rendicion_completo' => $numeroRendicionCompleto,
                'prestador_id' => $validated['prestador_id'],
                'sucursal_id' => $sucursalId,
                'fecha_inicio' => now(),
                'estado' => 1, // Abierta
                'total' => 0,
                'usu_alta' => $user->name ?? 'Sistema',
                'observacion' => $validated['observacion'],
            ]);

            DB::commit();

            return redirect()->route('rendiciones.show', $rendicion)
                ->with('success', 'Rendición creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la rendición: ' . $e->getMessage()]);
        }
    }

    public function show(Rendicion $rendicion)
    {
        $rendicion->load([
            'prestador',
            'sucursal',
            'detalles.orden.beneficiario',
            'detalles.orden.tipoComprobante',
        ]);

        return Inertia::render('Rendiciones/Show', [
            'rendicion' => $rendicion,
        ]);
    }

    public function cerrar(Rendicion $rendicion)
    {
        if ($rendicion->estado !== 1) {
            return back()->withErrors(['error' => 'Solo se pueden cerrar rendiciones abiertas']);
        }

        try {
            $rendicion->update([
                'estado' => 2,
                'fecha_cierre' => now(),
                'usu_cierre' => auth()->user()->name ?? 'Sistema',
            ]);

            return back()->with('success', 'Rendición cerrada exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cerrar la rendición: ' . $e->getMessage()]);
        }
    }

    // API: Buscar orden por número
    public function buscarOrden(Request $request)
    {
        $request->validate([
            'numero_orden' => 'required|string',
            'rendicion_id' => 'required|exists:rendiciones,id',
        ]);

        $rendicion = Rendicion::findOrFail($request->rendicion_id);

        // Buscar orden por número completo o número simple
        $orden = Orden::with(['beneficiario', 'tipoComprobante', 'prestador'])
            ->where(function ($query) use ($request) {
                $query->where('numero_orden_completo', $request->numero_orden)
                      ->orWhere('numero_orden', $request->numero_orden);
            })
            ->where('estado', 2) // Solo órdenes pagadas
            ->first();

        if (!$orden) {
            return response()->json([
                'error' => 'Orden no encontrada o no está pagada'
            ], 404);
        }

        // Verificar que la orden pertenezca a la sucursal correcta
        $user = auth()->user();
        if (!$user->hasRole('admin') && $orden->sucursal_id !== $rendicion->sucursal_id) {
            return response()->json([
                'error' => 'La orden no pertenece a la sucursal de esta rendición'
            ], 403);
        }

        // Verificar que la orden no esté ya en esta rendición
        $yaAgregada = DetalleRendicion::where('rendicion_id', $rendicion->id)
            ->where('orden_id', $orden->id)
            ->exists();

        if ($yaAgregada) {
            return response()->json([
                'error' => 'Esta orden ya fue agregada a la rendición'
            ], 400);
        }

        return response()->json($orden);
    }

    // API: Agregar orden a rendición
    public function agregarOrden(Request $request, Rendicion $rendicion)
    {
        $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
        ]);

        if ($rendicion->estado !== 1) {
            return response()->json([
                'error' => 'Solo se pueden agregar órdenes a rendiciones abiertas'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orden = Orden::findOrFail($request->orden_id);

            // Verificar que no esté ya agregada
            $yaAgregada = DetalleRendicion::where('rendicion_id', $rendicion->id)
                ->where('orden_id', $orden->id)
                ->exists();

            if ($yaAgregada) {
                return response()->json([
                    'error' => 'Esta orden ya fue agregada a la rendición'
                ], 400);
            }

            // Agregar detalle
            DetalleRendicion::create([
                'rendicion_id' => $rendicion->id,
                'orden_id' => $orden->id,
                'total_prestador' => $orden->total_prestador ?? 0,
            ]);

            // Actualizar total de la rendición
            $nuevoTotal = $rendicion->detalles()->sum('total_prestador');
            $rendicion->update(['total' => $nuevoTotal]);

            DB::commit();

            // Recargar con relaciones
            $rendicion->load([
                'detalles.orden.beneficiario',
                'detalles.orden.tipoComprobante',
            ]);

            return response()->json([
                'message' => 'Orden agregada exitosamente',
                'rendicion' => $rendicion,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al agregar la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    // API: Quitar orden de rendición
    public function quitarOrden(Request $request, Rendicion $rendicion)
    {
        $request->validate([
            'detalle_id' => 'required|exists:detalle_rendiciones,id',
        ]);

        if ($rendicion->estado !== 1) {
            return response()->json([
                'error' => 'Solo se pueden quitar órdenes de rendiciones abiertas'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $detalle = DetalleRendicion::where('id', $request->detalle_id)
                ->where('rendicion_id', $rendicion->id)
                ->firstOrFail();

            $detalle->delete();

            // Actualizar total de la rendición
            $nuevoTotal = $rendicion->detalles()->sum('total_prestador');
            $rendicion->update(['total' => $nuevoTotal]);

            DB::commit();

            return response()->json([
                'message' => 'Orden quitada exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al quitar la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    // Exportar rendición a PDF
    public function exportarPdf(Rendicion $rendicion)
    {
        $rendicion->load([
            'prestador',
            'sucursal',
            'detalles.orden.beneficiario',
            'detalles.orden.tipoComprobante',
        ]);

        $pdf = \PDF::loadView('pdf.rendicion', [
            'rendicion' => $rendicion,
            'fecha' => now()->format('d/m/Y H:i')
        ])->setPaper('a4', 'portrait');

        return $pdf->download('rendicion-' . $rendicion->numero_rendicion_completo . '.pdf');
    }
}
