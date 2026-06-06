<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstefanyAparicioSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar a Estefany Aparicio en la tabla de fisioterapeutas
        $fisio = DB::table('fisioterapeutas')
            ->where('nombre', 'LIKE', 'Estefan%')
            ->where('apellido', 'LIKE', 'Aparicio%')
            ->first();

        if (! $fisio) {
            $this->command->error('No se encontró a Estefany Aparicio en la tabla fisioterapeutas.');
            return;
        }

        // Evitar duplicados
        $existe = DB::table('usuarios')
            ->where('correo', 'estefany@fisiogest.com')
            ->exists();

        if ($existe) {
            $this->command->warn('El usuario estefany@fisiogest.com ya existe. No se creó duplicado.');
            return;
        }

        DB::table('usuarios')->insert([
            'nombre'            => 'Estefany Aparicio',
            'correo'            => 'estefany@fisiogest.com',
            'contrasena'        => Hash::make('estefany123'),
            'rol'               => 'fisioterapeuta',
            'fisioterapeuta_id' => $fisio->fisioterapeuta_id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->command->info("Usuario creado: estefany@fisiogest.com (fisioterapeuta_id: {$fisio->fisioterapeuta_id})");
    }
}
