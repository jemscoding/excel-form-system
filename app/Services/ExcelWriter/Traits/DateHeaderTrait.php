<?php

namespace App\Services\ExcelWriter\Traits;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait DateHeaderTrait
{

    protected function findLastDateRow(): ?int
    {
        $highestRow = $this->sheet->getHighestRow();
        for ($row = $highestRow; $row >= 3; $row--) {
            $cellValue = $this->sheet->getCell('A' . $row)->getValue();
            if (!empty($cellValue) && preg_match('/[A-Za-z]+\s\d{1,2},\s\d{4}/', $cellValue)) {
                return $row;
            }
        }
        return null;
    }

    protected function getLastStoredDate(?int $dateRow): ?string
    {
        if ($dateRow) {
            $dateStr = $this->sheet->getCell('A' . $dateRow)->getValue();
            return date('Y-m-d', strtotime($dateStr));
        }
        return null;
    }

    protected function addDateHeaderRow(int $row, string $displayDate): void
    {
        // Merge columns A to AI for the date header
        $this->sheet->mergeCells('A' . $row . ':AI' . $row);

        // Set the date in the merged cell
        $this->sheet->setCellValue('A' . $row, $displayDate);

        // Style the date header row
        $this->sheet->getStyle('A' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $this->sheet->getRowDimension($row)->setRowHeight(22);
    }
}
