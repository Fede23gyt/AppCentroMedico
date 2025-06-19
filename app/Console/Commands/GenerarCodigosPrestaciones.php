<?php

namespace App\Console\Commands;

use App\Models\Prestacion;
use Illuminate\Console\Command;

class GenerarCodigosPrestaciones extends Command
{
    protected $signature = 'prestaciones:generar-codigos {--dry-run : Solo mostrar los cambios sin aplicarlos}';
    protected $description = 'Generar códigos faltantes para prestaciones que no los tienen';

    public function handle()
    {
        $prestacionesSinCodigo = Prestacion::whereNull('codigo')
            ->orWhere('codigo', '')
            ->get();

        if ($prestacionesSinCodigo->isEmpty()) {
            $this->info('Todas las prestaciones ya tienen códigos asignados.');
            return 0;
        }

        $this->info("Se encontraron {$prestacionesSinCodigo->count()} prestaciones sin código.");

        $ultimoCodigo = Prestacion::whereNotNull('codigo')
            ->where('codigo', '!=', '')
            ->orderBy('codigo', 'desc')
            ->value('codigo');

        $siguienteCodigo = $ultimoCodigo ? intval($ultimoCodigo) + 1 : 1;

        foreach ($prestacionesSinCodigo as $prestacion) {
            $nuevoCodigo = str_pad($siguienteCodigo, 6, '0', STR_PAD_LEFT);

            $this->line("Prestación ID {$prestacion->id}: '{$prestacion->nombre}' -> Código: {$nuevoCodigo}");

            if (!$this->option('dry-run')) {
                $prestacion->update(['codigo' => $nuevoCodigo]);
            }

            $siguienteCodigo++;
        }

        if ($this->option('dry-run')) {
            $this->warn('Ejecución en modo dry-run. No se aplicaron cambios.');
        } else {
            $this->info('Códigos generados exitosamente.');
        }

        return 0;
    }
}
