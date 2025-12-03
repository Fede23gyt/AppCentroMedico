<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Afiliado;
use App\Models\Plan;

class AfiliadoSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = [
            'Juan', 'María', 'Carlos', 'Ana', 'Pedro', 'Laura', 'Diego', 'Sofía',
            'Fernando', 'Valentina', 'Martín', 'Camila', 'Gabriel', 'Lucía', 'Andrés',
            'Isabella', 'Santiago', 'Victoria', 'Mateo', 'Catalina'
        ];

        $apellidos = [
            'González', 'Rodríguez', 'Martínez', 'García', 'López', 'Hernández',
            'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez',
            'Díaz', 'Cruz', 'Morales', 'Reyes', 'Gutiérrez', 'Ortiz', 'Mendoza'
        ];

        $planes = Plan::all();

        if ($planes->isEmpty()) {
            $this->command->error('No hay planes disponibles. Ejecuta primero el seeder de planes.');
            return;
        }

        $certificadoBase = 100000;
        $dniBase = 20000000;

        // Crear 25 grupos familiares (promedio 4 afiliados por grupo = 100 afiliados)
        for ($i = 0; $i < 25; $i++) {
            $certificado = $certificadoBase + $i;
            $planAsignado = $planes->random();

            // Crear titular
            $apellidoFamilia = $apellidos[array_rand($apellidos)];
            $nombreTitular = $nombres[array_rand($nombres)];

            Afiliado::create([
                'certificado' => $certificado,
                'apellido' => $apellidoFamilia,
                'nombre' => $nombreTitular,
                'dni' => $dniBase + ($i * 10),
                'vinculo' => 1, // Titular
                'plan_id' => $planAsignado->id,
                'estado' => 'activo',
            ]);

            // 70% probabilidad de tener cónyuge
            if (rand(1, 10) <= 7) {
                $nombreConyuge = $nombres[array_rand($nombres)];
                Afiliado::create([
                    'certificado' => $certificado,
                    'apellido' => $apellidoFamilia,
                    'nombre' => $nombreConyuge,
                    'dni' => $dniBase + ($i * 10) + 1,
                    'vinculo' => 2, // Cónyuge
                    'plan_id' => $planAsignado->id,
                    'estado' => 'activo',
                ]);
            }

            // Número aleatorio de hijos (0 a 3)
            $cantidadHijos = rand(0, 3);
            for ($j = 0; $j < $cantidadHijos; $j++) {
                $nombreHijo = $nombres[array_rand($nombres)];
                Afiliado::create([
                    'certificado' => $certificado,
                    'apellido' => $apellidoFamilia,
                    'nombre' => $nombreHijo,
                    'dni' => $dniBase + ($i * 10) + 2 + $j,
                    'vinculo' => 3, // Hijo/a
                    'plan_id' => $planAsignado->id,
                    'estado' => 'activo',
                ]);
            }

            // 20% probabilidad de tener otro familiar
            if (rand(1, 10) <= 2) {
                $nombreOtro = $nombres[array_rand($nombres)];
                Afiliado::create([
                    'certificado' => $certificado,
                    'apellido' => $apellidoFamilia,
                    'nombre' => $nombreOtro,
                    'dni' => $dniBase + ($i * 10) + 6,
                    'vinculo' => 4, // Otro
                    'plan_id' => $planAsignado->id,
                    'estado' => 'activo',
                ]);
            }
        }

        $this->command->info('Se han creado ' . Afiliado::count() . ' afiliados en 25 grupos familiares.');
    }
}
