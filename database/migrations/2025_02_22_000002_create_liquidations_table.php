<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('mes'); // 1-12
            $table->unsignedSmallInteger('anio');
            $table->date('fecha_pago');
            $table->timestamps();

            $table->unique(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidations');
    }
};
