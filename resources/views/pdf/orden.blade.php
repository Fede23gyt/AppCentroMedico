<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden N° {{ $orden->numero_orden }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }

        .header-left {
            flex: 0 0 35%;
        }

        .logo {
            font-size: 16px;
            font-weight: bold;
            color: #2D6660;
            margin-bottom: 2px;
        }

        .header-info {
            font-size: 7px;
            line-height: 1.2;
        }

        .header-right {
            flex: 0 0 60%;
            text-align: center;
        }

        .orden-title {
            background-color: #d3d3d3;
            padding: 4px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }

        .orden-type {
            border: 2px solid #000;
            padding: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }

        .orden-number {
            text-align: right;
            font-size: 8px;
            margin-bottom: 2px;
        }

        .info-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 80px;
            flex-shrink: 0;
        }

        .info-value {
            flex: 1;
        }

        .section {
            margin-bottom: 5px;
        }

        .section-divider {
            border-bottom: 1px solid #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table.prestaciones {
            border: 1px solid #000;
        }

        table.prestaciones th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 3px;
            font-size: 7px;
            font-weight: bold;
            text-align: left;
        }

        table.prestaciones td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #000;
        }

        .footer-note {
            font-size: 7px;
            text-align: center;
            margin-bottom: 3px;
        }

        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-top: 5px;
        }

        .total-content {
            border: 2px solid #000;
            padding: 5px 15px;
            text-align: right;
        }

        .total-label {
            font-size: 9px;
            font-weight: bold;
        }

        .total-amount {
            font-size: 14px;
            font-weight: bold;
        }

        .barcode {
            text-align: center;
            margin-top: 8px;
        }

        .estado-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7px;
            font-weight: bold;
        }

        .estado-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }

        .estado-pagada {
            background-color: #d1fae5;
            color: #065f46;
        }

        .estado-anulada {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/LogoPieveSalud.jpg') }}" alt="Logo" style="height: 40px; width: auto; margin-bottom: 3px;">
            <div class="logo">{{ $sucursal->nombre ?? 'CENTRO MÉDICO' }}</div>
            <div class="header-info">
                {{ $sucursal->telefono ?? '' }}<br>
                Email: {{ $sucursal->email ?? '' }}
            </div>
        </div>
        <div class="header-right">
            <div class="orden-title">ORDEN REIMPRESA - NO VALIDA PARA ATENCION O RENDICION</div>
            <div class="orden-type">ORIGINAL</div>
            <div class="orden-number">
                <strong>{{ $orden->numero_orden_formateado }}</strong>
            </div>
        </div>
    </div>

    <!-- DATOS PRINCIPALES -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">TRATANTE:</span>
            <span class="info-value">{{ $orden->prestador ? $orden->prestador->apellido . ', ' . $orden->prestador->nombre : 'SIN ASIGNAR' }}</span>
            <span class="info-label" style="margin-left: 20px;">Usr:</span>
            <span class="info-value">{{ $orden->usu_alta }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">PROFESIONAL:</span>
            <span class="info-value">{{ $orden->prestador ? 'PIEVE' : '-' }}</span>
            <span class="info-label" style="margin-left: 20px;">Fecha:</span>
            <span class="info-value">{{ $orden->fec_ord->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- TITULAR -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">TITULAR:</span>
            <span class="info-value">{{ strtoupper($orden->titular->apellido) }}, {{ strtoupper($orden->titular->nombre) }}</span>
            <span class="info-label" style="margin-left: 20px;">CERTIFICADO:</span>
            <span class="info-value">{{ $orden->titular->certificado }}</span>
        </div>
    </div>

    <!-- BENEFICIARIO (AFILIADO) -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">AFILIADO:</span>
            <span class="info-value">{{ strtoupper($orden->beneficiario->apellido) }}, {{ strtoupper($orden->beneficiario->nombre) }}</span>
            <span class="info-label" style="margin-left: 20px;">DOC. ID:</span>
            <span class="info-value">{{ $orden->beneficiario->dni }}</span>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- PRESTACIONES -->
    <table class="prestaciones">
        <thead>
            <tr>
                <th style="width: 60px;">CANTIDAD</th>
                <th style="width: 80px;">CODIGO</th>
                <th>DENOMINACION</th>
                <th style="width: 80px;">COBERTURA</th>
                <th style="width: 80px;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td>{{ $detalle->prestacion->codigo }}</td>
                <td>{{ strtoupper($detalle->prestacion->descripcion) }}</td>
                <td class="text-center">{{ $orden->beneficiario->plan->nombre_corto ?? 'SINSAL' }}</td>
                <td class="text-right">${{ number_format($detalle->importe_total, 2, '.', ',') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER CON TOTAL -->
    <div class="footer">
        <div class="footer-note">
            ORDEN VALIDA HASTA 30 DIAS CORRIDOS DESDE LA FECHA DE EMISION
        </div>

        <div class="total-box">
            <div class="total-content">
                <div class="total-label">TOTAL</div>
                <div class="total-amount">$ {{ number_format($orden->total, 2, '.', ',') }}</div>
            </div>
        </div>

        <!-- Código de barras simulado -->
        <div class="barcode">
            <svg width="200" height="40">
                <rect x="0" y="0" width="3" height="40" fill="black"/>
                <rect x="5" y="0" width="1" height="40" fill="black"/>
                <rect x="8" y="0" width="3" height="40" fill="black"/>
                <rect x="13" y="0" width="1" height="40" fill="black"/>
                <rect x="16" y="0" width="2" height="40" fill="black"/>
                <rect x="20" y="0" width="1" height="40" fill="black"/>
                <rect x="23" y="0" width="3" height="40" fill="black"/>
                <rect x="28" y="0" width="2" height="40" fill="black"/>
                <rect x="32" y="0" width="1" height="40" fill="black"/>
                <rect x="35" y="0" width="3" height="40" fill="black"/>
                <rect x="40" y="0" width="1" height="40" fill="black"/>
                <rect x="43" y="0" width="2" height="40" fill="black"/>
                <rect x="47" y="0" width="3" height="40" fill="black"/>
                <rect x="52" y="0" width="1" height="40" fill="black"/>
                <rect x="55" y="0" width="2" height="40" fill="black"/>
                <rect x="59" y="0" width="3" height="40" fill="black"/>
                <rect x="64" y="0" width="1" height="40" fill="black"/>
                <rect x="67" y="0" width="3" height="40" fill="black"/>
                <rect x="72" y="0" width="2" height="40" fill="black"/>
                <rect x="76" y="0" width="1" height="40" fill="black"/>
                <rect x="79" y="0" width="3" height="40" fill="black"/>
                <rect x="84" y="0" width="1" height="40" fill="black"/>
                <rect x="87" y="0" width="2" height="40" fill="black"/>
                <rect x="91" y="0" width="3" height="40" fill="black"/>
            </svg>
        </div>

        @if($orden->estado === 3)
        <div style="text-align: center; margin-top: 5px; color: #991b1b; font-weight: bold; font-size: 8px;">
            ORDEN ANULADA - Fecha: {{ $orden->fec_anu->format('d/m/Y') }} - Usuario: {{ $orden->usu_anu }}
        </div>
        @endif
    </div>

    <div style="font-size: 7px; text-align: center; margin-top: 5px; color: #666;">
        Impreso el {{ $fecha }}
    </div>
</body>
</html>
