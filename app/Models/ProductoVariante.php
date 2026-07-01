<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoVariante extends Model
{
    use HasFactory;

    protected $table = 'producto_variante';

    protected $fillable = [
        'id_producto',
        'sku',
        'talla',
        'color',
        'precio_compra',
        'precio_venta',
        'precio_venta_mayorista',
        'stock_actual',
        'stock_minimo',
        'punto_reorden',
        'costo_promedio',
        'id_tecnica_inventario',
        'id_tecnica_costo',
        'estado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_venta_mayorista' => 'decimal:2',
        'costo_promedio' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function detallesCarrito()
    {
        return $this->hasMany(CarritoDetalle::class, 'producto_variante_id');
    }

    public function lotesInventario()
    {
        return $this->hasMany(LoteInventario::class, 'id_producto_variante');
    }
}