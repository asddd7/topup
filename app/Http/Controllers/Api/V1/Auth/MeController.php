<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }
}