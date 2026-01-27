<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('member_projek', function (Blueprint $table) {
            $table->id('mpk_id');

            $table->foreignId('usr_id')->constrained('users', 'usr_id')->onDelete('cascade');
            $table->foreignId('pjk_id')->constrained('projek', 'pjk_id')->onDelete('cascade');
            $table->string('mpk_role_projek', 50)->nullable();

            $table->dateTime('mpk_create_at')->useCurrent();
            $table->string('mpk_create_by')->nullable();
            $table->dateTime('mpk_modified_at')->nullable();
            $table->string('mpk_modified_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_projek');
    }
};
