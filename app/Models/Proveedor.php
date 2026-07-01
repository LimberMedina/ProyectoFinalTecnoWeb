<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedor';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'nit',
        'telefono',
        'email',
        'direccion',
        'estado',
        'fecha_creacion',
        'fecha_modificacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_modificacion' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Proveedor $proveedor) {
            $today = now()->toDateString();

            $proveedor->fecha_creacion = $proveedor->fecha_creacion ?: $today;
            $proveedor->fecha_modificacion = $today;
        });

        static::updating(function (Proveedor $proveedor) {
            $proveedor->fecha_modificacion = now()->toDateString();
        });
    }
}