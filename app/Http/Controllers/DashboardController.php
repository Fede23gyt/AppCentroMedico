<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Prestacion;
use App\Models\Sucursal;
use App\Models\Rendicion;
use App\Models\Prestador;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id;

        // Verificar si el usuario es admin o supervisor (pueden filtrar)
        $canFilter = $user->hasAnyRole(['Administrador', 'Supervisor']);

        // Obtener filtros (solo para admin/supervisor)
        $filtroSucursalId = $canFilter && $request->input('sucursal_id') ? $request->input('sucursal_id') : null;
        $filtroPlanId = $canFilter && $request->input('plan_id') ? $request->input('plan_id') : null;

        // Determinar la sucursal efectiva a usar en las consultas
        $sucursalEfectiva = $filtroSucursalId ?: $sucursalId;

        // Fechas para estadísticas
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        $inicioAnio = Carbon::now()->startOfYear();

        // === ESTADÍSTICAS GENERALES ===
        $stats = [
            // Órdenes
            'ordenes_hoy' => Orden::whereDate('fec_ord', $hoy)
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->count(),

            'ordenes_mes' => Orden::whereBetween('fec_ord', [$inicioMes, $finMes])
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->count(),

            'ordenes_pendientes' => Orden::where('estado', 1)
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->count(),

            'ordenes_pagadas' => Orden::where('estado', 2)
                ->whereBetween('fec_ord', [$inicioMes, $finMes])
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->count(),

            // Ventas
            'ventas_hoy' => Orden::whereDate('fec_ord', $hoy)
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->sum('total'),

            'ventas_mes' => Orden::whereBetween('fec_ord', [$inicioMes, $finMes])
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->sum('total'),

            'ventas_anio' => Orden::whereBetween('fec_ord', [$inicioAnio, $finMes])
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->sum('total'),

            // Rendiciones
            'rendiciones_abiertas' => Rendicion::where('estado', 1)
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->count(),

            'rendiciones_mes' => Rendicion::whereBetween('fecha_inicio', [$inicioMes, $finMes])
                ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
                ->count(),

            // Otros
            'total_prestadores' => Prestador::where('estado', 'activo')->count(),
            'total_usuarios' => User::where('is_active', true)->count(),
        ];

        // === VENTAS POR SUCURSAL (Solo para admin/supervisor sin filtro de sucursal) ===
        $ventasPorSucursal = [];
        if ($canFilter && !$filtroSucursalId && !$sucursalId) {
            $ventasPorSucursal = Orden::select('sucursales.nombre', DB::raw('COUNT(*) as total_ordenes'), DB::raw('SUM(ordenes.total) as total_ventas'))
                ->join('sucursales', 'ordenes.sucursal_id', '=', 'sucursales.id')
                ->whereBetween('ordenes.fec_ord', [$inicioMes, $finMes])
                ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
                ->groupBy('sucursales.id', 'sucursales.nombre')
                ->orderByDesc('total_ventas')
                ->get();
        }

        // === PRESTACIONES MÁS VENDIDAS ===
        $prestacionesMasVendidasQuery = DetalleOrden::select(
                'prestaciones.codigo',
                'prestaciones.descripcion',
                DB::raw('SUM(det_ordenes.cantidad) as total_cantidad'),
                DB::raw('SUM(det_ordenes.importe_total) as total_ventas')
            )
            ->join('prestaciones', 'det_ordenes.prestacion_id', '=', 'prestaciones.id')
            ->join('ordenes', 'det_ordenes.orden_id', '=', 'ordenes.id')
            ->whereBetween('ordenes.fec_ord', [$inicioMes, $finMes])
            ->when($sucursalEfectiva, fn($q) => $q->where('ordenes.sucursal_id', $sucursalEfectiva));

        if ($filtroPlanId) {
            $prestacionesMasVendidasQuery->join('beneficiarios', 'ordenes.beneficiario_id', '=', 'beneficiarios.id')
                ->where('beneficiarios.plan_id', $filtroPlanId);
        }

        $prestacionesMasVendidas = $prestacionesMasVendidasQuery
            ->groupBy('prestaciones.id', 'prestaciones.codigo', 'prestaciones.descripcion')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        // === VENTAS POR DÍA (ÚLTIMOS 30 DÍAS) ===
        $ventasPorDia = Orden::select(
                DB::raw('DATE(fec_ord) as fecha'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->where('fec_ord', '>=', Carbon::now()->subDays(30))
            ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
            ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
            ->groupBy(DB::raw('DATE(fec_ord)'))
            ->orderBy('fecha')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'cantidad' => $item->cantidad,
                    'total' => (float) $item->total,
                ];
            });

        // === ÓRDENES RECIENTES ===
        $ordenesRecientes = Orden::with(['tipoComprobante', 'beneficiario:id,apellido,nombre,plan_id', 'sucursal:id,nombre'])
            ->when($sucursalEfectiva, fn($q) => $q->where('sucursal_id', $sucursalEfectiva))
            ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
            ->orderBy('fec_ord', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($orden) {
                return [
                    'id' => $orden->id,
                    'numero_orden_completo' => $orden->numero_orden_completo,
                    'fecha' => $orden->fec_ord->format('d/m/Y H:i'),
                    'beneficiario' => $orden->beneficiario->apellido . ', ' . $orden->beneficiario->nombre,
                    'total' => $orden->total,
                    'estado' => $orden->estado,
                    'estado_texto' => $orden->estado_texto,
                    'sucursal' => $orden->sucursal->nombre ?? 'N/A',
                ];
            });

        // === TOP PRESTADORES (POR CANTIDAD DE ÓRDENES) ===
        $topPrestadores = Orden::select(
                'prestadores.apellido',
                'prestadores.nombre',
                DB::raw('COUNT(*) as total_ordenes'),
                DB::raw('SUM(ordenes.total) as total_facturado')
            )
            ->join('prestadores', 'ordenes.prestador_id', '=', 'prestadores.id')
            ->whereBetween('ordenes.fec_ord', [$inicioMes, $finMes])
            ->whereNotNull('ordenes.prestador_id')
            ->when($sucursalEfectiva, fn($q) => $q->where('ordenes.sucursal_id', $sucursalEfectiva))
            ->when($filtroPlanId, fn($q) => $q->whereHas('beneficiario', fn($q) => $q->where('plan_id', $filtroPlanId)))
            ->groupBy('prestadores.id', 'prestadores.apellido', 'prestadores.nombre')
            ->orderByDesc('total_ordenes')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->apellido . ', ' . $item->nombre,
                    'total_ordenes' => $item->total_ordenes,
                    'total_facturado' => (float) $item->total_facturado,
                ];
            });

        // Obtener listas para filtros (solo para admin/supervisor)
        $sucursalesDisponibles = [];
        $planesDisponibles = [];

        if ($canFilter) {
            $sucursalesDisponibles = Sucursal::where('is_active', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            $planesDisponibles = Plan::where('estado', 'activo')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'nombre_corto']);
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ventasPorSucursal' => $ventasPorSucursal,
            'prestacionesMasVendidas' => $prestacionesMasVendidas,
            'ventasPorDia' => $ventasPorDia,
            'ordenesRecientes' => $ordenesRecientes,
            'topPrestadores' => $topPrestadores,
            'sucursal' => $sucursalId ? (Sucursal::find($sucursalId)?->nombre ?? 'Desconocida') : 'Todas',
            'canFilter' => $canFilter,
            'sucursalesDisponibles' => $sucursalesDisponibles,
            'planesDisponibles' => $planesDisponibles,
            'filtros' => [
                'sucursal_id' => $filtroSucursalId,
                'plan_id' => $filtroPlanId,
            ],
        ]);
    }
}
