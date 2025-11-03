<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'approved')) {
                $table->boolean('approved')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'selected_account_id')) {
                $table->unsignedBigInteger('selected_account_id')->nullable()->after('approved')
                      ->comment('ID interno da conta (tabela company_accounts) selecionada pelo usuário');
            }
        });

        if (!Schema::hasTable('company_accounts')) {
            Schema::create('company_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('remote_id')->index()->comment('id vindo do /internal/api/v1/account');
                $table->string('uuid')->nullable();
                $table->string('agencyNumber')->nullable();
                $table->string('accountNumber')->nullable();
                $table->boolean('active')->default(true);
                $table->json('snapshot')->nullable(); // guarda payload bruto da API pra consulta rápida
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'selected_account_id')) {
                $table->dropColumn('selected_account_id');
            }
            if (Schema::hasColumn('users', 'approved')) {
                $table->dropColumn('approved');
            }
        });
        Schema::dropIfExists('company_accounts');
    }
};
