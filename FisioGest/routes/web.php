<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// =========================================================================
// 1. PANTALLA PRINCIPAL
// =========================================================================
Route::get('/', function () { 
    return view('welcome'); 
})->name('welcome');


// =========================================================================
// 2. MÓDULO DE PACIENTES
// =========================================================================
Route::get('/pacientes', function () {
    $pacientes = DB::table('pacientes')->get();
    $fisioterapeutas = DB::table('fisioterapeutas')->get();
    
    if ($fisioterapeutas->isEmpty()) {
        $fisioterapeutas = collect([
            (object)['fisioterapeuta_id' => 1, 'nombre' => 'Manrivel', 'apellido' => 'Gorado'],
            (object)['fisioterapeuta_id' => 2, 'nombre' => 'Barvis', 'apellido' => 'Raten'],
            (object)['fisioterapeuta_id' => 3, 'nombre' => 'Bardena', 'apellido' => 'Drides'],
            (object)['fisioterapeuta_id' => 4, 'nombre' => 'Marina', 'apellido' => 'Gomez'],
            (object)['fisioterapeuta_id' => 5, 'nombre' => 'Retmen', 'apellido' => 'Nones'],
        ]);
    }
    return view('pacientes', compact('pacientes', 'fisioterapeutas'));
})->name('pacientes.index');

Route::post('/pacientes', function (Request $request) {
    DB::table('pacientes')->insert([
        'usuario_id' => 1, 
        'nombre' => $request->nombre, 
        'apellido' => $request->apellido,
        'fecha_nacimiento' => $request->fecha_nacimiento, 
        'genero' => $request->genero,
        'telefono' => $request->telefono, 
        'created_at' => now(), 
        'updated_at' => now()
    ]);
    return redirect('/pacientes');
});


// =========================================================================
// 3. MÓDULO DE CITAS (BLINDADO)
// =========================================================================
Route::get('/citas', function () {
    $citas = DB::table('citas')->get();
    $pacientes = DB::table('pacientes')->get();
    $fisioterapeutas = DB::table('fisioterapeutas')->get();

    if ($fisioterapeutas->isEmpty()) {
        $fisioterapeutas = collect([
            (object)['fisioterapeuta_id' => 1, 'nombre' => 'Manrivel', 'apellido' => 'Gorado'],
            (object)['fisioterapeuta_id' => 2, 'nombre' => 'Barvis', 'apellido' => 'Raten'],
            (object)['fisioterapeuta_id' => 3, 'nombre' => 'Bardena', 'apellido' => 'Drides'],
            (object)['fisioterapeuta_id' => 4, 'nombre' => 'Marina', 'apellido' => 'Gomez'],
            (object)['fisioterapeuta_id' => 5, 'nombre' => 'Retmen', 'apellido' => 'Nones'],
        ]);
    }
    return view('citas', compact('citas', 'pacientes', 'fisioterapeutas'));
})->name('citas.index');

Route::post('/citas', function (Request $request) {
    DB::table('citas')->insert([
        'paciente_id' => $request->paciente_id, 
        'fisioterapeuta_id' => $request->fisioterapeuta_id,
        'fecha_hora' => $request->fecha . ' ' . $request->hora . ':00', 
        'motivo' => $request->motivo,
        'estado' => 'programada', 
        'created_at' => now(), 
        'updated_at' => now()
    ]);
    return redirect('/citas');
});


// =========================================================================
// 4. MÓDULOS DE INVENTARIO Y FISIOTERAPEUTAS
// =========================================================================
// ─── VISTA DE FISIOTERAPEUTA ──────────────────────────────────────────────

Route::get('/fisio/dashboard', function () {
    $pacientes = DB::table('pacientes')->get();
    return view('fisio.dashboard', compact('pacientes'));
})->name('fisio.dashboard');

Route::get('/fisio/pacientes', function (Request $request) {
    $pacientes = DB::table('pacientes')->get();
    $selectedId = $request->query('id', 'ER001');
    return view('fisio.pacientes', compact('pacientes', 'selectedId'));
})->name('fisio.pacientes');

Route::get('/fisio/asignaciones', function () {
    $items = DB::table('inventario')->get();
    $pacientes = DB::table('pacientes')->get();
    return view('fisio.asignaciones', compact('items', 'pacientes'));
})->name('fisio.asignaciones');

Route::get('/fisio/citas', function () {
    $citas = DB::table('citas')->where('fisioterapeuta_id', 1)->get();
    if ($citas->isEmpty()) {
        $citas = DB::table('citas')->get();
    }
    $pacientes = DB::table('pacientes')->get();
    return view('fisio.citas', compact('citas', 'pacientes'));
})->name('fisio.citas');

// ─────────────────────────────────────────────────────────────────────────
Route::get('/fisioterapeutas', function () {
    $fisioterapeutas = collect([
        (object)['fisioterapeuta_id' => 1, 'nombre' => 'Manrivel', 'apellido' => 'Gorado', 'especialidad' => 'Traumatología'],
        (object)['fisioterapeuta_id' => 2, 'nombre' => 'Barvis', 'apellido' => 'Raten', 'especialidad' => 'Deportiva'],
        (object)['fisioterapeuta_id' => 3, 'nombre' => 'Bardena', 'apellido' => 'Drides', 'especialidad' => 'Deportiva'],
        (object)['fisioterapeuta_id' => 4, 'nombre' => 'Marina', 'apellido' => 'Gomez', 'especialidad' => 'Traumatología'],
        (object)['fisioterapeuta_id' => 5, 'nombre' => 'Retmen', 'apellido' => 'Nones', 'especialidad' => 'Deportiva'],
    ]);
    return view('fisioterapeutas', compact('fisioterapeutas'));
})->name('fisioterapeutas.index');

Route::get('/inventario', function () {
    $items = DB::table('inventario')->get();
    $pacientes = DB::table('pacientes')->get();
    $fisioterapeutas = collect([
        (object)['fisioterapeuta_id' => 1, 'nombre' => 'Manrivel', 'apellido' => 'Gorado'],
        (object)['fisioterapeuta_id' => 2, 'nombre' => 'Barvis', 'apellido' => 'Raten'],
        (object)['fisioterapeuta_id' => 3, 'nombre' => 'Bardena', 'apellido' => 'Drides'],
        (object)['fisioterapeuta_id' => 4, 'nombre' => 'Marina', 'apellido' => 'Gomez'],
        (object)['fisioterapeuta_id' => 5, 'nombre' => 'Retmen', 'apellido' => 'Nones'],
    ]);
    return view('inventario', compact('items', 'pacientes', 'fisioterapeutas'));
})->name('inventario.index');


// =========================================================================
// 5. REGLA EXCLUSIVA PARA VUE (EVITA INTERCEPTAR TUS PÁGINAS)
// =========================================================================
// Esta regla modificada le prohíbe explícitamente a Laravel tocar tus URLs reales.
Route::get('/{any}', function () { 
    return view('welcome'); 
})->where('any', '^(?!api|pacientes|citas|inventario|fisioterapeutas|fisio|login|logout).*$');