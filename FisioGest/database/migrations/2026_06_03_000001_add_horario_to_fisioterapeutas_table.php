<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fisioterapeutas', function (Blueprint $table) {
            $table->json('horario')->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('fisioterapeutas', function (Blueprint $table) {
            $table->dropColumn('horario');
        });
    }
};
