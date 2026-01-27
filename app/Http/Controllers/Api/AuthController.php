<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/login",
        tags: ["Authentication"],
        summary: "Login User menggunakan Email atau Username",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["login_identity", "password"],
                properties: [
                    new OA\Property(property: "login_identity", type: "string", example: "rasyid_ridho atau rasyid@polman.astra.ac.id"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login Berhasil"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'login_identity' => 'required|string',
            'password' => 'required',
        ]);

        $fieldType = filter_var($request->login_identity, FILTER_VALIDATE_EMAIL) ? 'usr_email' : 'usr_username';

        if (Auth::attempt([$fieldType => $request->login_identity, 'password' => $request->password])) {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login success',
                'access_token' => $token,
                'user' => new \App\Http\Resources\UserResource($user)
            ]);
        }

        return response()->json(['message' => 'Email/Username atau Password salah'], 401);
    }


    #[OA\Post(
        path: "/api/logout",
        tags: ["Authentication"],
        summary: "Logout User",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout Berhasil"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return new \App\Http\Resources\UserResource($request->user());
    }
}
