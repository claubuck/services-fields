<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liquidation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('categoria', 100)->nullable(); // ej. COSECHADOR
            $table->decimal('total_bruto', 14, 2)->default(0);
            $table->decimal('total_retencion', 14, 2)->default(0);
            $table->decimal('total_no_remunerativo', 14, 2)->default(0);
            $table->decimal('neto_a_cobrar', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['liquidation_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_receipts');
    }
};
