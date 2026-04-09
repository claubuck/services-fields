<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EmployeeCamposExport implements FromArray, WithEvents
{
    /**
     * Placeholder de fechas como en el listado modelo (columnas E–L).
     */
    protected const DATE_HEADER = '../../......';

    /** Columnas de datos: A ítem, B nombre, C vacío, D CUIL, E–L fechas (8). */
    protected const COL_COUNT = 12;

    /**
     * @param  Collection<int, Employee>  $employees
     */
    public function __construct(
        protected Collection $employees,
        protected string $title = 'LISTADO CONTRATISTA CLAUDIO CAMPOS',
    ) {}

    public function array(): array
    {
        $rows = [];

        $titleRow = array_pad([$this->title], self::COL_COUNT, '');
        $rows[] = $titleRow;

        $rows[] = array_pad([], self::COL_COUNT, '');

        // Fila encabezado: A vacío | B Apellido y Nombre | C vacío | D C.U.I.L | E–L fechas (8)
        $headerRow = ['', 'Apellido y Nombre', '', 'C.U.I.L'];
        for ($i = 0; $i < 8; $i++) {
            $headerRow[] = self::DATE_HEADER;
        }
        $rows[] = $headerRow;

        $n = 1;
        foreach ($this->employees as $employee) {
            $row = [$n++, $employee->nombre_apellido, '', $employee->cuil];
            for ($i = 0; $i < 8; $i++) {
                $row[] = '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:L1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(18);
                $sheet->getRowDimension(2)->setRowHeight(8.25);

                $sheet->getStyle('B3:L3')->getFont()->setBold(true);
                $sheet->getStyle('A3:L3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B3:L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $lastRow = 3 + $this->employees->count();
                if ($lastRow >= 4) {
                    $sheet->getStyle('A4:D'.$lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->getColumnDimension('A')->setWidth(5.43);
                $sheet->getColumnDimension('B')->setWidth(35.14);
                $sheet->getColumnDimension('C')->setWidth(4);
                $sheet->getColumnDimension('D')->setWidth(15);
                foreach (range('E', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(9.29);
                }

                $sheet->getStyle('A3:L'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
