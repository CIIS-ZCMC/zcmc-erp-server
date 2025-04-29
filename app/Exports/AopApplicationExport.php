<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class AopApplicationExport implements FromCollection, WithHeadings
{
    protected $objectives;

    public function __construct($objectives)
    {
        $this->objectives = $objectives;
    }

    public function collection()
    {
        return collect($this->objectives); // Make sure $this->objectives is an array or collection
    }

    public function headings(): array
    {
        return [
            'OBJECTIVE',
            'SUCCESS INDICATOR',
            'PROGRAMS/ ACTIVITIES/ PROJECTS',
            'Timeframe',
            'TARGET (by Quarter)',
            'RESOURCE REQUIREMENT',
            'OBJECT CATEGORY (MOOE or CO)',
            'RESPONSIBLE PERSON',
            'GAD(YES/NO)'

        ];
    }
    public function styles(Worksheet $sheet)
    {
        // Apply style to header row A1 to Z1 (adjust range to fit your actual headers)
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Times New Roman', // Set font name
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 40,
            'C' => 50,

        ];
    }
}
