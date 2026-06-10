<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ya incluida en la migración base desde la refactorización; se omite si existe.
        if (!Schema::hasColumn('pacientes', 'portal_activo')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->boolean('portal_activo')->default(false)->after('usuario_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pacientes', 'portal_activo')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->dropColumn('portal_activo');
            });
        }
    }
};
