<?php

namespace App\Services\ExcelWriter\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait YearlySheetTrait
{
    protected Spreadsheet $spreadsheet;

    public function setSpreadsheet(Spreadsheet$spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    protected function setupYearlyShipmentSheet(Worksheet $sheet): void
    {
        $currentYear = date('Y');
        $sheet->setTitle($this->getYearlySheetName());

        $headers = [
            'A' => '#', 'B' => 'Container Code', 'C' => 'China Received', 'D' => 'Order Reference',
            'E' => 'Client Code', 'F' => 'Order Line/Product', 'G' => 'PKGs', 'H' => 'Total CBM',
            'I' => 'WEIGHT', 'J' => 'Applied Rate', 'K' => 'SOA Number', 'L' => 'Client Name',
            'M' => 'SERVICE FEE', 'N' => 'INBOUND COST', 'O' => 'OVERWEIGHT CHARGE',
            'P' => 'INITIAL BILLING', 'Q' => 'OTHERS', 'R' => 'Withholding Tax',
            'S' => 'Discount', 'T' => 'Amount to be Paid', 'U' => 'Actual Payment',
            'V' => 'Balance', 'W' => 'Status', 'Y' => 'AKPH BILLING', 'Z' => 'HANDLER',
        ];

        $headerRow = 1;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $this->applyYearlyHeaderStyle($sheet, $headerRow);
        $sheet->freezePane('A2');

        $columnWidths = [
            'A' => 8, 'B' => 18, 'C' => 18, 'D' => 20, 'E' => 18, 'F' => 60, 'G' => 10,
            'H' => 12, 'I' => 15, 'J' => 22, 'K' => 60, 'L' => 18, 'M' => 15, 'N' => 18,
            'O' => 60, 'P' => 18, 'Q' => 22, 'R' => 40, 'S' => 30, 'T' => 30, 'U' => 30,
            'V' => 12, 'W' => 12, 'Y' => 16, 'Z' => 16
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    protected function getStatusColor(string $status): string
    {
        switch (strtolower(trim($status))) {
            case 'paid':
                return 'C6EFCE'; // Light green
            case 'pending':
                return 'FFEB9C'; // Light yellow
            case 'unpaid':
                return 'FFC7CE'; // Light red
            default:
                return 'FFFFFF'; // White default
        }
    }

    protected function addToYearlyShipmentSheet(array $data, int $rowIndex)
    {
        $yearlySheet = $this->spreadsheet->getSheetByName($this->getYearlySheetName());
        if (!$yearlySheet) {
            $yearlySheet = new Worksheet($this->spreadsheet, $this->getYearlySheetName());
            $this->spreadsheet->addSheet($yearlySheet);
            $this->setupYearlyShipmentSheet($yearlySheet);
        }

        $lastRow = $yearlySheet->getHighestRow();
        if ($lastRow < 1) {
            $lastRow = 1;  // Headers are at row 1, so start data from row 2
        }

        $newRow = $lastRow + 1;

        $inboundCost = floatval($data['inbound_cost'] ?? 0);
        $serviceFee = floatval($data['service_fee'] ?? 0);
        $overweight = floatval($data['overweight'] ?? 0);
        $others = floatval($data['others'] ?? 0);
        $discount = floatval($data['discount'] ?? 0);
        $withholdingTax = floatval($data['withholding_tax'] ?? 0);
        $amountToBePaid = ($inboundCost + $serviceFee + $overweight + $others) - ($discount + $withholdingTax);
        $balance = '';

        $akphbilling = '';
        $handler = '';

        $productName = $data['product_name'] ?? '';
        $productCode = $data['product_code'] ?? $data['code'] ?? '';
        $productValue = trim($productName . ($productName && $productCode ? ' - ' : '') . $productCode);

        // Get status for color coding
        $status = $data['status'] ?? 'Pending';
        $statusColor = $this->getStatusColor($status);

        // Write data to cells
        $yearlySheet->setCellValue('A' . $newRow, $rowIndex);
        $yearlySheet->setCellValue('B' . $newRow, $data['container_code'] ?? '');
        $yearlySheet->setCellValue('C' . $newRow, isset($data['china_recieved_date']) ? date('m/d/Y', strtotime($data['china_recieved_date'])) : date('m/d/Y'));
        $yearlySheet->setCellValue('D' . $newRow, $data['order_reference'] ?? '');
        $yearlySheet->setCellValue('E' . $newRow, 'AKPH-' . ($data['client_code'] ?? ''));
        $yearlySheet->setCellValue('F' . $newRow, $productValue);
        $yearlySheet->setCellValue('G' . $newRow, $data['pkgs'] ?? '');
        $yearlySheet->setCellValue('H' . $newRow, $data['total_cbm'] ?? '');
        $yearlySheet->setCellValue('I' . $newRow, $data['weight'] ?? '');
        $yearlySheet->setCellValue('J' . $newRow, $data['applied_rate'] ?? '');
        $yearlySheet->setCellValue('K' . $newRow, $data['soa_number'] ?? '');
        $yearlySheet->setCellValue('L' . $newRow, $data['client_name'] ?? '');
        $yearlySheet->setCellValue('M' . $newRow, $serviceFee);
        $yearlySheet->setCellValue('N' . $newRow, $inboundCost);
        $yearlySheet->setCellValue('O' . $newRow, $overweight);
        $yearlySheet->setCellValue('P' . $newRow, $data['initial_billing'] ?? 0);
        $yearlySheet->setCellValue('Q' . $newRow, $others);
        $yearlySheet->setCellValue('R' . $newRow, $withholdingTax);
        $yearlySheet->setCellValue('S' . $newRow, $discount);
        $yearlySheet->setCellValue('T' . $newRow, $amountToBePaid);
        $yearlySheet->setCellValue('U' . $newRow, $amountToBePaid);
        $yearlySheet->setCellValue('V' . $newRow, $balance);
        $yearlySheet->setCellValue('W' . $newRow, $status);
        $yearlySheet->setCellValue('Y' . $newRow, $akphbilling);
        $yearlySheet->setCellValue('Z' . $newRow, $data['handler'] ?? '');

        // Apply base styling to all data columns (A to Z)
        $allColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
                       'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',];
        
        foreach ($allColumns as $col) {
            $cellCoordinate = $col . $newRow;
            
            $yearlySheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $yearlySheet->getStyle($cellCoordinate)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]]
            ]);
            
        }

            $yearlySheet->getStyle('X' . $newRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' =>Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
                ]
            ]);

            $yearlySheet->getStyle('Y'. $newRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

            $yearlySheet->getStyle('Y' . $newRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' =>Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '99ACBF']]
                ]
            ]);

            $yearlySheet->getStyle('Z' . $newRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

                $yearlySheet->getStyle('Z' . $newRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C78197']]
                    ]
                ]);

        // Apply currency formatting
        $currencyColumns = ['M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'];
        foreach ($currencyColumns as $col) {
            $yearlySheet->getStyle($col . $newRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        }

        // Make specific columns bold
        $boldColumns = ['A', 'E', 'K', 'T', 'U', 'W'];
        foreach ($boldColumns as $col) {
            $yearlySheet->getStyle($col . $newRow)->getFont()->setBold(true);
        }

        $yearlySheet->getRowDimension($newRow)->setRowHeight(20);
    }
}