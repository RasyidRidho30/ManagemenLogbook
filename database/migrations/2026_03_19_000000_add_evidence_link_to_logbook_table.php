<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook', function (Blueprint $table) {
            $table->string('lbk_evidence_link')->nullable()->after('lbk_progress');
        });

        // Update stored procedures to include evidence link field
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_logbook;');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_logbook;');

        DB::unprepared('
            CREATE PROCEDURE sp_create_logbook(
                IN p_tgs_id INT,
                IN p_tanggal DATE,
                IN p_deskripsi TEXT,
                IN p_komentar TEXT,
                IN p_progress INT,
                IN p_evidence_link VARCHAR(255)
            )
            BEGIN
                INSERT INTO logbook (tgs_id, lbk_tanggal, lbk_deskripsi, lbk_komentar, lbk_progress, lbk_evidence_link, lbk_create_at)
                VALUES (p_tgs_id, p_tanggal, p_deskripsi, p_komentar, p_progress, p_evidence_link, NOW());
            END
        ');

        DB::unprepared('
            CREATE PROCEDURE sp_update_logbook(
                IN p_lbk_id INT,
                IN p_tanggal DATE,
                IN p_deskripsi TEXT,
                IN p_komentar TEXT,
                IN p_progress INT,
                IN p_evidence_link VARCHAR(255)
            )
            BEGIN
                UPDATE logbook
                SET 
                    lbk_tanggal = p_tanggal,
                    lbk_deskripsi = p_deskripsi,
                    lbk_komentar = p_komentar,
                    lbk_progress = p_progress,
                    lbk_evidence_link = p_evidence_link,
                    lbk_modified_at = NOW()
                WHERE lbk_id = p_lbk_id;
            END
        ');
    }

    public function down(): void
    {
        Schema::table('logbook', function (Blueprint $table) {
            $table->dropColumn('lbk_evidence_link');
        });

        // Restore previous stored procedures without evidence link
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_logbook;');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_logbook;');

        DB::unprepared('
            CREATE PROCEDURE sp_create_logbook(
                IN p_tgs_id INT,
                IN p_tanggal DATE,
                IN p_deskripsi TEXT,
                IN p_komentar TEXT,
                IN p_progress INT
            )
            BEGIN
                INSERT INTO logbook (tgs_id, lbk_tanggal, lbk_deskripsi, lbk_komentar, lbk_progress, lbk_create_at)
                VALUES (p_tgs_id, p_tanggal, p_deskripsi, p_komentar, p_progress, NOW());
            END
        ');

        DB::unprepared('
            CREATE PROCEDURE sp_update_logbook(
                IN p_lbk_id INT,
                IN p_tanggal DATE,
                IN p_deskripsi TEXT,
                IN p_komentar TEXT,
                IN p_progress INT
            )
            BEGIN
                UPDATE logbook
                SET 
                    lbk_tanggal = p_tanggal,
                    lbk_deskripsi = p_deskripsi,
                    lbk_komentar = p_komentar,
                    lbk_progress = p_progress,
                    lbk_modified_at = NOW()
                WHERE lbk_id = p_lbk_id;
            END
        ');
    }
};
