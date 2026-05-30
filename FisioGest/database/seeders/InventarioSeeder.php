<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB; // <-- Asegúrate de tener esta línea arriba

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tus datos de inventario que ya funcionan (No los tocamos)
        Inventario::create([
            'nombre' => 'Prótesis de Rodilla A1',
            'tipo' => 'protesis',
            'cantidad' => 12,
            'estado' => 'disponible',
            'marca' => 'BioMed',
            'modelo' => 'X-200'
        ]);

        // 2. ¡AGREGAMOS PACIENTES DE PRUEBA!
        DB::table('pacientes')->insert([
            ['nombre' => 'Yolanda Marroquín', 'historial_clinico' => 'Rehabilitación de rodilla izquierda.'],
            ['nombre' => 'Carlos Fuentes', 'historial_clinico' => 'Terapia física por esguince lumbal.']
        ]);

        // 3. ¡AGREGAMOS FISIOTERAPEUTAS DE PRUEBA!
        DB::table('fisioterapeutas')->insert([
            ['nombre' => 'Dra. Elena Rostrán', 'especialidad' => 'Traumatología'],
            ['nombre' => 'Dr. Walter Amaya', 'especialidad' => 'Fisioterapia Deportiva']
        ]);
    }
}