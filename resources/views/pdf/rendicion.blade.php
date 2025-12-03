<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendición {{ $rendicion->numero_rendicion_completo }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2D6660;
        }

        .logo {
            flex: 0 0 auto;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .header-info {
            flex: 1;
            text-align: right;
        }

        .rendicion-number {
            font-size: 14pt;
            font-weight: bold;
            color: #2D6660;
            margin-bottom: 3px;
        }

        .fecha {
            font-size: 9pt;
            color: #666;
        }

        .info-section {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #2D6660;
            font-size: 9pt;
        }

        .info-value {
            color: #333;
            font-size: 9pt;
        }

        .section-title {
            background-color: #2D6660;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 11pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead {
            background-color: #2D6660;
            color: white;
        }

        th {
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            margin-top: 15px;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            background-color: #2D6660;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .total-label {
            font-size: 11pt;
            font-weight: bold;
        }

        .total-value {
            font-size: 16pt;
            font-weight: bold;
            margin-left: 10px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        .observacion {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
        }

        .observacion-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }

        .observacion-text {
            color: #856404;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/LogoPieveSalud.jpg') }}" alt="Logo">
        </div>
        <div class="header-info">
            <div class="rendicion-number">{{ $rendicion->numero_rendicion_completo }}</div>
            <div class="fecha">Fecha de emisión: {{ $fecha }}</div>
        </div>
    </div>

    <!-- Información Principal -->
    <div class="info-section">
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Sucursal:</span>
                    <span class="info-value">{{ $rendicion->sucursal->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Prestador:</span>
                    <span class="info-value">{{ $rendicion->prestador->apellido }}, {{ $rendicion->prestador->nombre }}</span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Fecha Inicio:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($rendicion->fecha_inicio)->format('d/m/Y') }}</span>
                </div>
                @if($rendicion->fecha_cierre)
                <div class="info-item">
                    <span class="info-label">Fecha Cierre:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($rendicion->fecha_cierre)->format('d/m/Y') }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">{{ $rendicion->estado === 1 ? 'Abierta' : 'Cerrada' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Órdenes -->
    <div class="section-title">ÓRDENES RENDIDAS</div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">N° Orden</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 35%;">Beneficiario</th>
                <th style="width: 18%;">Certificado</th>
                <th style="width: 15%;" class="text-right">Total Prestador</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rendicion->detalles as $detalle)
            <tr>
                <td>{{ $detalle->orden->numero_orden_completo }}</td>
                <td>{{ \Carbon\Carbon::parse($detalle->orden->fec_ord)->format('d/m/Y') }}</td>
                <td>{{ $detalle->orden->beneficiario->apellido }}, {{ $detalle->orden->beneficiario->nombre }}</td>
                <td>{{ $detalle->orden->beneficiario->certificado }}</td>
                <td class="text-right">${{ number_format($detalle->total_prestador, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay órdenes en esta rendición</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total -->
    <div class="total-section">
        <div class="total-box">
            <span class="total-label">TOTAL A PAGAR:</span>
            <span class="total-value">${{ number_format($rendicion->total, 2, ',', '.') }}</span>
        </div>
    </div>

    <!-- Observación -->
    @if($rendicion->observacion)
    <div class="observacion">
        <div class="observacion-title">Observación:</div>
        <div class="observacion-text">{{ $rendicion->observacion }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>Rendición generada el {{ $fecha }}</div>
        <div>Usuario: {{ $rendicion->usu_alta }}</div>
        @if($rendicion->fecha_cierre)
        <div>Cerrada por: {{ $rendicion->usu_cierre }} el {{ \Carbon\Carbon::parse($rendicion->fecha_cierre)->format('d/m/Y H:i') }}</div>
        @endif
    </div>
</body>
</html>
