<?php

namespace App\Http\Controllers\Views;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjekViewController extends Controller
{
    public function dashboard($id)
    {
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);

        if (empty($projek)) {
            abort(404, 'Projek tidak ditemukan');
        }
        $projek = $projek[0];

        $stats = DB::select('CALL sp_get_dashboard_card_stats(?)', [$id]);
        $stats = $stats[0] ?? null;

        $breakdown = DB::select('CALL sp_get_project_breakdown(?)', [$id]);

        $team = DB::select("
            SELECT 
                u.usr_first_name, 
                u.usr_last_name, 
                u.usr_role, 
                u.usr_avatar_url, 
                mp.mpk_role_projek 
            FROM member_projek mp
            JOIN users u ON mp.usr_id = u.usr_id
            WHERE mp.pjk_id = ?
            ORDER BY FIELD(mp.mpk_role_projek, 'Ketua', 'Member') ASC
        ", [$id]);

        return view('ProjekPage.Dashboard', [
            'projek' => $projek,
            'stats' => $stats,
            'breakdown' => $breakdown,
            'team' => $team,
            'activeMenu' => 'beranda'
        ]);
    }

    public function jobs($id)
    {
        // 1. Ambil data Projek utama
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);
        if (empty($projek)) abort(404);
        $projek = $projek[0];

        // 2. Ambil daftar Modul berdasarkan ID Projek
        $moduls = DB::select('CALL sp_read_modul(NULL, ?, NULL)', [$id]);

        // 3. Ambil Detail (Hierarchy: Modul -> Kegiatan -> Tugas)
        foreach ($moduls as $modul) {
            $modul->kegiatans = DB::select('SELECT * FROM kegiatan WHERE mdl_id = ?', [$modul->mdl_id]);

            foreach ($modul->kegiatans as $kegiatan) {
                $kegiatan->tugas = DB::select('CALL sp_read_tugas(NULL, ?, NULL, NULL, NULL)', [$kegiatan->kgt_id]);
            }
        }

        return view('ProjekPage.Jobs', [
            'projek' => $projek,
            'moduls' => $moduls,
            'activeMenu' => 'jobs',
            'projectId' => $id
        ]);
    }

    public function edit($id)
    {
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);

        if (empty($projek)) {
            abort(404, 'Projek tidak ditemukan');
        }

        $projek = $projek[0];

        return view('ProjekPage.Edit', [
            'projek' => $projek,
            'projectId' => $id,
            'activeMenu' => 'edit'
        ]);
    }
}
