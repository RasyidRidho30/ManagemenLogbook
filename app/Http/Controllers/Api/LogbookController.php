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
                    new OA\Property(property: "progress", type: "integer"),
                    new OA\Property(property: "evidence_link", type: "string", format: "uri", description: "Optional link to a Drive file/evidence")
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Logbook entry created")]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'tgs_id'        => 'required|exists:tugas,tgs_id',
            'tanggal'       => 'required|date',
            'deskripsi'     => 'required',
            'progress'      => 'nullable|integer|min:0|max:100',
            'evidence_link' => 'nullable|url'
        ]);

        try {
            $tgsId        = $request->tgs_id;
            $tanggal      = $request->tanggal;
            $progress     = $request->progress ?? 0;
            $evidenceLink = $request->evidence_link ?? null;

            // Rule 3: Cek total progress task ini, tidak boleh sudah >= 100
            $totalProgress = DB::table('logbook')
                ->where('tgs_id', $tgsId)
                ->sum('lbk_progress');

            if ($totalProgress >= 100) {
                return response()->json([
                    'message' => 'Task ini sudah mencapai 100% progress dan tidak bisa ditambah entry baru.'
                ], 422);
            }

            // Rule 3: Pastikan total setelah ditambah tidak melebihi 100
            if (($totalProgress + $progress) > 100) {
                return response()->json([
                    'message' => "Progress melebihi batas. Sisa progress yang bisa diinput: " . (100 - $totalProgress) . "%"
                ], 422);
            }

            // Rule 1: Cek apakah sudah ada entry untuk task + tanggal yang sama
            $existingSameDay = DB::table('logbook')
                ->where('tgs_id', $tgsId)
                ->whereDate('lbk_tanggal', $tanggal)
                ->first();

            if ($existingSameDay) {
                return response()->json([
                    'message' => 'Entry untuk task ini di tanggal yang sama sudah ada. Silakan edit entry tersebut.'
                ], 422);
            }

            DB::select('CALL sp_create_logbook(?, ?, ?, ?, ?, ?)', [
                $tgsId,
                $tanggal,
                $request->deskripsi,
                $request->komentar ?? '',
                $progress,
                $evidenceLink
            ]);

            return response()->json(['message' => 'Logbook entry created'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/api/logbook/{id}",
        tags: ["Logbook"],
        summary: "Update logbook entry (comment and/or progress)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "lbk_komentar", type: "string", example: "Sudah selesai review"),
                    new OA\Property(property: "lbk_progress", type: "integer", minimum: 0, maximum: 100, example: 75),
                    new OA\Property(property: "evidence_link", type: "string", format: "uri", description: "Link to proof (e.g. Drive) when progress is 100%")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Logbook entry updated"),
            new OA\Response(response: 404, description: "Logbook entry not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update($id, Request $request)
    {
        $request->validate([
            'lbk_komentar'      => 'nullable|string',
            'lbk_progress'      => 'nullable|integer|min:0|max:100',
            'evidence_link'     => 'nullable|url',
            'lbk_evidence_link' => 'nullable|url'
        ]);

        try {
            $logbook = DB::select('SELECT lbk_tanggal, lbk_deskripsi, lbk_komentar, lbk_progress, lbk_evidence_link, tgs_id FROM logbook WHERE lbk_id = ?', [$id]);

            if (empty($logbook)) {
                return response()->json(['message' => 'Logbook entry not found'], 404);
            }

            $current = $logbook[0];

            // Rule 1: Hanya boleh edit jika tanggal entry = hari ini
            if ($request->has('lbk_progress')) {
                $today       = now()->toDateString();
                $entryDate   = \Carbon\Carbon::parse($current->lbk_tanggal)->toDateString();

                if ($entryDate !== $today) {
                    return response()->json([
                        'message' => 'Progress hanya bisa diedit pada hari yang sama dengan tanggal entry.'
                    ], 422);
                }

                // Rule 3: Hitung total progress task ini DILUAR entry yang sedang diedit
                $totalOther = DB::table('logbook')
                    ->where('tgs_id', $current->tgs_id)
                    ->where('lbk_id', '!=', $id)
                    ->sum('lbk_progress');

                if (($totalOther + $request->lbk_progress) > 100) {
                    return response()->json([
                        'message' => "Total progress task melebihi 100%. Maksimal yang bisa diinput: " . (100 - $totalOther) . "%"
                    ], 422);
                }
            }

            $komentar = $request->has('lbk_komentar') ? $request->lbk_komentar : $current->lbk_komentar;
            $progress = $request->has('lbk_progress') ? $request->lbk_progress : $current->lbk_progress;
            $evidenceLink = $request->filled('evidence_link')
                ? $request->evidence_link
                : ($request->filled('lbk_evidence_link') ? $request->lbk_evidence_link : $current->lbk_evidence_link);

            DB::select('CALL sp_update_logbook(?, ?, ?, ?, ?, ?)', [
                $id,
                $current->lbk_tanggal,
                $current->lbk_deskripsi,
                $komentar ?? '',
                $progress ?? 0,
                $evidenceLink
            ]);

            return response()->json([
                'message' => 'Logbook entry updated successfully',
                'data'    => [
                    'lbk_id'        => (int) $id,
                    'lbk_komentar'  => $komentar,
                    'lbk_progress'  => $progress,
                    'evidence_link' => $evidenceLink
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/api/projek/{id}/logbook",
        tags: ["Logbook"],
        summary: "Get logbooks by Project ID",
        description: "Mengambil daftar logbook khusus untuk projek tertentu",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID Projek",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of logbooks",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Success"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "lbk_id", type: "integer"),
                                    new OA\Property(property: "lbk_tanggal", type: "string", format: "date"),
                                    new OA\Property(property: "lbk_deskripsi", type: "string"),
                                    new OA\Property(property: "tgs_nama", type: "string"),
                                    new OA\Property(property: "pic_name", type: "string")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Project not found")
        ]
    )]
    public function getByProject(Request $request, $id) // Tambahkan Request $request
    {
        $query = DB::table('logbook')
            ->join('tugas', 'logbook.tgs_id', '=', 'tugas.tgs_id')
            ->join('kegiatan', 'tugas.kgt_id', '=', 'kegiatan.kgt_id')
            ->join('modul', 'kegiatan.mdl_id', '=', 'modul.mdl_id')
            ->leftJoin('users', 'tugas.usr_id', '=', 'users.usr_id')
            ->where('modul.pjk_id', $id)
            ->select(
    'logbook.lbk_id',
    'logbook.tgs_id',        // ← TAMBAH INI eksplisit
    'logbook.lbk_tanggal',
    'logbook.lbk_deskripsi',
    'logbook.lbk_komentar',
    'logbook.lbk_progress',
    'logbook.lbk_evidence_link',
    'tugas.tgs_nama',
    'tugas.tgs_kode_prefix',
    'tugas.tgs_tanggal_mulai',
    'tugas.tgs_tanggal_selesai',
    DB::raw("CONCAT(users.usr_first_name, ' ', users.usr_last_name) as pic_name")
);

        // --- FILTER LOGIC ---

        // Filter by Task ID
        if ($request->has('tgs_id') && $request->tgs_id != '') {
            $query->where('logbook.tgs_id', $request->tgs_id);
        }

        // Filter by Date
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('logbook.lbk_tanggal', $request->tanggal);
        }

        // Filter by Search (Deskripsi)
        if ($request->has('search') && $request->search != '') {
            $query->where('logbook.lbk_deskripsi', 'like', '%' . $request->search . '%');
        }

        // --- END FILTER LOGIC ---

        $logbooks = $query->orderBy('logbook.lbk_tanggal', 'desc')
            // ->orderBy('logbook.lbk_create_at', 'desc') // Uncomment jika ada
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $logbooks
        ]);
    }




    #[OA\Get(
        path: "/api/logbook/task-progress/{tgsId}",
        tags: ["Logbook"],
        summary: "Get total progress & today entry for a task",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "tgsId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "Task progress info")]
    )]
    public function taskProgress($tgsId)
    {
        $total = DB::table('logbook')
            ->where('tgs_id', $tgsId)
            ->sum('lbk_progress');

        $todayEntry = DB::table('logbook')
            ->where('tgs_id', $tgsId)
            ->whereDate('lbk_tanggal', now()->toDateString())
            ->first();

        return response()->json([
            'tgs_id'         => (int) $tgsId,
            'total_progress' => (int) $total,
            'is_completed'   => $total >= 100,
            'today_entry'    => $todayEntry ? [
                'lbk_id'       => $todayEntry->lbk_id,
                'lbk_progress' => $todayEntry->lbk_progress,
            ] : null
        ]);
    }







    #[OA\Get(
    path: "/api/logbook/task-progress-by-lbk/{lbkId}",
    tags: ["Logbook"],
    summary: "Get total progress task by logbook ID",
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(name: "lbkId", in: "path", required: true, schema: new OA\Schema(type: "integer"))
    ],
    responses: [new OA\Response(response: 200, description: "Task progress info")]
)]
public function taskProgressByLbk($lbkId)
{
    $logbook = DB::table('logbook')->where('lbk_id', $lbkId)->first();

    if (!$logbook) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $totalProgress = DB::table('logbook')
        ->where('tgs_id', $logbook->tgs_id)
        ->sum('lbk_progress');

    return response()->json([
        'lbk_id'                 => (int) $lbkId,
        'tgs_id'                 => (int) $logbook->tgs_id,
        'total_progress'         => (int) $totalProgress,
        'current_entry_progress' => (int) $logbook->lbk_progress,
        'is_completed'           => $totalProgress >= 100,
    ]);
}
}
