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
            UsuarioSeeder::class,        // 1° — usuarios base (admin + fisioterapeutas)
            FisioterapeutaSeeder::class,  // 2° — fisioterapeutas (dependen de usuarios)
            InventarioSeeder::class,      // 3° — inventario
            ContactoSeeder::class,        // 4° — contactos
            ConfiguracionSeeder::class,   // 5° — configuración del sistema
        ]);
    }
}