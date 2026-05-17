<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Sesiones de terapia
        Schema::create('sesiones_terapia', function (Blueprint $table) {
            $table->id('id_sesion_terapia');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('fisioterapeuta_id');
            $table->date('fecha');
            $table->smallInteger('duracion_minutos')->default(60);
            $table->string('tipo_terapia', 120);
            $table->text('observaciones')->nullable();
            $table->enum('evolucion', ['mejora', 'estable', 'retroceso'])->default('estable');
            $table->timestamps();

            $table->foreign('paciente_id')
                  ->references('paciente_id')
                  ->on('pacientes')
                  ->onDelete('cascade');

            $table->foreign('fisioterapeuta_id')
                  ->references('fisioterapeuta_id')
                  ->on('fisioterapeutas')
                  ->onDelete('cascade');
        });

        // Asignaciones de equipo
        Schema::create('asignaciones_equipo', function (Blueprint $table) {
            $table->id('id_asignaciones');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('inventario_id');
            $table->date('fecha_asignacion');
            $table->date('fecha_devolucion')->nullable();
            $table->enum('estado', ['activo', 'devuelto'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('paciente_id')
                  ->references('paciente_id')
                  ->on('pacientes')
                  ->onDelete('cascade');

            $table->foreign('inventario_id')
                  ->references('id_inventario')
                  ->on('inventario')
                  ->onDelete('cascade');
        });

        // Citas
        Schema::create('citas', function (Blueprint $table) {
            $table->id('cita_id');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('fisioterapeuta_id');
            $table->dateTime('fecha_hora');
            $table->string('motivo', 200)->nullable();
            $table->enum('estado', ['programada', 'atendida', 'cancelada', 'reprogramada'])->default('programada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('paciente_id')
                  ->references('paciente_id')
                  ->on('pacientes')
                  ->onDelete('cascade');

            $table->foreign('fisioterapeuta_id')
                  ->references('fisioterapeuta_id')
                  ->on('fisioterapeutas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
        Schema::dropIfExists('asignaciones_equipo');
        Schema::dropIfExists('sesiones_terapia');
    }
};
