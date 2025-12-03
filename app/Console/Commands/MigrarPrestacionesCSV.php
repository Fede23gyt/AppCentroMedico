<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Rubro;
use App\Models\Prestacion;

class MigrarPrestacionesCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestaciones:migrar-csv {archivo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra prestaciones desde archivo CSV (formato: id_rubro;codigo;nombre;valor_ips)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $archivoPath = $this->argument('archivo');

        // Validar que el archivo existe
        if (!file_exists($archivoPath)) {
            $this->error("El archivo no existe: {$archivoPath}");
            return 1;
        }

        $this->info("Iniciando migración de prestaciones desde: {$archivoPath}");
        $this->newLine();

        DB::beginTransaction();

        try {
            $archivo = fopen($archivoPath, 'r');

            if ($archivo === false) {
                throw new \Exception("No se pudo abrir el archivo");
            }

            $linea = 0;
            $procesadas = 0;
            $errores = 0;
            $rubrosCache = [];

            while (($datos = fgetcsv($archivo, 1000, ';')) !== false) {
                $linea++;

                // Saltar líneas vacías
                if (empty($datos) || (count($datos) === 1 && empty($datos[0]))) {
                    continue;
                }

                // Validar que tenga al menos 4 campos
                if (count($datos) < 4) {
                    $this->warn("Línea {$linea}: Formato incorrecto (se esperan 4 campos, encontrados " . count($datos) . ")");
                    $errores++;
                    continue;
                }

                $rubroId = trim($datos[0]);
                $codigo = trim($datos[1]);
                $nombre = trim($datos[2]);
                $valorIps = trim($datos[3]);

                // Validar campos requeridos
                if (empty($rubroId) || empty($codigo) || empty($nombre)) {
                    $this->warn("Línea {$linea}: Campos vacíos (id_rubro: {$rubroId}, código: {$codigo}, nombre: {$nombre})");
                    $errores++;
                    continue;
                }

                // Validar que el ID del rubro sea numérico
                if (!is_numeric($rubroId)) {
                    $this->warn("Línea {$linea}: ID de rubro inválido '{$rubroId}' (debe ser numérico)");
                    $errores++;
                    continue;
                }

                $rubroId = (int) $rubroId;

                // Validar formato del código (6 dígitos)
                if (!preg_match('/^\d{6}$/', $codigo)) {
                    $this->warn("Línea {$linea}: Código inválido '{$codigo}' (debe ser 6 dígitos)");
                    $errores++;
                    continue;
                }

                try {
                    // Verificar que el rubro existe
                    if (!isset($rubrosCache[$rubroId])) {
                        $rubro = Rubro::find($rubroId);

                        if (!$rubro) {
                            throw new \Exception("El rubro ID {$rubroId} no existe en la base de datos");
                        }

                        $rubrosCache[$rubroId] = $rubro->nombre;
                    }

                    // Crear prestación
                    $prestacion = Prestacion::create([
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'rubro_id' => $rubroId,
                        'precio_general' => 0,
                        'valor_ips' => !empty($valorIps) && is_numeric($valorIps) ? floatval($valorIps) : 0,
                        'estado' => 'activo',
                        'descripcion' => $nombre,
                    ]);

                    // Asignar al plan ID=1
                    DB::table('prestaciones_planes')->insert([
                        'prestacion_id' => $prestacion->id,
                        'plan_id' => 1,
                        'valor_afiliado' => 0,
                        'valor_particular' => 0,
                        'estado' => 'activo',
                        'fecha_desde' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $procesadas++;

                    if ($procesadas % 50 === 0) {
                        $this->info("Procesadas: {$procesadas} prestaciones...");
                    }

                } catch (\Exception $e) {
                    $this->error("Línea {$linea}: Error al procesar - " . $e->getMessage());
                    $errores++;
                }
            }

            fclose($archivo);

            DB::commit();

            $this->newLine();
            $this->info("✓ Migración completada exitosamente");
            $this->table(
                ['Métrica', 'Cantidad'],
                [
                    ['Líneas procesadas', $linea],
                    ['Prestaciones creadas', $procesadas],
                    ['Errores', $errores],
                    ['Rubros creados/usados', count($rubrosCache)],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error fatal: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
