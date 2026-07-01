<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteInventario extends Model
{
    use HasFactory;

    protected $table = 'lote_inventario';

    protected $fillable = [
        'id_producto_variante',
        'id_detalle_compra',
        'fecha_ingreso',
        'cantidad_inicial',
        'cantidad_disponible',
        'costo_unitario',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'costo_unitario' => 'decimal:2',
    ];

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_producto_variante');
    }

    public function detalleCompra()
    {
        return $this->belongsTo(DetalleCompra::class, 'id_detalle_compra', 'id_detalle');
    }

    public function salidas()
    {
        return $this->hasMany(SalidaLote::class, 'id_lote');
    }
}