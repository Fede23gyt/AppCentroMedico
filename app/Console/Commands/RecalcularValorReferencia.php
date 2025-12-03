<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prestacion;

class RecalcularValorReferencia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestaciones:recalcular-valor-referencia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula el valor de referencia de todas las prestaciones aplicando redondeo estándar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando recálculo de valores de referencia...');

        $prestaciones = Prestacion::all();
        $total = $prestaciones->count();
        $actualizadas = 0;

        $this->info("Se encontraron {$total} prestaciones");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($prestaciones as $prestacion) {
            $valorAnterior = $prestacion->val_ref;

            // Calcular el nuevo valor con redondeo estándar
            $nuevoValor = round($prestacion->valor_ips * (1 + ($prestacion->porc_ips / 100)));

            if ($valorAnterior != $nuevoValor) {
                $prestacion->val_ref = $nuevoValor;
                $prestacion->save();
                $actualizadas++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Proceso completado");
        $this->info("Total de prestaciones: {$total}");
        $this->info("Prestaciones actualizadas: {$actualizadas}");
        $this->info("Sin cambios: " . ($total - $actualizadas));

        return Command::SUCCESS;
    }
}
