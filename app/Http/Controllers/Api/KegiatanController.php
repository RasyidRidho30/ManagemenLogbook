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
}