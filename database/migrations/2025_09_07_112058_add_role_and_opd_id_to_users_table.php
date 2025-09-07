<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'opd'])->default('opd')->after('email');
            $table->string('opd_id')->nullable()->after('role');
            $table->string('opd_nama')->nullable()->after('opd_id'); // Kolom untuk menyimpan nama OPD
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'opd_id', 'opd_nama']);
        });
    }
};