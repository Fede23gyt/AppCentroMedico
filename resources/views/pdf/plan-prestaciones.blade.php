<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Plan {{ $plan->nombre }} - Prestaciones</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18pt;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            color: #4b5563;
            font-weight: normal;
            margin-bottom: 3px;
        }

        .header .fecha {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 5px;
        }

        .info-plan {
            background-color: #f3f4f6;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 4px solid #2563eb;
        }

        .info-plan table {
            width: 100%;
            font-size: 9pt;
        }

        .info-plan td {
            padding: 3px 8px;
        }

        .info-plan .label {
            font-weight: bold;
            color: #374151;
            width: 25%;
        }

        .info-plan .value {
            color: #1f2937;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-activo {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-inactivo {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-suspendido {
            background-color: #fef3c7;
            color: #92400e;
        }

        .section-title {
            font-size: 12pt;
            color: #1e40af;
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        table.prestaciones {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }

        table.prestaciones thead {
            background-color: #1e40af;
            color: white;
        }

        table.prestaciones th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }

        table.prestaciones td {
            padding: 6px 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.prestaciones tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        table.prestaciones tbody tr:hover {
            background-color: #eff6ff;
        }

        .codigo {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 8pt;
            background-color: #f3f4f6;
            padding: 2px 4px;
            border-radius: 2px;
        }

        .rubro {
            font-size: 7pt;
            color: #6b7280;
            background-color: #dbeafe;
            padding: 2px 6px;
            border-radius: 8px;
            display: inline-block;
        }

        .precio {
            font-family: 'Courier New', monospace;
            text-align: right;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 1cm;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        .estadisticas {
            background-color: #eff6ff;
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
            display: table;
            width: 100%;
        }

        .estadisticas table {
            width: 100%;
        }

        .estadisticas td {
            padding: 4px 8px;
            font-size: 9pt;
        }

        .estadisticas .stat-label {
            color: #4b5563;
            font-weight: bold;
        }

        .estadisticas .stat-value {
            color: #1e40af;
            font-weight: bold;
            font-size: 11pt;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Prestaciones del Plan</h1>
        <h2>{{ $plan->nombre }}</h2>
        <div class="fecha">Generado el: {{ $fecha }}</div>
    </div>

    <!-- Información del Plan -->
    <div class="info-plan">
        <table>
            <tr>
                <td class="label">Código del Plan:</td>
                <td class="value"><strong>{{ $plan->nombre_corto }}</strong></td>
                <td class="label">Estado:</td>
                <td class="value">
                    <span class="badge badge-{{ $plan->estado }}">{{ ucfirst($plan->estado) }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Vigencia Desde:</td>
                <td class="value">{{ \Carbon\Carbon::parse($plan->vigencia_desde)->format('d/m/Y') }}</td>
                <td class="label">Vigencia Hasta:</td>
                <td class="value">
                    @if($plan->vigencia_hasta)
                        {{ \Carbon\Carbon::parse($plan->vigencia_hasta)->format('d/m/Y') }}
                    @else
                        Sin vencimiento
                    @endif
                </td>
            </tr>
            @if($plan->descripcion)
            <tr>
                <td class="label">Descripción:</td>
                <td colspan="3" class="value">{{ $plan->descripcion }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Estadísticas -->
    <div class="estadisticas">
        <table>
            <tr>
                <td class="stat-label">Total Prestaciones:</td>
                <td class="stat-value">{{ $plan->prestaciones->count() }}</td>
                <td class="stat-label">Activas:</td>
                <td class="stat-value">{{ $plan->prestaciones->where('pivot.estado', 'activo')->count() }}</td>
                <td class="stat-label">Inactivas:</td>
                <td class="stat-value">{{ $plan->prestaciones->where('pivot.estado', 'inactivo')->count() }}</td>
            </tr>
        </table>
    </div>

    <!-- Título de sección -->
    <div class="section-title">Detalle de Prestaciones</div>

    <!-- Tabla de Prestaciones -->
    <table class="prestaciones">
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 32%;">Descripción</th>
                <th style="width: 15%;">Rubro</th>
                <th style="width: 13%;">Precio Afiliado</th>
                <th style="width: 13%;">Precio Particular</th>
                <th style="width: 12%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plan->prestaciones as $prestacion)
            <tr>
                <td><span class="codigo">{{ $prestacion->codigo }}</span></td>
                <td>{{ $prestacion->nombre }}</td>
                <td><span class="rubro">{{ $prestacion->rubro->nombre ?? 'Sin rubro' }}</span></td>
                <td class="precio">${{ number_format($prestacion->pivot->valor_afiliado ?? 0, 2, ',', '.') }}</td>
                <td class="precio">${{ number_format($prestacion->pivot->valor_particular ?? 0, 2, ',', '.') }}</td>
                <td>
                    <span class="badge badge-{{ $prestacion->pivot->estado ?? 'activo' }}">
                        {{ ucfirst($prestacion->pivot->estado ?? 'activo') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                    No hay prestaciones asociadas a este plan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div>Centro Médico - Sistema de Gestión de Planes y Prestaciones</div>
        <div class="page-number"></div>
    </div>
</body>
</html>
