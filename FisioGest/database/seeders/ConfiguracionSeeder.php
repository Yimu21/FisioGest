<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Datos de la clínica
            'clinica_nombre'        => 'FisioGest Clínica',
            'clinica_direccion'     => '',
            'clinica_telefono'      => '',
            'clinica_email'         => '',
            // Configuración de citas
            'cita_duracion_minutos' => '60',
            'cita_horario_inicio'   => '08:00',
            'cita_horario_fin'      => '20:00',
            'cita_max_por_dia'      => '10',
            // Inventario
            'inventario_umbral'     => '5',
        ];

        foreach ($defaults as $clave => $valor) {
            DB::table('configuracion')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => $valor, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
