<?php

namespace App\Http\Controllers;

use App\Models\PrecioSucursal;
use App\Models\Prestacion;
use App\Models\HistorialPrecioSucursal;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PrecioSucursalController extends Controller
{
    public function index(Request $request)
    {
        $query = PrecioSucursal::with(['sucursal', 'prestacion.rubro']);

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('buscar')) {
            $query->whereHas('prestacion', fn($q) =>
                $q->where('nombre', 'like', '%'.$request->buscar.'%')
                  ->orWhere('codigo', 'like', '%'.$request->buscar.'%')
            );
        }

        return Inertia::render('PreciosSucursales/Index', [
            'excepciones' => $query->orderBy('sucursal_id')->paginate(20)->withQueryString(),
            'sucursales'  => Sucursal::orderBy('nombre')->get(['id', 'nombre']),
            'filters'     => $request->only(['sucursal_id', 'buscar']),
        ]);
    }

    public function create()
    {
        return Inertia::render('PreciosSucursales/CreateEdit', [
            'sucursales'  => Sucursal::orderBy('nombre')->get(['id', 'nombre']),
            'prestaciones' => Prestacion::activas()->orderBy('nombre')->get(['id', 'codigo', 'nombre', 'precio_1', 'precio_2', 'precio_3', 'precio_4']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id'   => 'required|exists:sucursales,id',
            'prestacion_id' => 'required|exists:prestaciones,id',
            'precio_1'      => 'nullable|numeric|min:0',
            'precio_2'      => 'nullable|numeric|min:0',
            'precio_3'      => 'nullable|numeric|min:0',
            'precio_4'      => 'nullable|numeric|min:0',
            'motivo'        => 'nullable|string|max:500',
        ]);

        $excepcion = PrecioSucursal::updateOrCreate(
            ['sucursal_id' => $validated['sucursal_id'], 'prestacion_id' => $validated['prestacion_id']],
            ['precio_1' => $validated['precio_1'] ?? 0, 'precio_2' => $validated['precio_2'] ?? 0,
             'precio_3' => $validated['precio_3'] ?? 0, 'precio_4' => $validated['precio_4'] ?? 0]
        );

        $this->registrarHistorial($excepcion, $validated['motivo'] ?? null);

        return redirect()->route('precios-sucursales.index')
            ->with('success', 'Excepción de precio guardada correctamente.');
    }

    public function edit(PrecioSucursal $preciosSucursale)
    {
        $preciosSucursale->load(['sucursal', 'prestacion']);
        $historial = HistorialPrecioSucursal::where('sucursal_id', $preciosSucursale->sucursal_id)
            ->where('prestacion_id', $preciosSucursale->prestacion_id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('PreciosSucursales/CreateEdit', [
            'excepcion'    => $preciosSucursale,
            'historial'    => $historial,
            'sucursales'   => Sucursal::orderBy('nombre')->get(['id', 'nombre']),
            'prestaciones' => Prestacion::activas()->orderBy('nombre')->get(['id', 'codigo', 'nombre', 'precio_1', 'precio_2', 'precio_3', 'precio_4']),
        ]);
    }

    public function update(Request $request, PrecioSucursal $preciosSucursale)
    {
        $validated = $request->validate([
            'precio_1' => 'nullable|numeric|min:0',
            'precio_2' => 'nullable|numeric|min:0',
            'precio_3' => 'nullable|numeric|min:0',
            'precio_4' => 'nullable|numeric|min:0',
            'motivo'   => 'nullable|string|max:500',
        ]);

        $preciosSucursale->update([
            'precio_1' => $validated['precio_1'] ?? 0,
            'precio_2' => $validated['precio_2'] ?? 0,
            'precio_3' => $validated['precio_3'] ?? 0,
            'precio_4' => $validated['precio_4'] ?? 0,
        ]);

        $this->registrarHistorial($preciosSucursale, $validated['motivo'] ?? null);

        return redirect()->route('precios-sucursales.index')
            ->with('success', 'Excepción actualizada correctamente.');
    }

    public function destroy(PrecioSucursal $preciosSucursale)
    {
        $preciosSucursale->delete();
        return redirect()->route('precios-sucursales.index')
            ->with('success', 'Excepción eliminada.');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function registrarHistorial(PrecioSucursal $excepcion, ?string $motivo): void
    {
        $hoy   = now()->toDateString();
        $userId = Auth::id();

        foreach ([1, 2, 3, 4] as $nivel) {
            $campo         = "precio_{$nivel}";
            $valorAnterior = (float) $excepcion->getOriginal($campo);
            $valorNuevo    = (float) $excepcion->{$campo};

            if (abs($valorAnterior - $valorNuevo) < 0.001) continue;

            // Cerrar vigencia anterior
            HistorialPrecioSucursal::where('sucursal_id', $excepcion->sucursal_id)
                ->where('prestacion_id', $excepcion->prestacion_id)
                ->where('nivel_precio', $nivel)
                ->whereNull('fecha_vigencia_hasta')
                ->update(['fecha_vigencia_hasta' => $hoy]);

            HistorialPrecioSucursal::create([
                'sucursal_id'          => $excepcion->sucursal_id,
                'prestacion_id'        => $excepcion->prestacion_id,
                'nivel_precio'         => $nivel,
                'valor_anterior'       => $valorAnterior,
                'valor_nuevo'          => $valorNuevo,
                'fecha_vigencia_desde' => $hoy,
                'fecha_vigencia_hasta' => null,
                'user_id'              => $userId,
                'motivo'               => $motivo,
            ]);
        }
    }
}
