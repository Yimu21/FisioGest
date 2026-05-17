<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id('paciente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('fisioterapeuta_id')->nullable();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['masculino', 'femenino', 'otro']);
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->text('diagnostico')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')
                  ->references('usuario_id')
                  ->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('fisioterapeuta_id')
                  ->references('fisioterapeuta_id')
                  ->on('fisioterapeutas')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
