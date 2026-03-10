<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Liquidation;
use App\Models\PayrollReceipt;
use App\Models\PayrollReceiptLine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollReceiptController extends Controller
{
    public function create(Liquidation $liquidation, Request $request)
    {
        $employeeId = $request->get('employee_id');
        $liquidation->load('payrollReceipts.employee');
        $employees = \App\Models\Employee::with(['category:id,nombre', 'liquidationModality:id,nombre,precio_por_unidad'])
            ->whereIn('estado', ['activo', 'pendiente_de_baja'])
            ->orderBy('nombre_apellido')
            ->get(['id', 'nombre_apellido', 'dni', 'cuil', 'fecha_inicio', 'category_id', 'liquidation_modality_id']);

        return Inertia::render('PayrollReceipts/Create', [
            'liquidation' => $liquidation,
            'employees' => $employees,
            'preselectedEmployeeId' => $employeeId ? (int) $employeeId : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'liquidation_id' => ['required', 'exists:liquidations,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'neto_a_cobrar' => ['required', 'numeric', 'min:0'],
            'total_bruto' => ['required', 'numeric', 'min:0'],
            'total_retencion' => ['required', 'numeric', 'min:0'],
            'total_no_remunerativo' => ['required', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.codigo' => ['nullable', 'string', 'max:20'],
            'lines.*.concepto' => ['nullable', 'string'],
            'lines.*.remuneracion' => ['nullable', 'numeric', 'min:0'],
            'lines.*.retencion' => ['nullable', 'numeric', 'min:0'],
            'lines.*.no_remunerativo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $liquidation = Liquidation::findOrFail($validated['liquidation_id']);
        if (PayrollReceipt::where('liquidation_id', $liquidation->id)->where('employee_id', $validated['employee_id'])->exists()) {
            return redirect()->back()->withErrors(['employee_id' => 'Ya existe un recibo para ese empleado en esta liquidación.']);
        }

        $employee = \App\Models\Employee::with('category')->find($validated['employee_id']);
        $categoria = $validated['categoria'] ?? null;
        if ($categoria === '' || $categoria === null) {
            $categoria = $employee->category?->nombre;
        }

        $receipt = PayrollReceipt::create([
            'liquidation_id' => $liquidation->id,
            'employee_id' => $validated['employee_id'],
            'categoria' => $categoria,
            'total_bruto' => $validated['total_bruto'],
            'total_retencion' => $validated['total_retencion'],
            'total_no_remunerativo' => $validated['total_no_remunerativo'],
            'neto_a_cobrar' => $validated['neto_a_cobrar'],
        ]);

        foreach ($validated['lines'] as $i => $line) {
            PayrollReceiptLine::create([
                'payroll_receipt_id' => $receipt->id,
                'codigo' => $line['codigo'] ?? null,
                'concepto' => $line['concepto'] ?? null,
                'remuneracion' => $line['remuneracion'] ?? 0,
                'retencion' => $line['retencion'] ?? 0,
                'no_remunerativo' => $line['no_remunerativo'] ?? 0,
                'orden' => $i,
            ]);
        }

        return redirect()->route('liquidations.show', $liquidation)
            ->with('success', 'Recibo creado.');
    }

    public function edit(PayrollReceipt $payrollReceipt)
    {
        $payrollReceipt->load(['liquidation', 'employee', 'lines']);

        return Inertia::render('PayrollReceipts/Edit', [
            'receipt' => $payrollReceipt,
        ]);
    }

    public function update(Request $request, PayrollReceipt $payrollReceipt)
    {
        $validated = $request->validate([
            'categoria' => ['nullable', 'string', 'max:100'],
            'neto_a_cobrar' => ['required', 'numeric', 'min:0'],
            'total_bruto' => ['required', 'numeric', 'min:0'],
            'total_retencion' => ['required', 'numeric', 'min:0'],
            'total_no_remunerativo' => ['required', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['nullable', 'integer'],
            'lines.*.codigo' => ['nullable', 'string', 'max:20'],
            'lines.*.concepto' => ['nullable', 'string'],
            'lines.*.remuneracion' => ['nullable', 'numeric', 'min:0'],
            'lines.*.retencion' => ['nullable', 'numeric', 'min:0'],
            'lines.*.no_remunerativo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payrollReceipt->update([
            'categoria' => $validated['categoria'] ?? null,
            'total_bruto' => $validated['total_bruto'],
            'total_retencion' => $validated['total_retencion'],
            'total_no_remunerativo' => $validated['total_no_remunerativo'],
            'neto_a_cobrar' => $validated['neto_a_cobrar'],
        ]);

        $payrollReceipt->lines()->delete();
        foreach ($validated['lines'] as $i => $line) {
            $payrollReceipt->lines()->create([
                'codigo' => $line['codigo'] ?? null,
                'concepto' => $line['concepto'] ?? null,
                'remuneracion' => $line['remuneracion'] ?? 0,
                'retencion' => $line['retencion'] ?? 0,
                'no_remunerativo' => $line['no_remunerativo'] ?? 0,
                'orden' => $i,
            ]);
        }

        return redirect()->route('liquidations.show', $payrollReceipt->liquidation_id)
            ->with('success', 'Recibo actualizado.');
    }

    public function destroy(PayrollReceipt $payrollReceipt)
    {
        $liquidationId = $payrollReceipt->liquidation_id;
        $payrollReceipt->delete();
        return redirect()->route('liquidations.show', $liquidationId)
            ->with('success', 'Recibo eliminado.');
    }

    public function print(PayrollReceipt $payrollReceipt)
    {
        $payrollReceipt->load(['employee', 'liquidation', 'lines']);
        $employer = Employer::first();

        return view('payroll-receipts.print', [
            'receipt' => $payrollReceipt,
            'employer' => $employer,
        ]);
    }
}
