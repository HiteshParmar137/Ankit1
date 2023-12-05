<?php

namespace App\Exports;

use App\Enums\Days;
use App\Helper\Helpers;
use App\Http\Resources\Api\V1\MyTimesheet\MyTimesheetLogsExportResource;
use App\Models\Holiday;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MyTimesheetLogsExports implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, WithEvents, WithColumnWidths
{
    public function __construct(
        protected $myTimesheetLogs,
        protected $user,
        protected $data = ([]),
    ) {
    }

    public function collection()
    {
        $this->data =  $this->myTimesheetLogs->map(
            fn ($myTimesheetLog): MyTimesheetLogsExportResource => new MyTimesheetLogsExportResource(
                $myTimesheetLog
            )
        );

        return $this->data;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            "Date",
            "Day",
            "Billed Hours",
            "Description",
            "Monthly Billed Total",
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 10,
            'C' => 15,
            'D' => 30,
            'E' => 15,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Total data count + (skip first three rows(3) + add (1) to add new last row at the bottom)
        $lastECell = ($this->data->count() + 4);

        // All borders
        $sheet->getStyle('A1:E' . $lastECell)
            ->applyFromArray([
                'borders' => [
                    'allBorders' => $this->getCellBorder(),
                ],
                'alignment' => $this->getCellAlignment(),
        ]);

        // Info cells top and bottom thick borders
        $sheet->getStyle('A2:E2')->applyFromArray([
            'borders' => [
                'left' => $this->getCellBorder(),
                'right' => $this->getCellBorder(),
                'top' => $this->getCellBorder(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
                ),
                'bottom' => $this->getCellBorder(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
                ),
            ],
        ]);

        $totalMonthHours = 0;

        $actualTotalMonthHours = 0;

        $lastRowOfData = $this->data->count();

        foreach ($this->data as $index => $data) {
            $row = $index + 4;

            $holidays = Holiday::pluck('name', 'date')->toArray();

            $weekEndDays = Helpers::getWeekEndDays();

            /**
             * Date: 10th May 2023
             * 
             * ! NOTE: Removed bellow condition from the bellow if() condition due to client requirement
             * of user can also manage the is_holiday_or_on_leave for the weekend days.
             * 
             * in_array(
             *     Carbon::createFromFormat('Y-m-d', $data->date)->format('l'),
             *     $weekEndDays
             * )
             */
            if (
                $data->is_holiday_or_on_leave ||
                array_key_exists(
                    Carbon::parse($data->date)->format('Y-m-d'),
                    $holidays
                )
            ) {
                $sheet->getStyle("B" . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFF700',
                        ]  
                    ],
                ]);

                if (array_key_exists(
                    Carbon::parse($data->date)->format('Y-m-d'),
                    $holidays
                )) {
                    $sheet->getStyle("D" . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'FFF700',
                            ]  
                        ],
                    ]);
                }
            }

            $actualTotalMonthHours += ($data->worked_hours ?? 0);
            $totalMonthHours += ($data->worked_hours ?? 0);

            if (
                $this->data->last()->date === $data->date ||
                Carbon::parse($data->date)->isLastOfMonth()
            ) {
                $sheet->getStyle('A' . $row . ':' . 'E' . $row)->applyFromArray([
                    'borders' => [
                        'left' => $this->getCellBorder(),
                        'right' => $this->getCellBorder(),
                        'top' => $this->getCellBorder(),
                        'bottom' => $this->getCellBorder(
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
                        ),
                    ],
                ]);

                $sheet->setCellValue('E' . $row, $totalMonthHours);
                $totalMonthHours = 0;
            }
        };

        // Last row of the data + add 4 to skip the first 4 row of the sheet = will add new last row for the year total
        $newLastRowForTotal = $lastRowOfData + 4;

        $sheet->getStyle('A' . $newLastRowForTotal . ':' . 'E' . $newLastRowForTotal)->applyFromArray([
            'borders' => [
                'left' => $this->getCellBorder(),
                'right' => $this->getCellBorder(),
                'top' => $this->getCellBorder(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
                ),
                'bottom' => $this->getCellBorder(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK
                ),
            ],
        ]);

        $sheet->setCellValue('A' . $newLastRowForTotal, "Year Totals");
        $sheet->setCellValue('C' . $newLastRowForTotal, $actualTotalMonthHours);
        $sheet->setCellValue('E' . $newLastRowForTotal, $actualTotalMonthHours);

        $sheet->getStyle('A' . $newLastRowForTotal)->getFont()->setBold(true);
        $sheet->getStyle('C' . $newLastRowForTotal)->getFont()->setBold(true);
        $sheet->getStyle('E' . $newLastRowForTotal)->getFont()->setBold(true);

        return [
            // Style the row as bold text.
            1 => ['font' => ['size' => 16, 'bold' => true]],
            2 => ['font' => ['size' => 16, 'bold' => true]],
            3 => ['font' => ['size' => 16, 'bold' => true]],
        ];
    }

    public function registerEvents(): array {
        
        return [
            AfterSheet::class => function(AfterSheet $event) {
                /** @var Sheet $sheet */
                $sheet = $event->sheet;

                $sheet->mergeCells('A1:E1');
                // $sheet->setCellValue('A1', $this->user->userProfile->profile->profile_code. " Timesheet Year " . date("Y"));
                $sheet->setCellValue('A1', config('app.timesheet_export_first_row') . " " . date("Y"));

                $sheet->mergeCells('A2:E2');
                // $sheet->setCellValue('A2', "Contractor's Name: __________________ Your Name: " . $this->user->full_name);
                $sheet->setCellValue('A2', "Contractor's Name: ".config('app.timesheet_export_contractors_name').",Your Name: " . $this->user->full_name);
                
                $sheet->getDelegate()
                    ->getStyle('A1:E1')
                    ->applyFromArray(
                        [
                            'alignment' => $this->getCellAlignment(),
                            'fill' => $this->getCellBackgroundColor('C4BD97'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getDelegate()
                    ->getStyle('A2:E2')
                    ->applyFromArray(
                        [
                            'fill' => $this->getCellBackgroundColor('FFF700'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getDelegate()
                    ->getStyle('A3:B3')
                    ->applyFromArray(
                        [
                            'fill' => $this->getCellBackgroundColor('C4BD97'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getDelegate()
                    ->getStyle('C3')
                    ->applyFromArray(
                        [
                            'fill' => $this->getCellBackgroundColor('FFB600'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                $sheet->getDelegate()
                    ->getStyle('D3')
                    ->applyFromArray(
                        [
                            'fill' => $this->getCellBackgroundColor('C4BD97'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getDelegate()
                    ->getStyle('E3')
                    ->applyFromArray(
                        [
                            'fill' => $this->getCellBackgroundColor('FFB600'),
                        ]
                    )
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);


                // Row height
                for ($i = 1; $i <= 400; $i++) {
                    $sheet->getDelegate()->getRowDimension($i)->setRowHeight(
                        ($i == 3 ? 80 : (in_array($i, [1,2]) ? 40 : 18))
                    );
                }

                // Font colors
                $sheet->getDelegate()->getStyle('A1:E3')
                    ->getFont()
                    ->getColor()
                    ->setARGB('0000FF');

                $event->sheet->getDelegate()->getStyle('A4:E400')->getFont()->setSize(10);
            },
        ];
    }

    private function getCellBackgroundColor(string $color)
    {
        //Set background style
        return [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => $color,
            ]           
        ];
    }

    private function getCellBorder(
        string $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    ) {
        //Set border style
        return [
            'borderStyle' => $borderStyle,
            'color' => ['argb' => 'FF000000'],           
        ];
    }

    private function getCellAlignment()
    {
        //Set cell alignment
        return [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ];
    }
}
