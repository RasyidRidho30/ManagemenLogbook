<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/users",
        tags: ["Users"],
        summary: "Get all users",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of users", content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object")))
        ]
    )]
    public function index()
    {
        // Get all users
        $users = DB::table('users')->select('usr_id', 'usr_username', 'usr_first_name', 'usr_last_name', 'usr_email', 'usr_role')->get();
        return \App\Http\Resources\UserResource::collection($users);
    }

    #[OA\Post(
        path: "/api/users",
        tags: ["Users"],
        summary: "Create a new user (public registration)",
        description: "Register a new user. The 'role' will be set to 'user' by the server regardless of client input.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "email", "password", "first_name"],
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                    new OA\Property(property: "first_name", type: "string"),
                    new OA\Property(property: "last_name", type: "string"),
                    new OA\Property(property: "role", type: "string", description: "Optional, will be ignored and set to 'user' by the server", example: "user")
                ],
                example: '{"username":"jdoe","email":"jdoe@example.com","password":"secret123","first_name":"John","last_name":"Doe"}'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User created"),
            new OA\Response(response: 400, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,usr_username',
            'email' => 'required|email|unique:users,usr_email',
            'password' => 'required|min:6',
            'first_name' => 'required'
        ]);

        try {
            // Role is forced to 'user'
            $role = 'user';

            // Panggil SP Create User
            $result = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', [
                $request->username,
                $request->email,
                Hash::make($request->password),
                $request->first_name,
                $request->last_name ?? '',
                $role
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'usr_id' => $result[0]->new_usr_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: "/api/profile/update",
        tags: ["Users"],
        summary: "Update current user profile",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "first_name", type: "string"),
                        new OA\Property(property: "last_name", type: "string"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "password", type: "string", format: "password"),
                        new OA\Property(property: "password_confirmation", type: "string"),
                        new OA\Property(property: "avatar", type: "string", format: "binary", description: "Profile picture file")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile updated successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]

    public function update(Request $request)
    {
        $userId = Auth::user()->usr_id;

        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,usr_email,' . $userId . ',usr_id',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user = DB::table('users')->where('usr_id', $userId)->first();

            // Validasi current password jika ingin mengubah password
            if ($request->filled('password')) {
                if (!$request->filled('current_password')) {
                    return response()->json([
                        'message' => 'Current password is required to change password'
                    ], 422);
                }

                if (!\Hash::check($request->current_password, $user->usr_password)) {
                    return response()->json([
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
            }

            $avatarUrl = $user->usr_avatar_url;

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $fileName = time() . '_' . $userId . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/avatars'), $fileName);
                $avatarUrl = asset('storage/avatars/' . $fileName);
            }

            DB::select('CALL sp_update_user_profile(?, ?, ?, ?, ?, ?)', [
                $userId,
                $request->first_name,
                $request->last_name ?? '',
                $request->email,
                $request->filled('password') ? Hash::make($request->password) : null,
                $avatarUrl
            ]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'avatar_url' => $avatarUrl
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }
}
