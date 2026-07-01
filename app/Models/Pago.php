<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'venta_id',
        'cuota_id',
        'cuota_credito_id',
        'metodo_pago_id',
        'monto',
        'recargo_extra',
        'interes_mora_cobrado',
        'fecha',
        'pago_facil_transaction_id',
        'pago_facil_payment_number',
        'pago_facil_qr_image',
        'pago_facil_status',
        'pago_facil_raw_response'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'recargo_extra' => 'decimal:2',
        'interes_mora_cobrado' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // Relaciones
    public function cuota()
    {
        return $this->belongsTo(Cuota::class);
    }

    public function venta()
    {
        return $this->belongsTo(\App\Models\Venta::class, 'venta_id');
    }

    public function cuotaCredito()
    {
        // Backwards-compatible accessor: some controllers/models referenced
        // `cuotaCredito`. Map it to the existing `Cuota` model using the
        // `cuota_id` foreign key so older code keeps working.
        return $this->belongsTo(Cuota::class, 'cuota_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    /**
     * Booted model events: recalcular montos de la cuota y sincronizar
     * el crédito relacionado cuando un pago cambia.
     */
    protected static function booted()
    {
        static::saved(function (Pago $pago) {
            $cuota = $pago->cuota;
            if (! $cuota) return;

            // Sumar pagos efectivos: pagos completados o pagos manuales (sin status)
            $montoPagado = $cuota->pagos()
                ->where(function ($q) {
                    $q->where('pago_facil_status', 'completed')
                      ->orWhereNull('pago_facil_status');
                })
                ->sum('monto');

            $mora = $cuota->mora ?? 0;
            $nuevoMontoPendiente = max(0, ($cuota->monto + $mora) - $montoPagado);

            $cuota->monto_pagado = (float) $montoPagado;
            $cuota->monto_pendiente = (float) $nuevoMontoPendiente;
            if ($cuota->monto_pendiente <= 0.01) {
                $cuota->estado = 'pagada';
                $cuota->monto_pendiente = 0;
            }

            $cuota->save();
        });

        static::deleted(function (Pago $pago) {
            $cuota = $pago->cuota;
            if (! $cuota) return;

            $montoPagado = $cuota->pagos()
                ->where(function ($q) {
                    $q->where('pago_facil_status', 'completed')
                      ->orWhereNull('pago_facil_status');
                })
                ->sum('monto');

            $mora = $cuota->mora ?? 0;
            $cuota->monto_pagado = (float) $montoPagado;
            $cuota->monto_pendiente = max(0, ($cuota->monto + $mora) - $montoPagado);
            $cuota->estado = $cuota->monto_pendiente > 0 ? 'pendiente' : 'pagada';
            $cuota->save();
        });
    }
}
