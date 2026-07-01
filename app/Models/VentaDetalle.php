<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'detalle_venta';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'id_producto_variante',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'costo_unitario',
        'costo_total',
        'utilidad_bruta',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'utilidad_bruta' => 'decimal:2',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_producto_variante');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function salidasLote()
    {
        return $this->hasMany(SalidaLote::class, 'id_detalle_venta');
    }
}
