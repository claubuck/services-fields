<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Establishment;
use App\Models\LiquidationModality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['establishment:id,nombre', 'category:id,nombre', 'liquidationModality:id,nombre,precio_por_unidad'])->orderBy('nombre_apellido');

        if ($search = $request->filled('search') ? trim($request->search) : null) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_apellido', 'ilike', "%{$search}%")
                    ->orWhere('dni', 'ilike', "%{$search}%")
                    ->orWhere('cuil', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('establishment_id')) {
            $query->where('establishment_id', $request->establishment_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $employees = $query->paginate(20)->withQueryString();
        $establishments = Establishment::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'establishments' => $establishments,
            'filters' => [
                'search' => $request->get('search', ''),
                'establishment_id' => $request->get('establishment_id', ''),
                'estado' => $request->get('estado', ''),
            ],
        ]);
    }

    public function create()
    {
        $establishments = Establishment::orderBy('nombre')->get(['id', 'nombre']);
        $categories = Category::orderBy('nombre')->get(['id', 'nombre']);
        $modalidades = LiquidationModality::orderBy('nombre')->get(['id', 'nombre', 'precio_por_unidad']);

        return Inertia::render('Employees/Create', [
            'establishments' => $establishments,
            'categories' => $categories,
            'modalidades' => $modalidades,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'exists:establishments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'liquidation_modality_id' => ['nullable', 'exists:liquidation_modalities,id'],
            'nombre_apellido' => ['required', 'string', 'max:255'],
            'cuil' => ['required', 'string', 'max:15', 'unique:employees,cuil'],
            'dni' => ['required', 'string', 'max:15'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', 'in:activo,inactivo,suspendido,pendiente_de_baja'],
            'fecha_inicio' => ['required', 'date'],
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function edit(Employee $employee)
    {
        $employee->load(['establishment:id,nombre', 'category:id,nombre', 'liquidationModality:id,nombre,precio_por_unidad']);
        $establishments = Establishment::orderBy('nombre')->get(['id', 'nombre']);
        $categories = Category::orderBy('nombre')->get(['id', 'nombre']);
        $modalidades = LiquidationModality::orderBy('nombre')->get(['id', 'nombre', 'precio_por_unidad']);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'establishments' => $establishments,
            'categories' => $categories,
            'modalidades' => $modalidades,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'exists:establishments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'liquidation_modality_id' => ['nullable', 'exists:liquidation_modalities,id'],
            'nombre_apellido' => ['required', 'string', 'max:255'],
            'cuil' => ['required', 'string', 'max:15', 'unique:employees,cuil,' . $employee->id],
            'dni' => ['required', 'string', 'max:15'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', 'in:activo,inactivo,suspendido,pendiente_de_baja'],
            'fecha_inicio' => ['required', 'date'],
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    public function buscarPorDni(Request $request)
    {
        $request->validate(['dni' => ['required', 'string', 'max:15']]);

        $response = Http::withHeaders([
            'Authorization' => config('services.inforapi.key'),
            'Content-Type' => 'application/json',
        ])->post('https://www.inforapi.com/api/v1/get-cuit', [
            'dni' => $request->dni,
        ]);

        if (!$response->successful()) {
            return response()->json(['success' => false, 'message' => 'No se pudo obtener los datos'], 422);
        }

        $body = $response->json();
        if (empty($body['success']) || empty($body['data'])) {
            return response()->json(['success' => false, 'message' => 'Respuesta inválida'], 422);
        }

        $data = $body['data'];
        return response()->json([
            'success' => true,
            'data' => [
                'nombre_apellido' => $data['nombre'] ?? '',
                'cuil' => $data['cuit'] ?? '',
            ],
        ]);
    }
}
