<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FisioterapeutaSeeder extends Seeder
{
    public function run(): void
    {
        // Los usuario_id 2-6 son los fisioterapeutas creados en UsuarioSeeder
        DB::table('fisioterapeutas')->insert([
            [
                'usuario_id'   => 2,
                'nombre'       => 'Manrivel',
                'apellido'     => 'Gorado',
                'especialidad' => 'Traumatología',
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'usuario_id'   => 3,
                'nombre'       => 'Barvis',
                'apellido'     => 'Raten',
                'especialidad' => 'Deportiva',
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'usuario_id'   => 4,
                'nombre'       => 'Bardena',
                'apellido'     => 'Drides',
                'especialidad' => 'Deportiva',
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'usuario_id'   => 5,
                'nombre'       => 'Marina',
                'apellido'     => 'Gomez',
                'especialidad' => 'Traumatología',
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'usuario_id'   => 6,
                'nombre'       => 'Retmen',
                'apellido'     => 'Nones',
                'especialidad' => 'Deportiva',
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
