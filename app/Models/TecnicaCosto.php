<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TecnicaCosto extends Model
{
    use HasFactory;

    protected $table = 'tecnica_costo';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_tecnica_costo');
    }
}
