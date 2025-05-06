<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;


class AopApplicationExport implements FromArray, WithEvents, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        // Return empty; we'll inject values manually
        return [];
    }

    public function title(): string
    {
        return 'AOP Application';
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                $templatePath = storage_path('app/template/operational_plan.xlsx');
                $spreadsheet = IOFactory::load($templatePath);

                // Replace the writer's spreadsheet with the loaded template
                $event->writer->setDelegate($spreadsheet);

                // Now populate the template
                $sheet = $spreadsheet->getActiveSheet();
                $row = 14;

                foreach ($this->data as $item) {
                    $sheet->setCellValue("B{$row}", $item['objective']);
                    $sheet->setCellValue("C{$row}", $item['success indicator']);

                    $activityDescriptions = collect($item['activities'])->map(function ($act) {
                        return $act['name'] . ' | ' . $act['responsible_people'];
                    })->implode("\n");

                    $sheet->setCellValue("D{$row}", $activityDescriptions);
                    $row++;
                }
            },
        ];
    }
}
