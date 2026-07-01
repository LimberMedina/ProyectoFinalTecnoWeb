<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TecnicaInventario extends Model
{
    use HasFactory;

    protected $table = 'tecnica_inventario';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_tecnica_inventario');
    }
}
