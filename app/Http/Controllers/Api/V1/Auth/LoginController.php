<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::where(
            'email',
            $credentials['email']
        )->first();

        if (
            !$user ||
            !Hash::check(
                $credentials['password'],
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Pastikan bukan admin
        |--------------------------------------------------------------------------
        */

        if ((int) $user->role_id !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak memiliki akses user.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus token lama
        |--------------------------------------------------------------------------
        */

        $user->tokens()
            ->where('name', 'user-api')
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Token baru
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken(
            'user-api'
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }
}