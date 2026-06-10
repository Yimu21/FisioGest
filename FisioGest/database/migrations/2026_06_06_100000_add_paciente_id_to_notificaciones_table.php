<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable()->after('fisioterapeuta_id');

            $table->foreign('paciente_id')
                  ->references('paciente_id')
                  ->on('pacientes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropColumn('paciente_id');
        });
    }
};
