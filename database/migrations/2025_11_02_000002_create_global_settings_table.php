<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_base', 255)->default('https://api.globalscm.app.br');

            // Credenciais de serviço (opcionais) para renovar token automaticamente
            $table->string('service_account', 64)->nullable();
            $table->string('service_password', 255)->nullable();

            // Token compartilhado entre usuários aprovados
            $table->longText('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
