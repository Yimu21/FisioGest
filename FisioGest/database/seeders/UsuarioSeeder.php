<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            // ID 1 — Administrador del sistema
            [
                'nombre'     => 'Administrador',
                'correo'     => 'admin@fisiogest.com',
                'contrasena' => Hash::make('admin123'),
                'rol'        => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // IDs 2-6 — Fisioterapeutas (uno por cada especialista)
            [
                'nombre'     => 'Manrivel Gorado',
                'correo'     => 'manrivel@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Barvis Raten',
                'correo'     => 'barvis@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Bardena Drides',
                'correo'     => 'bardena@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Marina Gomez',
                'correo'     => 'marina@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Retmen Nones',
                'correo'     => 'retmen@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
