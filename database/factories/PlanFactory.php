<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PlanFactory extends Factory
{
    public function definition()
    {
        $vigenciaDesde = $this->faker->dateTimeBetween('-2 years', 'now');
        $vigenciaHasta = $this->faker->optional(0.3)->dateTimeBetween($vigenciaDesde, '+2 years');

        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Plan Básico',
                'Plan Intermedio',
                'Plan Premium',
                'Plan Familiar',
                'Plan Ejecutivo',
                'Plan Joven',
                'Plan Senior',
                'Plan Completo',
                'Plan Essential',
                'Plan Gold',
                'Plan Silver',
                'Plan Bronze'
            ]),
            'nombre_corto' => strtoupper($this->faker->unique()->lexify('???')),
            'vigencia_desde' => $vigenciaDesde,
            'vigencia_hasta' => $vigenciaHasta,
            'estado' => $this->faker->randomElement(['activo', 'inactivo', 'suspendido']),
            'descripcion' => $this->faker->sentence(15)
        ];
    }

    public function activo()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'activo'
            ];
        });
    }

    public function vigente()
    {
        return $this->state(function (array $attributes) {
            return [
                'vigencia_desde' => Carbon::now()->subMonths(6),
                'vigencia_hasta' => Carbon::now()->addYear(),
                'estado' => 'activo'
            ];
        });
    }
}
