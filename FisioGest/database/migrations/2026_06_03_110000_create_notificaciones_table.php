<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('notificacion_id');
            $table->string('tipo', 60)->default('horario_actualizado');
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->unsignedBigInteger('fisioterapeuta_id')->nullable();
            $table->timestamps();

            $table->foreign('fisioterapeuta_id')
                  ->references('fisioterapeuta_id')
                  ->on('fisioterapeutas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
