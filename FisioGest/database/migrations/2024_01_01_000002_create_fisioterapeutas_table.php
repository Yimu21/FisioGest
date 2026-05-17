<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fisioterapeutas', function (Blueprint $table) {
            $table->id('fisioterapeuta_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('especialidad', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps(); // creado_en / actualizado_en

            $table->foreign('usuario_id')
                  ->references('usuario_id')
                  ->on('usuarios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fisioterapeutas');
    }
};
