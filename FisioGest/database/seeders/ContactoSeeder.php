<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('contactos')->insert([
 
            // ── Fisioterapeutas (mismos del UsuarioSeeder) ────────────────
            [
                'nombre'     => 'Manrivel Gorado',
                'tipo'       => 'fisioterapeuta',
                'telefono'   => '+503 7890-1001',
                'email'      => 'manrivel@fisiogest.com',
                'estado'     => 'activo',
                'notas'      => 'Especialidad: Traumatología.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Barvis Raten',
                'tipo'       => 'fisioterapeuta',
                'telefono'   => '+503 7890-1002',
                'email'      => 'barvis@fisiogest.com',
                'estado'     => 'activo',
                'notas'      => 'Especialidad: Deportiva.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Bardena Drides',
                'tipo'       => 'fisioterapeuta',
                'telefono'   => '+503 7890-1003',
                'email'      => 'bardena@fisiogest.com',
                'estado'     => 'activo',
                'notas'      => 'Especialidad: Neurología.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Marina Gomez',
                'tipo'       => 'fisioterapeuta',
                'telefono'   => '+503 7890-1004',
                'email'      => 'marina@fisiogest.com',
                'estado'     => 'activo',
                'notas'      => 'Especialidad: Pediatría.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Retmen Nones',
                'tipo'       => 'fisioterapeuta',
                'telefono'   => '+503 7890-1005',
                'email'      => 'retmen@fisiogest.com',
                'estado'     => 'activo',
                'notas'      => 'Especialidad: Rehabilitación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
 
            // ── Pacientes ─────────────────────────────────────────────────
            [
                'nombre'     => 'Carlos Rivas',
                'tipo'       => 'paciente',
                'telefono'   => '+503 6543-2101',
                'email'      => 'c.rivas@gmail.com',
                'estado'     => 'activo',
                'notas'      => 'Tratamiento de rodilla derecha. Cita semanal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Ana Martínez',
                'tipo'       => 'paciente',
                'telefono'   => '+503 7012-3456',
                'email'      => 'ana.martinez@hotmail.com',
                'estado'     => 'activo',
                'notas'      => 'Rehabilitación post-operatoria de hombro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Roberto Hernández',
                'tipo'       => 'paciente',
                'telefono'   => '+503 6789-4321',
                'email'      => 'roberto.h@gmail.com',
                'estado'     => 'pendiente',
                'notas'      => 'Primera consulta pendiente de confirmar.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Sofía Alvarado',
                'tipo'       => 'paciente',
                'telefono'   => '+503 7654-9870',
                'email'      => 'sofia.alv@yahoo.com',
                'estado'     => 'activo',
                'notas'      => 'Dolor lumbar crónico. Sesiones de lunes y miércoles.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Diego Castillo',
                'tipo'       => 'paciente',
                'telefono'   => '+503 6321-7890',
                'email'      => null,
                'estado'     => 'inactivo',
                'notas'      => 'Completó su tratamiento en marzo 2026.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
 
            // ── Referidos ─────────────────────────────────────────────────
            [
                'nombre'     => 'Dra. Laura Pérez',
                'tipo'       => 'referido',
                'telefono'   => '+503 2222-1001',
                'email'      => 'l.perez@hospitalcentral.sv',
                'estado'     => 'activo',
                'notas'      => 'Médico del Hospital Central. Refiere pacientes post-cirugía.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Dr. José Melara',
                'tipo'       => 'referido',
                'telefono'   => '+503 2233-5678',
                'email'      => 'j.melara@clinicasv.com',
                'estado'     => 'activo',
                'notas'      => 'Traumatólogo. Colaboración frecuente.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
 
            // ── Proveedores ───────────────────────────────────────────────
            [
                'nombre'     => 'MedSupply El Salvador',
                'tipo'       => 'proveedor',
                'telefono'   => '+503 2222-8888',
                'email'      => 'ventas@medsupply.sv',
                'estado'     => 'activo',
                'notas'      => 'Proveedor de prótesis y material clínico. Entrega en 48h.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'FisioEquipos S.A.',
                'tipo'       => 'proveedor',
                'telefono'   => '+503 2100-4455',
                'email'      => 'info@fisioequipos.sv',
                'estado'     => 'activo',
                'notas'      => 'Equipos de electroterapia y ultrasonido.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
 
        ]);
    }
}
