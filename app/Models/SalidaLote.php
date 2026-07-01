<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaLote extends Model
{
    use HasFactory;

    protected $table = 'salida_lote';

    protected $fillable = [
        'id_detalle_venta',
        'id_lote',
        'cantidad',
        'costo_unitario_aplicado',
        'costo_total',
    ];

    protected $casts = [
        'costo_unitario_aplicado' => 'decimal:2',
        'costo_total' => 'decimal:2',
    ];

    public function detalleVenta()
    {
        return $this->belongsTo(VentaDetalle::class, 'id_detalle_venta');
    }

    public function lote()
    {
        return $this->belongsTo(LoteInventario::class, 'id_lote');
    }
}