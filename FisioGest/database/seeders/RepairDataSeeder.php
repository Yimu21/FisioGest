<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * RepairDataSeeder — Corrige las referencias cruzadas rotas entre
 * las tablas `fisioterapeutas` y `usuarios`.
 *
 * Problemas que resuelve:
 *  1. fisioterapeutas(id=2, Estefani Aparicio) tenía usuario_id=3
 *     (Barvis Raten, del seeder original). Su portal no funcionaba.
 *     → usuario 9 (ada@fisiogest.com) es la cuenta real de Ada Chiflis.
 *       Se renombra correctamente y se vincula a fisioterapeuta 9.
 *     → Se crea una cuenta nueva para Estefani Aparicio y se vincula a fisioterapeuta 2.
 *
 *  2. fisioterapeutas(id=10, La tia Bubu) tenía usuario_id=1 (el admin),
 *     lo que hacía que el admin viera el portal de fisioterapeuta.
 *     → Se crea una cuenta nueva para La tia Bubu.
 *
 *  3. usuarios(id=5, Marina Gomez) tenía fisioterapeuta_id=4 que ya no existe.
 *     → Se pone en NULL.
 *
 * Es seguro ejecutarlo múltiples veces (idempotente).
 */
class RepairDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. Corregir usuario 9: su cuenta pertenece a Ada Chiflis (fisio 9),
        //    no a Estefani Aparicio (fisio 2).
        // ─────────────────────────────────────────────────────────────────────
        $usuario9 = DB::table('usuarios')->where('usuario_id', 9)->first();

        if ($usuario9) {
            $this->command->info("Corrigiendo usuario 9 ({$usuario9->nombre}) → Ada Chiflis / fisioterapeuta_id=9");
            DB::table('usuarios')->where('usuario_id', 9)->update([
                'nombre'            => 'Ada Chiflis',
                'fisioterapeuta_id' => 9,
                'updated_at'        => now(),
            ]);

            // Asegurar que fisioterapeuta 9 apunte a usuario 9
            DB::table('fisioterapeutas')->where('fisioterapeuta_id', 9)->update([
                'usuario_id'  => 9,
                'updated_at'  => now(),
            ]);
            $this->command->info('✓ fisioterapeutas(9).usuario_id = 9');
        } else {
            $this->command->warn('Usuario 9 no encontrado — omitiendo corrección.');
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. Crear cuenta para Estefani Aparicio (fisioterapeuta 2)
        //    si aún no existe una con ese correo.
        // ─────────────────────────────────────────────────────────────────────
        $correoEstefani = 'estefani@fisiogest.com';
        $existeEstefani = DB::table('usuarios')->where('correo', $correoEstefani)->exists();

        if (! $existeEstefani) {
            $this->command->info("Creando cuenta para Estefani Aparicio ({$correoEstefani})...");
            $nuevoId = DB::table('usuarios')->insertGetId([
                'nombre'            => 'Estefani Aparicio',
                'correo'            => $correoEstefani,
                'contrasena'        => Hash::make('fisio123'),
                'rol'               => 'fisioterapeuta',
                'fisioterapeuta_id' => 2,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $this->command->info("✓ Usuario creado con id={$nuevoId}");

            // Vincular fisioterapeuta 2 a este nuevo usuario
            DB::table('fisioterapeutas')->where('fisioterapeuta_id', 2)->update([
                'usuario_id'  => $nuevoId,
                'updated_at'  => now(),
            ]);
            $this->command->info("✓ fisioterapeutas(2).usuario_id = {$nuevoId}");
        } else {
            // Ya existe el correo → solo asegurar que fisioterapeuta 2 apunte a ese usuario
            $u = DB::table('usuarios')->where('correo', $correoEstefani)->first();
            $this->command->warn("Cuenta {$correoEstefani} ya existe (usuario_id={$u->usuario_id}) — solo verificando vínculo.");
            DB::table('fisioterapeutas')->where('fisioterapeuta_id', 2)->update([
                'usuario_id'  => $u->usuario_id,
                'updated_at'  => now(),
            ]);
            DB::table('usuarios')->where('usuario_id', $u->usuario_id)->update([
                'fisioterapeuta_id' => 2,
                'updated_at'        => now(),
            ]);
            $this->command->info("✓ fisioterapeutas(2).usuario_id = {$u->usuario_id}");
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. Crear cuenta para La tia Bubu (fisioterapeuta 10)
        //    — actualmente tiene usuario_id=1 (el admin), lo que es incorrecto.
        // ─────────────────────────────────────────────────────────────────────
        $correoTiaBubu = 'bubu@fisiogest.com';
        $existeBubu    = DB::table('usuarios')->where('correo', $correoTiaBubu)->exists();

        if (! $existeBubu) {
            $this->command->info("Creando cuenta para La tia Bubu ({$correoTiaBubu})...");
            $nuevoId = DB::table('usuarios')->insertGetId([
                'nombre'            => 'La tia Bubu',
                'correo'            => $correoTiaBubu,
                'contrasena'        => Hash::make('fisio123'),
                'rol'               => 'fisioterapeuta',
                'fisioterapeuta_id' => 10,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $this->command->info("✓ Usuario creado con id={$nuevoId}");

            DB::table('fisioterapeutas')->where('fisioterapeuta_id', 10)->update([
                'usuario_id'  => $nuevoId,
                'updated_at'  => now(),
            ]);
            $this->command->info("✓ fisioterapeutas(10).usuario_id = {$nuevoId}");
        } else {
            $u = DB::table('usuarios')->where('correo', $correoTiaBubu)->first();
            $this->command->warn("Cuenta {$correoTiaBubu} ya existe (usuario_id={$u->usuario_id}) — solo verificando vínculo.");
            DB::table('fisioterapeutas')->where('fisioterapeuta_id', 10)->update([
                'usuario_id'  => $u->usuario_id,
                'updated_at'  => now(),
            ]);
            DB::table('usuarios')->where('usuario_id', $u->usuario_id)->update([
                'fisioterapeuta_id' => 10,
                'updated_at'        => now(),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // 4. Limpiar usuarios con fisioterapeuta_id apuntando a fisioterapeutas
        //    que ya no existen (ej. usuario 5 → fisioterapeuta_id=4 eliminado).
        // ─────────────────────────────────────────────────────────────────────
        $this->command->info('Limpiando referencias de fisioterapeuta_id huérfanas en usuarios...');
        $usuariosConFisio = DB::table('usuarios')
            ->whereNotNull('fisioterapeuta_id')
            ->get(['usuario_id', 'nombre', 'fisioterapeuta_id']);

        foreach ($usuariosConFisio as $u) {
            $existe = DB::table('fisioterapeutas')
                ->where('fisioterapeuta_id', $u->fisioterapeuta_id)
                ->exists();
            if (! $existe) {
                $this->command->warn("  usuarios(id={$u->usuario_id}, {$u->nombre}): fisioterapeuta_id={$u->fisioterapeuta_id} no existe → poniendo NULL");
                DB::table('usuarios')
                    ->where('usuario_id', $u->usuario_id)
                    ->update(['fisioterapeuta_id' => null, 'updated_at' => now()]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // Resumen final
        // ─────────────────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info('  Estado final — portales de fisioterapeutas');
        $this->command->info('══════════════════════════════════════════════');

        $fisios = DB::table('fisioterapeutas')
            ->orderBy('fisioterapeuta_id')
            ->get(['fisioterapeuta_id', 'nombre', 'apellido', 'usuario_id']);

        foreach ($fisios as $f) {
            $u = $f->usuario_id
                ? DB::table('usuarios')->where('usuario_id', $f->usuario_id)->first()
                : null;
            $correo = $u?->correo ?? 'SIN CUENTA';
            $nombre = "{$f->nombre} {$f->apellido}";
            $this->command->line(sprintf(
                '  Fisio #%-3d %-22s → usuario_id=%-4s correo=%s',
                $f->fisioterapeuta_id,
                $nombre,
                $f->usuario_id ?? 'NULL',
                $correo
            ));
        }

        $this->command->newLine();
        $this->command->info('✓ Reparación completada.');
        $this->command->line('  Credenciales nuevas (contraseña: fisio123):');
        $this->command->line("  - Estefani Aparicio → {$correoEstefani}");
        $this->command->line("  - La tia Bubu       → {$correoTiaBubu}");
        $this->command->line('  Cambia las contraseñas desde el panel de administración.');
    }
}
