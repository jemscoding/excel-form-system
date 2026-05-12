<?php

namespace App\Services\ExcelWriter\Traits;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

trait StylingTrait
{

    protected function applyCellStyle(int $row, bool $isHeader = false)
    {

        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'
        ];

        foreach ($columns as $column) {
            $cellCoordinate = $column . $row;

            if ($isHeader) {
                $this->sheet->getStyle($cellCoordinate)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false);

                $styleConfig = [
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];

                switch ($column) {
                    case 'A': case 'B': case 'C': case 'D': case 'E': case 'F': case 'G': case 'H': case 'I': case 'J':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E1C1E8']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'K': case 'L': case 'N': case 'O': case 'P': case 'Q': case 'R': case 'S': case 'T':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'M':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1BF72C']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'U': case 'AI':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C8AFC']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'W': case 'X':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'V':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E06458']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'Y': case 'Z': case 'AA': case 'AB': case 'AC': case 'AD': case 'AE': case 'AF': case 'AG': case 'AH':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCF2C0']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    default:
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                }

                $this->sheet->getStyle($cellCoordinate)->applyFromArray($styleConfig);
            } else {
                $this->sheet->getStyle($cellCoordinate)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $this->sheet->getStyle($cellCoordinate)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
            }
        }

        if ($isHeader) {
            $this->sheet->getRowDimension($row)->setRowHeight(30);
        } else {
            $this->sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    protected function applyAlternateRowColor(int $row): void
    {
        if ($row % 2 == 0) {
            $this->sheet->getStyle('A' . $row . ':AI' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);
        }
    }

    protected function applyDataRowStyle(int $row): void
    {
        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'
        ];

        foreach ($columns as $column) {
            $cellCoordinate = $column . $row;
            $this->sheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

            $this->sheet->getStyle($cellCoordinate)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }

        $this->sheet->getRowDimension($row)->setRowHeight(20);
    }

    protected function applyYearlyHeaderStyle(Worksheet $sheet, int $row) : void
    {
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T','U', 'V', 'W', 'Y', 'Z'];

        foreach ($columns as $column) {
            $cellCoordinate = $column . $row;

            $sheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(false);

            $styleConfig = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ];

            switch ($column) {
                case 'A': case 'B': case 'C': case 'D': case 'E': case 'F': case 'G': case 'H': case 'I':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCF928']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'J': case 'K': case 'L': case 'M': case 'N': case 'O': case 'P': case 'Q': case 'R': case 'S': case 'T':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'U': case 'V':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCF2C0']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'W':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '297A09']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'Y': case 'Z':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FA7932']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                default:
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']];
                    break;
            }

            $sheet->getStyle($cellCoordinate)->applyFromArray($styleConfig);
        }

        $sheet->getRowDimension($row)->setRowHeight(25);
    }

    public function applyBankTransactionHeaderStyle(Worksheet $sheet, int $row)
    {
        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'
        ];

        foreach ($columns as $column) {
            $cellCoordinate = $column . $row;
              $sheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(false);

                $sheet->getStyle($cellCoordinate)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '297A09']]
                ]);
             
        };
    }   
}