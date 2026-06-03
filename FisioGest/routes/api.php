<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - FisioGest
|--------------------------------------------------------------------------
*/

// =========================================================================
// 0. AUTENTICACIÓN
// =========================================================================
Route::post('/login',    [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me',      [\App\Http\Controllers\Api\AuthController::class, 'me']);

    // ── Endpoints exclusivos del fisioterapeuta ──────────────────────────────
    Route::get('/fisio/mis-pacientes', function (Request $request) {
        $fisioId = $request->user()->fisioterapeuta_id;
        if (!$fisioId) return response()->json([]);
        $pacienteIds = DB::table('citas')
            ->where('fisioterapeuta_id', $fisioId)
            ->pluck('paciente_id')
            ->unique();
        return response()->json(
            DB::table('pacientes')->whereIn('paciente_id', $pacienteIds)->get()
        );
    });

    Route::get('/fisio/mis-citas', function (Request $request) {
        $fisioId = $request->user()->fisioterapeuta_id;
        if (!$fisioId) return response()->json([]);
        return response()->json(
            DB::table('citas')->where('fisioterapeuta_id', $fisioId)->get()
        );
    });

    Route::get('/fisio/mis-asignaciones', function (Request $request) {
        $fisioId = $request->user()->fisioterapeuta_id;
        if (!$fisioId) return response()->json([]);
        $pacienteIds = DB::table('citas')
            ->where('fisioterapeuta_id', $fisioId)
            ->pluck('paciente_id')
            ->unique();
        return response()->json(
            DB::table('asignaciones_equipo')
                ->whereIn('paciente_id', $pacienteIds)
                ->where('estado', 'activo')
                ->get()
        );
    });
});

// =========================================================================
// 2. MÓDULO DE INVENTARIO (Rutas originales estables)
// =========================================================================
Route::get('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'index']);
Route::post('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'store']);

Route::put('/inventario/{id}', function (Request $request, $id) {
    DB::table('inventario')->where('id_inventario', $id)->update([
        'nombre'     => $request->nombre,
        'tipo'       => $request->tipo,
        'marca'      => $request->marca,
        'modelo'     => $request->modelo,
        'estado'     => $request->estado,
        'cantidad'   => $request->cantidad,
        'updated_at' => now(),
    ]);
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

Route::post('/asignaciones', function (Request $request) {
    $id = DB::table('asignaciones_equipo')->insertGetId([
        'paciente_id'      => $request->paciente_id,
        'inventario_id'    => $request->inventario_id,
        'fecha_asignacion' => now()->toDateString(),
        'estado'           => 'activo',
        'notas'            => $request->notas ?? null,
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    return response()->json(['success' => true, 'id' => $id, 'message' => 'Equipo asignado correctamente.'], 201);
});

Route::patch('/asignaciones/{id}/liberar', function (Request $request, $id) {
    DB::table('asignaciones_equipo')->where('id_asignaciones', $id)->update([
        'estado'           => 'devuelto',
        'fecha_devolucion' => now()->toDateString(),
        'updated_at'       => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Equipo liberado correctamente.']);
});


// =========================================================================
// 2. MÓDULO DE PACIENTES (Blindado contra errores NOT NULL de usuario_id)
// =========================================================================

// Obtener lista de pacientes para Vue
Route::get('/pacientes', function () {
    $pacientes = DB::table('pacientes')->get();
    return response()->json($pacientes);
});

// Editar paciente
Route::put('/pacientes/{id}', function (Request $request, $id) {
    DB::table('pacientes')->where('paciente_id', $id)->update([
        'nombre'            => $request->nombre,
        'apellido'          => $request->apellido,
        'fecha_nacimiento'  => $request->fecha_nacimiento,
        'genero'            => $request->genero,
        'telefono'          => $request->telefono,
        'fisioterapeuta_id' => $request->fisioterapeuta_id ?: null,
        'updated_at'        => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Paciente actualizado.']);
});

// Eliminar paciente
Route::delete('/pacientes/{id}', function ($id) {
    DB::table('pacientes')->where('paciente_id', $id)->delete();
    return response()->json(['success' => true, 'message' => 'Paciente eliminado.']);
});

// Guardar paciente desde Vue
Route::post('/pacientes', function (Request $request) {
    DB::table('pacientes')->insert([
        'usuario_id'        => 1,
        'nombre'            => $request->nombre,
        'apellido'          => $request->apellido,
        'fecha_nacimiento'  => $request->fecha_nacimiento,
        'genero'            => $request->genero,
        'telefono'          => $request->telefono,
        'fisioterapeuta_id' => $request->fisioterapeuta_id ?: null,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Paciente guardado exitosamente.'
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
    DB::table('citas')->insert([
        'paciente_id'       => $request->paciente_id,
        'fisioterapeuta_id' => $request->fisioterapeuta_id,
        'fecha_hora'        => $request->fecha_hora,
        'motivo'            => $request->motivo,
        'estado'            => 'programada',
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Cita agendada exitosamente.'
    ]);
});

// Editar todos los campos de una cita
Route::put('/citas/{id}', function (Request $request, $id) {
    DB::table('citas')->where('cita_id', $id)->update([
        'paciente_id'       => $request->paciente_id,
        'fisioterapeuta_id' => $request->fisioterapeuta_id,
        'fecha_hora'        => $request->fecha_hora,
        'motivo'            => $request->motivo,
        'updated_at'        => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Cita actualizada.']);
});

// Actualizar estado de una cita (cancelar libera el bloque de tiempo)
Route::patch('/citas/{id}', function (Request $request, $id) {
    $estados = ['programada', 'atendida', 'cancelada'];
    if (!in_array($request->estado, $estados)) {
        return response()->json(['success' => false, 'message' => 'Estado inválido.'], 422);
    }
    DB::table('citas')->where('cita_id', $id)->update([
        'estado'     => $request->estado,
        'updated_at' => now(),
    ]);
    return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
});


// =========================================================================
// 4. MÓDULO DE FISIOTERAPEUTAS
// =========================================================================

Route::get('/fisioterapeutas', function () {
    $fisioterapeutas = DB::table('fisioterapeutas')->get()->map(function ($f) {
        $raw = $f->horario ?? null;
        $f->horario = $raw ? json_decode($raw, true) : null;
        return $f;
    });
    return response()->json($fisioterapeutas);
});

Route::post('/fisioterapeutas', function (Request $request) {
    $partes   = explode(' ', trim($request->nombre), 2);
    $nombre   = $partes[0];
    $apellido = $partes[1] ?? '';

    $id = DB::table('fisioterapeutas')->insertGetId([
        'usuario_id'   => 1,
        'nombre'       => $nombre,
        'apellido'     => $apellido,
        'especialidad' => $request->especialidad,
        'telefono'     => $request->telefono,
        'activo'       => $request->activo ? 1 : 0,
        'created_at'   => now(),
        'updated_at'   => now(),
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