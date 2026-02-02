<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class LogbookController extends Controller
{
    #[OA\Get(
        path: "/api/logbook",
        tags: ["Logbook"],
        security: [["bearerAuth" => []]],
        summary: "Get logbooks (Filter Task/Date/Search)",
        parameters: [
            new OA\Parameter(name: "tgs_id", in: "query", required: false, schema: new OA\Schema(type: "integer"), description: "Filter Task ID"),
            new OA\Parameter(name: "tanggal", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"), description: "Filter Date"),
            new OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"), description: "Search description")
        ],
        responses: [new OA\Response(response: 200, description: "List of logbook entries")]
    )]
    public function index(Request $request)
    {
        try {
            $tgs_id = $request->tgs_id;
            $tanggal = $request->tanggal;
            $search = $request->search;

            if ($tgs_id === '') $tgs_id = null;
            if ($tanggal === '') $tanggal = null;
            if ($search === '') $search = null;

            $logs = DB::select('CALL sp_read_logbook(NULL, ?, ?, ?)', [
                $tgs_id,
                $tanggal,
                $search
            ]);

            return \App\Http\Resources\LogbookResource::collection(collect($logs));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input_params' => $request->all()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/logbook",
        tags: ["Logbook"],
        summary: "Create logbook entry",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["tgs_id", "tanggal", "deskripsi"],
                properties: [
                    new OA\Property(property: "tgs_id", type: "integer"),
                    new OA\Property(property: "tanggal", type: "string", format: "date"),
                    new OA\Property(property: "deskripsi", type: "string"),
                    new OA\Property(property: "komentar", type: "string"),
                    new OA\Property(property: "progress", type: "integer")
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Logbook entry created")]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'tgs_id' => 'required|exists:tugas,tgs_id',
            'tanggal' => 'required|date',
            'deskripsi' => 'required',
            'progress' => 'nullable|integer|min:0|max:100'
        ]);

        try {
            DB::select('CALL sp_create_logbook(?, ?, ?, ?, ?)', [
                $request->tgs_id,
                $request->tanggal,
                $request->deskripsi,
                $request->komentar ?? '',
                $request->progress ?? 0
            ]);
            return response()->json(['message' => 'Logbook entry created'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/api/logbook/{id}",
        tags: ["Logbook"],
        summary: "Update logbook entry (comment)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "lbk_komentar", type: "string")
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Logbook entry updated")]
    )]
    public function update($id, Request $request)
    {
        try {
            // Fetch current logbook data
            $logbook = DB::select('SELECT lbk_tanggal, lbk_deskripsi, lbk_progress FROM logbook WHERE lbk_id = ?', [$id]);

            if (empty($logbook)) {
                return response()->json(['message' => 'Logbook entry not found'], 404);
            }

            $current = $logbook[0];
            $komentar = $request->lbk_komentar ?? '';

            // Call sp_update_logbook with all 5 parameters
            DB::select('CALL sp_update_logbook(?, ?, ?, ?, ?)', [
                $id,
                $current->lbk_tanggal,
                $current->lbk_deskripsi,
                $komentar,
                $current->lbk_progress
            ]);

            return response()->json(['message' => 'Comment updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
