<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id('kgt_id');

            $table->foreignId('mdl_id')->constrained('modul', 'mdl_id')->onDelete('cascade');
            $table->string('kgt_nama', 100)->nullable();
            $table->string('kgt_kode_prefix', 10)->nullable();

            $table->dateTime('kgt_create_at')->useCurrent();
            $table->string('kgt_create_by')->nullable();
            $table->dateTime('kgt_modified_at')->nullable();
            $table->string('kgt_modified_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kegiatan');
    }
};
