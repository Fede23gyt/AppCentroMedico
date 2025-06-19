<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PrestacionesModuleSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RubroSeeder::class,
            PlanSeeder::class,
            PrestacionSeeder::class,
        ]);
    }
}
