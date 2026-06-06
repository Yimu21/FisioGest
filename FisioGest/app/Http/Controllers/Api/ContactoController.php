<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Contacto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactoRequest;

class ContactoController extends Controller
{
      public function index(Request $request)
    {
        return Contacto::query()
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%"))
            ->when($request->tipo, fn($q, $t) =>
                $q->where('tipo', $t))
            ->latest()
            ->paginate(15);
    }

    public function store(ContactoRequest $request)
    {
        $contacto = Contacto::create($request->validated());
        return response()->json($contacto, 201);
    }

    public function update(ContactoRequest $request, Contacto $contacto)
    {
        $contacto->update($request->validated());
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
        'total'           => Contacto::count(),
        'fisioterapeutas' => Contacto::where('tipo', 'fisioterapeuta')->count(),
        'referidos'       => Contacto::where('tipo', 'referido')->count(),
        'proveedores'     => Contacto::where('tipo', 'proveedor')->count(),
        ]);
    }
}
