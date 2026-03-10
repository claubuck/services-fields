<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployerController extends Controller
{
    public function edit()
    {
        $employer = Employer::first();

        return Inertia::render('Employer/Edit', [
            'employer' => $employer,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:255'],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'cuit' => ['nullable', 'string', 'max:20'],
        ]);

        $employer = Employer::first();
        if ($employer) {
            $employer->update($validated);
        } else {
            Employer::create($validated);
        }

        return redirect()->route('employer.edit')
            ->with('success', 'Datos del empleador actualizados.');
    }
}
