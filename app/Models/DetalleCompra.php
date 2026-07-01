<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compra';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'id_producto_variante',
        'cantidad',
        'precio',
        'descuento',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_producto_variante', 'id');
    }
}
