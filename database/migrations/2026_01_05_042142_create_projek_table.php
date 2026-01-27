<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projek', function (Blueprint $table) {
            $table->id('pjk_id');

            $table->string('pjk_nama', 100);
            $table->string('pjk_pic')->nullable();
            $table->text('pjk_deskripsi')->nullable();
            $table->date('pjk_tanggal_mulai')->nullable();
            $table->date('pjk_tanggal_selesai')->nullable();
            $table->string('pjk_status', 20)->nullable();
            $table->decimal('pjk_persentasi_progress', 5, 2)->default(0);

            // Audit Columns dengan Prefix pjk_
            $table->dateTime('pjk_create_at')->useCurrent();
            $table->string('pjk_create_by')->nullable();
            $table->dateTime('pjk_modified_at')->nullable();
            $table->string('pjk_modified_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projek');
    }
};
