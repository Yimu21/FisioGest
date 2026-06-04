<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos_agenda', function (Blueprint $table) {
            $table->id('evento_id');
            $table->unsignedBigInteger('fisioterapeuta_id');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->enum('tipo', ['personal', 'capacitacion', 'vacaciones', 'bloqueo'])->default('personal');
            $table->timestamps();

            $table->foreign('fisioterapeuta_id')
                  ->references('fisioterapeuta_id')
                  ->on('fisioterapeutas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_agenda');
    }
};
