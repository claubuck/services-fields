<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeCamposExport;
use App\Exports\EmployeeNormalExport;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Establishment;
use App\Models\LiquidationModality;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:excel,pdf'],
            'template' => ['nullable', 'in:normal,campos'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string', 'in:nombre_apellido,cuil,dni,establishment,categoria,modalidad,estado,fecha_inicio,direccion,telefono'],
            'search' => ['nullable', 'string', 'max:255'],
            'establishment_id' => ['nullable', 'exists:establishments,id'],
            'estado' => ['nullable', 'in:activo,inactivo,suspendido,pendiente_de_baja'],
        ]);

        $employees = $this->employeesQueryForExport($request)->get();

        $format = $validated['format'];
        $template = $validated['template'] ?? 'normal';

        if ($format === 'excel' && $template === 'campos') {
            $filename = 'listado_campos_'.date('Y-m-d_His').'.xlsx';

            return Excel::download(new EmployeeCamposExport($employees), $filename);
        }

        $columns = $this->normalizeExportColumns($validated['columns'] ?? []);

        if ($format === 'excel') {
            $filename = 'empleados_'.date('Y-m-d_His').'.xlsx';

            return Excel::download(new EmployeeNormalExport($employees, $columns), $filename);
        }

        $headings = $this->exportHeadings($columns);
        $rows = $employees->map(fn (Employee $e) => $this->mapEmployeeExportCells($e, $columns))->all();

        $pdf = Pdf::loadView('exports.employees_pdf', [
            'headings' => $headings,
            'rows' => $rows,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('empleados_'.date('Y-m-d_His').'.pdf');
    }

    /**
     * Same filters as index(), without pagination.
     */
    protected function employeesQueryForExport(Request $request)
    {
        $query = Employee::with(['establishment:id,nombre', 'category:id,nombre', 'liquidationModality:id,nombre,precio_por_unidad'])
            ->orderBy('nombre_apellido');

        if ($search = $request->filled('search') ? trim((string) $request->search) : null) {
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

        return $query;
    }

    /**
     * @param  array<int, string>|null  $columns
     * @return array<int, string>
     */
    protected function normalizeExportColumns(?array $columns): array
    {
        $allowed = [
            'nombre_apellido', 'cuil', 'dni', 'establishment', 'categoria',
            'modalidad', 'estado', 'fecha_inicio', 'direccion', 'telefono',
        ];

        $columns = array_values(array_intersect($allowed, $columns ?? []));

        $merged = array_unique(array_merge(['nombre_apellido', 'cuil'], $columns));

        $order = array_flip($allowed);
        usort($merged, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        return array_values($merged);
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    protected function exportHeadings(array $columns): array
    {
        $labels = [
            'nombre_apellido' => 'Nombre y Apellido',
            'cuil' => 'CUIL',
            'dni' => 'DNI',
            'establishment' => 'Establecimiento',
            'categoria' => 'Categoría',
            'modalidad' => 'Modalidad',
            'estado' => 'Estado',
            'fecha_inicio' => 'Fecha Inicio',
            'direccion' => 'Dirección',
            'telefono' => 'Teléfono',
        ];

        return array_map(fn ($c) => $labels[$c] ?? $c, $columns);
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    protected function mapEmployeeExportCells(Employee $employee, array $columns): array
    {
        $cells = [];
        foreach ($columns as $col) {
            $cells[] = match ($col) {
                'nombre_apellido' => $employee->nombre_apellido,
                'cuil' => $employee->cuil,
                'dni' => $employee->dni,
                'establishment' => $employee->establishment?->nombre ?? '',
                'categoria' => $employee->category?->nombre ?? '',
                'modalidad' => $employee->liquidationModality?->nombre ?? '',
                'estado' => $this->estadoLabelForExport($employee->estado),
                'fecha_inicio' => $employee->fecha_inicio?->format('d/m/Y') ?? '',
                'direccion' => $employee->direccion ?? '',
                'telefono' => $employee->telefono ?? '',
                default => '',
            };
        }

        return $cells;
    }

    protected function estadoLabelForExport(?string $estado): string
    {
        return match ($estado) {
            'activo' => 'Activo',
            'inactivo' => 'Inactivo',
            'suspendido' => 'Suspendido',
            'pendiente_de_baja' => 'Pendiente de baja',
            default => $estado ?? '',
        };
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
            'cuil' => ['required', 'string', 'max:15', 'unique:employees,cuil,'.$employee->id],
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

        if (! $response->successful()) {
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
