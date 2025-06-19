<?php


namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $planes = [
            [
                'nombre' => 'Plan Básico Familiar',
                'nombre_corto' => 'BAS',
                'vigencia_desde' => Carbon::now()->subYear(),
                'vigencia_hasta' => null,
                'estado' => 'activo',
                'descripcion' => 'Plan básico para cobertura familiar con prestaciones esenciales'
            ],
            [
                'nombre' => 'Plan Intermedio Plus',
                'nombre_corto' => 'INT',
                'vigencia_desde' => Carbon::now()->subMonths(8),
                'vigencia_hasta' => null,
                'estado' => 'activo',
                'descripcion' => 'Plan intermedio con mayor cobertura y mejores prestaciones'
            ],
            [
                'nombre' => 'Plan Premium Gold',
                'nombre_corto' => 'GOLD',
                'vigencia_desde' => Carbon::now()->subMonths(6),
                'vigencia_hasta' => null,
                'estado' => 'activo',
                'descripcion' => 'Plan premium con cobertura completa y sin límites'
            ],
            [
                'nombre' => 'Plan Joven Activo',
                'nombre_corto' => 'JOV',
                'vigencia_desde' => Carbon::now()->subMonths(4),
                'vigencia_hasta' => null,
                'estado' => 'activo',
                'descripcion' => 'Plan especial para jóvenes de 18 a 35 años'
            ],
            [
                'nombre' => 'Plan Senior Care',
                'nombre_corto' => 'SEN',
                'vigencia_desde' => Carbon::now()->subMonths(10),
                'vigencia_hasta' => null,
                'estado' => 'activo',
                'descripcion' => 'Plan especializado para adultos mayores de 65 años'
            ],
            [
                'nombre' => 'Plan Ejecutivo Corporate',
                'nombre_corto' => 'EXEC',
                'vigencia_desde' => Carbon::now()->subYear(),
                'vigencia_hasta' => Carbon::now()->addYear(),
                'estado' => 'activo',
                'descripcion' => 'Plan corporativo para empresas y ejecutivos'
            ]
        ];

        foreach ($planes as $plan) {
            Plan::create($plan);
        }

        // Crear algunos planes adicionales con factory
        Plan::factory(4)->activo()->create();
    }
}
