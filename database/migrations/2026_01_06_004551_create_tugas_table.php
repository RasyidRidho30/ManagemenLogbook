<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id('tgs_id');

            $table->foreignId('kgt_id')->constrained('kegiatan', 'kgt_id')->onDelete('cascade');
            $table->foreignId('usr_id')->constrained('users', 'usr_id')->onDelete('cascade'); // PIC

            $table->string('tgs_kode_prefix', 20)->nullable();
            $table->string('tgs_nama', 200)->nullable();
            $table->date('tgs_tanggal_mulai')->nullable();
            $table->date('tgs_tanggal_selesai')->nullable();
            $table->integer('tgs_bobot')->default(0);
            $table->decimal('tgs_persentasi_progress', 5, 2)->default(0);
            $table->string('tgs_status', 20)->nullable();

            $table->dateTime('tgs_create_at')->useCurrent();
            $table->string('tgs_create_by')->nullable();
            $table->dateTime('tgs_modified_at')->nullable();
            $table->string('tgs_modified_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tugas');
    }
};
