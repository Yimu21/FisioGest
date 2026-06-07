<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - FisioGest
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\ContactoController;

/**
 * Genera un aviso (no bloquea) si fecha_hora cae fuera del horario del fisioterapeuta.
 * Retorna null si todo está bien o si no hay horario configurado.
 * El admin SIEMPRE puede agendar; este aviso es solo informativo.
 */
function avisoHorarioCita(?int $fisioId, ?string $fechaHora): ?string
{
    if (!$fisioId || !$fechaHora) return null;

    $fisio = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
    if (!$fisio || !$fisio->horario) return null;

    $horario = json_decode($fisio->horario, true);
    if (!is_array($horario)) return null;

    $ts = strtotime($fechaHora);
    if (!$ts || $ts <= 0) return null; // fecha inválida: sin restricción

    $mapaDias = [1 => 'lun', 2 => 'mar', 3 => 'mie', 4 => 'jue', 5 => 'vie', 6 => 'sab', 7 => 'dom'];
    $diaClave = $mapaDias[(int) date('N', $ts)] ?? null;
    if (!$diaClave) return null;

    $diaConf = $horario[$diaClave] ?? null;

    // Solo avisar si el día existe en el horario y está explícitamente marcado como no laborable
    if ($diaConf !== null && !($diaConf['activo'] ?? false)) {
        $nombres  = ['lun'=>'lunes','mar'=>'martes','mie'=>'miércoles','jue'=>'jueves',
                     'vie'=>'viernes','sab'=>'sábados','dom'=>'domingos'];
        return "Aviso: el fisioterapeuta no atiende los {$nombres[$diaClave]}.";
    }

    if ($diaConf === null || !($diaConf['activo'] ?? false)) return null;

    $horaRaw = date('H:i', $ts);
    if (!empty($diaConf['entrada']) && $horaRaw < $diaConf['entrada']) {
        return "Aviso: la hora ({$horaRaw}) es antes del inicio de atención ({$diaConf['entrada']}).";
    }
    if (!empty($diaConf['salida']) && $horaRaw >= $diaConf['salida']) {
        return "Aviso: la hora ({$horaRaw}) supera el cierre de atención ({$diaConf['salida']}).";
    }

    return null;
}

/**
 * Verifica si el fisioterapeuta ya tiene una cita programada que se solape.
 * Asume 60 minutos de duración por cita. Excluye $excludeId (para ediciones).
 * Retorna string de error o null si no hay conflicto.
 */
function verificarDisponibilidad(?int $fisioId, ?string $fechaHora, ?int $excludeId = null): ?string
{
    if (!$fisioId || !$fechaHora) return null;

    $inicio = new \DateTime($fechaHora);
    $fin    = (clone $inicio)->modify('+60 minutes');

    $query = DB::table('citas')
        ->where('fisioterapeuta_id', $fisioId)
        ->where('estado', 'programada')
        ->where('fecha_hora', '<', $fin->format('Y-m-d H:i:s'))
        ->whereRaw("DATE_ADD(fecha_hora, INTERVAL 60 MINUTE) > ?", [$inicio->format('Y-m-d H:i:s')]);

    if ($excludeId) $query->where('cita_id', '!=', $excludeId);

    if ($query->exists()) {
        return "El fisioterapeuta ya tiene una cita programada en ese horario. Por favor elige otro horario disponible.";
    }

    return null;
}

/**
 * Valida si fecha_hora respeta el horario del fisioterapeuta.
 * Retorna un string de error si NO es válido, o null si está bien.
 * Maneja ambos formatos de horario: abreviado (lun/entrada/salida) y admin (Lunes/inicio/fin).
 */
function validarHorarioCita(?int $fisioId, ?string $fechaHora): ?string
{
    if (!$fisioId || !$fechaHora) return null;

    $fisio = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
    if (!$fisio || !$fisio->horario) return null;

    $horario = json_decode($fisio->horario, true);
    if (!is_array($horario)) return null;

    $ts = strtotime($fechaHora);
    if (!$ts || $ts <= 0) return null;

    // Normalizar: si las claves son nombres completos (formato admin), convertir
    $mapaAdmin = [
        'Lunes'=>'lun','Martes'=>'mar','Miércoles'=>'mie',
        'Jueves'=>'jue','Viernes'=>'vie','Sábado'=>'sab','Domingo'=>'dom'
    ];
    $normalizado = [];
    foreach ($horario as $k => $v) {
        $clave = $mapaAdmin[$k] ?? $k;
        $normalizado[$clave] = [
            'activo'  => $v['activo']  ?? false,
            'entrada' => $v['entrada'] ?? $v['inicio'] ?? null,
            'salida'  => $v['salida']  ?? $v['fin']    ?? null,
        ];
    }

    $mapaDias = [1=>'lun',2=>'mar',3=>'mie',4=>'jue',5=>'vie',6=>'sab',7=>'dom'];
    $nombres  = ['lun'=>'lunes','mar'=>'martes','mie'=>'miércoles','jue'=>'jueves',
                 'vie'=>'viernes','sab'=>'sábado','dom'=>'domingo'];
    $diaClave = $mapaDias[(int) date('N', $ts)] ?? null;
    if (!$diaClave) return null;

    $conf = $normalizado[$diaClave] ?? null;

    // Si el día existe en el horario y está marcado como no laborable → bloquear
    if ($conf !== null && !($conf['activo'] ?? false)) {
        return "El fisioterapeuta no atiende los {$nombres[$diaClave]}. No es posible agendar en este día.";
    }

    if ($conf === null) return null; // día no configurado, permitir

    $horaRaw = date('H:i', $ts);
    if (!empty($conf['entrada']) && $horaRaw < $conf['entrada']) {
        return "Fuera del horario: el fisioterapeuta atiende desde las {$conf['entrada']}.";
    }
    if (!empty($conf['salida']) && $horaRaw >= $conf['salida']) {
        return "Fuera del horario: el fisioterapeuta atiende hasta las {$conf['salida']}.";
    }

    return null;
}

// =========================================================================
// 0. AUTENTICACIÓN
// =========================================================================
Route::post('/login',    [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me',      [\App\Http\Controllers\Api\AuthController::class, 'me']);

    // ── Helper: obtener fisioterapeuta_id desde el usuario autenticado ─────────
    // Retorna null si el fisioterapeuta no existe o está inactivo.
    $getFisioId = function (Request $request) {
        $fisio = DB::table('fisioterapeutas')
            ->where('usuario_id', $request->user()->usuario_id)
            ->first();
        if (!$fisio || !$fisio->activo) return null;
        return $fisio->fisioterapeuta_id;
    };

    // ── Endpoints exclusivos del fisioterapeuta ──────────────────────────────
    // ── Perfil propio del fisioterapeuta ─────────────────────────────────────
    Route::get('/fisio/mi-perfil', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(null, 403);
        return response()->json(
            DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first()
        );
    });

    Route::put('/fisio/mi-perfil', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false], 403);

        DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->update([
            'nombre'       => $request->nombre,
            'apellido'     => $request->apellido,
            'especialidad' => $request->especialidad,
            'telefono'     => $request->telefono,
            'updated_at'   => now(),
        ]);

        // Sincronizar nombre en la tabla usuarios
        $usuario = $request->user();
        DB::table('usuarios')->where('usuario_id', $usuario->usuario_id)->update([
            'nombre'     => trim("{$request->nombre} {$request->apellido}"),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Perfil actualizado.']);
    });

    // ── Cambiar contraseña del fisioterapeuta ─────────────────────────────────
    Route::put('/fisio/mi-cuenta', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false], 403);

        $usuario = $request->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->contrasena_actual, $usuario->contrasena)) {
            return response()->json(['success' => false, 'message' => 'La contraseña actual no es correcta.'], 422);
        }
        if (empty($request->contrasena_nueva)) {
            return response()->json(['success' => false, 'message' => 'La nueva contraseña no puede estar vacía.'], 422);
        }

        DB::table('usuarios')->where('usuario_id', $usuario->usuario_id)->update([
            'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena_nueva),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    });

    Route::get('/fisio/mis-pacientes', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json([]);
        // Pacientes asignados directamente a este fisioterapeuta
        return response()->json(
            DB::table('pacientes')->where('fisioterapeuta_id', $fisioId)->get()
        );
    });

    Route::get('/fisio/mis-citas', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json([]);
        return response()->json(
            DB::table('citas')->where('fisioterapeuta_id', $fisioId)->get()
        );
    });

    Route::get('/fisio/mis-asignaciones', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json([]);
        $pacienteIds = DB::table('pacientes')
            ->where('fisioterapeuta_id', $fisioId)
            ->pluck('paciente_id');
        return response()->json(
            DB::table('asignaciones_equipo')
                ->whereIn('paciente_id', $pacienteIds)
                ->where('estado', 'activo')
                ->get()
        );
    });

    // ── Horario laboral del fisioterapeuta autenticado ───────────────────────
    Route::get('/fisio/mi-horario', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(null);
        $row = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
        $horario = $row ? json_decode($row->horario ?? 'null', true) : null;
        return response()->json($horario);
    });

    Route::put('/fisio/mi-horario', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false], 403);

        DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->update([
            'horario'    => json_encode($request->horario),
            'updated_at' => now(),
        ]);

        // Crear notificación para el administrador
        $fisio = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
        $nombre = $fisio ? "{$fisio->nombre} {$fisio->apellido}" : 'Un fisioterapeuta';
        DB::table('notificaciones')->insert([
            'tipo'              => 'horario_actualizado',
            'titulo'            => 'Horario actualizado',
            'mensaje'           => "{$nombre} ha actualizado su horario de trabajo.",
            'leida'             => false,
            'fisioterapeuta_id' => $fisioId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Horario guardado.']);
    });

    // ── Agendar nueva cita desde el portal del fisioterapeuta ───────────────
    Route::post('/fisio/citas', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);

        $error = validarHorarioCita($fisioId, $request->fecha_hora);
        if ($error) return response()->json(['success' => false, 'message' => $error], 422);

        $conflicto = verificarDisponibilidad($fisioId, $request->fecha_hora);
        if ($conflicto) return response()->json(['success' => false, 'message' => $conflicto], 409);

        DB::table('citas')->insert([
            'paciente_id'       => $request->paciente_id,
            'fisioterapeuta_id' => $fisioId,
            'fecha_hora'        => $request->fecha_hora,
            'motivo'            => $request->motivo ?? null,
            'estado'            => 'programada',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $fisio    = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
        $paciente = DB::table('pacientes')->where('paciente_id', $request->paciente_id)->first();
        $nombreFisio = $fisio    ? "{$fisio->nombre} {$fisio->apellido}" : 'Un fisioterapeuta';
        $nombrePac   = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
        $fechaFmt    = (new \DateTime($request->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

        // Notificar al admin
        DB::table('notificaciones')->insert([
            'tipo'              => 'cita_agendada_fisio',
            'titulo'            => 'Nueva cita agendada por fisioterapeuta',
            'mensaje'           => "{$nombreFisio} agendó una nueva cita para {$nombrePac} el {$fechaFmt}.",
            'leida'             => false,
            'fisioterapeuta_id' => $fisioId,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Notificar al paciente
        if ($paciente) {
            DB::table('notificaciones')->insert([
                'tipo'        => 'cita_programada',
                'titulo'      => 'Cita programada',
                'mensaje'     => "Tu fisioterapeuta {$nombreFisio} ha programado una cita para el {$fechaFmt}.",
                'leida'       => false,
                'paciente_id' => $paciente->paciente_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cita agendada correctamente.'], 201);
    });

    // ── Reagendar cita desde el portal del fisioterapeuta ───────────────────
    Route::put('/fisio/citas/{id}', function (Request $request, $id) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);

        $cita = DB::table('citas')
            ->where('cita_id', $id)
            ->where('fisioterapeuta_id', $fisioId)
            ->first();
        if (!$cita) return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);

        $error = validarHorarioCita($fisioId, $request->fecha_hora);
        if ($error) return response()->json(['success' => false, 'message' => $error], 422);

        $conflicto = verificarDisponibilidad($fisioId, $request->fecha_hora, (int) $id);
        if ($conflicto) return response()->json(['success' => false, 'message' => $conflicto], 409);

        DB::table('citas')->where('cita_id', $id)->update([
            'fecha_hora'  => $request->fecha_hora,
            'motivo'      => $request->motivo ?? $cita->motivo,
            'estado'      => 'programada',
            'updated_at'  => now(),
        ]);

        $fisio    = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
        $paciente = DB::table('pacientes')->where('paciente_id', $cita->paciente_id)->first();
        $nombreFisio = $fisio    ? "{$fisio->nombre} {$fisio->apellido}" : 'Un fisioterapeuta';
        $nombrePac   = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
        $fechaFmt    = (new \DateTime($request->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

        // Notificar al admin
        DB::table('notificaciones')->insert([
            'tipo'              => 'cita_agendada_fisio',
            'titulo'            => 'Cita reagendada por fisioterapeuta',
            'mensaje'           => "{$nombreFisio} reagendó la cita de {$nombrePac} para el {$fechaFmt}.",
            'leida'             => false,
            'fisioterapeuta_id' => $fisioId,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Notificar al paciente
        if ($paciente) {
            DB::table('notificaciones')->insert([
                'tipo'        => 'cita_reagendada',
                'titulo'      => 'Cita reagendada',
                'mensaje'     => "Tu fisioterapeuta {$nombreFisio} ha reagendado tu cita para el {$fechaFmt}.",
                'leida'       => false,
                'paciente_id' => $paciente->paciente_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cita reagendada correctamente.']);
    });

    // ── Actualizar estado de cita desde el portal del fisioterapeuta ────────
    Route::patch('/fisio/citas/{id}/estado', function (Request $request, $id) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);

        $estados = ['programada', 'atendida', 'cancelada'];
        if (!in_array($request->estado, $estados)) {
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 422);
        }

        // Verificar que la cita pertenece a este fisioterapeuta
        $cita = DB::table('citas')
            ->where('cita_id', $id)
            ->where('fisioterapeuta_id', $fisioId)
            ->first();

        if (!$cita) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
        }

        DB::table('citas')->where('cita_id', $id)->update([
            'estado'     => $request->estado,
            'updated_at' => now(),
        ]);

        // Notificar al administrador
        $fisio    = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
        $paciente = DB::table('pacientes')->where('paciente_id', $cita->paciente_id)->first();
        $nombreFisio = $fisio ? "{$fisio->nombre} {$fisio->apellido}" : 'Un fisioterapeuta';
        $nombrePac   = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
        $fechaFmt    = (new \DateTime($cita->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

        if ($request->estado === 'cancelada') {
            $titulo  = 'Cita cancelada — Se requiere reprogramar';
            $mensaje = "{$nombreFisio} canceló la cita de {$nombrePac} del {$fechaFmt} por un imprevisto. Es necesario reprogramarla.";
        } elseif ($request->estado === 'atendida') {
            $titulo  = 'Cita marcada como atendida';
            $mensaje = "{$nombreFisio} marcó la cita de {$nombrePac} del {$fechaFmt} como atendida.";
        } else {
            $titulo  = 'Cita reprogramada a pendiente';
            $mensaje = "{$nombreFisio} restableció la cita de {$nombrePac} del {$fechaFmt} como programada.";
        }

        // Notificar al admin
        DB::table('notificaciones')->insert([
            'tipo'              => 'cita_actualizada_fisio',
            'titulo'            => $titulo,
            'mensaje'           => $mensaje,
            'leida'             => false,
            'fisioterapeuta_id' => $fisioId,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Notificar al paciente
        if ($paciente) {
            if ($request->estado === 'cancelada') {
                $titPac = 'Tu cita fue cancelada';
                $msgPac = "Tu fisioterapeuta {$nombreFisio} canceló la cita del {$fechaFmt}. Será necesario reprogramarla.";
            } elseif ($request->estado === 'atendida') {
                $titPac = 'Cita completada';
                $msgPac = "Tu cita del {$fechaFmt} fue marcada como atendida. ¡Gracias por asistir!";
            } else {
                $titPac = 'Cita reprogramada a pendiente';
                $msgPac = "Tu cita del {$fechaFmt} fue restablecida a programada.";
            }
            DB::table('notificaciones')->insert([
                'tipo'        => 'estado_cita',
                'titulo'      => $titPac,
                'mensaje'     => $msgPac,
                'leida'       => false,
                'paciente_id' => $paciente->paciente_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    });

    // ── Notificaciones (admin) ───────────────────────────────────────────────
    // Notificaciones destinadas al admin
    $tiposAdmin = ['horario_actualizado', 'cita_actualizada_fisio', 'cita_agendada_fisio', 'cita_agendada_paciente'];

    Route::get('/admin/notificaciones', function (Request $request) use ($tiposAdmin) {
        $notifs = DB::table('notificaciones')
            ->whereIn('tipo', $tiposAdmin)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json($notifs);
    });

    Route::patch('/admin/notificaciones/{id}/leida', function (Request $request, $id) use ($tiposAdmin) {
        DB::table('notificaciones')
            ->where('notificacion_id', $id)
            ->whereIn('tipo', $tiposAdmin)
            ->update(['leida' => true, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    });

    Route::patch('/admin/notificaciones/marcar-todas', function () use ($tiposAdmin) {
        DB::table('notificaciones')
            ->whereIn('tipo', $tiposAdmin)
            ->where('leida', false)
            ->update(['leida' => true, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    });

    // ── Notificaciones del fisioterapeuta autenticado ────────────────────────
    Route::get('/fisio/notificaciones', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json([]);
        return response()->json(
            DB::table('notificaciones')
                ->where('fisioterapeuta_id', $fisioId)
                ->whereIn('tipo', ['nueva_cita', 'estado_cita'])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
        );
    });

    Route::patch('/fisio/notificaciones/{id}/leida', function (Request $request, $id) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        DB::table('notificaciones')
            ->where('notificacion_id', $id)
            ->where('fisioterapeuta_id', $fisioId)
            ->update(['leida' => true, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    });

    Route::patch('/fisio/notificaciones/marcar-todas', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false], 403);
        DB::table('notificaciones')
            ->where('fisioterapeuta_id', $fisioId)
            ->where('leida', false)
            ->update(['leida' => true, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    });

    // =========================================================================
    // CONFIGURACIÓN DEL SISTEMA (admin)
    // =========================================================================

    Route::get('/admin/configuracion', function () {
        $rows = DB::table('configuracion')->get();
        $config = [];
        foreach ($rows as $row) {
            $config[$row->clave] = $row->valor;
        }
        return response()->json($config);
    });

    Route::put('/admin/configuracion', function (Request $request) {
        foreach ($request->all() as $clave => $valor) {
            DB::table('configuracion')->updateOrInsert(
                ['clave' => $clave],
                ['valor' => $valor, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        return response()->json(['success' => true, 'message' => 'Configuración guardada.']);
    });

    // Cambiar datos y contraseña del administrador
    Route::put('/admin/cuenta', function (Request $request) {
        $usuario = $request->user();
        $update  = ['updated_at' => now()];

        if (!empty($request->nombre)) {
            $update['nombre'] = $request->nombre;
        }

        if (!empty($request->contrasena_actual)) {
            if (!\Illuminate\Support\Facades\Hash::check($request->contrasena_actual, $usuario->contrasena)) {
                return response()->json(['success' => false, 'message' => 'La contraseña actual no es correcta.'], 422);
            }
            if (empty($request->contrasena_nueva)) {
                return response()->json(['success' => false, 'message' => 'La nueva contraseña no puede estar vacía.'], 422);
            }
            $update['contrasena'] = \Illuminate\Support\Facades\Hash::make($request->contrasena_nueva);
        }

        DB::table('usuarios')->where('usuario_id', $usuario->usuario_id)->update($update);
        return response()->json(['success' => true, 'message' => 'Cuenta actualizada correctamente.']);
    });

    // ── Exportación de datos (CSV) ────────────────────────────────────────────
    Route::get('/admin/exportar/pacientes', function () {
        $pacientes = DB::table('pacientes as p')
            ->leftJoin('fisioterapeutas as f', 'p.fisioterapeuta_id', '=', 'f.fisioterapeuta_id')
            ->select('p.nombre', 'p.apellido', 'p.genero', 'p.fecha_nacimiento', 'p.telefono', 'p.correo',
                     DB::raw("CONCAT(f.nombre, ' ', f.apellido) as especialista"))
            ->get();

        $csv = "Nombre,Apellido,Género,Fecha Nacimiento,Teléfono,Correo,Especialista\n";
        foreach ($pacientes as $p) {
            $correo = $p->correo ?? '';
            $csv .= "\"{$p->nombre}\",\"{$p->apellido}\",\"{$p->genero}\",\"{$p->fecha_nacimiento}\",\"{$p->telefono}\",\"{$correo}\",\"{$p->especialista}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pacientes_' . now()->format('Y-m-d') . '.csv"',
        ]);
    });

    Route::get('/admin/exportar/citas', function () {
        $citas = DB::table('citas as c')
            ->leftJoin('pacientes as p', 'c.paciente_id', '=', 'p.paciente_id')
            ->leftJoin('fisioterapeutas as f', 'c.fisioterapeuta_id', '=', 'f.fisioterapeuta_id')
            ->select('c.fecha_hora', 'c.estado', 'c.motivo',
                     DB::raw("CONCAT(p.nombre, ' ', p.apellido) as paciente"),
                     DB::raw("CONCAT(f.nombre, ' ', f.apellido) as fisioterapeuta"))
            ->orderByDesc('c.fecha_hora')
            ->get();

        $csv = "Fecha y Hora,Paciente,Fisioterapeuta,Estado,Motivo\n";
        foreach ($citas as $c) {
            $csv .= "\"{$c->fecha_hora}\",\"{$c->paciente}\",\"{$c->fisioterapeuta}\",\"{$c->estado}\",\"{$c->motivo}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="citas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    });

    Route::get('/admin/exportar/inventario', function () {
        $items = DB::table('inventario')->get();
        $asignaciones = DB::table('asignaciones_equipo')->where('estado', 'activo')->get()->groupBy('inventario_id');

        // Leer umbral desde configuración (default 5)
        $umbralRow = DB::table('configuracion')->where('clave', 'inventario_umbral')->first();
        $umbral    = $umbralRow ? (int) $umbralRow->valor : 5;

        $etiquetas = ['disponible' => 'Disponible', 'no_disponible' => 'No Disponible', 'baja' => 'Stock Bajo', 'mantenimiento' => 'Mantenimiento'];

        $csv = "Equipo,Tipo,Cantidad Total,Asignados,Disponibles,Estado\n";
        foreach ($items as $item) {
            $asignados   = isset($asignaciones[$item->id_inventario]) ? $asignaciones[$item->id_inventario]->count() : 0;
            $disponibles = max(0, $item->cantidad - $asignados);

            // Calcular estado real igual que el frontend
            if ($item->estado === 'mantenimiento')       $estadoReal = 'mantenimiento';
            elseif ($disponibles === 0)                         $estadoReal = 'no_disponible';
            elseif ($disponibles < $umbral)              $estadoReal = 'baja';
            else                                         $estadoReal = 'disponible';

            $estadoTexto = $etiquetas[$estadoReal] ?? $estadoReal;
            $csv .= "\"{$item->nombre}\",\"{$item->tipo}\",\"{$item->cantidad}\",\"{$asignados}\",\"{$disponibles}\",\"{$estadoTexto}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="inventario_' . now()->format('Y-m-d') . '.csv"',
        ]);
    });

    Route::get('/admin/exportar/especialistas', function () {
        $fisios = DB::table('fisioterapeutas as f')
            ->leftJoin('usuarios as u', 'f.usuario_id', '=', 'u.usuario_id')
            ->select('f.nombre', 'f.apellido', 'f.especialidad', 'f.telefono', 'f.activo', 'u.correo')
            ->orderBy('f.nombre')
            ->get();

        $csv = "Nombre,Apellido,Especialidad,Teléfono,Estado,Correo\n";
        foreach ($fisios as $f) {
            $estado = $f->activo ? 'Activo' : 'Inactivo';
            $csv .= "\"{$f->nombre}\",\"{$f->apellido}\",\"{$f->especialidad}\",\"{$f->telefono}\",\"{$estado}\",\"{$f->correo}\"\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="especialistas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    });

    // ── Eventos de agenda personal ───────────────────────────────────────────
    Route::get('/fisio/eventos', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json([]);
        return response()->json(
            DB::table('eventos_agenda')->where('fisioterapeuta_id', $fisioId)->orderBy('fecha')->get()
        );
    });

    Route::post('/fisio/eventos', function (Request $request) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        if (!$fisioId) return response()->json(['success' => false], 403);
        $id = DB::table('eventos_agenda')->insertGetId([
            'fisioterapeuta_id' => $fisioId,
            'titulo'            => $request->titulo,
            'descripcion'       => $request->descripcion ?? null,
            'fecha'             => $request->fecha,
            'hora_inicio'       => $request->hora_inicio ?? null,
            'hora_fin'          => $request->hora_fin ?? null,
            'tipo'              => $request->tipo ?? 'personal',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        return response()->json(['success' => true, 'id' => $id], 201);
    });

    Route::put('/fisio/eventos/{id}', function (Request $request, $id) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        DB::table('eventos_agenda')
            ->where('evento_id', $id)
            ->where('fisioterapeuta_id', $fisioId)
            ->update([
                'titulo'      => $request->titulo,
                'descripcion' => $request->descripcion ?? null,
                'fecha'       => $request->fecha,
                'hora_inicio' => $request->hora_inicio ?? null,
                'hora_fin'    => $request->hora_fin ?? null,
                'tipo'        => $request->tipo ?? 'personal',
                'updated_at'  => now(),
            ]);
        return response()->json(['success' => true, 'message' => 'Evento actualizado.']);
    });

    Route::delete('/fisio/eventos/{id}', function (Request $request, $id) use ($getFisioId) {
        $fisioId = $getFisioId($request);
        DB::table('eventos_agenda')
            ->where('evento_id', $id)
            ->where('fisioterapeuta_id', $fisioId)
            ->delete();
        return response()->json(['success' => true, 'message' => 'Evento eliminado.']);
    });
});

// ── Eventos de agenda por fisioterapeuta y fecha (accesible sin auth) ────────
// Usado por ambos portales para bloquear slots al agendar citas
Route::get('/eventos-fisio/{fisioId}/{fecha}', function ($fisioId, $fecha) {
    $eventos = DB::table('eventos_agenda')
        ->where('fisioterapeuta_id', $fisioId)
        ->where('fecha', $fecha)
        ->get(['evento_id', 'titulo', 'hora_inicio', 'hora_fin', 'tipo']);
    return response()->json($eventos);
});

// ── Helper global: recalcula y persiste el estado real de un item ────────────
$sincEstado = function (int $inventarioId) {
    $item = DB::table('inventario')->where('id_inventario', $inventarioId)->first();
    if (!$item || $item->estado === 'mantenimiento') return;

    $umbralRow   = DB::table('configuracion')->where('clave', 'inventario_umbral')->first();
    $umbral      = $umbralRow ? (int) $umbralRow->valor : 5;
    $asignados   = DB::table('asignaciones_equipo')
                     ->where('inventario_id', $inventarioId)
                     ->where('estado', 'activo')
                     ->count();
    $disponibles = max(0, $item->cantidad - $asignados);

    if ($disponibles === 0)         $nuevoEstado = 'en_uso';
    elseif ($disponibles < $umbral) $nuevoEstado = 'baja';
    else                            $nuevoEstado = 'disponible';

    DB::table('inventario')
      ->where('id_inventario', $inventarioId)
      ->update(['estado' => $nuevoEstado, 'updated_at' => now()]);
};

// =========================================================================
// 2–5. MÓDULOS PROTEGIDOS (requieren token de sesión)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () use ($sincEstado) {

Route::get('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'index']);
Route::post('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'store']);

Route::put('/inventario/{id}', function (Request $request, $id) use ($sincEstado) {
    // Si el admin marca mantenimiento explícitamente, respetar ese valor
    // En cualquier otro caso, calcular el estado desde el stock real
    $esMant = $request->estado === 'mantenimiento';

    DB::table('inventario')->where('id_inventario', $id)->update([
        'nombre'     => $request->nombre,
        'tipo'       => $request->tipo,
        'marca'      => $request->marca,
        'modelo'     => $request->modelo,
        'estado'     => $esMant ? 'mantenimiento' : $request->estado, // valor temporal
        'cantidad'   => $request->cantidad,
        'updated_at' => now(),
    ]);

    // Recalcular y persistir estado real (si no es mantenimiento)
    if (!$esMant) $sincEstado((int) $id);

    return response()->json(['success' => true, 'message' => 'Equipo actualizado.']);
});

Route::delete('/inventario/{id}', function ($id) {
    DB::table('inventario')->where('id_inventario', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Equipo eliminado.']);
});

// =========================================================================
// 2b. MÓDULO DE ASIGNACIONES DE EQUIPO
// =========================================================================
Route::get('/asignaciones', function () {
    return response()->json(
        DB::table('asignaciones_equipo')->where('estado', 'activo')->get()
    );
});

Route::post('/asignaciones', function (Request $request) use ($sincEstado) {
    $inventarioId = (int) $request->inventario_id;

    // Verificar stock disponible antes de asignar (evita race conditions)
    $item      = DB::table('inventario')->where('id_inventario', $inventarioId)->first();
    $asignados = DB::table('asignaciones_equipo')
                   ->where('inventario_id', $inventarioId)
                   ->where('estado', 'activo')
                   ->count();
    $disponibles = $item ? max(0, $item->cantidad - $asignados) : 0;

    if ($disponibles <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Este equipo no tiene unidades disponibles.',
        ], 422);
    }

    $id = DB::table('asignaciones_equipo')->insertGetId([
        'paciente_id'      => $request->paciente_id,
        'inventario_id'    => $inventarioId,
        'fecha_asignacion' => now()->toDateString(),
        'estado'           => 'activo',
        'notas'            => $request->notas ?? null,
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);

    $sincEstado($inventarioId); // Actualizar estado en BD

    // Notificar al paciente
    if ($request->paciente_id && $item) {
        DB::table('notificaciones')->insert([
            'tipo'        => 'equipo_asignado',
            'titulo'      => 'Equipo asignado',
            'mensaje'     => "Se te ha asignado el equipo \"{$item->nombre}\" para tu tratamiento.",
            'leida'       => false,
            'paciente_id' => $request->paciente_id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    return response()->json(['success' => true, 'id' => $id, 'message' => 'Equipo asignado correctamente.'], 201);
});

Route::patch('/asignaciones/{id}/liberar', function (Request $request, $id) use ($sincEstado) {
    $asignacion = DB::table('asignaciones_equipo')->where('id_asignaciones', $id)->first();

    DB::table('asignaciones_equipo')->where('id_asignaciones', $id)->update([
        'estado'           => 'devuelto',
        'fecha_devolucion' => now()->toDateString(),
        'updated_at'       => now(),
    ]);

    if ($asignacion) {
        $sincEstado((int) $asignacion->inventario_id); // Actualizar estado en BD

        // Notificar al paciente
        $itemLiberado = DB::table('inventario')->where('id_inventario', $asignacion->inventario_id)->first();
        $nombreEquipo = $itemLiberado ? $itemLiberado->nombre : 'el equipo asignado';
        DB::table('notificaciones')->insert([
            'tipo'        => 'equipo_liberado',
            'titulo'      => 'Equipo devuelto',
            'mensaje'     => "El equipo \"{$nombreEquipo}\" ha sido registrado como devuelto.",
            'leida'       => false,
            'paciente_id' => $asignacion->paciente_id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    return response()->json(['success' => true, 'message' => 'Equipo liberado correctamente.']);
});


// =========================================================================
// 2. MÓDULO DE PACIENTES (Blindado contra errores NOT NULL de usuario_id)
// =========================================================================

// Obtener lista de pacientes (correo vive directamente en pacientes)
Route::get('/pacientes', function () {
    return response()->json(DB::table('pacientes')->get());
});

// Editar paciente
Route::put('/pacientes/{id}', function (Request $request, $id) {
    $data = [
        'nombre'            => $request->nombre,
        'apellido'          => $request->apellido,
        'fecha_nacimiento'  => $request->fecha_nacimiento,
        'genero'            => $request->genero,
        'telefono'          => $request->telefono,
        'fisioterapeuta_id' => $request->fisioterapeuta_id ?: null,
        'updated_at'        => now(),
    ];

    // Guardar correo directamente en pacientes si se envió
    if (!empty($request->correo)) {
        $existe = DB::table('pacientes')
            ->where('correo', $request->correo)
            ->where('paciente_id', '!=', $id)
            ->exists();
        if ($existe) {
            return response()->json(['success' => false, 'message' => 'El correo ya está en uso.'], 409);
        }
        $data['correo'] = $request->correo;

        // Sincronizar con usuarios si el paciente tiene cuenta propia (rol=paciente)
        $paciente = DB::table('pacientes')->where('paciente_id', $id)->first();
        if ($paciente?->usuario_id) {
            $u = DB::table('usuarios')
                ->where('usuario_id', $paciente->usuario_id)
                ->where('rol', 'paciente')
                ->first();
            if ($u) {
                DB::table('usuarios')->where('usuario_id', $u->usuario_id)
                    ->update(['correo' => $request->correo, 'updated_at' => now()]);
            }
        }
    }

    // Cambiar contraseña si se envió (solo si tiene cuenta propia)
    if (!empty($request->contrasena)) {
        $paciente = $paciente ?? DB::table('pacientes')->where('paciente_id', $id)->first();
        if ($paciente?->usuario_id) {
            $u = DB::table('usuarios')
                ->where('usuario_id', $paciente->usuario_id)
                ->where('rol', 'paciente')
                ->first();
            if ($u) {
                DB::table('usuarios')->where('usuario_id', $u->usuario_id)
                    ->update(['contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena), 'updated_at' => now()]);
            }
        }
    }

    DB::table('pacientes')->where('paciente_id', $id)->update($data);

    return response()->json(['success' => true, 'message' => 'Paciente actualizado.']);
});

// Crear credenciales de acceso al portal para un paciente (primera vez)
Route::post('/pacientes/{id}/credenciales', function (Request $request, $id) {
    $paciente = DB::table('pacientes')->where('paciente_id', $id)->first();
    if (!$paciente) return response()->json(['success' => false, 'message' => 'Paciente no encontrado.'], 404);

    // Si ya tiene usuario vinculado, solo activar portal_activo
    if ($paciente->usuario_id) {
        DB::table('pacientes')->where('paciente_id', $id)->update([
            'portal_activo' => true,
            'updated_at'    => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Acceso al portal habilitado.']);
    }

    if (empty($request->correo) || empty($request->contrasena)) {
        return response()->json(['success' => false, 'message' => 'Correo y contraseña son requeridos.'], 422);
    }

    if (DB::table('usuarios')->where('correo', $request->correo)->exists()) {
        return response()->json(['success' => false, 'message' => 'El correo ya está registrado.'], 409);
    }

    $usuarioId = DB::table('usuarios')->insertGetId([
        'nombre'     => trim("{$paciente->nombre} {$paciente->apellido}"),
        'correo'     => $request->correo,
        'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena),
        'rol'        => 'paciente',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Guardar correo también en pacientes.correo (fuente de verdad)
    DB::table('pacientes')->where('paciente_id', $id)->update([
        'usuario_id'    => $usuarioId,
        'correo'        => $request->correo,
        'portal_activo' => true,
        'updated_at'    => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Acceso al portal habilitado correctamente.']);
});

// Revocar acceso: solo desactiva portal_activo, conserva el usuario
Route::patch('/pacientes/{id}/revocar-acceso', function ($id) {
    $paciente = DB::table('pacientes')->where('paciente_id', $id)->first();
    if (!$paciente || !$paciente->usuario_id) {
        return response()->json(['success' => false, 'message' => 'El paciente no tiene cuenta vinculada.'], 404);
    }

    // Invalidar tokens activos
    DB::table('personal_access_tokens')->where('tokenable_id', $paciente->usuario_id)->delete();

    DB::table('pacientes')->where('paciente_id', $id)->update([
        'portal_activo' => false,
        'updated_at'    => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Acceso al portal revocado.']);
});

// Habilitar acceso: reactiva portal_activo sin necesitar nueva contraseña
Route::patch('/pacientes/{id}/habilitar-acceso', function ($id) {
    $paciente = DB::table('pacientes')->where('paciente_id', $id)->first();
    if (!$paciente || !$paciente->usuario_id) {
        return response()->json(['success' => false, 'message' => 'El paciente no tiene cuenta vinculada. Usa el formulario de credenciales.'], 422);
    }

    DB::table('pacientes')->where('paciente_id', $id)->update([
        'portal_activo' => true,
        'updated_at'    => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Acceso al portal habilitado.']);
});

// Eliminar paciente
Route::delete('/pacientes/{id}', function ($id) {
    DB::table('pacientes')->where('paciente_id', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Paciente eliminado.']);
});

// Crear nuevo paciente
Route::post('/pacientes', function (Request $request) {
    $pacienteId = DB::table('pacientes')->insertGetId([
        'usuario_id'        => null,
        'nombre'            => $request->nombre,
        'apellido'          => $request->apellido,
        'fecha_nacimiento'  => $request->fecha_nacimiento,
        'genero'            => $request->genero,
        'telefono'          => $request->telefono,
        'fisioterapeuta_id' => $request->fisioterapeuta_id ?: null,
        'portal_activo'     => false,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    // Si se enviaron credenciales, crear usuario y vincularlo
    if (!empty($request->correo) && !empty($request->contrasena)) {
        if (!DB::table('usuarios')->where('correo', $request->correo)->exists()) {
            $paciente   = DB::table('pacientes')->where('paciente_id', $pacienteId)->first();
            $usuarioId  = DB::table('usuarios')->insertGetId([
                'nombre'     => trim("{$paciente->nombre} {$paciente->apellido}"),
                'correo'     => $request->correo,
                'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena),
                'rol'        => 'paciente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('pacientes')->where('paciente_id', $pacienteId)->update([
                'usuario_id'    => $usuarioId,
                'correo'        => $request->correo,
                'portal_activo' => true,
                'updated_at'    => now(),
            ]);
        }
    }

    return response()->json([
        'success'     => true,
        'message'     => 'Paciente guardado exitosamente.',
        'paciente_id' => $pacienteId,
    ]);
});


// =========================================================================
// 3. MÓDULO DE CITAS (Blindado para guardar correctamente)
// =========================================================================

// Obtener lista de citas para tu Agenda
Route::get('/citas', function () {
    $citas = DB::table('citas')->get();
    return response()->json($citas);
});

// Guardar cita desde tu formulario
Route::post('/citas', function (Request $request) {
    $error = validarHorarioCita($request->fisioterapeuta_id, $request->fecha_hora);
    if ($error) return response()->json(['success' => false, 'message' => $error], 422);

    $conflicto = verificarDisponibilidad($request->fisioterapeuta_id, $request->fecha_hora);
    if ($conflicto) return response()->json(['success' => false, 'message' => $conflicto], 409);

    DB::table('citas')->insert([
        'paciente_id'       => $request->paciente_id,
        'fisioterapeuta_id' => $request->fisioterapeuta_id,
        'fecha_hora'        => $request->fecha_hora,
        'motivo'            => $request->motivo,
        'estado'            => 'programada',
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $paciente  = DB::table('pacientes')->where('paciente_id', $request->paciente_id)->first();
    $nombrePac = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
    $fechaFmt  = (new \DateTime($request->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

    // Notificar al fisioterapeuta
    if ($request->fisioterapeuta_id) {
        $fisio     = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $request->fisioterapeuta_id)->first();
        $nombreFisio = $fisio ? "{$fisio->nombre} {$fisio->apellido}" : 'tu especialista';
        DB::table('notificaciones')->insert([
            'tipo'              => 'nueva_cita',
            'titulo'            => 'Nueva cita agendada',
            'mensaje'           => "{$nombrePac} tiene una cita programada para el {$fechaFmt}.",
            'leida'             => false,
            'fisioterapeuta_id' => $request->fisioterapeuta_id,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    // Notificar al paciente
    if ($paciente) {
        $fisio = $fisio ?? ($request->fisioterapeuta_id ? DB::table('fisioterapeutas')->where('fisioterapeuta_id', $request->fisioterapeuta_id)->first() : null);
        $nombreFisio = $fisio ? "{$fisio->nombre} {$fisio->apellido}" : 'tu especialista';
        DB::table('notificaciones')->insert([
            'tipo'        => 'cita_programada',
            'titulo'      => 'Cita programada',
            'mensaje'     => "Tu cita ha sido programada para el {$fechaFmt} con {$nombreFisio}.",
            'leida'       => false,
            'paciente_id' => $paciente->paciente_id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    return response()->json(['success' => true, 'message' => 'Cita agendada exitosamente.']);
});

// Editar todos los campos de una cita
Route::put('/citas/{id}', function (Request $request, $id) {
    $error = validarHorarioCita($request->fisioterapeuta_id, $request->fecha_hora);
    if ($error) return response()->json(['success' => false, 'message' => $error], 422);

    $conflicto = verificarDisponibilidad($request->fisioterapeuta_id, $request->fecha_hora, (int) $id);
    if ($conflicto) return response()->json(['success' => false, 'message' => $conflicto], 409);

    DB::table('citas')->where('cita_id', $id)->update([
        'paciente_id'       => $request->paciente_id,
        'fisioterapeuta_id' => $request->fisioterapeuta_id,
        'fecha_hora'        => $request->fecha_hora,
        'motivo'            => $request->motivo,
        'updated_at'        => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Cita actualizada.']);
});

// Eliminar cita
Route::delete('/citas/{id}', function ($id) {
    DB::table('citas')->where('cita_id', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Cita eliminada.']);
});

// Actualizar estado de una cita (cancelar libera el bloque de tiempo)
Route::patch('/citas/{id}', function (Request $request, $id) {
    $estados = ['programada', 'atendida', 'cancelada'];
    if (!in_array($request->estado, $estados)) {
        return response()->json(['success' => false, 'message' => 'Estado inválido.'], 422);
    }

    $cita = DB::table('citas')->where('cita_id', $id)->first();

    DB::table('citas')->where('cita_id', $id)->update([
        'estado'     => $request->estado,
        'updated_at' => now(),
    ]);

    // Notificar al fisioterapeuta y paciente por cualquier cambio de estado
    if ($cita && $cita->fisioterapeuta_id) {
        $paciente  = DB::table('pacientes')->where('paciente_id', $cita->paciente_id)->first();
        $nombrePac = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
        $fechaFmt  = (new \DateTime($cita->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

        if ($request->estado === 'cancelada') {
            $titulo  = 'Cita cancelada';
            $mensaje = "La cita de {$nombrePac} del {$fechaFmt} ha sido cancelada. El horario queda disponible.";
            $titPac  = 'Tu cita fue cancelada';
            $msgPac  = "Tu cita del {$fechaFmt} ha sido cancelada. Contacta a tu fisioterapeuta para reprogramarla.";
        } elseif ($request->estado === 'atendida') {
            $titulo  = 'Cita marcada como atendida';
            $mensaje = "La cita de {$nombrePac} del {$fechaFmt} fue marcada como atendida.";
            $titPac  = 'Cita marcada como atendida';
            $msgPac  = "Tu cita del {$fechaFmt} fue marcada como atendida. ¡Gracias por tu asistencia!";
        } else {
            $titulo  = 'Cita reprogramada';
            $mensaje = "La cita de {$nombrePac} del {$fechaFmt} ha sido reprogramada como pendiente.";
            $titPac  = 'Tu cita fue reprogramada';
            $msgPac  = "Tu cita del {$fechaFmt} ha sido reprogramada. Está nuevamente activa.";
        }

        DB::table('notificaciones')->insert([
            'tipo'              => 'estado_cita',
            'titulo'            => $titulo,
            'mensaje'           => $mensaje,
            'leida'             => false,
            'fisioterapeuta_id' => $cita->fisioterapeuta_id,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Notificar al paciente
        if ($paciente) {
            DB::table('notificaciones')->insert([
                'tipo'        => 'estado_cita',
                'titulo'      => $titPac,
                'mensaje'     => $msgPac,
                'leida'       => false,
                'paciente_id' => $paciente->paciente_id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
});


// =========================================================================
// 4. MÓDULO DE FISIOTERAPEUTAS
// =========================================================================

Route::get('/fisioterapeutas', function () {
    $fisioterapeutas = DB::table('fisioterapeutas')->get()->map(function ($f) {
        $raw = $f->horario ?? null;
        $f->horario = $raw ? json_decode($raw, true) : null;
        // Incluir correo del usuario si existe
        if ($f->usuario_id) {
            $u = DB::table('usuarios')->where('usuario_id', $f->usuario_id)->first();
            $f->correo_usuario = $u?->correo ?? null;
        } else {
            $f->correo_usuario = null;
        }
        return $f;
    });
    return response()->json($fisioterapeutas);
});

// Crear credenciales para un fisioterapeuta existente sin cuenta
Route::post('/fisioterapeutas/{id}/credenciales', function (Request $request, $id) {
    $fisio = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->first();
    if (!$fisio) return response()->json(['success' => false, 'message' => 'Fisioterapeuta no encontrado.'], 404);
    if ($fisio->usuario_id) return response()->json(['success' => false, 'message' => 'Este fisioterapeuta ya tiene credenciales.'], 409);

    // Verificar que el correo no esté en uso
    $existe = DB::table('usuarios')->where('correo', $request->correo)->exists();
    if ($existe) return response()->json(['success' => false, 'message' => 'El correo ya está registrado.'], 409);

    $usuarioId = DB::table('usuarios')->insertGetId([
        'nombre'     => "{$fisio->nombre} {$fisio->apellido}",
        'correo'     => $request->correo,
        'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena),
        'rol'        => 'fisioterapeuta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->update([
        'usuario_id'  => $usuarioId,
        'updated_at'  => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Credenciales creadas correctamente.']);
});

Route::post('/fisioterapeutas', function (Request $request) {
    // Verificar que el correo no esté ya en uso
    if (DB::table('usuarios')->where('correo', $request->correo)->exists()) {
        return response()->json(['success' => false, 'message' => 'El correo ya está registrado por otro usuario.'], 409);
    }

    $partes   = explode(' ', trim($request->nombre), 2);
    $nombre   = $partes[0];
    $apellido = $partes[1] ?? '';

    // Crear cuenta de usuario para el fisioterapeuta (sin fisioterapeuta_id aún)
    $usuarioId = DB::table('usuarios')->insertGetId([
        'nombre'     => trim($request->nombre),
        'correo'     => $request->correo,
        'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena),
        'rol'        => 'fisioterapeuta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Crear registro en fisioterapeutas vinculado al usuario recién creado
    $id = DB::table('fisioterapeutas')->insertGetId([
        'usuario_id'   => $usuarioId,
        'nombre'       => $nombre,
        'apellido'     => $apellido,
        'especialidad' => $request->especialidad,
        'telefono'     => $request->telefono,
        'activo'       => $request->activo ? 1 : 0,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    // Sincronizar: guardar el fisioterapeuta_id en el usuario recién creado
    DB::table('usuarios')->where('usuario_id', $usuarioId)->update([
        'fisioterapeuta_id' => $id,
        'updated_at'        => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Fisioterapeuta guardado.', 'id' => $id], 201);
});

Route::put('/fisioterapeutas/{id}', function (Request $request, $id) {
    $partes   = explode(' ', trim($request->nombre), 2);
    $nombre   = $partes[0];
    $apellido = $partes[1] ?? '';

    DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->update([
        'nombre'       => $nombre,
        'apellido'     => $apellido,
        'especialidad' => $request->especialidad,
        'telefono'     => $request->telefono,
        'activo'       => $request->activo ? 1 : 0,
        'updated_at'   => now(),
    ]);

    // Actualizar credenciales y nombre en usuarios si tiene cuenta vinculada
    $fisio = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->first();
    if ($fisio?->usuario_id) {
        $credUpdate = [
            'nombre'     => trim("{$nombre} {$apellido}"),
            'updated_at' => now(),
        ];

        if (!empty($request->correo)) {
            // Verificar que el correo no esté en uso por otro usuario
            $existe = DB::table('usuarios')
                ->where('correo', $request->correo)
                ->where('usuario_id', '!=', $fisio->usuario_id)
                ->exists();
            if ($existe) {
                return response()->json(['success' => false, 'message' => 'El correo ya está en uso por otro usuario.'], 409);
            }
            $credUpdate['correo'] = $request->correo;
        }

        if (!empty($request->contrasena)) {
            $credUpdate['contrasena'] = \Illuminate\Support\Facades\Hash::make($request->contrasena);
        }

        if (count($credUpdate) > 1) {
            DB::table('usuarios')->where('usuario_id', $fisio->usuario_id)->update($credUpdate);
        }
    }

    return response()->json(['success' => true, 'message' => 'Fisioterapeuta actualizado.']);
});

Route::patch('/fisioterapeutas/{id}/activo', function (Request $request, $id) {
    DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->update([
        'activo'     => $request->activo ? 1 : 0,
        'updated_at' => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
});

Route::delete('/fisioterapeutas/{id}', function ($id) {
    DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Fisioterapeuta eliminado.']);
});

Route::put('/fisioterapeutas/{id}/horario', function (Request $request, $id) {
    DB::table('fisioterapeutas')->where('fisioterapeuta_id', $id)->update([
        'horario'    => json_encode($request->horario),
        'updated_at' => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Horario guardado.']);
});

// =========================================================================
// 5. MÓDULO DE CONTACTOS
// =========================================================================
Route::get('contactos/stats', [ContactoController::class, 'stats']);
Route::apiResource('contactos', ContactoController::class);

// =========================================================================
// 6. PORTAL DEL PACIENTE
// =========================================================================

/** Helper: obtener paciente_id desde el usuario autenticado */
$getPacienteId = function (Request $request) {
    $paciente = DB::table('pacientes')
        ->where('usuario_id', $request->user()->usuario_id)
        ->first();
    return $paciente?->paciente_id;
};

// ── Perfil personal ──────────────────────────────────────────────────────
Route::get('/paciente/mi-perfil', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(null, 403);
    return response()->json(
        DB::table('pacientes')->where('paciente_id', $pacienteId)->first()
    );
});

Route::put('/paciente/mi-perfil', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(['success' => false], 403);

    DB::table('pacientes')->where('paciente_id', $pacienteId)->update([
        'nombre'           => $request->nombre,
        'apellido'         => $request->apellido,
        'telefono'         => $request->telefono,
        'direccion'        => $request->direccion ?? null,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'genero'           => $request->genero,
        'updated_at'       => now(),
    ]);

    // Sincronizar nombre en usuarios
    DB::table('usuarios')->where('usuario_id', $request->user()->usuario_id)->update([
        'nombre'     => trim("{$request->nombre} {$request->apellido}"),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Perfil actualizado.']);
});

// ── Cambiar contraseña ───────────────────────────────────────────────────
Route::put('/paciente/mi-cuenta', function (Request $request) {
    $usuario = $request->user();

    if (!\Illuminate\Support\Facades\Hash::check($request->contrasena_actual, $usuario->contrasena)) {
        return response()->json(['success' => false, 'message' => 'La contraseña actual no es correcta.'], 422);
    }
    if (empty($request->contrasena_nueva)) {
        return response()->json(['success' => false, 'message' => 'La nueva contraseña no puede estar vacía.'], 422);
    }

    DB::table('usuarios')->where('usuario_id', $usuario->usuario_id)->update([
        'contrasena' => \Illuminate\Support\Facades\Hash::make($request->contrasena_nueva),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
});

// ── Mis citas ────────────────────────────────────────────────────────────
Route::get('/paciente/mis-citas', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json([]);

    $citas = DB::table('citas as c')
        ->leftJoin('fisioterapeutas as f', 'c.fisioterapeuta_id', '=', 'f.fisioterapeuta_id')
        ->where('c.paciente_id', $pacienteId)
        ->orderByDesc('c.fecha_hora')
        ->select(
            'c.cita_id', 'c.fecha_hora', 'c.motivo', 'c.estado',
            DB::raw("CONCAT_WS(' ', f.nombre, f.apellido) as fisioterapeuta_nombre"),
            'f.especialidad'
        )
        ->get();

    return response()->json($citas);
});

// ── Slots ocupados de un fisio en una fecha ──────────────────────────────
Route::get('/paciente/slots-ocupados', function (Request $request) {
    $fisioId = $request->query('fisioterapeuta_id');
    $fecha   = $request->query('fecha');
    if (!$fisioId || !$fecha) return response()->json([]);

    $slotsBase = [];
    for ($h = 7; $h < 19; $h++) {
        $slotsBase[] = sprintf('%02d:00', $h);
        $slotsBase[] = sprintf('%02d:30', $h);
    }

    // Citas ya agendadas (no canceladas) — cada cita dura 1 hora = bloquea 2 slots de 30 min
    $horasCitas = DB::table('citas')
        ->where('fisioterapeuta_id', $fisioId)
        ->whereDate('fecha_hora', $fecha)
        ->whereNotIn('estado', ['cancelada'])
        ->pluck('fecha_hora')
        ->map(fn($fh) => substr($fh, 11, 5)); // "HH:MM"

    $bloqueadosPorCita = collect($slotsBase)->filter(function ($slot) use ($horasCitas) {
        foreach ($horasCitas as $inicio) {
            // Calcular fin = inicio + 1 hora
            [$hh, $mm] = explode(':', $inicio);
            $finMin = (int)$hh * 60 + (int)$mm + 60;
            $fin    = sprintf('%02d:%02d', intdiv($finMin, 60), $finMin % 60);
            if ($slot >= $inicio && $slot < $fin) return true;
        }
        return false;
    })->values();

    // Eventos de agenda — bloquean todos los slots dentro de su rango
    $eventos = DB::table('eventos_agenda')
        ->where('fisioterapeuta_id', $fisioId)
        ->where('fecha', $fecha)
        ->get(['hora_inicio', 'hora_fin']);

    $bloqueadosPorEvento = collect($slotsBase)->filter(function ($slot) use ($eventos) {
        foreach ($eventos as $ev) {
            $inicio = substr($ev->hora_inicio, 0, 5);
            $fin    = substr($ev->hora_fin, 0, 5);
            if ($slot >= $inicio && $slot < $fin) return true;
        }
        return false;
    })->values();

    return response()->json(
        $bloqueadosPorCita->merge($bloqueadosPorEvento)->unique()->values()
    );
});

// ── Agendar nueva cita (paciente elige fisio, fecha y hora) ──────────────
Route::post('/paciente/citas', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);

    $fisioId   = (int) $request->fisioterapeuta_id;
    $fechaHora = $request->fecha_hora;

    // Validar que el fisio solicitado es el asignado al paciente
    $pacienteRow = DB::table('pacientes')->where('paciente_id', $pacienteId)->first();
    if (!$pacienteRow || (int) $pacienteRow->fisioterapeuta_id !== $fisioId) {
        return response()->json(['success' => false, 'message' => 'Solo puedes agendar con tu fisioterapeuta asignado.'], 403);
    }

    $error = validarHorarioCita($fisioId, $fechaHora);
    if ($error) return response()->json(['success' => false, 'message' => $error], 422);

    $conflicto = verificarDisponibilidad($fisioId, $fechaHora);
    if ($conflicto) return response()->json(['success' => false, 'message' => $conflicto], 409);

    DB::table('citas')->insert([
        'paciente_id'       => $pacienteId,
        'fisioterapeuta_id' => $fisioId,
        'fecha_hora'        => $fechaHora,
        'motivo'            => $request->motivo ?? null,
        'estado'            => 'programada',
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    // Notificar al fisioterapeuta
    $paciente  = DB::table('pacientes')->where('paciente_id', $pacienteId)->first();
    $nombrePac = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
    $fechaFmt  = (new \DateTime($fechaHora))->format('d/m/Y \a \l\a\s H:i');

    DB::table('notificaciones')->insert([
        'tipo'              => 'nueva_cita',
        'titulo'            => 'Nueva cita agendada',
        'mensaje'           => "{$nombrePac} agendó una cita para el {$fechaFmt}.",
        'leida'             => false,
        'fisioterapeuta_id' => $fisioId,
        'paciente_id'       => null,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    // Notificar al admin
    DB::table('notificaciones')->insert([
        'tipo'              => 'cita_agendada_paciente',
        'titulo'            => 'Nueva cita agendada por paciente',
        'mensaje'           => "{$nombrePac} agendó una cita para el {$fechaFmt}.",
        'leida'             => false,
        'fisioterapeuta_id' => $fisioId,
        'paciente_id'       => null,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    // Notificar al paciente (confirmación)
    $fisioObj    = DB::table('fisioterapeutas')->where('fisioterapeuta_id', $fisioId)->first();
    $nombreFisio = $fisioObj ? "{$fisioObj->nombre} {$fisioObj->apellido}" : 'tu especialista';
    DB::table('notificaciones')->insert([
        'tipo'        => 'cita_programada',
        'titulo'      => 'Cita confirmada',
        'mensaje'     => "Tu cita ha sido programada para el {$fechaFmt} con {$nombreFisio}.",
        'leida'       => false,
        'paciente_id' => $pacienteId,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Cita agendada correctamente.'], 201);
});

// ── Cancelar cita propia ─────────────────────────────────────────────────
Route::patch('/paciente/citas/{id}/cancelar', function (Request $request, $id) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(['success' => false], 403);

    $cita = DB::table('citas')
        ->where('cita_id', $id)
        ->where('paciente_id', $pacienteId)
        ->first();

    if (!$cita) return response()->json(['success' => false, 'message' => 'Cita no encontrada.'], 404);
    if ($cita->estado !== 'programada') {
        return response()->json(['success' => false, 'message' => 'Solo se pueden cancelar citas programadas.'], 422);
    }

    DB::table('citas')->where('cita_id', $id)->update([
        'estado'     => 'cancelada',
        'updated_at' => now(),
    ]);

    // Notificar al fisioterapeuta
    if ($cita->fisioterapeuta_id) {
        $paciente  = DB::table('pacientes')->where('paciente_id', $pacienteId)->first();
        $nombrePac = $paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'Un paciente';
        $fechaFmt  = (new \DateTime($cita->fecha_hora))->format('d/m/Y \a \l\a\s H:i');

        DB::table('notificaciones')->insert([
            'tipo'              => 'estado_cita',
            'titulo'            => 'Cita cancelada por el paciente',
            'mensaje'           => "{$nombrePac} canceló la cita del {$fechaFmt}. El horario queda disponible.",
            'leida'             => false,
            'fisioterapeuta_id' => $cita->fisioterapeuta_id,
            'paciente_id'       => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Confirmar cancelación al paciente
        DB::table('notificaciones')->insert([
            'tipo'        => 'cita_cancelada',
            'titulo'      => 'Cita cancelada',
            'mensaje'     => "Tu cita del {$fechaFmt} ha sido cancelada exitosamente.",
            'leida'       => false,
            'paciente_id' => $pacienteId,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    return response()->json(['success' => true, 'message' => 'Cita cancelada.']);
});

// ── Mi equipo asignado (inventario) ─────────────────────────────────────
Route::get('/paciente/mi-equipo', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json([]);

    $equipos = DB::table('asignaciones_equipo as ae')
        ->join('inventario as i', 'ae.inventario_id', '=', 'i.id_inventario')
        ->where('ae.paciente_id', $pacienteId)
        ->where('ae.estado', 'activo')
        ->select(
            'ae.id_asignaciones',
            'ae.fecha_asignacion',
            'ae.notas',
            'i.nombre',
            'i.tipo'
        )
        ->get();

    return response()->json($equipos);
});

// ── Mi fisioterapeuta asignado ────────────────────────────────────────────
Route::get('/paciente/mi-fisioterapeuta', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(null);

    $paciente = DB::table('pacientes')->where('paciente_id', $pacienteId)->first();
    if (!$paciente || !$paciente->fisioterapeuta_id) return response()->json(null);

    $fisio = DB::table('fisioterapeutas as f')
        ->leftJoin('usuarios as u', 'f.usuario_id', '=', 'u.usuario_id')
        ->where('f.fisioterapeuta_id', $paciente->fisioterapeuta_id)
        ->select('f.fisioterapeuta_id', 'f.nombre', 'f.apellido', 'f.especialidad', 'f.telefono', 'f.horario', 'u.correo')
        ->first();

    if ($fisio) $fisio->horario = $fisio->horario ? json_decode($fisio->horario, true) : null;

    return response()->json($fisio);
});

// ── Fisioterapeutas disponibles para agendar ─────────────────────────────
Route::get('/paciente/fisioterapeutas', function () {
    return response()->json(
        DB::table('fisioterapeutas')
            ->where('activo', true)
            ->select('fisioterapeuta_id', 'nombre', 'apellido', 'especialidad', 'horario')
            ->get()
            ->map(function ($f) {
                $f->horario = $f->horario ? json_decode($f->horario, true) : null;
                return $f;
            })
    );
});

// ── Notificaciones del paciente ──────────────────────────────────────────
Route::get('/paciente/notificaciones', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json([]);

    return response()->json(
        DB::table('notificaciones')
            ->where('paciente_id', $pacienteId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
    );
});

Route::patch('/paciente/notificaciones/{id}/leida', function (Request $request, $id) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    DB::table('notificaciones')
        ->where('notificacion_id', $id)
        ->where('paciente_id', $pacienteId)
        ->update(['leida' => true, 'updated_at' => now()]);
    return response()->json(['success' => true]);
});

Route::patch('/paciente/notificaciones/marcar-todas', function (Request $request) use ($getPacienteId) {
    $pacienteId = $getPacienteId($request);
    if (!$pacienteId) return response()->json(['success' => false], 403);
    DB::table('notificaciones')
        ->where('paciente_id', $pacienteId)
        ->where('leida', false)
        ->update(['leida' => true, 'updated_at' => now()]);
    return response()->json(['success' => true]);
});

// Actualizar tipos de notificaciones del admin para incluir citas agendadas por paciente
Route::patch('/admin/notificaciones/tipos', function () {
    // No-op: se gestiona en la consulta de admin/notificaciones
    return response()->json(['success' => true]);
});

}); // fin middleware auth:sanctum (módulos 2–5)
