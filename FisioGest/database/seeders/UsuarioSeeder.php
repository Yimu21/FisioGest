<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'nombre'     => 'Administrador',
                'correo'     => 'admin@fisiogest.com',
                'contrasena' => Hash::make('admin123'),
                'rol'        => 'admin',
            ],
            [
                'nombre'     => 'Manrivel Gorado',
                'correo'     => 'manrivel@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
            ],
            [
                'nombre'     => 'Barvis Raten',
                'correo'     => 'barvis@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
            ],
            [
                'nombre'     => 'Bardena Drides',
                'correo'     => 'bardena@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
            ],
            [
                'nombre'     => 'Marina Gomez',
                'correo'     => 'marina@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
            ],
            [
                'nombre'     => 'Retmen Nones',
                'correo'     => 'retmen@fisiogest.com',
                'contrasena' => Hash::make('fisio123'),
                'rol'        => 'fisioterapeuta',
            ],
        ];

        foreach ($usuarios as $u) {
            DB::table('usuarios')->updateOrInsert(
                ['correo' => $u['correo']],
                array_merge($u, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
