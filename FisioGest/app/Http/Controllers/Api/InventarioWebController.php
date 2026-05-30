<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioWebController extends Controller
{
    public function index()
    {
        $items = DB::table('inventario')->get();

        // Stats para las tarjetas del dashboard de inventario
        $totalItems    = $items->count();
        $enUso         = $items->where('estado', 'en_uso')->count();
        $stockBajo     = $items->where('cantidad', '<', 5)->count();
        $alertasCriticas = $items->where('cantidad', 0)->count();

        return view('inventario', compact('items', 'totalItems', 'enUso', 'stockBajo', 'alertasCriticas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:protesis,ortesis,maquina,otro',
            'cantidad' => 'required|integer|min:0',
            'estado'   => 'required|in:disponible,en_uso,mantenimiento,baja',
        ]);

        DB::table('inventario')->insert([
            'nombre'      => $request->input('nombre'),
            'tipo'        => $request->input('tipo'),
            'modelo'      => $request->input('modelo'),      // FIX: antes era modelo_marca
            'estado'      => $request->input('estado', 'disponible'),
            'cantidad'    => $request->input('cantidad', 1),
            'descripcion' => $request->input('descripcion'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('inventario.index')->with('success', 'Equipo añadido correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:protesis,ortesis,maquina,otro',
            'cantidad' => 'required|integer|min:0',
            'estado'   => 'required|in:disponible,en_uso,mantenimiento,baja',
        ]);

        DB::table('inventario')->where('id_inventario', $id)->update([
            'nombre'      => $request->input('nombre'),
            'tipo'        => $request->input('tipo'),
            'modelo'      => $request->input('modelo'),
            'estado'      => $request->input('estado'),
            'cantidad'    => $request->input('cantidad'),
            'descripcion' => $request->input('descripcion'),
            'updated_at'  => now(),
        ]);

        return redirect()->route('inventario.index')->with('success', 'Equipo actualizado.');
    }

    public function destroy($id)
    {
        DB::table('inventario')->where('id_inventario', $id)->delete();
        return redirect()->route('inventario.index')->with('success', 'Equipo eliminado.');
    }
}