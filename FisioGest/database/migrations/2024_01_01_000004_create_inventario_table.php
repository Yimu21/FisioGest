<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->string('nombre', 150);
            $table->string('tipo', 100); // protesis | ortesis | maquina | otro
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->enum('estado', ['disponible', 'en_uso', 'mantenimiento', 'baja'])->default('disponible');
            $table->smallInteger('cantidad')->default(1);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
