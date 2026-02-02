<?php

namespace App\Http\Controllers\Views;

use App\Http\Controllers\Controller;
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
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);
        if (empty($projek)) abort(404);
        $projek = $projek[0];

        $moduls = DB::select('CALL sp_read_modul(NULL, ?, NULL)', [$id]);

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

    public function list($id)
    {
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);
        if (empty($projek)) abort(404);
        $projek = $projek[0];

        $moduls = DB::select('CALL sp_read_modul(NULL, ?, NULL)', [$id]);

        foreach ($moduls as $modul) {
            $modul->kegiatans = DB::select('SELECT * FROM kegiatan WHERE mdl_id = ?', [$modul->mdl_id]);

            foreach ($modul->kegiatans as $kegiatan) {
                $kegiatan->tugas = DB::select('CALL sp_read_tugas(NULL, ?, NULL, NULL, NULL)', [$kegiatan->kgt_id]);
            }
        }

        return view('ProjekPage.List', [
            'projek' => $projek,
            'moduls' => $moduls,
            'activeMenu' => 'list',
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

    public function logbook($id)
    {
        $projek = DB::select('CALL sp_read_projek(?, NULL, NULL)', [$id]);

        $daftarTugas = DB::table('tugas')
            ->join('kegiatan', 'tugas.kgt_id', '=', 'kegiatan.kgt_id')
            ->join('modul', 'kegiatan.mdl_id', '=', 'modul.mdl_id')
            ->where('modul.pjk_id', $id)
            ->select('tugas.tgs_id', 'tugas.tgs_nama', 'tugas.tgs_kode_prefix')
            ->get();

        if (empty($projek)) {
            abort(404, 'Projek tidak ditemukan');
        }
        $projek = $projek[0];

        $logbooks = DB::table('logbook')
            ->join('tugas', 'logbook.tgs_id', '=', 'tugas.tgs_id')
            ->join('kegiatan', 'tugas.kgt_id', '=', 'kegiatan.kgt_id')
            ->join('modul', 'kegiatan.mdl_id', '=', 'modul.mdl_id')
            ->join('users', 'tugas.usr_id', '=', 'users.usr_id')
            ->where('modul.pjk_id', $id)
            ->select(
                'logbook.lbk_id',
                'logbook.lbk_tanggal',
                'logbook.lbk_deskripsi',
                'logbook.lbk_komentar',
                'logbook.lbk_progress',
                'tugas.tgs_nama',
                'tugas.tgs_kode_prefix',
                'tugas.tgs_status',
                'tugas.tgs_tanggal_mulai',
                'tugas.tgs_tanggal_selesai',
                'users.usr_first_name',
                'users.usr_last_name',
                'users.usr_avatar_url',
                DB::raw('CONCAT(users.usr_first_name, " ", users.usr_last_name) as pic_name')
            )
            ->orderBy('logbook.lbk_tanggal', 'desc')
            ->get();

        return view('ProjekPage.Logbook', [
            'projek'     => $projek,
            'logbooks'   => $logbooks,
            'activeMenu' => 'logbook',
            'projectId'  => $id,
            'tugas'      => $daftarTugas
        ]);
    }
}
