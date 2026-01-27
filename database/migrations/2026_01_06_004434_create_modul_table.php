<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modul', function (Blueprint $table) {
            $table->id('mdl_id');

            $table->foreignId('pjk_id')->constrained('projek', 'pjk_id')->onDelete('cascade');
            $table->string('mdl_nama', 100)->nullable();
            $table->integer('mdl_urut')->nullable();
            $table->dateTime('mdl_create_at')->useCurrent();
            $table->string('mdl_create_by')->nullable();
            $table->dateTime('mdl_modified_at')->nullable();
            $table->string('mdl_modified_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('modul');
    }
};
