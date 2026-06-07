<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:100',
            'correo'     => 'required|email|unique:usuarios,correo',
            'contraseña' => 'required|string|min:8|confirmed',
            'rol'        => 'sometimes|in:admin,fisioterapeuta,paciente',
        ]);

        $usuario = Usuario::create([
            'nombre'    => $data['nombre'],
            'correo'    => $data['correo'],
            'contrasena' => Hash::make($data['contraseña']),
            'rol'       => $data['rol'] ?? 'paciente',
        ]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $usuario,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'correo'     => 'required|email',
            'contraseña' => 'required|string',
        ]);

        $usuario = Usuario::where('correo', $data['correo'])->first();

        if (! $usuario || ! Hash::check($data['contraseña'], $usuario->contrasena)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales no son correctas.'],
            ]);
        }

        // Bloquear acceso si el fisioterapeuta está inactivo
        if ($usuario->rol === 'fisioterapeuta') {
            $fisio = \Illuminate\Support\Facades\DB::table('fisioterapeutas')
                ->where('usuario_id', $usuario->usuario_id)
                ->first();
            if ($fisio && ! $fisio->activo) {
                throw ValidationException::withMessages([
                    'correo' => ['Tu cuenta ha sido desactivada. Contacta al administrador.'],
                ]);
            }
            // Adjuntar activo al objeto usuario para el frontend
            $usuario->activo = $fisio?->activo ?? true;
        }

        // Bloquear acceso si el paciente no tiene portal activo
        if ($usuario->rol === 'paciente') {
            $paciente = \Illuminate\Support\Facades\DB::table('pacientes')
                ->where('usuario_id', $usuario->usuario_id)
                ->first();
            if (! $paciente || ! $paciente->portal_activo) {
                throw ValidationException::withMessages([
                    'correo' => ['Tu acceso al portal ha sido desactivado. Contacta al administrador.'],
                ]);
            }
            $usuario->portal_activo = $paciente->portal_activo;
        }

        $usuario->tokens()->delete();

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $usuario,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request)
    {
        $usuario = $request->user();
        if ($usuario->rol === 'fisioterapeuta') {
            $fisio = \Illuminate\Support\Facades\DB::table('fisioterapeutas')
                ->where('usuario_id', $usuario->usuario_id)
                ->first();
            $usuario->activo = $fisio?->activo ?? true;
        }
        if ($usuario->rol === 'paciente') {
            $paciente = \Illuminate\Support\Facades\DB::table('pacientes')
                ->where('usuario_id', $usuario->usuario_id)
                ->first();
            $usuario->portal_activo = $paciente?->portal_activo ?? false;
        }
        return response()->json($usuario);
    }
}
