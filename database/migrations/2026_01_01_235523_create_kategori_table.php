<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id('ktg_id');

            $table->string('ktg_nama', 100);
            $table->text('ktg_deskripsi')->nullable();
            $table->boolean('ktg_is_active')->default(true);

            $table->dateTime('ktg_create_at')->useCurrent();
            $table->string('ktg_create_by', 50)->nullable();
            $table->dateTime('ktg_modified_at')->nullable();
            $table->string('ktg_modified_by', 50)->nullable();

            $table->dateTime('ktg_deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
