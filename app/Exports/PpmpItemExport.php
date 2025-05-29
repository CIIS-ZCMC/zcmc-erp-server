<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PpmpItemExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $ppmpTotal;

    // Accept data via constructor
    public function __construct($data)
    {
        $this->data = $data;
        $this->ppmpTotal = $data['ppmp_items']->sum(fn($item) => $item->item->total_amount ?? 0);
    }

    public function collection()
    {
        return $this->data['ppmp_items']->map(function ($item) {
            // Build a month-to-quantity map with default values
            $months = [
                '1' => '-',
                '2' => '-',
                '3' => '-',
                '4' => '-',
                '5' => '-',
                '6' => '-',
                '7' => '-',
                '8' => '-',
                '9' => '-',
                '10' => '-',
                '11' => '-',
                '12' => '-',
            ] ?? '-';

            // Populate quantities if available and not zero
            foreach ($item->ppmpSchedule as $schedule) {
                $month = $schedule['month'] ?? '-';
                $quantity = $schedule['quantity'] ?? 0;

                if ($month && array_key_exists($month, $months)) {
                    $months[$month] = ($quantity == 0) ? '-' : $quantity;
                }
            }

            return array_merge([
                $item->item->name ?? '-',
                $item->item->itemClassification->name ?? '-',
                $item->item->itemCategory->name ?? '-',
                $item->total_quantity === 0 ? '-' : $item->total_quantity ?? '-',
                $item->item->itemUnit->code ?? '-',
                round($item->item->estimated_budget, 2) ?? '-',
                round($item->item->total_amount, 2) ?? '-',
            ], array_values($months), [
                $item->procurementMode->name ?? '-',
                $item->remarks ?? '-',
            ]);
        });
    }

    // Header row
    public function headings(): array
    {
        return [
            ['PROJECT PROCUREMENT MANAGEMENT PLAN'], // Merged title
            [
                'GENERAL DESCRIPTION',
                'CLASSIFICATION',
                'ITEM CATEGORY',
                'QTY',
                'UNIT',
                'ESTIMATED BUDGET',
                'TOTAL AMOUNT',
                'SCHEDULE / MILESTONE',
                '', // Jan–Dec placeholders (12)
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'MODE OF PROCUREMENT',
                'REMARKS',
            ],
            ['', '', '', '', '', '', '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'MODE OF PROCUREMENT', 'REMARKS'],     // Row 2

        ];
    }

    // Styling the header row
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:S1');

        // Style title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
        ]);

        // Style main header row (Row 2)
        $sheet->getStyle('A2:U2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
        ]);

        // Style month row (Row 3)
        $sheet->getStyle('H3:S3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
        ]);


        // Style "PPMP TOTAL" label
        $sheet->getStyle('T1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
        ]);

        // Style value cell
        $sheet->getStyle('U1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set "PPMP TOTAL" in U1
                // Set "PPMP TOTAL" label and value in U1 and V1
                $sheet->setCellValue('T1', 'PPMP TOTAL');
                $sheet->setCellValue('U1', number_format($this->ppmpTotal, 2));

                // Merge "SCHEDULE / MILESTONE" across Jan–Dec (H2:S2)
                $sheet->mergeCells('H2:S2');

                // Merge other vertical cells for headers (A2:A3, etc.)
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'T', 'U', 'V'] as $col) {
                    $sheet->mergeCells("{$col}2:{$col}3");
                }

                // Apply border to entire header section
                $sheet->getStyle('A1:U3')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Format columns F and G as numbers with 2 decimal places
                $sheet->getStyle('F4:F1000')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('G4:G1000')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Optional: freeze header rows
                // $sheet->freezePane('A4');
            },
        ];
    }

    public function title(): string
    {
        return 'PPMP ITEM';
    }
}
