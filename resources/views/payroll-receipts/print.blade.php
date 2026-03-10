<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo - {{ $receipt->employee->nombre_apellido ?? 'Liquidación' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; max-width: 800px; margin: 0 auto; }
        .recibo { border: 1px solid #333; padding: 16px; margin-bottom: 24px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .empleador { flex: 1; }
        .empleador strong { display: block; }
        .titulo { text-align: right; }
        .titulo h2 { margin: 0; font-size: 14px; }
        .beneficiario { margin: 12px 0; }
        .beneficiario strong { display: block; }
        table.conceptos { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.conceptos th, table.conceptos td { border: 1px solid #333; padding: 4px 8px; text-align: left; }
        table.conceptos th { background: #eee; font-size: 10px; }
        table.conceptos .num { text-align: right; }
        .totales { font-weight: bold; margin: 8px 0; }
        .neto { font-size: 13px; margin: 12px 0; }
        .firmas { display: flex; justify-content: space-between; margin-top: 32px; padding-top: 8px; border-top: 1px solid #333; font-size: 10px; }
        .duplicado { margin-top: 24px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .recibo { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button type="button" onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">Imprimir</button>
        <a href="{{ route('liquidations.show', $receipt->liquidation_id) }}" style="margin-left: 8px;">Volver a la liquidación</a>
    </div>

    @php
        $employer = $employer ?? null;
        $empNombre = $employer ? strtoupper($employer->razon_social) : '—';
        $empDomicilio = $employer && $employer->domicilio ? $employer->domicilio : '—';
        $empCuit = $employer && $employer->cuit ? $employer->cuit : '—';
        $periodo = str_pad($receipt->liquidation->mes, 2, '0', STR_PAD_LEFT) . '*' . $receipt->liquidation->anio;
        $empleado = $receipt->employee;
        $fechaIngreso = $empleado->fecha_inicio ? $empleado->fecha_inicio->format('d/m/Y') : '—';
        $fechaPago = $receipt->liquidation->fecha_pago->format('d/m/Y');
        $neto = number_format((float) $receipt->neto_a_cobrar, 2, ',', '.');
    @endphp

    {{-- Original --}}
    <div class="recibo">
        <div class="header">
            <div class="empleador">
                <strong>{{ $empNombre }}</strong>
                Domicilio: {{ $empDomicilio }}<br>
                CUIT: {{ $empCuit }}
            </div>
            <div class="titulo">
                <h2>LIQUIDACION DE HABERES</h2>
                Periodo de liquidacion {{ $periodo }}<br>
                *Original
            </div>
        </div>
        <div class="beneficiario">
            <strong>Beneficiario:</strong>
            {{ strtoupper($empleado->nombre_apellido) }} &nbsp;&nbsp; Documento Nº {{ $empleado->dni }}<br>
            CUIL: {{ $empleado->cuil }} &nbsp;&nbsp; Categoria: {{ strtoupper($receipt->categoria ?? '—') }}
        </div>
        <table class="conceptos">
            <thead>
                <tr>
                    <th>CODIGO</th>
                    <th>CONCEPTO</th>
                    <th class="num">Remunerac.</th>
                    <th class="num">Retención</th>
                    <th class="num">No Remunerativo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt->lines as $line)
                <tr>
                    <td>{{ $line->codigo ?? '' }}</td>
                    <td>{{ $line->concepto ?? '' }}</td>
                    <td class="num">{{ number_format((float) $line->remuneracion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $line->retencion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $line->no_remunerativo, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="totales">
                    <td colspan="2">T. BRUTO T. RETENCION NO REMUNERATIVO</td>
                    <td class="num">{{ number_format((float) $receipt->total_bruto, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $receipt->total_retencion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $receipt->total_no_remunerativo, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <p>Son pesos:</p>
        <div class="neto">NETO A COBRAR &nbsp;&nbsp; {{ $neto }} $</div>
        <p>FECHA DE INGRESO {{ $fechaIngreso }} &nbsp;&nbsp; FECHA DE PAGO {{ $fechaPago }}</p>
        <p>Recibí de conformidad los importes y duplicados de la presente liquidación.-</p>
        <div class="firmas">
            <span>Firma del empleador</span>
            <span>Firma del empleado</span>
        </div>
    </div>

    {{-- Duplicado --}}
    <div class="recibo duplicado">
        <div class="header">
            <div class="empleador">
                <strong>{{ $empNombre }}</strong>
                Domicilio: {{ $empDomicilio }}<br>
                CUIT: {{ $empCuit }}
            </div>
            <div class="titulo">
                <h2>LIQUIDACION DE HABERES</h2>
                Periodo de liquidacion {{ $periodo }}<br>
                *Duplicado
            </div>
        </div>
        <div class="beneficiario">
            <strong>Beneficiario:</strong>
            {{ strtoupper($empleado->nombre_apellido) }} &nbsp;&nbsp; Documento Nº {{ $empleado->dni }}<br>
            CUIL: {{ $empleado->cuil }} &nbsp;&nbsp; Categoria: {{ strtoupper($receipt->categoria ?? '—') }}
        </div>
        <table class="conceptos">
            <thead>
                <tr>
                    <th>CODIGO</th>
                    <th>CONCEPTO</th>
                    <th class="num">Remunerac.</th>
                    <th class="num">Retención</th>
                    <th class="num">No Remunerativo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt->lines as $line)
                <tr>
                    <td>{{ $line->codigo ?? '' }}</td>
                    <td>{{ $line->concepto ?? '' }}</td>
                    <td class="num">{{ number_format((float) $line->remuneracion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $line->retencion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $line->no_remunerativo, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="totales">
                    <td colspan="2">T. BRUTO T. RETENCION NO REMUNERATIVO</td>
                    <td class="num">{{ number_format((float) $receipt->total_bruto, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $receipt->total_retencion, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $receipt->total_no_remunerativo, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <p>Son pesos:</p>
        <div class="neto">NETO A COBRAR &nbsp;&nbsp; {{ $neto }} $</div>
        <p>FECHA DE INGRESO {{ $fechaIngreso }} &nbsp;&nbsp; FECHA DE PAGO {{ $fechaPago }}</p>
        <p>Recibí de conformidad los importes y duplicados de la presente liquidación.-</p>
        <div class="firmas">
            <span>Firma del empleador</span>
            <span>Firma del empleado</span>
        </div>
    </div>
</body>
</html>
