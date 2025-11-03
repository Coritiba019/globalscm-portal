<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Dados mínimos para dropdown/troca rápida
            $table->string('account_number', 32);      // ex.: "00100001674"
            $table->string('digital_account_id', 64);  // ex.: "1222"
            $table->string('agency', 16)->default('0001');

            $table->boolean('active')->default(true);

            // Evita duplicidade da mesma conta por usuário
            $table->unique(['user_id', 'digital_account_id'], 'user_digital_account_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_accounts');
    }
};
