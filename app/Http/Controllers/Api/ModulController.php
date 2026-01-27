<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ModulController extends Controller
{
    #[OA\Get(
        path: "/api/modul",
        tags: ["Modul"],
        summary: "Get list modul",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "pjk_id", in: "query", required: false, schema: new OA\Schema(type: "integer"), description: "Filter by Projek ID"),
            new OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"), description: "Search modul name")
        ],
        responses: [new OA\Response(response: 200, description: "List of modul", content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object")))]
    )]
    public function index(Request $request)
    {
        // Param: p_mdl_id(NULL), p_pjk_id, p_search
        $modul = DB::select('CALL sp_read_modul(NULL, ?, ?)', [
            $request->pjk_id,
            $request->search
        ]);
        return \App\Http\Resources\ModulResource::collection(collect($modul));
    }

    #[OA\Post(
        path: "/api/modul",
        tags: ["Modul"],
        summary: "Create modul",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["pjk_id", "nama", "urut"],
                properties: [
                    new OA\Property(property: "pjk_id", type: "integer", example: 1),
                    new OA\Property(property: "nama", type: "string", example: "Modul Database"),
                    new OA\Property(property: "urut", type: "integer", example: 1)
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Modul created")]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'pjk_id' => 'required|exists:projek,pjk_id',
            'nama' => 'required',
            'urut' => 'required|integer'
        ]);

        try {
            DB::select('CALL sp_create_modul(?, ?, ?, ?)', [
                $request->pjk_id,
                $request->nama,
                $request->urut,
                Auth::user()->usr_username
            ]);
            return response()->json(['message' => 'Modul created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/api/modul/{id}",
        tags: ["Modul"],
        summary: "Get modul detail",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Modul detail")]
    )]
    public function show($id)
    {
        $result = DB::select('CALL sp_read_modul(?, NULL, NULL)', [$id]);
        if (empty($result)) return response()->json(['message' => 'Not Found'], 404);

        return new \App\Http\Resources\ModulResource($result[0]);
    }

    #[OA\Put(
        path: "/api/modul/{id}",
        tags: ["Modul"],
        summary: "Update modul",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nama", type: "string"),
                    new OA\Property(property: "urut", type: "integer")
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Modul updated")]
    )]
    public function update(Request $request, $id)
    {
        // Get old data
        $oldData = DB::select('CALL sp_read_modul(?, NULL, NULL)', [$id])[0] ?? null;
        if (!$oldData) return response()->json(['message' => 'Not Found'], 404);

        try {
            DB::select('CALL sp_update_modul(?, ?, ?, ?)', [
                $id,
                $request->nama ?? $oldData->mdl_nama,
                $request->urut ?? $oldData->mdl_urut,
                Auth::user()->usr_username
            ]);
            return response()->json(['message' => 'Modul updated']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/modul/{id}",
        tags: ["Modul"],
        summary: "Delete modul",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Modul deleted")]
    )]
    public function destroy($id)
    {
        DB::select('CALL sp_delete_modul(?)', [$id]);
        return response()->json(['message' => 'Modul deleted']);
    }
}
