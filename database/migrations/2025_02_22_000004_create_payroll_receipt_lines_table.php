<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_receipt_id')->constrained()->cascadeOnDelete();
            $table->string('codigo', 20)->nullable(); // ej. *00.013
            $table->string('concepto')->nullable();
            $table->decimal('remuneracion', 14, 2)->default(0);
            $table->decimal('retencion', 14, 2)->default(0);
            $table->decimal('no_remunerativo', 14, 2)->default(0);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_receipt_lines');
    }
};
