<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prestacion;
use Illuminate\Support\Facades\DB;

class ImportarPreciosCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:importar-precios-csv {file=docs/Prestaciones-Plan-Precios.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa precios desde un archivo CSV a las 4 columnas de la tabla prestaciones.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe en: $filePath");
            return;
        }

        $this->info("Iniciando importación desde $filePath...");

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file, 0, ';'); // Delimitador punto y coma

        if (!$header) {
            $this->error("El archivo está vacío o el formato es incorrecto.");
            return;
        }

        // Mapeo selectivo de columnas por nombre
        // CODIGO;PRACTICA;Precio1;Precio2;Precio3;Precio4
        $colMap = array_flip($header);

        $total = 0;
        $actualizados = 0;
        $noEncontrados = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file, 0, ';')) !== false) {
                if (empty($row[0])) continue;

                $total++;
                $codigoOriginal = trim($row[$colMap['CODIGO']]);
                // Rellenar con ceros a la izquierda hasta 6 caracteres
                $codigo = str_pad($codigoOriginal, 6, '0', STR_PAD_LEFT);

                // Buscar la prestación
                $prestacion = Prestacion::where('codigo', $codigo)->first();

                if ($prestacion) {
                    $prestacion->update([
                        'precio_1' => $this->parseAmount($row[$colMap['Precio1']]),
                        'precio_2' => $this->parseAmount($row[$colMap['Precio2']]),
                        'precio_3' => $this->parseAmount($row[$colMap['Precio3']]),
                        'precio_4' => $this->parseAmount($row[$colMap['Precio4']]),
                    ]);
                    $actualizados++;
                } else {
                    $noEncontrados++;
                    // Opcional: registrar en log
                }
            }

            DB::commit();
            $this->info("\nImportación finalizada:");
            $this->line("- Total procesados: $total");
            $this->info("- Actualizados: $actualizados");
            if ($noEncontrados > 0) {
                $this->warn("- No encontrados en DB: $noEncontrados");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error durante la importación: " . $e->getMessage());
        } finally {
            fclose($file);
        }
    }

    /**
     * Limpia el formato de moneda del CSV (ej: 8.500 -> 8500.00)
     */
    private function parseAmount($value)
    {
        // Quitar puntos de miles y reemplazar coma decimal si existiera
        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }
}
