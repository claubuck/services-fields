<?php

namespace App\Http\Controllers;

use App\Models\LiquidationModality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiquidationModalityController extends Controller
{
    public function index()
    {
        $modalidades = LiquidationModality::orderBy('nombre')->withCount('employees')->get();

        return Inertia::render('Modalidades/Index', [
            'modalidades' => $modalidades,
        ]);
    }

    public function create()
    {
        return Inertia::render('Modalidades/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'precio_por_unidad' => ['required', 'numeric', 'min:0'],
        ]);

        LiquidationModality::create($validated);

        return redirect()->route('modalidades.index')
            ->with('success', 'Modalidad de liquidación creada correctamente.');
    }

    public function edit(LiquidationModality $modalidad)
    {
        return Inertia::render('Modalidades/Edit', [
            'modalidad' => $modalidad,
        ]);
    }

    public function update(Request $request, LiquidationModality $modalidad)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'precio_por_unidad' => ['required', 'numeric', 'min:0'],
        ]);

        $modalidad->update($validated);

        return redirect()->route('modalidades.index')
            ->with('success', 'Modalidad de liquidación actualizada correctamente.');
    }

    public function destroy(LiquidationModality $modalidad)
    {
        if ($modalidad->employees()->exists()) {
            return redirect()->route('modalidades.index')
                ->with('error', 'No se puede eliminar: hay empleados con esta modalidad.');
        }

        $modalidad->delete();

        return redirect()->route('modalidades.index')
            ->with('success', 'Modalidad de liquidación eliminada correctamente.');
    }
}
