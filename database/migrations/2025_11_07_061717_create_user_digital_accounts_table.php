<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_digital_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ID da conta digital vindo do provedor externo (sem FK local)
            $table->unsignedBigInteger('digital_account_id')->index();

            $table->timestamps();

            $table->unique(['user_id', 'digital_account_id'], 'uda_user_digital_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_digital_accounts');
    }
};
