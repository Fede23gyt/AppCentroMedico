<?php


namespace Database\Seeders;

use App\Models\Rubro;
use Illuminate\Database\Seeder;

class RubroSeeder extends Seeder
{
    public function run()
    {
        $rubros = [
            [
                'nombre' => 'Consultas Médicas',
                'descripcion' => 'Consultas con médicos generalistas y especialistas',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Estudios Diagnósticos',
                'descripcion' => 'Radiografías, ecografías, tomografías y resonancias',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Análisis de Laboratorio',
                'descripcion' => 'Análisis de sangre, orina y otros estudios de laboratorio',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Procedimientos Quirúrgicos',
                'descripcion' => 'Cirugías menores y mayores',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Servicios de Internación',
                'descripcion' => 'Internación en sala común y privada',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Emergencias',
                'descripcion' => 'Atención de urgencias y emergencias médicas',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Rehabilitación',
                'descripcion' => 'Fisioterapia, kinesiología y rehabilitación',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Servicios Odontológicos',
                'descripcion' => 'Tratamientos dentales y cirugías orales',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Salud Mental',
                'descripcion' => 'Consultas psicológicas y psiquiátricas',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Medicina Preventiva',
                'descripcion' => 'Chequeos médicos y medicina preventiva',
                'estado' => 'activo'
            ]
        ];

        foreach ($rubros as $rubro) {
            Rubro::create($rubro);
        }

        // Crear algunos rubros adicionales con factory
        Rubro::factory(5)->create();
    }
}
