<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class JobsImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function headingRow(): int
    {
        return 4; // Sesuaikan dengan baris header di Excel Anda
    }

    public function collection(Collection $rows)
    {

        if ($rows->isEmpty()) {
            throw new Exception("File Excel kosong atau tidak terbaca.");
        }

        // ====================================================================
        // 1. DYNAMIC COLUMN DETECTION
        // ====================================================================
        $firstRow = $rows->first()->toArray();

        $tipeKey  = $this->findColumnKey($firstRow, ['tipe', 'type']);
        $kodeKey  = $this->findColumnKey($firstRow, ['kode', 'prefix']);
        $namaKey  = $this->findColumnKey($firstRow, ['nama', 'pekerjaan', 'task']);
        $startKey = $this->findColumnKey($firstRow, ['start', 'mulai']);
        $endKey   = $this->findColumnKey($firstRow, ['end', 'selesai']);
        $bobotKey = $this->findColumnKey($firstRow, ['bobot', 'weight']);
        $picKey   = $this->findColumnKey($firstRow, ['pic', 'email']);

        if (!$tipeKey || !$namaKey) {
            throw new Exception("Kolom 'TIPE' atau 'NAMA PEKERJAAN' tidak ditemukan di Excel. Pastikan format sesuai template.");
        }

        // ====================================================================
        // 2. PERSIAPAN DATA VALIDASI (Email & Batas Tanggal Projek)
        // ====================================================================

        $validEmails = DB::table('member_projek')
            ->join('users', 'member_projek.usr_id', '=', 'users.usr_id')
            ->where('member_projek.pjk_id', $this->projectId)
            ->pluck('users.usr_email')
            ->map(function ($email) {
                return strtolower(trim($email));
            })
            ->toArray();

        $projek = DB::table('projek')->where('pjk_id', $this->projectId)->first();
        if (!$projek) {
            throw new Exception("Data Projek tidak ditemukan.");
        }

        // Rentang Waktu Projek
        $pjkStartDate = Carbon::parse($projek->pjk_tanggal_mulai)->startOfDay();
        $pjkEndDate   = Carbon::parse($projek->pjk_tanggal_selesai)->startOfDay();

        // ====================================================================
        // 3. TAHAP 1: VALIDASI DATA
        // ====================================================================
        $errors = [];
        $hasModul = false;
        $hasKegiatan = false;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 5; // +5 karena header di baris 4
            $tipe = isset($row[$tipeKey]) ? trim(strtolower($row[$tipeKey])) : '';

            if (empty($tipe) && empty(trim($row[$namaKey] ?? ''))) continue;

            if (!in_array($tipe, ['modul', 'activity', 'aktivitas', 'task', 'tugas'])) {
                $errors[] = "Baris $rowNum: Tipe '$tipe' tidak dikenali (Gunakan: Modul, Activity, Task).";
                continue;
            }

            if (empty(trim($row[$namaKey] ?? ''))) {
                $errors[] = "Baris $rowNum: Kolom 'Nama Pekerjaan' tidak boleh kosong.";
            }

            if ($tipe === 'modul') {
                $hasModul = true;
                $hasKegiatan = false;
            } elseif (in_array($tipe, ['activity', 'aktivitas'])) {
                if (!$hasModul) $errors[] = "Baris $rowNum: Activity ditemukan sebelum ada Modul Induk.";
                $hasKegiatan = true;
            } elseif (in_array($tipe, ['task', 'tugas'])) {
                if (!$hasKegiatan) $errors[] = "Baris $rowNum: Task ditemukan sebelum ada Activity Induk.";

                // --- Validasi Email ---
                $emailPic = isset($row[$picKey]) ? strtolower(trim($row[$picKey])) : '';
                if (empty($emailPic)) {
                    $errors[] = "Baris $rowNum: Email PIC wajib diisi.";
                } elseif (!in_array($emailPic, $validEmails)) {
                    $errors[] = "Baris $rowNum: Email PIC ($emailPic) TIDAK TERDAFTAR sebagai anggota di projek ini.";
                }

                // --- Validasi Bobot ---
                $bobot = trim($row[$bobotKey] ?? '');
                if ($bobot === '' || !is_numeric($bobot) || $bobot < 0) {
                    $errors[] = "Baris $rowNum: Bobot tugas harus berupa angka positif.";
                }

                // --- Validasi Tanggal ---
                $rawStart = trim($row[$startKey] ?? '');
                $rawEnd   = trim($row[$endKey] ?? '');
                $parsedStart = null;
                $parsedEnd   = null;

                // Cek Start Date
                if (empty($rawStart)) {
                    $errors[] = "Baris $rowNum: Start Date wajib diisi.";
                } else {
                    $parsedStart = $this->parseDate($rawStart);
                    if ($parsedStart === false) {
                        $errors[] = "Baris $rowNum: Format Start Date tidak valid.";
                    } else {
                        // REVISI: Start Date hanya dicek agar tidak kurang dari Tanggal Mulai Projek
                        if ($parsedStart->lessThan($pjkStartDate)) {
                            $errors[] = "Baris $rowNum: Start Date tidak boleh mendahului tanggal mulai projek (" . $pjkStartDate->format('d/m/Y') . ").";
                        }
                    }
                }

                // Cek End Date
                if (empty($rawEnd)) {
                    $errors[] = "Baris $rowNum: End Date wajib diisi.";
                } else {
                    $parsedEnd = $this->parseDate($rawEnd);
                    if ($parsedEnd === false) {
                        $errors[] = "Baris $rowNum: Format End Date tidak valid.";
                    } else {
                        // Aturan: End Date <= Tanggal Akhir Projek
                        if ($parsedEnd->greaterThan($pjkEndDate)) {
                            $errors[] = "Baris $rowNum: End Date melebihi batas tanggal selesai projek (" . $pjkEndDate->format('d/m/Y') . ").";
                        }
                        // Aturan: End Date >= Start Date
                        if ($parsedStart && $parsedStart !== false && $parsedEnd->lessThan($parsedStart)) {
                            $errors[] = "Baris $rowNum: End Date tidak boleh mendahului Start Date.";
                        }
                    }
                }
            }
        }

        // --- GAGALKAN JIKA ADA ERROR (UI Scrollable) ---
        if (count($errors) > 0) {
            $totalErrors = count($errors);
            $errorMessage = "
            <div class='text-start'>
                <div style='background-color: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 10px;'>
                    <div class='d-flex align-items-center mb-2'>
                        <h6 class='mb-0 fw-bold text-dark'>
                            Import Digagalkan! Ditemukan {$totalErrors} Kesalahan Format
                        </h6>
                    </div>
                    <div style='max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.7); border: 1px solid #ffe69c; border-radius: 6px; padding: 10px;'>
                        <ul class='mb-0 text-dark' style='font-size: 0.85rem; padding-left: 1.2rem; font-family: monospace;'>";

            foreach ($errors as $err) {
                $errFormatted = preg_replace('/(Baris \d+:)/', '<b>$1</b>', $err);
                $errorMessage .= "<li class='mb-1'>{$errFormatted}</li>";
            }

            $errorMessage .= "
                        </ul>
                    </div>
                </div>
                <div class='text-muted small mt-2'>
                    <i>*Silakan perbaiki sel yang bermasalah pada file Excel Anda, lalu upload kembali.</i>
                </div>
            </div>";

            throw new Exception($errorMessage);
        }

        // ====================================================================
        // 4. TAHAP 2: EKSEKUSI INSERT
        // ====================================================================
        $currentModulId = null;
        $currentKgtId = null;
        $urutModul = 1;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $tipe = isset($row[$tipeKey]) ? trim(strtolower($row[$tipeKey])) : '';
                if (empty($tipe)) continue;

                if ($tipe === 'modul') {
                    $currentModulId = DB::table('modul')->insertGetId([
                        'pjk_id' => $this->projectId,
                        'mdl_nama' => $row[$namaKey],
                        'mdl_urut' => $urutModul,
                        'mdl_create_at' => now(),
                    ]);
                    $urutModul++;
                    $currentKgtId = null;
                } elseif (in_array($tipe, ['activity', 'aktivitas'])) {
                    $currentKgtId = DB::table('kegiatan')->insertGetId([
                        'mdl_id' => $currentModulId,
                        'kgt_nama' => $row[$namaKey],
                        'kgt_kode_prefix' => $row[$kodeKey] ?? '',
                        'kgt_create_at' => now(),
                    ]);
                } elseif (in_array($tipe, ['task', 'tugas'])) {
                    $user = DB::table('users')->where('usr_email', trim($row[$picKey]))->first();
                    $userId = $user ? $user->usr_id : null;

                    $startDateObj = $this->parseDate($row[$startKey]);
                    $endDateObj   = $this->parseDate($row[$endKey]);

                    DB::table('tugas')->insert([
                        'kgt_id' => $currentKgtId,
                        'usr_id' => $userId,
                        'tgs_kode_prefix' => $row[$kodeKey] ?? '',
                        'tgs_nama' => $row[$namaKey],
                        'tgs_tanggal_mulai' => $startDateObj ? $startDateObj->format('Y-m-d') : null,
                        'tgs_tanggal_selesai' => $endDateObj ? $endDateObj->format('Y-m-d') : null,
                        'tgs_bobot' => $row[$bobotKey],
                        'tgs_persentasi_progress' => 0,
                        'tgs_status' => 'Pending',
                        'tgs_create_at' => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Gagal menyimpan ke Database: " . $e->getMessage());
        }
    }

    private function findColumnKey($rowArray, array $searchTerms)
    {
        foreach ($rowArray as $key => $value) {
            foreach ($searchTerms as $term) {
                if (str_contains(strtolower((string)$key), $term)) {
                    return $key;
                }
            }
        }
        return null;
    }

    private function parseDate($value)
    {
        if (empty(trim($value))) return null;
        try {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->startOfDay();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
}
