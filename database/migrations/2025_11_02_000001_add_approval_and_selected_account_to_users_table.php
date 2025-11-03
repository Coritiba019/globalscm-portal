<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'approval_status')) {
                $table->string('approval_status', 16)->default('PENDING')->after('email'); // PENDING | APPROVED | REJECTED
            }
            if (!Schema::hasColumn('users', 'selected_digital_account_id')) {
                $table->string('selected_digital_account_id', 64)->nullable()->after('approval_status'); // ex.: "1222"
            }
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('selected_digital_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
            if (Schema::hasColumn('users', 'selected_digital_account_id')) {
                $table->dropColumn('selected_digital_account_id');
            }
            if (Schema::hasColumn('users', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
