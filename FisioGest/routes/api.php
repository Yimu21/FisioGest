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
        return response()->json(
            DB::table('pacientes')->where('fisioterapeuta_id', $fisioId)->get()
        );
    });

    Route::get('/fisio/mis-citas', function (Request $request) {
        $fisioId = $request->user()->fisioterapeuta_id;
        if (!$fisioId) return response()->json([]);
        return response()->json(
            DB::table('citas')->where('fisioterapeuta_id', $fisioId)->get()
        );
    });
});

// =========================================================================
// 1. FISIOTERAPEUTAS (desde BD real)
// =========================================================================
Route::get('/fisioterapeutas', function () {
    return response()->json(DB::table('fisioterapeutas')->get());
});

// =========================================================================
// 2. MÓDULO DE INVENTARIO (Rutas originales estables)
// =========================================================================
Route::get('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'index']);
Route::post('/inventario', [\App\Http\Controllers\Api\InventarioController::class, 'store']);


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
    $fisioterapeutas = DB::table('fisioterapeutas')->get();
    return response()->json($fisioterapeutas);
});

Route::post('/fisioterapeutas', function (Request $request) {
    $partes    = explode(' ', trim($request->nombre), 2);
    $nombre    = $partes[0];
    $apellido  = $partes[1] ?? '';

    $id = DB::table('fisioterapeutas')->insertGetId([
        'usuario_id'   => 1,
        'nombre'       => $nombre,
        'apellido'     => $apellido,
        'especialidad' => $request->especialidad,
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
        'activo'       => $request->activo ? 1 : 0,
        'updated_at'   => now(),
    ]);

    return response()->json(['success' => true, 'message' => 'Fisioterapeuta actualizado.']);
});


// =========================================================================
// 5. MÓDULO DE CONTACTOS
// =========================================================================
Route::get('contactos/stats', [ContactoController::class, 'stats']);
Route::apiResource('contactos', ContactoController::class);