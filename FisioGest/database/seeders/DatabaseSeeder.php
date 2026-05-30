<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Comentamos o eliminamos la línea de User::factory() que causa el error.
        // Solo dejamos el llamado a tu seeder real de inventario:
        $this->call([
            InventarioSeeder::class,
        ]);
    }
}