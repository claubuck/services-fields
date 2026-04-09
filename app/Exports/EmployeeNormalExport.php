<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeNormalExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    /** @var array<string, string> */
    protected const COLUMN_LABELS = [
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

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  array<int, string>  $columns
     */
    public function __construct(
        protected Collection $employees,
        protected array $columns,
    ) {}

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $key) {
            if (isset(self::COLUMN_LABELS[$key])) {
                $headings[] = self::COLUMN_LABELS[$key];
            }
        }

        return $headings;
    }

    /**
     * @param  Employee  $employee
     */
    public function map($employee): array
    {
        $row = [];
        foreach ($this->columns as $col) {
            $row[] = match ($col) {
                'nombre_apellido' => $employee->nombre_apellido,
                'cuil' => $employee->cuil,
                'dni' => $employee->dni,
                'establishment' => $employee->establishment?->nombre ?? '',
                'categoria' => $employee->category?->nombre ?? '',
                'modalidad' => $employee->liquidationModality?->nombre ?? '',
                'estado' => $this->estadoLabel($employee->estado),
                'fecha_inicio' => $employee->fecha_inicio?->format('d/m/Y') ?? '',
                'direccion' => $employee->direccion ?? '',
                'telefono' => $employee->telefono ?? '',
                default => '',
            };
        }

        return $row;
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach ($this->columns as $index => $col) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            $widths[$letter] = match ($col) {
                'nombre_apellido' => 35,
                'direccion' => 40,
                'establishment' => 25,
                default => 15,
            };
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    protected function estadoLabel(?string $e): string
    {
        return match ($e) {
            'activo' => 'Activo',
            'inactivo' => 'Inactivo',
            'suspendido' => 'Suspendido',
            'pendiente_de_baja' => 'Pendiente de baja',
            default => $e ?? '',
        };
    }
}
