<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FisioterapeutaSeeder extends Seeder
{
    public function run(): void
    {
        $fisios = [
            ['correo' => 'manrivel@fisiogest.com', 'nombre' => 'Manrivel', 'apellido' => 'Gorado',  'especialidad' => 'Traumatología'],
            ['correo' => 'barvis@fisiogest.com',   'nombre' => 'Barvis',   'apellido' => 'Raten',   'especialidad' => 'Deportiva'],
            ['correo' => 'bardena@fisiogest.com',  'nombre' => 'Bardena',  'apellido' => 'Drides',  'especialidad' => 'Deportiva'],
            ['correo' => 'marina@fisiogest.com',   'nombre' => 'Marina',   'apellido' => 'Gomez',   'especialidad' => 'Traumatología'],
            ['correo' => 'retmen@fisiogest.com',   'nombre' => 'Retmen',   'apellido' => 'Nones',   'especialidad' => 'Deportiva'],
        ];

        foreach ($fisios as $f) {
            $usuario = DB::table('usuarios')->where('correo', $f['correo'])->first();
            if (!$usuario) continue;

            // Solo insertar si este usuario aún no tiene registro en fisioterapeutas
            $existe = DB::table('fisioterapeutas')->where('usuario_id', $usuario->usuario_id)->exists();
            if (!$existe) {
                DB::table('fisioterapeutas')->insert([
                    'usuario_id'   => $usuario->usuario_id,
                    'nombre'       => $f['nombre'],
                    'apellido'     => $f['apellido'],
                    'especialidad' => $f['especialidad'],
                    'activo'       => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
