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
        Schema::create('users', function (Blueprint $table) {
            $table->id('usr_id'); // Primary Key
            
            $table->string('usr_username', 50);
            $table->string('usr_email', 100)->unique();
            $table->string('usr_password', 255);
            $table->string('usr_first_name', 50)->nullable();
            $table->string('usr_last_name', 50)->nullable();
            $table->string('usr_avatar_url', 255)->nullable();
            $table->string('usr_role', 20)->nullable();

            $table->dateTime('usr_create_at')->useCurrent();
            $table->string('usr_create_by')->nullable();
            $table->dateTime('usr_modified_at')->nullable();
            $table->string('usr_modified_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
