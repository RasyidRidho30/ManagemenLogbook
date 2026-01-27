<?php

namespace App\Http\Controllers\Components;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

class ProjekCardController extends Controller
{
    /**
     * Render project cards dari data yang dikirim
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderCards(Request $request)
    {
        $projects = $request->input('projects', []);

        $html = '';

        foreach ($projects as $project) {
            $cardHtml = View::make('components.ProjekCard', [
                'id' => $project['id'] ?? null,
                'nama' => $project['nama'] ?? 'Tanpa Nama',
                'deskripsi' => $project['deskripsi'] ?? '-',
                'progress' => $project['persentase_progress'] ?? 0,
                'tanggalMulai' => $project['tanggal_mulai'] ?? null,
                'tanggalSelesai' => $project['tanggal_selesai'] ?? null,
                'user' => $project['creator_name'] ?? '-',
                'pic' => $project['pic'] ?? '-',
                'leader' => $project['leader_name'] ?? '-',
                'tasksDone' => $project['completed_tasks'] ?? 0,
                'tasksTotal' => $project['total_tasks'] ?? 0,
            ])->render();

            // Bungkus setiap card di column agar tampil 3 per baris pada resolusi md (Bootstrap)
            $html .= '<div class="col-12 col-sm-6 col-md-4">' . $cardHtml . '</div>';
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
