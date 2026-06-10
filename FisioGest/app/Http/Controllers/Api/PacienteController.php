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

    // La lógica de creación de pacientes vive en routes/api.php (POST /pacientes).
    // Este método no está enrutado y se conserva solo para estructura MVC.
    public function store(Request $request)
    {
        return response()->json(['message' => 'Usa el endpoint POST /api/pacientes.'], 501);
    }
}