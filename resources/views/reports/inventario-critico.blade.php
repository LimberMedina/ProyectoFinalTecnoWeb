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
        .alerta { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 15px 0; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-center { text-align: center; }
        .critico { background-color: #f8d7da; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TIENDA ELENA</h1>
        <p>{{ $titulo }}</p>
    </div>

    <div class="alerta">
        <strong>Productos con stock bajo o agotado</strong>
    </div>

    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Color</th>
                    <th>SKU</th>
                    <th class="text-center">Stock Actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos as $index => $producto)
                <tr class="{{ $producto->stock_actual == 0 ? 'critico' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->producto->nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->talla ?? 'N/A' }}</td>
                    <td>{{ $producto->color ?? 'N/A' }}</td>
                    <td>{{ $producto->sku ?? 'N/A' }}</td>
                    <td class="text-center">{{ $producto->stock_actual }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top: 15px;">
            <strong>Total de productos críticos:</strong> {{ count($datos) }}
        </p>
    @else
        <p style="text-align: center; padding: 20px;">No se encontraron productos con stock crítico.</p>
    @endif

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
