<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PacientePortalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear usuario con rol paciente
        $usuarioId = DB::table('usuarios')->insertGetId([
            'nombre'     => 'Carlos Méndez',
            'correo'     => 'paciente@fisiogest.com',
            'contrasena' => Hash::make('paciente123'),
            'rol'        => 'paciente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Obtener el primer fisioterapeuta activo (para asignarlo)
        $fisio = DB::table('fisioterapeutas')->where('activo', true)->first();

        // 3. Crear registro en pacientes vinculado al usuario
        DB::table('pacientes')->insert([
            'usuario_id'        => $usuarioId,
            'nombre'            => 'Carlos',
            'apellido'          => 'Méndez',
            'fecha_nacimiento'  => '1990-04-15',
            'genero'            => 'masculino',
            'telefono'          => '8888-1234',
            'direccion'         => 'San José, Costa Rica',
            'diagnostico'       => 'Lumbalgia crónica - en tratamiento',
            'fisioterapeuta_id' => $fisio?->fisioterapeuta_id ?? null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->command->info('✓ Paciente de prueba creado:');
        $this->command->info('  Correo:     paciente@fisiogest.com');
        $this->command->info('  Contraseña: paciente123');
        $fisioNombre = $fisio ? "{$fisio->nombre} {$fisio->apellido}" : 'Ninguno';
        $this->command->info("  Fisio asignado: {$fisioNombre}");
    }
}
