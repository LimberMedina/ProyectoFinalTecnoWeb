<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    protected $fillable = [
        'venta_id',
        'user_id',
        'monto_credito',
        'interes',
        'cuotas_total',
        'dias_mora',
        'monto_pagado',
        'monto_pendiente',
        'fecha_otorgamiento',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'monto_credito' => 'decimal:2',
        'interes' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_otorgamiento' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function scopeEnMora($query)
    {
        return $query->where('dias_mora', '>', 0);
    }

    // Accesorios
    public function getEstaMoraAttribute()
    {
        return $this->dias_mora > 0;
    }

    public function getPorcentajePagadoAttribute()
    {
        if ($this->monto_credito == 0) return 0;
        return ($this->monto_pagado / $this->monto_credito) * 100;
    }

    public function getCuotasPagadasAttribute()
    {
        return $this->cuotas()->where('estado', 'pagada')->count();
    }

    public function getCuotasPendientesAttribute()
    {
        return $this->cuotas()->where('estado', 'pendiente')->count();
    }

    public function sincronizarEstado(): void
    {
        // Primero: asegurar que cada cuota refleje correctamente la suma de sus pagos
        foreach ($this->cuotas as $cuota) {
            // Sumar pagos considerados efectivos (completados o sin estado)
            $pagosQuery = $cuota->pagos()->where(function ($q) {
                $q->where('pago_facil_status', 'completed')
                  ->orWhereNull('pago_facil_status');
            });

            $montoPagado = (float) $pagosQuery->sum('monto');
            $mora = $cuota->mora ?? 0;

            $cuota->monto_pagado = $montoPagado;
            $cuota->monto_pendiente = max(0, ($cuota->monto + $mora) - $montoPagado);
            if ($cuota->monto_pendiente <= 0.01) {
                $cuota->estado = 'pagada';
                $cuota->monto_pendiente = 0;
            }

            // Guardar solo si hay cambios para evitar overhead
            if ($cuota->isDirty(['monto_pagado', 'monto_pendiente', 'estado'])) {
                $cuota->save();
            }
        }

        // Recalcular totales del crédito a partir de las cuotas actualizadas
        $cuotasQuery = $this->cuotas();
        $this->monto_pagado = (float) $cuotasQuery->sum('monto_pagado');
        $this->monto_pendiente = (float) $cuotasQuery->sum('monto_pendiente');

        $cuotasPendientes = (clone $cuotasQuery)->whereIn('estado', ['pendiente', 'vencida'])->count();
        $cuotasVencidas = (clone $cuotasQuery)->where('estado', 'vencida')->count();

        if ($cuotasPendientes === 0 && $this->monto_pendiente <= 0.01) {
            $this->estado = 'pagado';
            $this->monto_pendiente = 0;
            $this->dias_mora = 0;
        } elseif ($cuotasVencidas > 0) {
            $this->estado = 'vencido';
        } else {
            $this->estado = 'pendiente';
        }

        $this->save();
    }

    /**
     * Sincroniza en lote los créditos para asegurar que su estado y montos
     * reflejen el estado actual de sus cuotas.
     *
     * @param bool $onlyPendientes Si true, sincroniza solo créditos con estado 'pendiente' o 'vencido'.
     *                             Si false, sincroniza todos los créditos.
     * @return void
     */
    public static function sincronizarTodos(bool $onlyPendientes = true): void
    {
        $query = static::with('cuotas');

        if ($onlyPendientes) {
            $query->whereIn('estado', ['pendiente', 'vencido']);
        }

        $query->chunkById(50, function ($creditos) {
            foreach ($creditos as $credito) {
                $credito->sincronizarEstado();
            }
        });
    }

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }
}
