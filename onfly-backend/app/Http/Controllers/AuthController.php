<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = auth('api')->login($user);

            return response()->json([
                'message' => 'Usuário registrado com sucesso.',
                'access_token' => $token
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors() // <-- já traz erros individuais por campo
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erro no banco de dados.',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.'
                ], 401);
            }

            return response()->json([
                'message' => 'Login realizado com sucesso.',
                'access_token' => $token
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    public function refresh()
    {
        return response()->json(['access_token' => auth('api')->refresh()]);
    }
}
