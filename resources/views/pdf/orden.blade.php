<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $orden->tipoComprobante->nombre ?? 'Comprobante' }} N° {{ $orden->numero_orden }}</title>
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
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            padding: 5mm;
        }

        /* PRIMERA FILA SUPERIOR */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }

        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }

        .header-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }

        .logo {
            height: 60px;
            width: auto;
        }

        .tipo-comprobante {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .estado-impresion {
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            display: inline-block;
        }

        .header-right-line {
            font-size: 11px;
            margin-bottom: 2px;
        }

        .header-right-line strong {
            font-weight: bold;
        }

        .numero-comprobante {
            font-size: 14px;
            font-weight: bold;
        }

        /* SEGUNDA SECCION */
        .section {
            margin-bottom: 5px;
        }

        .info-row {
            font-size: 11px;
            margin-bottom: 3px;
        }

        .info-row-titular {
            display: table;
            width: 100%;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .info-row-titular-left {
            display: table-cell;
            width: 70%;
        }

        .info-row-titular-right {
            display: table-cell;
            width: 30%;
            text-align: right;
        }

        .info-label {
            font-weight: bold;
        }

        .section-divider {
            border-bottom: 1px solid #000;
            margin: 5px 0;
        }

        /* TABLA DE PRESTACIONES */
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
            font-size: 11px;
            font-weight: bold;
            text-align: left;
        }

        table.prestaciones td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 11px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* PIE DE PAGINA CON TOTAL */
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 5mm;
            right: 5mm;
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 5px;
        }

        .total-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
        }

        .total-label {
            font-weight: bold;
            text-align: right;
            width: 80%;
        }

        .total-amount {
            font-weight: bold;
            text-align: right;
            font-size: 14px;
        }

        .estado-anulada {
            text-align: center;
            margin-top: 5px;
            color: #991b1b;
            font-weight: bold;
            font-size: 10px;
        }

        .footer-note {
            font-size: 9px;
            text-align: center;
            margin-top: 3px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- PRIMERA FILA SUPERIOR -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/LogoPieveSalud.jpg') }}" alt="Logo" class="logo">
        </div>
        <div class="header-center">
            <div class="tipo-comprobante">
                @if($orden->tipoComprobante && $orden->tipoComprobante->codigo == 'NC')
                    NOTA DE CREDITO
                @else
                    COMPROBANTE
                @endif
            </div>
            <div class="estado-impresion">
                {{ $esReimpresion ?? false ? 'REIMPRESO' : 'ORIGINAL' }}
            </div>
        </div>
        <div class="header-right">
            <div class="header-right-line">
                <strong>N° Comprobante:</strong> <span class="numero-comprobante">{{ $orden->numero_orden_completo ?? $orden->numero_orden_formateado }}</span>
            </div>
            <div class="header-right-line">
                <strong>Fecha:</strong> {{ $orden->fec_ord->format('d/m/Y H:i') }}
            </div>
            <div class="header-right-line">
                <strong>Usuario:</strong> {{ $orden->usu_alta }}
            </div>
        </div>
    </div>

    <!-- SEGUNDA SECCION -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">Profesional:</span>
            {{ $orden->prestador ? $orden->prestador->apellido . ', ' . $orden->prestador->nombre : '' }}
        </div>
    </div>

    <div class="section">
        <div class="info-row-titular">
            <div class="info-row-titular-left">
                <span class="info-label">Titular:</span>
                {{ strtoupper($orden->titular->apellido) }}, {{ strtoupper($orden->titular->nombre) }}
                &nbsp;&nbsp;&nbsp;
                <span class="info-label">DNI:</span> {{ $orden->titular->dni ?? 'S/D' }}
            </div>
            <div class="info-row-titular-right">
                <span class="info-label">Certificado:</span> {{ $orden->titular->certificado }}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="info-row">
            <span class="info-label">Beneficiario:</span>
            {{ strtoupper($orden->beneficiario->apellido) }}, {{ strtoupper($orden->beneficiario->nombre) }}
            &nbsp;&nbsp;&nbsp;
            <span class="info-label">DNI:</span> {{ $orden->beneficiario->dni }}
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- TABLA DE PRESTACIONES -->
    <table class="prestaciones">
        <thead>
            <tr>
                <th style="width: 60px;" class="text-center">Cantidad</th>
                <th style="width: 80px;">Cod Prestacion</th>
                <th>Descripcion prestacion</th>
                <th style="width: 80px;" class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td>{{ $detalle->prestacion->codigo }}</td>
                <td>{{ strtoupper($detalle->prestacion->descripcion) }}</td>
                <td class="text-right">${{ number_format($detalle->importe_total, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PIE DE PAGINA CON TOTAL -->
    <div class="footer">
        <table class="total-table">
            <tr>
                <td class="total-label">TOTAL</td>
                <td class="total-amount">$ {{ number_format($orden->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        @if($orden->estado === 3)
        <div class="estado-anulada">
            ORDEN ANULADA - Fecha: {{ $orden->fec_anu->format('d/m/Y') }} - Usuario: {{ $orden->usu_anu }}
            @if($orden->motivo_anulacion)
            <br>Motivo: {{ $orden->motivo_anulacion }}
            @endif
        </div>
        @endif

        <div class="footer-note">
            Impreso el {{ $fecha }}
        </div>
    </div>
</body>
</html>
