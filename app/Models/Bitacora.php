<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';

    protected $fillable = [
        'user_id',
        'accion',
        'entidad',
        'entidad_id',
        'entidad_nombre',
        'cambios',
        'ip_address',
        'user_agent',
        'descripcion',
    ];

    protected $casts = [
        'cambios' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes útiles
    public function scopeReciente($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorEntidad($query, $entidad)
    {
        return $query->where('entidad', $entidad);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where('descripcion', 'like', "%{$termino}%")
            ->orWhere('entidad_nombre', 'like', "%{$termino}%")
            ->orWhereHas('user', function ($q) use ($termino) {
                $q->where('name', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            });
    }

    // Métodos estáticos para registrar eventos
    public static function registrar($accion, $entidad = null, $entidadId = null, $entidadNombre = null, $cambios = null, $descripcion = null)
    {
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'entidad_nombre' => $entidadNombre,
            'cambios' => $cambios,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'descripcion' => $descripcion,
        ]);
    }

    // Método para registrar login
    public static function registrarLogin($user)
    {
        return self::create([
            'user_id' => $user->id,
            'accion' => 'login',
            'entidad' => 'Usuario',
            'entidad_id' => $user->id,
            'entidad_nombre' => $user->name,
            'descripcion' => "Inicio de sesión del usuario {$user->name} ({$user->email})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Método para registrar logout
    public static function registrarLogout($user)
    {
        return self::create([
            'user_id' => $user->id,
            'accion' => 'logout',
            'entidad' => 'Usuario',
            'entidad_id' => $user->id,
            'entidad_nombre' => $user->name,
            'descripcion' => "Cierre de sesión del usuario {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
