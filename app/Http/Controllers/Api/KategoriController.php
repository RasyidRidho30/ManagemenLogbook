<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Exception;

#[OA\Tag(name: "Kategori", description: "API Endpoints untuk mengelola Kategori Projek")]
class KategoriController extends Controller
{
    #[OA\Get(
        path: "/api/kategori",
        summary: "Mengambil semua data kategori",
        description: "Bisa ditambahkan query ?active_only=true untuk dropdown form",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "active_only", in: "query", required: false, schema: new OA\Schema(type: "boolean"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Berhasil mengambil data kategori")
        ]
    )]
    public function index(Request $request)
    {
        $query = DB::table('kategori')->whereNull('ktg_deleted_at');

        // Jika request dari dropdown form minta yang aktif saja
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
        summary: "Ambil detail satu kategori",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Berhasil mengambil data"),
            new OA\Response(response: 404, description: "Data tidak ditemukan")
        ]
    )]
    public function show($id)
    {
        $kategori = DB::table('kategori')->where('ktg_id', $id)->first();

        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }

    #[OA\Post(
        path: "/api/kategori",
        summary: "Membuat kategori baru",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nama"],
                properties: [
                    new OA\Property(property: "nama", type: "string", example: "IT & Software"),
                    new OA\Property(property: "deskripsi", type: "string", example: "Kategori untuk projek software")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Kategori berhasil dibuat"),
            new OA\Response(response: 500, description: "Gagal menyimpan")
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
                'message' => 'Kategori berhasil ditambahkan!',
                'data'    => ['id' => $id]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Put(
        path: "/api/kategori/{id}",
        summary: "Update kategori yang sudah ada",
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
            new OA\Response(response: 200, description: "Berhasil diperbarui"),
            new OA\Response(response: 404, description: "Tidak ditemukan")
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
                    return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori berhasil diperbarui!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Delete(
        path: "/api/kategori/{id}",
        summary: "Hapus kategori",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Kategori berhasil dihapus"),
            new OA\Response(response: 404, description: "Kategori tidak ditemukan")
        ]
    )]
    public function destroy($id)
    {
        try {
            $deleted = DB::table('kategori')->where('ktg_id', $id)->delete();

            if (!$deleted) {
                return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori berhasil dihapus!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    #[OA\Patch(
        path: "/api/kategori/{id}/toggle-status",
        summary: "Aktifkan / Nonaktifkan Kategori",
        tags: ["Kategori"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Status berhasil diubah"),
            new OA\Response(response: 404, description: "Kategori tidak ditemukan")
        ]
    )]
    public function toggleStatus($id)
    {
        try {
            // Cari data kategori (pastikan belum di-soft delete)
            $kategori = DB::table('kategori')
                ->where('ktg_id', $id)
                ->whereNull('ktg_deleted_at')
                ->first();

            if (!$kategori) {
                return response()->json(['message' => 'Kategori tidak ditemukan atau sudah dihapus'], 404);
            }

            // Balikkan statusnya (Jika True jadi False, Jika False jadi True)
            $newStatus = !$kategori->ktg_is_active;
            $user = auth()->check() ? auth()->user()->usr_username : 'System';

            DB::table('kategori')->where('ktg_id', $id)->update([
                'ktg_is_active'   => $newStatus,
                'ktg_modified_at' => now(),
                'ktg_modified_by' => $user
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Status kategori berhasil diubah menjadi ' . ($newStatus ? 'Aktif' : 'Nonaktif'),
                'data'    => ['is_active' => $newStatus]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }
}
