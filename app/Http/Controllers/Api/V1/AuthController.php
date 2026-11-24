<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::with('rt')->where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Username atau password salah.', 401);
        }

        if (! $user->is_active) {
            return $this->errorResponse('Akun Anda sedang dinonaktifkan.', 403);
        }

        $deviceName = $validated['device_name'] ?? 'flutter-mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ], 'Login berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('rt');

        return $this->successResponse($this->formatUser($user), 'Profil pengguna berhasil dimuat.');
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(null, 'Logout berhasil.');
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'rt_id' => $user->rt_id,
            'rt' => $user->rt,
            'permissions' => $user->permissions ?? [],
            'is_active' => $user->is_active,
        ];
    }
}

