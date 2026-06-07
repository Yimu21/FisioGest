<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Contacto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ContactoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $tipo   = $request->tipo;

        // ── Fisioterapeutas (desde su propia tabla) ──
        $fisios = (!$tipo || $tipo === 'fisioterapeuta')
            ? DB::table('fisioterapeutas as f')
                ->join('usuarios as u', 'u.usuario_id', '=', 'f.usuario_id')
                ->select(
                    DB::raw("CONCAT('fis_', f.fisioterapeuta_id) as id"),
                    DB::raw("CONCAT_WS(' ', f.nombre, f.apellido) as nombre"),
                    DB::raw("'fisioterapeuta' as tipo"),
                    'f.telefono',
                    DB::raw('u.correo as email'),
                    DB::raw("IF(f.activo = 1, 'activo', 'inactivo') as estado"),
                    DB::raw("'fisioterapeuta' as origen"),
                    DB::raw('NULL as raw_id')
                )
                ->when($search, fn($q) =>
                    $q->where(DB::raw("CONCAT_WS(' ', f.nombre, f.apellido)"), 'like', "%$search%")
                      ->orWhere('u.correo', 'like', "%$search%")
                )
                ->get()
            : collect();

        // ── Pacientes (desde su propia tabla) ──
        $pacientes = (!$tipo || $tipo === 'paciente')
            ? DB::table('pacientes as p')
                ->select(
                    DB::raw("CONCAT('pac_', p.paciente_id) as id"),
                    DB::raw("CONCAT_WS(' ', p.nombre, p.apellido) as nombre"),
                    DB::raw("'paciente' as tipo"),
                    'p.telefono',
                    DB::raw("NULL as email"),
                    DB::raw("'activo' as estado"),
                    DB::raw("'paciente' as origen"),
                    DB::raw('NULL as raw_id')
                )
                ->when($search, fn($q) =>
                    $q->where(DB::raw("CONCAT_WS(' ', p.nombre, p.apellido)"), 'like', "%$search%")
                )
                ->get()
            : collect();

        // ── Referidos y Proveedores (tabla contactos) ──
        $contactosQuery = DB::table('contactos')
            ->select(
                DB::raw("CONCAT('con_', id) as id"),
                'nombre',
                'tipo',
                'telefono',
                'email',
                'estado',
                DB::raw("'contacto' as origen"),
                DB::raw('id as raw_id')
            )
            ->whereIn('tipo', ['referido', 'proveedor'])
            ->when($search, fn($q) =>
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
            );

        if (in_array($tipo, ['referido', 'proveedor'])) {
            $contactosQuery->where('tipo', $tipo);
        }

        $contactos = (!$tipo || in_array($tipo, ['referido', 'proveedor']))
            ? $contactosQuery->get()
            : collect();

        // ── Unificar y devolver todos (sin paginación) ──
        $all = $fisios->concat($pacientes)->concat($contactos)->values();

        return response()->json($all);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:referido,proveedor',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:150',
            'estado'   => 'nullable|in:activo,inactivo,pendiente',
            'notas'    => 'nullable|string',
        ]);

        $contacto = Contacto::create([
            'nombre'   => $request->nombre,
            'tipo'     => $request->tipo,
            'telefono' => $request->telefono,
            'email'    => $request->email,
            'estado'   => $request->estado ?? 'activo',
            'notas'    => $request->notas,
        ]);

        return response()->json($contacto, 201);
    }

    public function update(Request $request, Contacto $contacto)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:referido,proveedor',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:150',
            'estado'   => 'nullable|in:activo,inactivo,pendiente',
            'notas'    => 'nullable|string',
        ]);

        $contacto->update($request->only(['nombre', 'tipo', 'telefono', 'email', 'estado', 'notas']));
        return response()->json($contacto);
    }

    public function destroy(Contacto $contacto)
    {
        $contacto->delete();
        return response()->json(['message' => 'Eliminado']);
    }

    public function stats()
    {
        return response()->json([
            'total'           => DB::table('fisioterapeutas')->count()
                               + DB::table('pacientes')->count()
                               + DB::table('contactos')->count(),
            'fisioterapeutas' => DB::table('fisioterapeutas')->count(),
            'pacientes'       => DB::table('pacientes')->count(),
            'referidos'       => DB::table('contactos')->where('tipo', 'referido')->count(),
            'proveedores'     => DB::table('contactos')->where('tipo', 'proveedor')->count(),
        ]);
    }
}
