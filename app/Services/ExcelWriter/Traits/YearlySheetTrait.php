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
    
    // Track current month to avoid duplicate headers
    protected string $currentMonthHeader = '';

    public function setSpreadsheet(Spreadsheet $spreadsheet)
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

    /**
     * Get the month from a date
     */
    protected function getMonthFromDate(?string $date): string
    {
        if (empty($date)) {
            return "AKPH " . date('Y') . " - " . "Empty Month";
        }
        return "AKPH " . date('Y', strtotime($date)) . " - " . strtoupper(date('F', strtotime($date)));
    }

    /**
     * Add a month header row
     */
    protected function addMonthHeaderRow(Worksheet $sheet, int $row, string $monthYear): void
    {
        // Format: AKPH 2024 - JANUARY
        $headerText = strtoupper($monthYear);
        
        // Merge columns A to Z for the month header
        $sheet->mergeCells('A' . $row . ':Z' . $row);
        $sheet->setCellValue('A' . $row, $headerText);
        
        // Style the month header
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D91111']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    /**
     * Find the last month header row
     */
    protected function findLastMonthHeaderRow(Worksheet $sheet): ?int
    {
        $highestRow = $sheet->getHighestRow();
        for ($row = $highestRow; $row >= 2; $row--) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            // Check if this matches the new pattern: AKPH 2024 - JANUARY
            if (!empty($cellValue) && preg_match('/AKPH\s\d{4}\s-\s[A-Z]+/', $cellValue)) {
                return $row;
            }
        }
        return null;
    }

    protected function getStatusColor(string $status): string
    {
        switch (strtolower(trim($status))) {
            case 'paid':
                return 'F0CFA3';
            case 'pending':
                return 'F2E6B6'; 
            case 'unpaid':
                return 'FFFFFF';
            default:
                return 'FFFFFF';
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

        // Get the month from the china received date or created_at
        $dateToCheck = $data['china_received_date'] ?? $data['created_at'] ?? null;
        $currentMonth = $this->getMonthFromDate($dateToCheck);
        
        // Get the last row with data
        $lastRow = $yearlySheet->getHighestRow();
        if ($lastRow < 1) {
            $lastRow = 1;
        }
        
        // Find the last month header row
        $lastMonthHeaderRow = $this->findLastMonthHeaderRow($yearlySheet);
        $lastMonth = $lastMonthHeaderRow ? $yearlySheet->getCell('A' . $lastMonthHeaderRow)->getValue() : null;
        
        $newRow = $lastRow + 1;
        
        // ALWAYS show month header BEFORE data for new month
        if ($lastMonth !== $currentMonth) {
            // Insert a new row for the month header at the current position
            $yearlySheet->insertNewRowBefore($newRow);
            $this->addMonthHeaderRow($yearlySheet, $newRow, $currentMonth);
            $newRow++; // Move past the header row
        }

        // Now write the data row
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
        $yearlySheet->setCellValue('C' . $newRow, isset($data['china_received_date']) ? date('m/d/Y', strtotime($data['china_received_date'])) : date('m/d/Y'));
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

        // Apply styles to all columns
        $allColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
                    'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'Y', 'Z'];
        
        $specialColors = [
            'Y' => ['color' => '99ACBF'],
            'Z' => ['color' => 'C78197'],
        ];
        
        foreach ($allColumns as $col) {
            $cellCoordinate = $col . $newRow;
            
            // Set alignment
            $yearlySheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            
            // Set borders
            $yearlySheet->getStyle($cellCoordinate)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
            // Set fill color
            if (isset($specialColors[$col])) {
                $yearlySheet->getStyle($cellCoordinate)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $specialColors[$col]['color']]
                    ]
                ]);
            } else {
                $yearlySheet->getStyle($cellCoordinate)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $statusColor]
                    ]
                ]);
            }
        }

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