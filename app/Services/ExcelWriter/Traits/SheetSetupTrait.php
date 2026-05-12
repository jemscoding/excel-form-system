<?php

namespace App\Services\ExcelWriter\Traits;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait SheetSetupTrait
{
    protected Worksheet$sheet;

    protected function getYearlySheetName()
    {
        return "AKPH " . date('Y') . " SHIPMENT";
    }

    protected function getBankTransactionSheetName()
    {
        return "BANK TRANSACTION " . date('Y');
    }

    protected function setupDailySheet(Worksheet $sheet) : void
    {
        $currentYear = date('Y');
        $this->sheet = $sheet;
        $this->applyGlobalStyles();

        $noteRow = ['N1', 'U1', 'V1', 'Y1', 'AC1', 'AD1'];

        $noteRow2 = ['AH1', 'AI1'];

        // Daily Header
        $sheet->setCellValue('A1', "OUTPUT" ."\n" . "AREA");
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000BFF']],
        ]);

        $sheet->setCellValue('D1', 'NOTE: From Container Code to Weight - copy data from AKPH'. date('Y') .' Sheet MNL WH Tab');
        $sheet->mergeCells('D1:J1');
        $sheet->getStyle('D1')->applyFromArray([
            'font' => ['size' => 10, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
        ]);

        $sheet->setCellValue('K1', "AKPH \n" . $currentYear . ' | ' . $currentYear);
        $sheet->getStyle('K1')->getFont()->setUnderline(true);
        $sheet->getStyle('K1')->getFont()->setBold(true);
        $sheet->getStyle('K1')->applyFromArray([
            'font' => ['size' => 13, 'color' => ['rgb' => '000BB0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('K1')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('L1', "AKPH-SAP \n SHIPMENT");
        $sheet->getStyle('L1')->getFont()->setUnderline(true);
        $sheet->getStyle('L1')->getFont()->setBold(true);
        $sheet->getStyle('L1')->applyFromArray([
            'font' => ['size' => 13, 'color' => ['rgb' => '000BB0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('L1')->getAlignment()->setWrapText(true);

        $sheet->getStyle('M1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']]
        ]);

        foreach ($noteRow as $row) {
            $sheet->setCellValue($row, "NOTE: Line ". "\n" . " has formula ". "\n" . " DON'T EDIT");
            $sheet->getStyle($row)->getFont()->setBold(true);
            $sheet->getStyle($row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle($row)->getAlignment()->setWrapText(true);
        };

        $sheet->setCellValue('AH1', "NOTE: DATA IS COPIED ". "\n" . " FROM ORDER REFERENCE NUMBER ". "\n" . " (DO NOT EDIT - LINE HAS FORMULA)");
        $sheet->getStyle('AH1')->getFont()->setBold(true);
        $sheet->getStyle('AH1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('AH1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        

        $sheet->setCellValue('O1', "COPY OF \n AKPH \n" . date('Y'));
        $sheet->getStyle('O1')->getFont()->setUnderline(true);
        $sheet->getStyle('O1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('O1')->getFont()->setBold(true);
        $sheet->getStyle('O1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->setCellValue('AI1', "NOTE: DATA IS COPIED ". "\n" . " FROM SOA NUMBER ". "\n" . " (DO NOT EDIT - LINE HAS FORMULA)");
        $sheet->getStyle('AI1')->getFont()->setBold(true);
        $sheet->getStyle('AI1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('AI1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);


        $headers = [
            'A' => '#', 'B' => 'Container Code', 'C' => 'China Received', 'D' => 'Order Reference #',
            'E' => 'Client Code', 'F' => 'Order Lines/Product', 'G' => 'PKGs', 'H' => 'Total CBM',
            'I' => 'WEIGHT', 'J' => 'Applied Rate', 'K' => 'SOA Number', 'L' => 'Client Name',
            'M' => 'ACTUAL PAYMENT', 'N' => 'INITIAL BILLING', 'O' => 'Witholding Tax',
            'P' => 'INBOUND COST', 'Q' => 'SERVICE FEE', 'R' => 'OVERWEIGHT CHARGE',
            'S' => 'Discount', 'T' => 'OTHERS', 'U' => 'AMOUNT TO BE PAID (SOA)',
            'V' => 'BALANCE', 'W' => 'DEPOSITING BANK', 'X' => 'PAYMENT REFERENCE NUMBER',
            'Y' => 'Status', 'Z' => 'DATE POSTED', 'AA' => 'DEPOSIT DATE', 'AB' => 'TIME',
            'AC' => 'CLIENT CODE', 'AD' => 'TOTAL PHP', 'AE' => 'RECEIVING BANK',
            'AF' => 'PURPOSE', 'AG' => 'AGENT', 'AH' => 'SHIPMENT REF #', 'AI' => 'SOA CODE',
        ];

        $headerRow = 2;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $this->applyCellStyle($headerRow, true);
        // $sheet->freezePane('' . ($headerRow + 1));

        $columnWidths = [
            'A' => 8, 'B' => 18, 'C' => 15, 'D' => 20, 'E' => 18, 'F' => 100, 'G' => 10,
            'H' => 12, 'I' => 12, 'J' => 15, 'K' => 60, 'L' => 25, 'M' => 18, 'N' => 18,
            'O' => 15, 'P' => 18, 'Q' => 18, 'R' => 20, 'S' => 15, 'T' => 15, 'U' => 22,
            'V' => 15, 'W' => 20, 'X' => 25, 'Y' => 12, 'Z' => 15, 'AA' => 15, 'AB' => 12,
            'AC' => 18, 'AD' => 18, 'AE' => 22, 'AF' => 18, 'AG' => 20, 'AH' => 18, 'AI' => 22
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    protected function applyGlobalStyles()
    {
        $this->sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $this->sheet->getPageMargins()->setTop(0.75);
        $this->sheet->getPageMargins()->setRight(0.7);
        $this->sheet->getPageMargins()->setLeft(0.7);
        $this->sheet->getPageMargins()->setBottom(0.75);
        $this->sheet->getPageSetup()->setPrintArea('A1:AI200');

        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'
        ];

        foreach ($columns as $column) {
            $this->sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}