<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // =================================================================
        // 0. CLEANUP (Hapus Data Lama)
        // =================================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('projek')->truncate();
        DB::table('member_projek')->truncate();
        DB::table('modul')->truncate();
        DB::table('kegiatan')->truncate();
        DB::table('tugas')->truncate();
        DB::table('logbook')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🧹 Database berhasil dibersihkan.');

        // =================================================================
        // 1. CREATE USERS
        // =================================================================
        $password = Hash::make('password123');
        $pswAdmin = Hash::make('admin');

        $u1 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['rasyid_ridho', 'rasyid@polman.astra.ac.id', $password, 'Rasyid', 'Ridho', 'User']);
        $idRasyid = $u1[0]->new_usr_id;

        $u2 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['raihan_masri', 'raihan@example.com', $password, 'Raihan', 'Masri', 'User']);
        $idRaihan = $u2[0]->new_usr_id;

        $u3 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['yuwan_yoga', 'yuwan@example.com', $password, 'Yuwan', 'Yoga', 'User']);
        $idYuwan = $u3[0]->new_usr_id;

        $u4 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['fakhri_majid', 'fakhri@example.com', $password, 'Fakhri', 'Majid', 'User']);
        $idFakhri = $u4[0]->new_usr_id;

        $u5 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['pinow_tems', 'pinow@example.com', $password, 'Pinow', 'Tems', 'User']);
        $idPinow = $u5[0]->new_usr_id;

        $u6 = DB::select('CALL sp_create_user(?, ?, ?, ?, ?, ?)', ['admin', 'admin@admin.com', $pswAdmin, 'Admin', '', 'Admin']);

        $this->command->info('✅ 5 User berhasil dibuat.');

        // =================================================================
        // PROJEK 1: SISTEM MANAJEMEN LOGBOOK
        // =================================================================
        $pjk1 = DB::select('CALL sp_create_projek_with_leader(?, ?, ?, ?, ?, ?, ?)', [
            'Sistem Manajemen Logbook',
            'Aplikasi monitoring aktivitas magang mahasiswa',
            'Raihan Ramadhan',
            '2026-02-01',
            '2026-06-30',
            $idRasyid,
            'rasyid_ridho'
        ]);
        $idPjkLogbook = $pjk1[0]->pjk_id;

        DB::table('member_projek')->insert([
            ['usr_id' => $idRaihan, 'pjk_id' => $idPjkLogbook, 'mpk_role_projek' => 'Backend Dev', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
            ['usr_id' => $idYuwan, 'pjk_id' => $idPjkLogbook, 'mpk_role_projek' => 'Frontend Dev', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
        ]);

        // MODUL 1 (Urutan 1 -> Prefix Romawi "I")
        $mdlLogbook = DB::table('modul')->insertGetId([
            'pjk_id' => $idPjkLogbook,
            'mdl_nama' => 'Modul Laporan',
            'mdl_urut' => 1,
            'mdl_create_at' => now(),
            'mdl_create_by' => 'rasyid_ridho'
        ]);

        // KEGIATAN (Otomatis: I.A dan I.B)
        $kgt1 = DB::select('CALL sp_create_kegiatan(?, ?, ?)', [$mdlLogbook, 'Perancangan Database', 'rasyid_ridho']);
        $idKgtDesign = $kgt1[0]->new_kgt_id;

        $kgt2 = DB::select('CALL sp_create_kegiatan(?, ?, ?)', [$mdlLogbook, 'Development API', 'rasyid_ridho']);
        $idKgtApi = $kgt2[0]->new_kgt_id;

        // TUGAS (Otomatis: I.A1, I.B1, I.B2)
        $t1 = DB::select('CALL sp_create_tugas(?, ?, ?, ?, ?, ?, ?)', [$idKgtDesign, $idRaihan, 'Membuat ERD dan Schema MySQL', '2026-02-02', '2026-02-05', 10, 'rasyid_ridho']);
        $idTugas1 = $t1[0]->new_tgs_id;

        $t2 = DB::select('CALL sp_create_tugas(?, ?, ?, ?, ?, ?, ?)', [$idKgtApi, $idYuwan, 'Setup Laravel 11 dan Sanctum', '2026-02-06', '2026-02-07', 5, 'rasyid_ridho']);
        $idTugas2 = $t2[0]->new_tgs_id;

        $t3 = DB::select('CALL sp_create_tugas(?, ?, ?, ?, ?, ?, ?)', [$idKgtApi, $idRaihan, 'Membuat API CRUD Logbook', '2026-02-08', '2026-02-12', 15, 'rasyid_ridho']);
        $idTugas3 = $t3[0]->new_tgs_id;

        // =================================================================
        // PROJEK 2: SISTEM MANAJEMEN P-KNOW
        // =================================================================
        $pjk2 = DB::select('CALL sp_create_projek_with_leader(?, ?, ?, ?, ?, ?, ?)', [
            'Sistem Manajemen P-KNOW',
            'Platform Knowledge Management System Astra',
            'Pinow Prastyo',
            '2026-03-01',
            '2026-08-30',
            $idRasyid,
            'rasyid_ridho'
        ]);
        $idPjkPknow = $pjk2[0]->pjk_id;

        DB::table('member_projek')->insert([
            ['usr_id' => $idFakhri, 'pjk_id' => $idPjkPknow, 'mpk_role_projek' => 'UI/UX Designer', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
            ['usr_id' => $idPinow, 'pjk_id' => $idPjkPknow, 'mpk_role_projek' => 'Fullstack Dev', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
        ]);

        $mdlPknow = DB::table('modul')->insertGetId([
            'pjk_id' => $idPjkPknow,
            'mdl_nama' => 'Modul Materi',
            'mdl_urut' => 1,
            'mdl_create_at' => now(),
            'mdl_create_by' => 'rasyid_ridho'
        ]);

        // KEGIATAN (Otomatis: I.A)
        $kgt3 = DB::select('CALL sp_create_kegiatan(?, ?, ?)', [$mdlPknow, 'Manajemen Upload Materi', 'rasyid_ridho']);
        $idKgtMateri = $kgt3[0]->new_kgt_id;

        // TUGAS (Otomatis: I.A1, I.A2)
        DB::select('CALL sp_create_tugas(?, ?, ?, ?, ?, ?, ?)', [$idKgtMateri, $idFakhri, 'Desain Mockup Halaman Materi', '2026-03-02', '2026-03-04', 10, 'rasyid_ridho']);
        $t5 = DB::select('CALL sp_create_tugas(?, ?, ?, ?, ?, ?, ?)', [$idKgtMateri, $idPinow, 'Implementasi Fitur Upload File', '2026-03-05', '2026-03-10', 20, 'rasyid_ridho']);
        $idTugas5 = $t5[0]->new_tgs_id;

        // =================================================================
        // 4. UPDATE PROGRESS & LOGBOOK (SIMULASI)
        // =================================================================
        DB::select('CALL sp_update_tugas(?, ?, ?, ?, ?, ?)', [$idTugas1, $idRaihan, 'Membuat ERD (Final Revised)', '2026-02-05', 100.00, 'raihan_masri']);
        DB::select('CALL sp_update_tugas(?, ?, ?, ?, ?, ?)', [$idTugas2, $idYuwan, 'Setup Laravel', '2026-02-07', 20.00, 'yuwan_yoga']);
        DB::select('CALL sp_update_tugas(?, ?, ?, ?, ?, ?)', [$idTugas5, $idPinow, 'Upload File (Backend Done)', '2026-03-08', 50.00, 'pinow_tems']);

        // ISI LOGBOOK
        DB::select('CALL sp_create_logbook(?, ?, ?, ?)', [$idTugas1, '2026-02-03', 'Finalisasi relasi antar tabel.', 'Sudah di-approve Pak Rasyid.']);
        DB::select('CALL sp_create_logbook(?, ?, ?, ?)', [$idTugas5, '2026-03-06', 'Berhasil membuat fungsi storage link.', 'Tinggal integrasi ke frontend.']);

        $this->command->info('🎉 SEEDING SELESAI DENGAN KODE AUTO-GENERATED!');
    }
}
