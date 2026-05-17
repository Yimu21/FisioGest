<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('usuario_id');
            $table->string('nombre', 100);
            $table->string('correo', 150)->unique();
            $table->string('contrasena', 255);
            $table->enum('rol', ['admin', 'fisioterapeuta', 'paciente'])->default('paciente');
            $table->timestamp('correo_verificado_en')->nullable();
            $table->string('recordar_token', 100)->nullable();
            $table->timestamps(); // creado_en / actualizado_en
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
