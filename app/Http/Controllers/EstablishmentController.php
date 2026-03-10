<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EstablishmentController extends Controller
{
    public function index()
    {
        $establishments = Establishment::orderBy('nombre')->get();

        return Inertia::render('Establishments/Index', [
            'establishments' => $establishments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Establishments/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        Establishment::create($validated);

        return redirect()->route('establishments.index')
            ->with('success', 'Establecimiento creado correctamente.');
    }

    public function edit(Establishment $establishment)
    {
        return Inertia::render('Establishments/Edit', [
            'establishment' => $establishment,
        ]);
    }

    public function update(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $establishment->update($validated);

        return redirect()->route('establishments.index')
            ->with('success', 'Establecimiento actualizado correctamente.');
    }

    public function destroy(Establishment $establishment)
    {
        $establishment->delete();

        return redirect()->route('establishments.index')
            ->with('success', 'Establecimiento eliminado correctamente.');
    }
}
