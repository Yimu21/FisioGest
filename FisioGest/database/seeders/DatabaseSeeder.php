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
        $this->call([
            UsuarioSeeder::class,        // 1° — usuarios base (admin + fisioterapeutas)
            FisioterapeutaSeeder::class,  // 2° — fisioterapeutas (dependen de usuarios)
            InventarioSeeder::class,      // 3° — inventario
            ContactoSeeder::class,        // 4° — contactos
            ConfiguracionSeeder::class,   // 5° — configuración del sistema
            PacientePortalSeeder::class,  // 6° — paciente de prueba con acceso al portal
        ]);
        // NOTA: RepairDataSeeder y EstefanyAparicioSeeder son parches para bases de datos
        // existentes con datos inconsistentes. No ejecutar en instalaciones nuevas.
    }
}