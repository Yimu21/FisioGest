<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    // Esta es la parte 1 que ya te cargó bien
    public function index()
    {
        return response()->json(Inventario::all());
    }

    // ==========================================
    // ¡AQUÍ HACES LA PARTE 2! (Método store)
    // ==========================================
    public function store(Request $request)
    {
        // 1. Validamos los campos que vienen del formulario Blade (Criterio 4)
        $validatedData = $request->validate([
            'nombre'       => 'required|string|max:150',
            'tipo'         => 'required|string|max:100',
            'marca'        => 'nullable|string|max:100',
            'modelo'       => 'nullable|string|max:100',
            'estado'       => 'required|string',
            'cantidad'     => 'required|integer|min:1',
        ]);

        // 2. Insertamos los datos validados directamente en SQLite (Criterio 3)
        Inventario::create($validatedData);

        // 3. Redirigimos de vuelta al inventario con un mensaje de éxito
        return response()->json(['success' => true, 'message' => 'Equipo añadido con éxito.'], 201);
    }
}