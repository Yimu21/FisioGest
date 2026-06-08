<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ambas columnas ya están en la migración base desde la refactorización.
        // Esta migración se conserva para instalaciones existentes que migraron
        // antes del cambio; en instancias nuevas simplemente no hace nada.
        Schema::table('pacientes', function (Blueprint $table) {
            if (!Schema::hasColumn('pacientes', 'correo')) {
                $table->string('correo', 191)->nullable()->unique()->after('usuario_id');
            }
        });

        // Hacer usuario_id nullable solo si todavía es NOT NULL
        $sm     = Schema::getConnection()->getDoctrineSchemaManager();
        $col    = $sm->listTableColumns('pacientes')['usuario_id'] ?? null;
        if ($col && !$col->getNotnull() === false) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->dropForeign(['usuario_id']);
                $table->unsignedBigInteger('usuario_id')->nullable()->change();
                $table->foreign('usuario_id')
                      ->references('usuario_id')
                      ->on('usuarios')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pacientes', 'correo')) {
            Schema::table('pacientes', function (Blueprint $table) {
                $table->dropColumn('correo');
            });
        }
    }
};
