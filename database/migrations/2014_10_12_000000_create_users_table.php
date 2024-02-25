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
            $table->id();
            $table->string('rol')->nullable();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('link_photo')->nullable();
            $table->string('external_id')->nullable();
            $table->string('external_auth')->nullable();
            $table->rememberToken(); // Genera un token para que el usuario permanezca autenticado, incluso después de cerrar el navegador
            $table->timestamps();
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
