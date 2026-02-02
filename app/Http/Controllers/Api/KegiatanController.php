<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class KegiatanController extends Controller
{
    #[OA\Get(
        path: "/api/kegiatan",
        tags: ["Kegiatan"],
        summary: "Get list kegiatan (Filter by Modul)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "mdl_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "List kegiatan")]
    )]
    public function index(Request $request)
    {
        $query = DB::table('kegiatan');
        if ($request->has('mdl_id')) {
            $query->where('mdl_id', $request->mdl_id);
        }
        $kegiatan = $query->get();
        return \App\Http\Resources\KegiatanResource::collection($kegiatan);
    }

    #[OA\Post(
        path: "/api/kegiatan",
        tags: ["Kegiatan"],
        summary: "Create kegiatan with auto-generated prefix (e.g., I.A)",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["mdl_id", "nama"],
                properties: [
                    new OA\Property(property: "mdl_id", type: "integer", example: 1),
                    new OA\Property(property: "nama", type: "string", example: "Kegiatan Setup Database")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Kegiatan created successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]

    public function store(Request $request)
    {
        $request->validate([
            'mdl_id' => 'required|exists:modul,mdl_id',
            'nama' => 'required|string|max:255',
        ]);

        try {
            $result = DB::select('CALL sp_create_kegiatan(?, ?, ?)', [
                $request->mdl_id,
                $request->nama,
                Auth::user()->usr_username
            ]);

            $newKgtId = $result[0]->new_kgt_id ?? null;

            if (!$newKgtId) {
                throw new \Exception("Gagal mendapatkan ID kegiatan baru.");
            }

            $kgt = DB::table('kegiatan')->where('kgt_id', $newKgtId)->first();

            return (new \App\Http\Resources\KegiatanResource($kgt))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/kegiatan/{id}",
        tags: ["Kegiatan"],
        summary: "Update kegiatan name",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["nama"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "Nama Kegiatan Baru")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Kegiatan updated successfully"),
            new OA\Response(response: 404, description: "Kegiatan not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kegiatan = DB::table('kegiatan')->where('kgt_id', $id)->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan not found'], 404);
        }

        try {
            DB::table('kegiatan')
                ->where('kgt_id', $id)
                ->update([
                    'kgt_nama' => $request->nama,
                    'kgt_modified_at' => now(),
                    'kgt_modified_by' => Auth::user()->usr_username
                ]);

            $updated = DB::table('kegiatan')->where('kgt_id', $id)->first();

            return response()->json([
                'message' => 'Kegiatan updated successfully',
                'data' => new \App\Http\Resources\KegiatanResource($updated)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal update kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/kegiatan/{id}",
        tags: ["Kegiatan"],
        summary: "Delete kegiatan",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Kegiatan deleted successfully"),
            new OA\Response(response: 404, description: "Kegiatan not found")
        ]
    )]
    public function destroy($id)
    {
        $kegiatan = DB::table('kegiatan')->where('kgt_id', $id)->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan not found'], 404);
        }

        try {
            DB::table('kegiatan')->where('kgt_id', $id)->delete();

            return response()->json([
                'message' => 'Kegiatan deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal delete kegiatan: ' . $e->getMessage()
            ], 500);
        }
    }
}