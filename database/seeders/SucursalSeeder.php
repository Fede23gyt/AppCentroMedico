<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            [
                'nombre' => 'Sucursal Centro',
                'codigo' => 'SUC001',
                'direccion' => 'Av. Principal 123',
                'telefono' => '123456789',
                'email' => 'centro@empresa.com',
            ],
            [
                'nombre' => 'Sucursal Norte',
                'codigo' => 'SUC002',
                'direccion' => 'Calle Norte 456',
                'telefono' => '987654321',
                'email' => 'norte@empresa.com',
            ],
        ];

        foreach ($sucursales as $sucursal) {
            Sucursal::create($sucursal);
        }
    }
}
