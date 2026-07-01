<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventario';

    protected $fillable = [
        'id_producto',
        'id_producto_variante',
        'id_tecnica_inventario',
        'id_tecnica_costo',
        'tipo_movimiento',
        'motivo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'stock_anterior',
        'stock_resultante',
        'fecha',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
    ];

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_producto_variante');
    }

    public function tecnicaInventario()
    {
        return $this->belongsTo(TecnicaInventario::class, 'id_tecnica_inventario');
    }

    public function tecnicaCosto()
    {
        return $this->belongsTo(TecnicaCosto::class, 'id_tecnica_costo');
    }
}
