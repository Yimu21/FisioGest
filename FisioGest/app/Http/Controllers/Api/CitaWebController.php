<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaWebController extends Controller
{
    // Esto es lo que busca Vue cuando carga la pantalla de citas
    public function index()
    {
        $citas = DB::table('citas')->get();
        return response()->json($citas);
    }

    // Esto es lo que se activa cuando creas una nueva cita
    public function store(Request $request)
    {
        DB::table('citas')->insert([
            'paciente_id' => $request->paciente_id,
            'fisioterapeuta_id' => $request->fisioterapeuta_id,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'motivo' => $request->motivo ?? '',
        ]);

        return response()->json(['success' => true, 'message' => 'Cita guardada']);
    }
}