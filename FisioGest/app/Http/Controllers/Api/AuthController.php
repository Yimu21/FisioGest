<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:100',
            'correo'     => 'required|email|unique:users,email',
            'contraseña' => 'required|string|min:8|confirmed',
            'rol'        => 'sometimes|in:admin,fisioterapeuta,paciente',
        ]);

        $user = User::create([
            'name'     => $data['nombre'],
            'email'    => $data['correo'],
            'password' => Hash::make($data['contraseña']),
            'rol'      => $data['rol'] ?? 'paciente',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'correo'     => 'required|email',
            'contraseña' => 'required|string',
        ]);

        $user = User::where('email', $data['correo'])->first();

        if (! $user || ! Hash::check($data['contraseña'], $user->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales no son correctas.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
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
        return response()->json($request->user());
    }
}
