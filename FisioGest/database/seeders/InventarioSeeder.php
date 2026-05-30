<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB; // <-- Asegúrate de tener esta línea arriba

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventario')->insert([
            ['nombre' => 'Prótesis de Rodilla A1',    'tipo' => 'Prótesis',                 'marca' => 'BioMed',      'modelo' => 'X-200',       'cantidad' => 12, 'estado' => 'disponible',    'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cinta Elástica Roja',        'tipo' => 'Material Clínico',         'marca' => 'TheraBand',   'modelo' => 'Rojo',        'cantidad' => 3,  'estado' => 'baja',          'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ultrasonido Terapéutico',    'tipo' => 'Electroterapia',            'marca' => 'Chattanooga', 'modelo' => 'U1',          'cantidad' => 5,  'estado' => 'en_uso',        'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Camilla de Tratamiento',     'tipo' => 'Equipo de Rehabilitación', 'marca' => 'Hausmann',    'modelo' => '4700',        'cantidad' => 8,  'estado' => 'disponible',    'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bicicleta Estática',         'tipo' => 'Equipo de Rehabilitación', 'marca' => 'NuStep',      'modelo' => 'T5',          'cantidad' => 2,  'estado' => 'baja',          'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Electoestimulador TENS',     'tipo' => 'Electroterapia',            'marca' => 'Compex',      'modelo' => 'Sport Elite', 'cantidad' => 4,  'estado' => 'disponible',    'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pelota de Rehabilitación',   'tipo' => 'Material Clínico',         'marca' => 'Gymnic',      'modelo' => '65cm',        'cantidad' => 10, 'estado' => 'disponible',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}