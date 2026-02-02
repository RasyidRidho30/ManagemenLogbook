<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logbook', function (Blueprint $table) {
            $table->id('lbk_id');

            // Relasi ke Tugas
            $table->foreignId('tgs_id')->constrained('tugas', 'tgs_id')->onDelete('cascade');

            $table->date('lbk_tanggal')->nullable();
            $table->text('lbk_deskripsi')->nullable();
            $table->text('lbk_komentar')->nullable();
            $table->integer('lbk_progress')->default(0)->nullable();

            $table->dateTime('lbk_create_at')->useCurrent();
            $table->string('lbk_create_by')->nullable();
            $table->dateTime('lbk_modified_at')->nullable();
            $table->string('lbk_modified_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('logbook');
    }
};
