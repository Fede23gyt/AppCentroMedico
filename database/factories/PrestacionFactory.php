<?php

namespace Database\Factories;

use App\Models\Rubro;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestacionFactory extends Factory
{
    public function definition()
    {
        return [
            'codigo' => $this->faker->unique()->numerify('######'),
            'nombre' => $this->faker->randomElement([
                    'Consulta Médica General',
                    'Consulta Especialista',
                    'Electrocardiograma',
                    'Radiografía de Tórax',
                    'Análisis de Sangre Completo',
                    'Ecografía Abdominal',
                    'Resonancia Magnética',
                    'Tomografía Computada',
                    'Endoscopía',
                    'Colonoscopía',
                    'Mamografía',
                    'Papanicolaou',
                    'Consulta Cardiológica',
                    'Consulta Traumatológica',
                    'Fisioterapia',
                    'Sesión de Kinesiología',
                    'Consulta Psicológica',
                    'Consulta Nutricional',
                    'Limpieza Dental',
                    'Extracción Dental',
                    'Cirugía Menor',
                    'Cirugía Mayor',
                    'Internación por día',
                    'Urgencias 24hs'
                ]) . ' - ' . $this->faker->word(),
            'descripcion' => $this->faker->sentence(12),
            'estado' => $this->faker->randomElement(['activo', 'inactivo', 'suspendido']),
            'rubro_id' => Rubro::factory(),
            'precio_general' => $this->faker->randomFloat(2, 500, 50000),
            'valor_ips' => $this->faker->optional(0.7)->randomFloat(2, 300, 30000),
            'observaciones' => $this->faker->optional(0.4)->sentence(8)
        ];
    }

    public function activa()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'activo'
            ];
        });
    }

    public function conRubro($rubroId)
    {
        return $this->state(function (array $attributes) use ($rubroId) {
            return [
                'rubro_id' => $rubroId
            ];
        });
    }
}
