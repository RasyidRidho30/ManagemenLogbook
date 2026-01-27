<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Mahasiwa;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MahasiswaController extends Controller
{
    #[OA\Get(
        path: "/api/mahasiswa",
        tags: ["Mahasiswa"],
        summary: "Get all mahasiswa",
        responses: [
            new OA\Response(response: 200, description: "List of mahasiswa", content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object")))
        ]
    )]
    public function listMahasiswa()
    {
        $mahasiswa = Mahasiwa::all(['mhs_id', 'mhs_nim', 'mhs_nama']);
        return response()->json([
            'success' => true,
            'data' => $mahasiswa,
            'message' => 'Data mahasiswa berhasil diambil'
        ]);
    }
}