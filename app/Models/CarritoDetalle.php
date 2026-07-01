<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoDetalle extends Model
{
    use HasFactory;

    protected $table = 'carrito_detalle';

    protected $fillable = [
        'carrito_id',
        'producto_variante_id',
        'cantidad',
        'precio_unitario',
        'descuento',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
    ];

    // Relaciones
    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'producto_variante_id');
    }

    // Métodos auxiliares
    public function getSubtotalAttribute()
    {
        $precioUnitario = (float) $this->precio_unitario;

        return $precioUnitario * (int) $this->cantidad;
    }
}
