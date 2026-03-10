<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->string('nombre_apellido');
            $table->string('cuil', 15)->unique();
            $table->string('dni', 15);
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->date('fecha_inicio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
