<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compra';

    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'id_proveedor',
        'num_compra',
        'precio_total',
        'fecha_compra',
    ];

    protected $casts = [
        'precio_total' => 'decimal:2',
        'fecha_compra' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function propietario()
    {
        return $this->belongsTo(User::class, 'id_propietario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id');
    }
}
