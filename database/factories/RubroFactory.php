<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RubroFactory extends Factory
{
    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Consultas Médicas',
                'Estudios Diagnósticos',
                'Laboratorio',
                'Cirugías',
                'Internación',
                'Emergencias',
                'Rehabilitación',
                'Odontología',
                'Ginecología',
                'Pediatría',
                'Cardiología',
                'Traumatología',
                'Neurología',
                'Psicología',
                'Nutrición'
            ]),
            'descripcion' => $this->faker->sentence(10),
            'estado' => $this->faker->randomElement(['activo', 'inactivo'])
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
}
