<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'imagen',
        'qr',
        'precio_venta_base',
        'precio_venta_mayorista_base',
        'marca',
        'descripcion',
        'genero',
        'categoria_id',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'precio_venta_base' => 'decimal:2',
        'precio_venta_mayorista_base' => 'decimal:2',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function promociones()
    {
        return $this->belongsToMany(Promocion::class, 'promocion_productos', 'producto_id', 'promocion_id');
    }

    public function kardex()
    {
        return $this->hasMany(KardexInventario::class);
    }

    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class, 'id_producto');
    }

    public function detallesVenta()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function detallesCarrito()
    {
        return $this->hasMany(CarritoDetalle::class);
    }
}
