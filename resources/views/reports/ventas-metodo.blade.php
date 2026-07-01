<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .periodo { text-align: center; margin-bottom: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TIENDA ELENA</h1>
        <p>{{ $titulo }}</p>
    </div>

    <div class="periodo">
        Período: {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Monto Total</th>
                    <th class="text-right">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @php $totalVentas = 0; $totalMonto = 0; @endphp
                @foreach($datos as $metodo)
                <tr>
                    <td>{{ ucfirst(data_get($metodo, 'metodo_pago') ?: data_get($metodo, 'metodoPago.nombre') ?: 'N/A') }}</td>
                    <td class="text-center">{{ data_get($metodo, 'cantidad', 0) }}</td>
                    <td class="text-right">Bs. {{ number_format(data_get($metodo, 'monto_total', 0), 2) }}</td>
                    <td class="text-right">Bs. {{ number_format(data_get($metodo, 'monto_total', 0) / max(data_get($metodo, 'cantidad', 1), 1), 2) }}</td>
                </tr>
                @php 
                    $totalVentas += $metodo->cantidad; 
                    $totalMonto += $metodo->monto_total;
                @endphp
                @endforeach
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td>TOTAL</td>
                    <td class="text-center">{{ $totalVentas }}</td>
                    <td class="text-right">Bs. {{ number_format($totalMonto, 2) }}</td>
                    <td class="text-right">Bs. {{ $totalVentas > 0 ? number_format($totalMonto / $totalVentas, 2) : '0.00' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px;">No se encontraron datos para el período seleccionado.</p>
    @endif

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
