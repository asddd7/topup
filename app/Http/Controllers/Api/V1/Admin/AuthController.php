<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Admin Login
     */
    public function login(Request $request)
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (!Hash::check(
            $credentials['password'],
            $user->password
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /**
         * Pastikan user adalah admin
         */
        if ((int) $user->role_id !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Akun bukan administrator.',
            ], 403);
        }

        /**
         * Hapus token lama
         *
         * Opsional, tetapi bagus untuk testing.
         */
        $user->tokens()->delete();

        /**
         * Buat token baru
         */
        $token = $user->createToken(
            'admin-api'
        )->plainTextToken;

        return response()->json([
            'success' => true,

            'message' => 'Login admin berhasil.',

            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                ],

                'token' => $token,
            ],
        ]);
    }


    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        /**
         * Hapus token yang sedang digunakan
         */
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }


    /**
     * Admin profile
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ],
        ]);
    }
}