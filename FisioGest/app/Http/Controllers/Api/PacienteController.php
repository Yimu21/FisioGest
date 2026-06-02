<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    // Esto es lo que lee Vue para mostrar la tabla
    public function index()
    {
        $pacientes = DB::table('pacientes')->get();
        return response()->json($pacientes);
    }

    // ¡AQUÍ CORREGIMOS EL ERROR DE INTEGRIDAD DE RAÍZ!
    public function store(Request $request)
    {
        DB::table('pacientes')->insert([
            'nombre' => $request->nombre,
            'usuario_id' => 1 // Amárralo al usuario 1 obligatorio para que la DB no tire el error 23000
        ]);

        return response()->json(['success' => true, 'message' => 'Paciente guardado con éxito']);
    }
}