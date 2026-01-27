<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ProjekController extends Controller
{
    #[OA\Get(
        path: "/api/projek",
        tags: ["Projek"],
        summary: "List projects (Filter & Search)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"), description: "Cari nama projek"),
            new OA\Parameter(name: "status", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["InProgress", "Completed", "OnHold"]), description: "Filter status")
        ],
        responses: [new OA\Response(response: 200, description: "List of projects", content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object")))]
    )]
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->usr_id; 
        $userRole = $user->usr_role; 

        // Logika Filter Berdasarkan Role
        if ($userRole === 'admin') {
            $projek = DB::select('CALL sp_read_projek(NULL, ?, ?)', [
                $request->search,
                $request->status
            ]);
        } else {
            $query = "
                SELECT p.* FROM projek p
                INNER JOIN member_projek m ON p.pjk_id = m.pjk_id
                WHERE m.usr_id = ?
            ";

            // Tambahkan filter search dan status jika ada
            if ($request->search) $query .= " AND p.pjk_nama LIKE '%{$request->search}%'";
            if ($request->status) $query .= " AND p.pjk_status = '{$request->status}'";

            $projek = DB::select($query, [$userId]);
        }

        // Enrich data (Tetap sama seperti kode Anda sebelumnya)
        foreach ($projek as $p) {
            // Ambil Statistik
            $stats = DB::select('CALL sp_get_dashboard_card_stats(?)', [$p->pjk_id]);
            $stats = $stats[0] ?? null;
            $p->total_tasks = $stats->total_tasks ?? 0;
            $p->completed_tasks = $stats->completed_tasks ?? 0;

            // Ambil Nama Ketua
            $leader = DB::select('SELECT CONCAT(u.usr_first_name, " ", u.usr_last_name) as leader_name FROM users u JOIN member_projek m ON u.usr_id = m.usr_id WHERE m.pjk_id = ? AND m.mpk_role_projek = "Ketua" LIMIT 1', [$p->pjk_id]);
            $p->leader_name = $leader[0]->leader_name ?? null;

            // Ambil Nama PIC (Member selain Ketua)
            $pic = DB::select('SELECT CONCAT(u.usr_first_name, " ", u.usr_last_name) as pic_name FROM users u JOIN member_projek m ON u.usr_id = m.usr_id WHERE m.pjk_id = ? AND m.mpk_role_projek <> "Ketua" ORDER BY m.mpk_create_at ASC LIMIT 1', [$p->pjk_id]);
            $p->pic_name = $pic[0]->pic_name ?? null;

            // Ambil Nama Pembuat Projek
            $creator = DB::select('SELECT CONCAT(u.usr_first_name, " ", u.usr_last_name) as creator_name FROM users u WHERE u.usr_username = ? LIMIT 1', [$p->pjk_create_by]);
            $p->creator_name = $creator[0]->creator_name ?? null;
        }

        return \App\Http\Resources\ProjekResource::collection(collect($projek));
    }

    #[OA\Post(
        path: "/api/projek",
        tags: ["Projek"],
        summary: "Create a project",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["nama", "pic", "tgl_mulai", "tgl_selesai"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "Sistem Managemen Logbook"),
                    new OA\Property(property: "pic", type: "string", example: "Acid"),
                    new OA\Property(property: "deskripsi", type: "string", example: "Projek untuk mengelola logbook tugas"),
                    new OA\Property(property: "tgl_mulai", type: "string", format: "date", example: "2026-01-10"),
                    new OA\Property(property: "tgl_selesai", type: "string", format: "date", example: "2026-06-30")
                ],
                example: '{"nama":"Sistem Managemen Logbook", "pic":"Acid","deskripsi":"Projek untuk mengelola logbook tugas","tgl_mulai":"2026-01-10","tgl_selesai":"2026-06-30"}'
            )
        ),
        responses: [new OA\Response(response: 201, description: "Projek created"), new OA\Response(response: 400, description: "Validation error")]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'pic' => 'required',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
        ]);

        $user = Auth::user();

        try {
            $result = DB::select('CALL sp_create_projek_with_leader(?, ?, ?, ?, ?, ?, ?)', [
                $request->nama,
                $request->deskripsi ?? '-',
                $request->pic,
                $request->tgl_mulai,
                $request->tgl_selesai,
                $user->usr_id,
                $user->usr_username
            ]);

            return response()->json([
                'message' => 'Projek created successfully',
                'pjk_id' => $result[0]->pjk_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/api/projek/{id}",
        tags: ["Projek"],
        summary: "Get project details",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Project details"), new OA\Response(response: 404, description: "Not Found")]
    )]
    public function show($id)
    {
        // 1. Panggil SP Read Projek (p_pjk_id = $id)
        $result = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);
        $projek = $result[0] ?? null;

        if (!$projek) return response()->json(['message' => 'Not Found'], 404);

        // 2. Ambil Statistik Dashboard
        $stats = DB::select('CALL sp_get_dashboard_card_stats(?)', [$id]);

        // 3. Ambil Breakdown Modul & Kegiatan
        $breakdown = DB::select('CALL sp_get_project_breakdown(?)', [$id]);

        return response()->json([
            'detail' => new \App\Http\Resources\ProjekResource($projek),
            'stats' => $stats[0] ?? null,
            'structure' => $breakdown
        ]);
    }

    #[OA\Put(
        path: "/api/projek/{id}",
        tags: ["Projek"],
        summary: "Update project",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "Nama Projek Baru"),
                    new OA\Property(property: "deskripsi", type: "string", example: "Deskripsi proyek"),
                    new OA\Property(property: "tgl_mulai", type: "string", format: "date", example: "2026-01-10"),
                    new OA\Property(property: "tgl_selesai", type: "string", format: "date", example: "2026-06-30"),
                    new OA\Property(property: "status", type: "string", example: "InProgress")
                ],
                example: '{"nama":"Nama Projek Baru","deskripsi":"Deskripsi proyek","tgl_mulai":"2026-01-10","tgl_selesai":"2026-06-30","status":"InProgress"}'
            )
        ),
        responses: [new OA\Response(response: 200, description: "Projek updated"), new OA\Response(response: 404, description: "Not Found")]
    )]
    public function update(Request $request, $id)
    {
        // Ambil data lama untuk fallback jika parameter tidak dikirim
        $oldData = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id])[0] ?? null;
        if (!$oldData) return response()->json(['message' => 'Not Found'], 404);

        try {
            DB::select('CALL sp_update_projek(?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $request->nama ?? $oldData->pjk_nama,
                $request->pic ?? $oldData->pjk_pic,
                $request->deskripsi ?? $oldData->pjk_deskripsi,
                $request->tgl_mulai ?? $oldData->pjk_tanggal_mulai,
                $request->tgl_selesai ?? $oldData->pjk_tanggal_selesai,
                $request->status ?? $oldData->pjk_status,
                Auth::user()->usr_username
            ]);

            return response()->json(['message' => 'Projek updated']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/projek/{id}",
        tags: ["Projek"],
        summary: "Delete project",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Projek deleted"), new OA\Response(response: 404, description: "Not Found")]
    )]
    public function destroy($id)
    {
        DB::select('CALL sp_delete_projek(?)', [$id]);
        return response()->json(['message' => 'Projek deleted']);
    }

    // ... (GetDashboardStats & Recalculate tetap sama seperti sebelumnya, tidak berubah)
    #[OA\Get(
        path: "/api/projek/{id}/stats",
        tags: ["Projek"],
        summary: "Get Project Dashboard Stats",
        description: "Mengambil statistik ringkas (Total Task, Completed, Progress %) via SP sp_get_dashboard_card_stats",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "ID Projek", schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "pjk_id", type: "integer"),
                        new OA\Property(property: "total_tasks", type: "integer", example: 26),
                        new OA\Property(property: "completed_tasks", type: "integer", example: 18),
                        new OA\Property(property: "project_progress", type: "number", format: "float", example: 75.50)
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Projek tidak ditemukan")
        ]
    )]
    public function getDashboardStats($id)
    {
        $stats = DB::select('CALL sp_get_dashboard_card_stats(?)', [$id]);
        if (empty($stats)) return response()->json(['message' => 'Projek tidak ditemukan atau data kosong'], 404);
        return response()->json($stats[0]);
    }

    #[OA\Get(
        path: "/api/projek/{id}/breakdown",
        tags: ["Projek"],
        summary: "Get Project Breakdown (Modul & Kegiatan)",
        description: "Mengambil detail progress per modul dan kegiatan via SP sp_get_project_breakdown",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "ID Projek", schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(type: "array", items: new OA\Items(properties: [new OA\Property(property: "nama_item", type: "string")]))
            )
        ]
    )]
    public function getProjectBreakdown($id)
    {
        $breakdown = DB::select('CALL sp_get_project_breakdown(?)', [$id]);
        return response()->json($breakdown);
    }

    #[OA\Post(
        path: "/api/projek/{id}/recalculate",
        tags: ["Projek"],
        summary: "Force Recalculate Progress",
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "ID Projek", schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function recalculateProgress($id)
    {
        try {
            DB::select('CALL sp_kalkulasi_progress_projek(?)', [$id]);
            return response()->json(['message' => 'Progress projek berhasil dihitung ulang']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghitung ulang: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/api/projek/{id}/members",
        tags: ["Projek"],
        summary: "Get list of project members",
        description: "Mengambil daftar anggota yang terdaftar dalam projek tertentu",
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
                description: "List of members",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "usr_id", type: "integer"),
                                    new OA\Property(property: "usr_first_name", type: "string"),
                                    new OA\Property(property: "usr_last_name", type: "string"),
                                    new OA\Property(property: "mpk_role_projek", type: "string")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Projek tidak ditemukan")
        ]
    )]
    public function getMembers($id)
    {
        $projekExists = DB::select('SELECT 1 FROM projek WHERE pjk_id = ? LIMIT 1', [$id]);
        if (empty($projekExists)) {
            return response()->json(['message' => 'Projek tidak ditemukan'], 404);
        }

        $members = DB::table('member_projek')
            ->join('users', 'member_projek.usr_id', '=', 'users.usr_id')
            ->where('member_projek.pjk_id', $id)
            ->select(
                'users.usr_id',
                'users.usr_first_name',
                'users.usr_last_name',
                'member_projek.mpk_role_projek',
                'users.usr_role'
            )
            ->get();

        return response()->json(['data' => $members]);
    }
}
