<?php


namespace Database\Seeders;

use App\Models\Prestacion;
use App\Models\Rubro;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PrestacionSeeder extends Seeder
{
    public function run()
    {
        $rubros = Rubro::all();

        if ($rubros->isEmpty()) {
            $this->command->warn('No hay rubros disponibles. Ejecute primero RubroSeeder.');
            return;
        }

        // Prestaciones básicas por rubro
        $prestacionesPorRubro = [
            'Consultas Médicas' => [
                ['codigo' => '100001', 'nombre' => 'Consulta Médica General', 'precio' => 2500],
                ['codigo' => '100002', 'nombre' => 'Consulta Cardiología', 'precio' => 3500],
                ['codigo' => '100003', 'nombre' => 'Consulta Traumatología', 'precio' => 3200],
                ['codigo' => '100004', 'nombre' => 'Consulta Ginecología', 'precio' => 3000],
                ['codigo' => '100005', 'nombre' => 'Consulta Pediatría', 'precio' => 2800]
            ],
            'Estudios Diagnósticos' => [
                ['codigo' => '200001', 'nombre' => 'Radiografía de Tórax', 'precio' => 1800],
                ['codigo' => '200002', 'nombre' => 'Ecografía Abdominal', 'precio' => 4500],
                ['codigo' => '200003', 'nombre' => 'Tomografía Computada', 'precio' => 12000],
                ['codigo' => '200004', 'nombre' => 'Resonancia Magnética', 'precio' => 18000],
                ['codigo' => '200005', 'nombre' => 'Electrocardiograma', 'precio' => 1200]
            ],
            'Análisis de Laboratorio' => [
                ['codigo' => '300001', 'nombre' => 'Hemograma Completo', 'precio' => 1500],
                ['codigo' => '300002', 'nombre' => 'Perfil Lipídico', 'precio' => 1800],
                ['codigo' => '300003', 'nombre' => 'Glucemia', 'precio' => 800],
                ['codigo' => '300004', 'nombre' => 'Función Renal', 'precio' => 2200],
                ['codigo' => '300005', 'nombre' => 'Perfil Tiroideo', 'precio' => 3500]
            ]
        ];

        foreach ($prestacionesPorRubro as $nombreRubro => $prestaciones) {
            $rubro = $rubros->where('nombre', $nombreRubro)->first();

            if ($rubro) {
                foreach ($prestaciones as $prestacionData) {
                    Prestacion::create([
                        'codigo' => $prestacionData['codigo'],
                        'nombre' => $prestacionData['nombre'],
                        'descripcion' => "Prestación médica: {$prestacionData['nombre']}",
                        'estado' => 'activo',
                        'rubro_id' => $rubro->id,
                        'precio_general' => $prestacionData['precio'],
                        'valor_ips' => $prestacionData['precio'] * 0.8, // 80% del precio general
                        'observaciones' => null
                    ]);
                }
            }
        }

        // Crear prestaciones adicionales con factory para cada rubro
        foreach ($rubros as $rubro) {
            Prestacion::factory(rand(3, 8))
                ->activa()
                ->conRubro($rubro->id)
                ->create();
        }

        $this->command->info('Prestaciones creadas exitosamente.');

        // Asociar prestaciones a planes
        $this->asociarPrestacionesAPlanes();
    }

    private function asociarPrestacionesAPlanes()
    {
        $prestaciones = Prestacion::activas()->get();
        $planes = Plan::activos()->get();

        if ($prestaciones->isEmpty() || $planes->isEmpty()) {
            $this->command->warn('No hay prestaciones o planes disponibles para asociar.');
            return;
        }

        foreach ($planes as $plan) {
            // Cada plan tendrá entre 15 y 40 prestaciones asociadas
            $prestacionesAAsociar = $prestaciones->random(rand(15, min(40, $prestaciones->count())));

            foreach ($prestacionesAAsociar as $prestacion) {
                // Calcular valores según el tipo de plan
                $factorPlan = $this->getFactorPlan($plan->nombre_corto);
                $valorAfiliado = round($prestacion->precio_general * $factorPlan['afiliado'], 2);
                $valorParticular = round($prestacion->precio_general * $factorPlan['particular'], 2);

                $plan->prestaciones()->attach($prestacion->id, [
                    'valor_afiliado' => $valorAfiliado,
                    'valor_particular' => $valorParticular,
                    'cant_max_individual' => rand(0, 1) ? null : rand(2, 12), // 50% sin límite
                    'cant_max_grupo' => rand(0, 1) ? null : rand(5, 50),      // 50% sin límite
                    'estado' => 'activo',
                    'fecha_desde' => $plan->vigencia_desde,
                    'fecha_hasta' => $plan->vigencia_hasta,
                    'observaciones' => rand(0, 4) ? null : 'Observación de prueba para la asociación',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('Prestaciones asociadas a planes exitosamente.');
    }

    private function getFactorPlan($nombreCorto)
    {
        $factores = [
            'BAS' => ['afiliado' => 0.3, 'particular' => 0.7],   // Plan Básico
            'INT' => ['afiliado' => 0.2, 'particular' => 0.6],   // Plan Intermedio
            'GOLD' => ['afiliado' => 0.1, 'particular' => 0.5],  // Plan Premium
            'JOV' => ['afiliado' => 0.25, 'particular' => 0.65], // Plan Joven
            'SEN' => ['afiliado' => 0.15, 'particular' => 0.55], // Plan Senior
            'EXEC' => ['afiliado' => 0.1, 'particular' => 0.4],  // Plan Ejecutivo
        ];

        return $factores[$nombreCorto] ?? ['afiliado' => 0.3, 'particular' => 0.7];
    }
}
