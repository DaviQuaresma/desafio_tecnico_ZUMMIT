<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'name.required' => 'O nome é obrigatório.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'O e-mail deve ser válido.',
                'email.unique' => 'Este e-mail já está em uso.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $result = $this->authService->register($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso.',
            'data' => $result,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'O e-mail deve ser válido.',
                'password.required' => 'A senha é obrigatória.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $result = $this->authService->login($validated);

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'data' => $result,
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Token atualizado com sucesso.',
            'data' => $result,
        ]);
    }

    public function me(): JsonResponse
    {
        $result = $this->authService->me();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
