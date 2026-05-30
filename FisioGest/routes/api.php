<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - FisioGest (Modo de Rescate de Integridad)
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API para tu aplicación.
| Cada ruta está blindada para inyectar los datos obligatorios 
| que la base de datos SQLite exige y evitar errores 500.
|
*/

// =========================================================================
// 1. MÓDULO DE INVENTARIO (Rutas originales estables)
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