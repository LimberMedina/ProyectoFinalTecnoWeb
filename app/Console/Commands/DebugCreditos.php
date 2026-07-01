<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Credito;

class DebugCreditos extends Command
{
    protected $signature = 'debug:creditos {credito_id?} {--only-pendientes}';
    protected $description = 'Lista créditos pendientes y detalle de sus cuotas para depuración';

    public function handle()
    {
        $onlyPendientes = $this->option('only-pendientes');
        $creditoId = $this->argument('credito_id');

        $query = Credito::with(['cuotas.pagos']);
        if ($creditoId) {
            $query->where('id', $creditoId);
        } elseif ($onlyPendientes) {
            $query->where('estado', 'pendiente');
        }

        $creditos = $query->get();

        if ($creditos->isEmpty()) {
            $this->info('No se encontraron créditos con las condiciones solicitadas.');
            return 0;
        }

        foreach ($creditos as $c) {
            $sumCuotasPendiente = $c->cuotas->sum('monto_pendiente');
            $allPaid = true;
            foreach ($c->cuotas as $q) {
                if ($q->estado !== 'pagada' && ((float)$q->monto_pendiente) > 0.01) {
                    $allPaid = false;
                    break;
                }
            }

            $this->line("Credito {$c->id} estado:{$c->estado} monto_credito:{$c->monto_credito} monto_pagado:{$c->monto_pagado} monto_pendiente:{$c->monto_pendiente} suma_cuotas_pendiente:{$sumCuotasPendiente} allCuotasPaid:" . ($allPaid ? 'YES' : 'NO'));

            foreach ($c->cuotas as $q) {
                $this->line("  Cuota {$q->id} num:{$q->numero_cuota} estado:{$q->estado} monto:{$q->monto} monto_pagado:{$q->monto_pagado} monto_pendiente:{$q->monto_pendiente} mora:" . ($q->mora ?? 0));
            }

            $this->line(str_repeat('-', 80));
        }

        return 0;
    }
}
