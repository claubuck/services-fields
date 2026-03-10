<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Liquidation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiquidationController extends Controller
{
    public function index()
    {
        $liquidations = Liquidation::withCount('payrollReceipts')
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();

        return Inertia::render('Liquidations/Index', [
            'liquidations' => $liquidations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Liquidations/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'fecha_pago' => ['required', 'date'],
        ]);

        if (Liquidation::where('mes', $validated['mes'])->where('anio', $validated['anio'])->exists()) {
            return redirect()->back()->withErrors(['mes' => 'Ya existe una liquidación para ese período.']);
        }

        Liquidation::create($validated);

        return redirect()->route('liquidations.index')
            ->with('success', 'Liquidación creada.');
    }

    public function show(Liquidation $liquidation)
    {
        $liquidation->load(['payrollReceipts.employee:id,nombre_apellido,dni,cuil,fecha_inicio']);
        $employees = Employee::whereIn('estado', ['activo', 'pendiente_de_baja'])
            ->orderBy('nombre_apellido')
            ->get(['id', 'nombre_apellido', 'dni', 'cuil']);

        return Inertia::render('Liquidations/Show', [
            'liquidation' => $liquidation,
            'employees' => $employees,
        ]);
    }

    public function destroy(Liquidation $liquidation)
    {
        $liquidation->delete();
        return redirect()->route('liquidations.index')
            ->with('success', 'Liquidación eliminada.');
    }
}
