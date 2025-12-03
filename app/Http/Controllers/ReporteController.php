<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Prestacion;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Caja;
use App\Models\Plan;
use App\Models\Rendicion;
use App\Models\Rubro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReporteController extends Controller
{
    // Reporte de Órdenes
    public function ordenes(Request $request)
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id;

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));
        $sucursalFiltro = $request->input('sucursal_id');
        $estado = $request->input('estado');

        $query = Orden::with(['sucursal:id,nombre', 'tipoComprobante:id,nombre', 'beneficiario:id,apellido,nombre'])
            ->whereBetween('fec_ord', [$fechaDesde, $fechaHasta])
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->when($sucursalFiltro, fn($q) => $q->where('sucursal_id', $sucursalFiltro))
            ->when($estado, fn($q) => $q->where('estado', $estado));

        // Estadísticas generales
        $stats = [
            'total_ordenes' => $query->count(),
            'total_ventas' => $query->sum('total'),
            'ordenes_pendientes' => (clone $query)->where('estado', 1)->count(),
            'ordenes_pagadas' => (clone $query)->where('estado', 2)->count(),
            'ordenes_anuladas' => (clone $query)->where('estado', 3)->count(),
        ];

        $ordenes = $query->orderBy('fec_ord', 'desc')
            ->paginate(50)
            ->through(function ($orden) {
                return [
                    'id' => $orden->id,
                    'numero_orden_completo' => $orden->numero_orden_completo,
                    'fecha' => $orden->fec_ord->format('d/m/Y H:i'),
                    'beneficiario' => $orden->beneficiario->apellido . ', ' . $orden->beneficiario->nombre,
                    'sucursal' => $orden->sucursal->nombre,
                    'tipo_comprobante' => $orden->tipoComprobante->nombre,
                    'total' => $orden->total,
                    'estado' => $orden->estado,
                    'estado_texto' => $orden->estado_texto,
                ];
            });

        $sucursales = !$sucursalId ? Sucursal::where('is_active', true)->get(['id', 'nombre']) : [];

        return Inertia::render('Reportes/Ordenes', [
            'ordenes' => $ordenes,
            'stats' => $stats,
            'sucursales' => $sucursales,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'sucursal_id' => $sucursalFiltro,
                'estado' => $estado,
            ]
        ]);
    }

    // Reporte de Prestaciones
    public function prestaciones(Request $request)
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id;

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

        $prestaciones = DetalleOrden::select(
                'prestaciones.id',
                'prestaciones.codigo',
                'prestaciones.descripcion',
                DB::raw('COALESCE(rubros.nombre, "Sin Rubro") as rubro'),
                DB::raw('COUNT(DISTINCT det_ordenes.orden_id) as total_ordenes'),
                DB::raw('SUM(det_ordenes.cantidad) as total_cantidad'),
                DB::raw('SUM(det_ordenes.importe_total) as total_ventas'),
                DB::raw('AVG(det_ordenes.precio_unitario) as precio_promedio')
            )
            ->join('prestaciones', 'det_ordenes.prestacion_id', '=', 'prestaciones.id')
            ->leftJoin('rubros', 'prestaciones.rubro_id', '=', 'rubros.id')
            ->join('ordenes', 'det_ordenes.orden_id', '=', 'ordenes.id')
            ->whereBetween('ordenes.fec_ord', [$fechaDesde, $fechaHasta])
            ->when($sucursalId, fn($q) => $q->where('ordenes.sucursal_id', $sucursalId))
            ->groupBy('prestaciones.id', 'prestaciones.codigo', 'prestaciones.descripcion', 'rubros.id', 'rubros.nombre')
            ->orderByDesc('total_cantidad')
            ->paginate(50);

        $stats = [
            'total_prestaciones' => DetalleOrden::join('ordenes', 'det_ordenes.orden_id', '=', 'ordenes.id')
                ->whereBetween('ordenes.fec_ord', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('ordenes.sucursal_id', $sucursalId))
                ->distinct('det_ordenes.prestacion_id')
                ->count('det_ordenes.prestacion_id'),
            'total_ventas' => DetalleOrden::join('ordenes', 'det_ordenes.orden_id', '=', 'ordenes.id')
                ->whereBetween('ordenes.fec_ord', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('ordenes.sucursal_id', $sucursalId))
                ->sum('det_ordenes.importe_total'),
            'total_cantidad' => DetalleOrden::join('ordenes', 'det_ordenes.orden_id', '=', 'ordenes.id')
                ->whereBetween('ordenes.fec_ord', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('ordenes.sucursal_id', $sucursalId))
                ->sum('det_ordenes.cantidad'),
        ];

        return Inertia::render('Reportes/Prestaciones', [
            'prestaciones' => $prestaciones,
            'stats' => $stats,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ]
        ]);
    }

    // Reporte de Sucursales
    public function sucursales(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));
        $rubroId = $request->input('rubro_id');

        $query = Orden::select(
                'sucursales.id',
                'sucursales.nombre',
                'sucursales.direccion',
                DB::raw('COUNT(DISTINCT ordenes.id) as total_ordenes'),
                DB::raw('SUM(ordenes.total) as total_ventas'),
                DB::raw('AVG(ordenes.total) as promedio_venta'),
                DB::raw('SUM(CASE WHEN ordenes.estado = 1 THEN 1 ELSE 0 END) as ordenes_pendientes'),
                DB::raw('SUM(CASE WHEN ordenes.estado = 2 THEN 1 ELSE 0 END) as ordenes_pagadas')
            )
            ->join('sucursales', 'ordenes.sucursal_id', '=', 'sucursales.id')
            ->whereBetween('ordenes.fec_ord', [$fechaDesde, $fechaHasta]);

        // Aplicar filtro por rubro si se especifica
        if ($rubroId) {
            $query->join('detalles_orden', 'ordenes.id', '=', 'detalles_orden.orden_id')
                  ->join('prestaciones', 'detalles_orden.prestacion_id', '=', 'prestaciones.id')
                  ->where('prestaciones.rubro_id', $rubroId);
        }

        $sucursales = $query->groupBy('sucursales.id', 'sucursales.nombre', 'sucursales.direccion')
            ->orderByDesc('total_ventas')
            ->get();

        $stats = [
            'total_sucursales' => $sucursales->count(),
            'total_ventas' => $sucursales->sum('total_ventas'),
            'total_ordenes' => $sucursales->sum('total_ordenes'),
            'promedio_por_sucursal' => $sucursales->count() > 0 ? $sucursales->sum('total_ventas') / $sucursales->count() : 0,
        ];

        // Obtener rubros para el filtro
        $rubros = Rubro::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Reportes/Sucursales', [
            'sucursales' => $sucursales,
            'stats' => $stats,
            'rubros' => $rubros,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'rubro_id' => $rubroId,
            ]
        ]);
    }

    // Reporte de Cajas
    public function cajas(Request $request)
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id;

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

        $cajas = Caja::with(['sucursal:id,nombre', 'usuario:id,name'])
            ->whereBetween('fecha_apertura', [$fechaDesde, $fechaHasta])
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('fecha_apertura', 'desc')
            ->paginate(50)
            ->through(function ($caja) {
                return [
                    'id' => $caja->id,
                    'numero_caja' => $caja->numero_caja,
                    'sucursal' => $caja->sucursal->nombre,
                    'usuario' => $caja->usuario->name,
                    'fecha_apertura' => $caja->fecha_apertura ? Carbon::parse($caja->fecha_apertura)->format('d/m/Y H:i') : null,
                    'fecha_cierre' => $caja->fecha_cierre ? Carbon::parse($caja->fecha_cierre)->format('d/m/Y H:i') : null,
                    'saldo_inicial' => $caja->saldo_inicial,
                    'saldo_final' => $caja->saldo_final,
                    'total_ingresos' => $caja->total_ingresos,
                    'total_egresos' => $caja->total_egresos,
                    'estado' => $caja->estado,
                    'estado_texto' => $caja->estado == 1 ? 'Abierta' : 'Cerrada',
                ];
            });

        $stats = [
            'total_cajas' => Caja::whereBetween('fecha_apertura', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->count(),
            'cajas_abiertas' => Caja::where('estado', 1)
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->count(),
            'total_ingresos' => Caja::whereBetween('fecha_apertura', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->sum('total_ingresos'),
            'total_egresos' => Caja::whereBetween('fecha_apertura', [$fechaDesde, $fechaHasta])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->sum('total_egresos'),
        ];

        return Inertia::render('Reportes/Cajas', [
            'cajas' => $cajas,
            'stats' => $stats,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ]
        ]);
    }

    // Reporte de Usuarios (Actividad)
    public function usuarios(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

        $usuarios = User::select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COALESCE(sucursales.nombre, "Sin Sucursal") as sucursal'),
                DB::raw('COUNT(DISTINCT CASE WHEN ordenes.fec_ord BETWEEN "' . $fechaDesde . '" AND "' . $fechaHasta . '" THEN ordenes.id END) as total_ordenes'),
                DB::raw('COALESCE(SUM(CASE WHEN ordenes.fec_ord BETWEEN "' . $fechaDesde . '" AND "' . $fechaHasta . '" THEN ordenes.total END), 0) as total_ventas'),
                DB::raw('COUNT(DISTINCT CASE WHEN cajas.fecha_apertura BETWEEN "' . $fechaDesde . '" AND "' . $fechaHasta . '" THEN cajas.id END) as total_cajas')
            )
            ->leftJoin('sucursales', 'users.sucursal_id', '=', 'sucursales.id')
            ->leftJoin('ordenes', 'users.id', '=', 'ordenes.usu_alta')
            ->leftJoin('cajas', 'users.id', '=', 'cajas.usuario_id')
            ->where('users.is_active', true)
            ->groupBy('users.id', 'users.name', 'users.email', 'sucursales.id', 'sucursales.nombre')
            ->having('total_ordenes', '>', 0)
            ->orderByDesc('total_ordenes')
            ->get();

        $stats = [
            'total_usuarios_activos' => User::where('is_active', true)->count(),
            'usuarios_con_ordenes' => $usuarios->where('total_ordenes', '>', 0)->count(),
            'total_ordenes' => $usuarios->sum('total_ordenes'),
            'total_ventas' => $usuarios->sum('total_ventas'),
        ];

        return Inertia::render('Reportes/Usuarios', [
            'usuarios' => $usuarios,
            'stats' => $stats,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ]
        ]);
    }

    // Reporte de Planes
    public function planes(Request $request)
    {
        $user = auth()->user();
        $sucursalId = $user->sucursal_id;

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));

        $planes = Plan::select(
                'planes.id',
                'planes.nombre',
                'planes.nombre_corto',
                DB::raw('COUNT(DISTINCT CASE WHEN ordenes.fec_ord BETWEEN "' . $fechaDesde . '" AND "' . $fechaHasta . '" THEN ordenes.id END) as total_ordenes'),
                DB::raw('COALESCE(SUM(CASE WHEN ordenes.fec_ord BETWEEN "' . $fechaDesde . '" AND "' . $fechaHasta . '" THEN ordenes.total END), 0) as total_ventas'),
                DB::raw('COUNT(DISTINCT beneficiarios.id) as total_beneficiarios')
            )
            ->leftJoin('beneficiarios', 'planes.id', '=', 'beneficiarios.plan_id')
            ->leftJoin('ordenes', function($join) use ($sucursalId) {
                $join->on('beneficiarios.id', '=', 'ordenes.beneficiario_id');
                if ($sucursalId) {
                    $join->where('ordenes.sucursal_id', '=', $sucursalId);
                }
            })
            ->where('planes.estado', 'activo')
            ->groupBy('planes.id', 'planes.nombre', 'planes.nombre_corto')
            ->orderByDesc('total_ordenes')
            ->get();

        $stats = [
            'total_planes' => $planes->count(),
            'total_ordenes' => $planes->sum('total_ordenes'),
            'total_ventas' => $planes->sum('total_ventas'),
            'total_beneficiarios' => $planes->sum('total_beneficiarios'),
        ];

        return Inertia::render('Reportes/Planes', [
            'planes' => $planes,
            'stats' => $stats,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ]
        ]);
    }

    // Reporte de Ventas
    public function ventas(Request $request)
    {
        $user = auth()->user();
        $canFilterAll = $user->hasAnyRole(['Administrador', 'Supervisor']);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));
        $sucursalId = $request->input('sucursal_id');

        // Si el usuario no puede filtrar todas, usar su sucursal
        if (!$canFilterAll) {
            $sucursalId = $user->sucursal_id;
        }

        $query = Orden::with([
            'tipoComprobante',
            'beneficiario:id,certificado,apellido,nombre',
            'titular:id,certificado,apellido,nombre',
            'sucursal:id,nombre',
            'prestador:id,apellido,nombre'
        ])
        ->whereBetween('fec_ord', [$fechaDesde, $fechaHasta]);

        // Filtrar por sucursal si se especifica
        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $ordenes = $query->orderBy('fec_ord', 'desc')
            ->orderBy('numero_orden', 'desc')
            ->get();

        // Calcular estadísticas
        $totalVendido = $ordenes->where('estado', 2)->sum('total'); // Estado 2 = Pagada
        $totalAnulado = $ordenes->where('estado', 3)->sum('total'); // Estado 3 = Anulada
        $totalPendiente = $ordenes->where('estado', 1)->sum('total'); // Estado 1 = Pendiente

        $stats = [
            'total_vendido' => $totalVendido,
            'total_anulado' => $totalAnulado,
            'total_pendiente' => $totalPendiente,
            'total_ordenes' => $ordenes->count(),
            'ordenes_pagadas' => $ordenes->where('estado', 2)->count(),
            'ordenes_anuladas' => $ordenes->where('estado', 3)->count(),
            'ordenes_pendientes' => $ordenes->where('estado', 1)->count(),
        ];

        // Obtener sucursales (solo para admin/supervisor)
        $sucursales = $canFilterAll
            ? Sucursal::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre'])
            : [];

        // Obtener nombre de la sucursal actual si está filtrado
        $sucursalActual = null;
        if ($sucursalId) {
            $sucursalActual = Sucursal::find($sucursalId);
        }

        return Inertia::render('Reportes/Ventas', [
            'ordenes' => $ordenes,
            'stats' => $stats,
            'sucursales' => $sucursales,
            'sucursalActual' => $sucursalActual,
            'canFilterAll' => $canFilterAll,
            'userSucursalId' => $user->sucursal_id ?? null,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'sucursal_id' => $sucursalId,
            ]
        ]);
    }
}
