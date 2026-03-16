<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Exception;

#[OA\Tag(name: "Kategori", description: "API Endpoints to manage Project Categories")]
class KategoriController extends Controller
{
    #[OA\Get(
        path: "/api/kategori",
        summary: "Retrieve all categories",
        description: "Add query ?active_only=true for dropdown forms",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "active_only", in: "query", required: false, schema: new OA\Schema(type: "boolean"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successfully retrieved categories")
        ]
    )]
    public function index(Request $request)
    {
        $query = DB::table('kategori')->whereNull('ktg_deleted_at');

        if ($request->query('active_only') == 'true') {
            $query->where('ktg_is_active', true);
        }

        $kategori = $query->orderBy('ktg_nama', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }

    #[OA\Get(
        path: "/api/kategori/{id}",
        summary: "Retrieve a single category detail",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successfully retrieved data"),
            new OA\Response(response: 404, description: "Data not found")
        ]
    )]
    public function show($id)
    {
        $kategori = DB::table('kategori')->where('ktg_id', $id)->first();

        if (!$kategori) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }

    #[OA\Post(
        path: "/api/kategori",
        summary: "Create a new category",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "IT & Software"),
                    new OA\Property(property: "deskripsi", type: "string", example: "Category for software projects")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Category successfully created"),
            new OA\Response(response: 500, description: "Failed to save")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string'
        ]);

        try {
            $user = auth()->check() ? auth()->user()->usr_username : 'System';

            $id = DB::table('kategori')->insertGetId([
                'ktg_nama'      => $request->nama,
                'ktg_deskripsi' => $request->deskripsi,
                'ktg_create_at' => now(),
                'ktg_create_by' => $user
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Category successfully added!',
                'data'    => ['id' => $id]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save category: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/kategori/{id}",
        summary: "Update an existing category",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "IT Development Updated"),
                    new OA\Property(property: "deskripsi", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Successfully updated"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'nullable|string'
        ]);

        try {
            $user = auth()->check() ? auth()->user()->usr_username : 'System';

            $updated = DB::table('kategori')->where('ktg_id', $id)->update([
                'ktg_nama'        => $request->nama,
                'ktg_deskripsi'   => $request->deskripsi,
                'ktg_modified_at' => now(),
                'ktg_modified_by' => $user
            ]);

            if ($updated === 0) {
                $exists = DB::table('kategori')->where('ktg_id', $id)->exists();
                if (!$exists) {
                    return response()->json(['message' => 'Category not found'], 404);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Category successfully updated!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/kategori/{id}",
        summary: "Delete a category",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Category successfully deleted"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function destroy($id)
    {
        try {
            $deleted = DB::table('kategori')->where('ktg_id', $id)->delete();

            if (!$deleted) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Category successfully deleted!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Patch(
        path: "/api/kategori/{id}/toggle-status",
        summary: "Enable / Disable Category",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Status successfully changed"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function toggleStatus($id)
    {
        try {
            $kategori = DB::table('kategori')
                ->where('ktg_id', $id)
                ->whereNull('ktg_deleted_at')
                ->first();

            if (!$kategori) {
                return response()->json(['message' => 'Category not found or already deleted'], 404);
            }

            $newStatus = !$kategori->ktg_is_active;
            $user = auth()->check() ? auth()->user()->usr_username : 'System';

            DB::table('kategori')->where('ktg_id', $id)->update([
                'ktg_is_active'   => $newStatus,
                'ktg_modified_at' => now(),
                'ktg_modified_by' => $user
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Category status successfully changed to ' . ($newStatus ? 'Active' : 'Inactive'),
                'data'    => ['is_active' => $newStatus]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to change status: ' . $e->getMessage()
            ], 500);
        }
    }
}
