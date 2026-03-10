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
        DB::table('kategori')->truncate(); // Bersihkan Kategori
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

        $idRasyid = DB::table('users')->insertGetId([
            'usr_username' => 'rasyid_ridho',
            'usr_email' => 'rasyid@polman.astra.ac.id',
            'usr_password' => $password,
            'usr_first_name' => 'Rasyid',
            'usr_last_name' => 'Ridho',
            'usr_role' => 'User',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);
        $idRaihan = DB::table('users')->insertGetId([
            'usr_username' => 'raihan_masri',
            'usr_email' => 'raihan@example.com',
            'usr_password' => $password,
            'usr_first_name' => 'Raihan',
            'usr_last_name' => 'Masri',
            'usr_role' => 'User',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);
        $idYuwan = DB::table('users')->insertGetId([
            'usr_username' => 'yuwan_yoga',
            'usr_email' => 'yuwan@example.com',
            'usr_password' => $password,
            'usr_first_name' => 'Yuwan',
            'usr_last_name' => 'Yoga',
            'usr_role' => 'User',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);
        $idFakhri = DB::table('users')->insertGetId([
            'usr_username' => 'fakhri_majid',
            'usr_email' => 'fakhri@example.com',
            'usr_password' => $password,
            'usr_first_name' => 'Fakhri',
            'usr_last_name' => 'Majid',
            'usr_role' => 'User',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);
        $idPinow = DB::table('users')->insertGetId([
            'usr_username' => 'pinow_tems',
            'usr_email' => 'pinow@example.com',
            'usr_password' => $password,
            'usr_first_name' => 'Pinow',
            'usr_last_name' => 'Tems',
            'usr_role' => 'User',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);
        DB::table('users')->insert([
            'usr_username' => 'admin',
            'usr_email' => 'admin@admin.com',
            'usr_password' => $pswAdmin,
            'usr_first_name' => 'Administrator',
            'usr_last_name' => '',
            'usr_role' => 'Admin',
            'usr_create_at' => now(),
            'usr_create_by' => 'System'
        ]);

        $this->command->info('✅ 5 User & 1 Admin berhasil dibuat.');

        // =================================================================
        // 2. CREATE KATEGORI (Data Baru)
        // =================================================================
        $idKtgIT = DB::table('kategori')->insertGetId([
            'ktg_nama' => 'IT & Software Development',
            'ktg_deskripsi' => 'Projek pengembangan aplikasi, website, dan sistem informasi.',
            'ktg_create_at' => now(),
            'ktg_create_by' => 'System'
        ]);

        $idKtgRiset = DB::table('kategori')->insertGetId([
            'ktg_nama' => 'Research & Development',
            'ktg_deskripsi' => 'Projek riset dan pengembangan inovasi baru.',
            'ktg_create_at' => now(),
            'ktg_create_by' => 'System'
        ]);

        $this->command->info('✅ Kategori Projek berhasil dibuat.');

        // =================================================================
        // 3. PROJEK 1: SISTEM MANAJEMEN LOGBOOK
        // =================================================================
        $idPjkLogbook = DB::table('projek')->insertGetId([
            'ktg_id' => $idKtgIT, // Assign ke Kategori IT
            'pjk_nama' => 'Sistem Manajemen Logbook',
            'pjk_deskripsi' => 'Aplikasi monitoring aktivitas magang mahasiswa',
            'pjk_pic' => 'Raihan Ramadhan',
            'pjk_status' => 'In Progress',
            'pjk_tanggal_mulai' => '2026-02-01',
            'pjk_tanggal_selesai' => '2026-06-30',
            'pjk_create_at' => now(),
            'pjk_create_by' => 'rasyid_ridho'
        ]);

        DB::table('member_projek')->insert([
            ['usr_id' => $idRasyid, 'pjk_id' => $idPjkLogbook, 'mpk_role_projek' => 'Ketua', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
            ['usr_id' => $idRaihan, 'pjk_id' => $idPjkLogbook, 'mpk_role_projek' => 'Backend Dev', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
            ['usr_id' => $idYuwan, 'pjk_id' => $idPjkLogbook, 'mpk_role_projek' => 'Frontend Dev', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
        ]);

        $mdlLogbook = DB::table('modul')->insertGetId([
            'pjk_id' => $idPjkLogbook,
            'mdl_nama' => 'Modul Laporan',
            'mdl_urut' => 1,
            'mdl_create_at' => now(),
            'mdl_create_by' => 'rasyid_ridho'
        ]);

        $idKgtDesign = DB::table('kegiatan')->insertGetId(['mdl_id' => $mdlLogbook, 'kgt_nama' => 'Perancangan Database', 'kgt_kode_prefix' => 'I.A', 'kgt_create_at' => now(), 'kgt_create_by' => 'rasyid_ridho']);
        $idKgtApi = DB::table('kegiatan')->insertGetId(['mdl_id' => $mdlLogbook, 'kgt_nama' => 'Development API', 'kgt_kode_prefix' => 'I.B', 'kgt_create_at' => now(), 'kgt_create_by' => 'rasyid_ridho']);

        $idTugas1 = DB::table('tugas')->insertGetId([
            'kgt_id' => $idKgtDesign,
            'usr_id' => $idRaihan,
            'tgs_kode_prefix' => 'I.A1',
            'tgs_nama' => 'Membuat ERD dan Schema MySQL',
            'tgs_tanggal_mulai' => '2026-02-02',
            'tgs_tanggal_selesai' => '2026-02-05',
            'tgs_bobot' => 10,
            'tgs_persentasi_progress' => 0,
            'tgs_status' => 'Pending',
            'tgs_create_at' => now(),
            'tgs_create_by' => 'rasyid_ridho'
        ]);
        $idTugas2 = DB::table('tugas')->insertGetId([
            'kgt_id' => $idKgtApi,
            'usr_id' => $idYuwan,
            'tgs_kode_prefix' => 'I.B1',
            'tgs_nama' => 'Setup Laravel 11 dan Sanctum',
            'tgs_tanggal_mulai' => '2026-02-06',
            'tgs_tanggal_selesai' => '2026-02-07',
            'tgs_bobot' => 5,
            'tgs_persentasi_progress' => 0,
            'tgs_status' => 'Pending',
            'tgs_create_at' => now(),
            'tgs_create_by' => 'rasyid_ridho'
        ]);
        $idTugas3 = DB::table('tugas')->insertGetId([
            'kgt_id' => $idKgtApi,
            'usr_id' => $idRaihan,
            'tgs_kode_prefix' => 'I.B2',
            'tgs_nama' => 'Membuat API CRUD Logbook',
            'tgs_tanggal_mulai' => '2026-02-08',
            'tgs_tanggal_selesai' => '2026-02-12',
            'tgs_bobot' => 15,
            'tgs_persentasi_progress' => 0,
            'tgs_status' => 'Pending',
            'tgs_create_at' => now(),
            'tgs_create_by' => 'rasyid_ridho'
        ]);

        // =================================================================
        // 4. PROJEK 2: SISTEM MANAJEMEN P-KNOW
        // =================================================================
        $idPjkPknow = DB::table('projek')->insertGetId([
            'ktg_id' => $idKtgRiset, // Assign ke Kategori Riset
            'pjk_nama' => 'Sistem Manajemen P-KNOW',
            'pjk_deskripsi' => 'Platform Knowledge Management System Astra',
            'pjk_pic' => 'Pinow Prastyo',
            'pjk_status' => 'In Progress',
            'pjk_tanggal_mulai' => '2026-03-01',
            'pjk_tanggal_selesai' => '2026-08-30',
            'pjk_create_at' => now(),
            'pjk_create_by' => 'rasyid_ridho'
        ]);

        DB::table('member_projek')->insert([
            ['usr_id' => $idRasyid, 'pjk_id' => $idPjkPknow, 'mpk_role_projek' => 'Ketua', 'mpk_create_at' => now(), 'mpk_create_by' => 'rasyid_ridho'],
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

        $idKgtMateri = DB::table('kegiatan')->insertGetId(['mdl_id' => $mdlPknow, 'kgt_nama' => 'Manajemen Upload Materi', 'kgt_kode_prefix' => 'I.A', 'kgt_create_at' => now(), 'kgt_create_by' => 'rasyid_ridho']);

        DB::table('tugas')->insert([
            'kgt_id' => $idKgtMateri,
            'usr_id' => $idFakhri,
            'tgs_kode_prefix' => 'I.A1',
            'tgs_nama' => 'Desain Mockup Halaman Materi',
            'tgs_tanggal_mulai' => '2026-03-02',
            'tgs_tanggal_selesai' => '2026-03-04',
            'tgs_bobot' => 10,
            'tgs_persentasi_progress' => 0,
            'tgs_status' => 'Pending',
            'tgs_create_at' => now(),
            'tgs_create_by' => 'rasyid_ridho'
        ]);

        $idTugas5 = DB::table('tugas')->insertGetId([
            'kgt_id' => $idKgtMateri,
            'usr_id' => $idPinow,
            'tgs_kode_prefix' => 'I.A2',
            'tgs_nama' => 'Implementasi Fitur Upload File',
            'tgs_tanggal_mulai' => '2026-03-05',
            'tgs_tanggal_selesai' => '2026-03-10',
            'tgs_bobot' => 20,
            'tgs_persentasi_progress' => 0,
            'tgs_status' => 'Pending',
            'tgs_create_at' => now(),
            'tgs_create_by' => 'rasyid_ridho'
        ]);

        // =================================================================
        // 5. UPDATE PROGRESS & LOGBOOK (SIMULASI UPDATE TUGAS LAMA)
        // =================================================================
        DB::table('tugas')->where('tgs_id', $idTugas1)->update([
            'tgs_nama' => 'Membuat ERD (Final Revised)',
            'tgs_tanggal_selesai' => '2026-02-05',
            'tgs_persentasi_progress' => 100.00,
            'tgs_status' => 'Selesai',
            'tgs_modified_at' => now(),
            'tgs_modified_by' => 'raihan_masri'
        ]);

        DB::table('tugas')->where('tgs_id', $idTugas2)->update([
            'tgs_nama' => 'Setup Laravel',
            'tgs_tanggal_selesai' => '2026-02-07',
            'tgs_persentasi_progress' => 20.00,
            'tgs_status' => 'In Progress',
            'tgs_modified_at' => now(),
            'tgs_modified_by' => 'yuwan_yoga'
        ]);

        DB::table('tugas')->where('tgs_id', $idTugas5)->update([
            'tgs_nama' => 'Upload File (Backend Done)',
            'tgs_tanggal_selesai' => '2026-03-08',
            'tgs_persentasi_progress' => 50.00,
            'tgs_status' => 'In Progress',
            'tgs_modified_at' => now(),
            'tgs_modified_by' => 'pinow_tems'
        ]);

        // ISI LOGBOOK
        DB::table('logbook')->insert([
            ['tgs_id' => $idTugas1, 'lbk_tanggal' => '2026-02-03', 'lbk_deskripsi' => 'Finalisasi relasi antar tabel.', 'lbk_komentar' => 'Sudah di-approve Pak Rasyid.', 'lbk_progress' => 100, 'lbk_create_at' => now(), 'lbk_create_by' => 'raihan_masri'],
            ['tgs_id' => $idTugas5, 'lbk_tanggal' => '2026-03-06', 'lbk_deskripsi' => 'Berhasil membuat fungsi storage link.', 'lbk_komentar' => 'Tinggal integrasi ke frontend.', 'lbk_progress' => 50, 'lbk_create_at' => now(), 'lbk_create_by' => 'pinow_tems'],
        ]);

        $this->command->info('🎉 SEEDING SELESAI (Menggunakan Laravel Query Builder)!');
    }
}
