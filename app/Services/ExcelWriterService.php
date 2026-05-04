<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;

class ExcelWriterService
{
    protected $filePath;
    protected $spreadsheet;
    protected $sheet;

    public function __construct()
    {
        $this->filePath = storage_path('app/excel_forms.xlsx');

        // Ensure directory exists
        $this->ensureDirectoryExists();

        Log::info('Excel file path: ' . $this->filePath);
        $this->initializeExcel();
    }

    protected function ensureDirectoryExists()
    {
        $directory = dirname($this->filePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
            Log::info('Created directory: ' . $directory);
        }
    }

    protected function initializeExcel()
    {
        if (file_exists($this->filePath)) {
            Log::info('Loading existing Excel file');
            $this->spreadsheet = IOFactory::load($this->filePath);
        } else {
            Log::info('Creating new Excel file');
            $this->spreadsheet = new Spreadsheet();
            $this->createSheets();

            // Save the file immediately after creating
            $writer = new Xlsx($this->spreadsheet);
            $writer->save($this->filePath);
            Log::info('New Excel file created and saved at: ' . $this->filePath);
        }

        $this->sheet = $this->spreadsheet->getSheetByName('DAILY') ?? $this->spreadsheet->getActiveSheet();
        if (!$this->sheet) {
            $this->sheet = $this->spreadsheet->getActiveSheet();
            $this->sheet->setTitle('DAILY');
        }
    }

    protected function createSheets()
    {
        $dailySheet = $this->spreadsheet->getActiveSheet();
        $dailySheet->setTitle('DAILY');
        $this->setupDailySheet($dailySheet);

        $yearlyShipmentSheet = new Worksheet($this->spreadsheet, $this->getYearlySheetName());
        $this->spreadsheet->addSheet($yearlyShipmentSheet);
        $this->setupYearlyShipmentSheet($yearlyShipmentSheet);
    }

    protected function getYearlySheetName()
    {
        return "AKPH " . date('Y') . " SHIPMENT";
    }

    protected function applyGlobalStyles()
    {
        // Set default font and size for all cells
        $this->sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Set page margins
        $this->sheet->getPageMargins()->setTop(0.75);
        $this->sheet->getPageMargins()->setRight(0.7);
        $this->sheet->getPageMargins()->setLeft(0.7);
        $this->sheet->getPageMargins()->setBottom(0.75);

        // Set print area
        $this->sheet->getPageSetup()->setPrintArea('A1:AI200');

        // Set all columns to auto-size initially
        $columns = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI'
        ];

        foreach ($columns as $column) {
            $this->sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    protected function applyCellStyle($row, $isHeader = false)
    {
        $columns = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI'
        ];

        foreach ($columns as $column) {
            $cellCoordinate = $column . $row;

            if ($isHeader) {
                // Header styling - NO WRAPPING, with padding
                $this->sheet->getStyle($cellCoordinate)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false);

                // Default header styling
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

                // Apply specific background colors based on column
                switch ($column) {
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E1C1E8']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'K':
                    case 'L':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'M':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1BF72C']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'U':
                    case 'AI':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C8AFC']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'W':
                    case 'X':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0FFBF']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'V':
                        $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E06458']];
                        $styleConfig['font']['color'] = ['rgb' => '000000'];
                        break;
                    case 'Y':
                    case 'Z':
                    case 'AA':
                    case 'AB':
                    case 'AC':
                    case 'AD':
                    case 'AE':
                    case 'AF':
                    case 'AG':
                    case 'AH':
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
                // Data rows styling
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

        // Set row height
        if ($isHeader) {
            $this->sheet->getRowDimension($row)->setRowHeight(30);
        } else {
            $this->sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    protected function setupDailySheet($sheet)
    {
        $currentYear = date('Y');
        $this->sheet = $sheet;
        $this->applyGlobalStyles();

        // Header title at row 1
        $sheet->setCellValue('A1', 'OUTPUT AREA');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000BFF']],
        ]);

        // Note at row 1
        $sheet->setCellValue('D1', 'NOTE: From Container Code to Weight - copy data from AKPH 2025 Sheet MNL WH Tab');
        $sheet->mergeCells('D1:J1');
        $sheet->getStyle('D1')->applyFromArray([
            'font' => ['size' => 10, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
        ]);

        // Year display with line break
        $sheet->setCellValue('K1', "AKPH\n" . $currentYear . '|' . $currentYear);
        $sheet->getStyle('K1')->getFont()->setUnderline(true);
        $sheet->getStyle('K1')->getFont()->setBold(true);
        $sheet->getStyle('K1')->applyFromArray([
            'font' => ['size' => 13, 'color' => ['rgb' => '000BB0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getStyle('K1')->getAlignment()->setWrapText(true);

        // Set headers at row 2
        $headers = [
            'A' => '#',
            'B' => 'Container Code',
            'C' => 'China Received',
            'D' => 'Order Reference #',
            'E' => 'Client Code',
            'F' => 'Order Lines/Product',
            'G' => 'PKGs',
            'H' => 'Total CBM',
            'I' => 'WEIGHT',
            'J' => 'Applied Rate',
            'K' => 'SOA Number',
            'L' => 'Client Name',
            'M' => 'ACTUAL PAYMENT',
            'N' => 'INITIAL BILLING',
            'O' => 'Witholding Tax',
            'P' => 'INBOUND COST',
            'Q' => 'SERVICE FEE',
            'R' => 'OVERWEIGHT CHARGE',
            'S' => 'Discount',
            'T' => 'OTHERS',
            'U' => 'AMOUNT TO BE PAID (SOA)',
            'V' => 'BALANCE',
            'W' => 'DEPOSITING BANK',
            'X' => 'PAYMENT REFERENCE NUMBER',
            'Y' => 'Status',
            'Z' => 'DATE POSTED',
            'AA' => 'DEPOSIT DATE',
            'AB' => 'TIME',
            'AC' => 'CLIENT CODE',
            'AD' => 'TOTAL PHP',
            'AE' => 'RECEIVING BANK',
            'AF' => 'PURPOSE',
            'AG' => 'AGENT',
            'AH' => 'SHIPMENT REF #',
            'AI' => 'SOA CODE',
        ];

        $headerRow = 2;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $this->applyCellStyle($headerRow, true);
        $sheet->freezePane('A3');

        // Set column widths
        $columnWidths = [
            'A' => 8,
            'B' => 18,
            'C' => 15,
            'D' => 20,
            'E' => 18,
            'F' => 30,
            'G' => 10,
            'H' => 12,
            'I' => 12,
            'J' => 15,
            'K' => 22,
            'L' => 25,
            'M' => 18,
            'N' => 18,
            'O' => 15,
            'P' => 18,
            'Q' => 18,
            'R' => 20,
            'S' => 15,
            'T' => 15,
            'U' => 22,
            'V' => 15,
            'W' => 20,
            'X' => 25,
            'Y' => 12,
            'Z' => 15,
            'AA' => 15,
            'AB' => 12,
            'AC' => 18,
            'AD' => 18,
            'AE' => 22,
            'AF' => 18,
            'AG' => 20,
            'AH' => 18,
            'AI' => 22
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }
    protected function setupYearlyShipmentSheet($sheet)
    {
        $currentYear = date('Y');
        $sheet->setTitle($this->getYearlySheetName());

        // Headers
        $headers = [
            'A' => '#',
            'B' => 'Container Code',
            'C' => 'China Received',
            'D' => 'Order Reference',
            'E' => 'Client Code',
            'F' => 'Order Line/Product',
            'G' => 'PKGs',
            'H' => 'Total CBM',
            'I' => 'WEIGHT',
            'J' => 'Applied Rate',
            'K' => 'SOA Number',
            'L' => 'Client Name',
            'M' => 'SERVICE FEE',
            'N' => 'INBOUND COST',
            'O' => 'OVERWEIGHT CHARGE',
            'P' => 'INITIAL BILLING',
            'Q' => 'OTHERS',
            'R' => 'Withholding Tax',
            'S' => 'Discount',
            'T' => 'Amount to be Paid',
            'U' => 'Actual Payment',
            'V' => 'Balance',
            'W' => 'Status',
            'Y' => 'AKPH BILLING',
            'Z' => 'HANDLER',
        ];

        $headerRow = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        // Apply header styling
        $this->applyYearlyHeaderStyle($sheet, $headerRow);

        // Freeze header row
        $sheet->freezePane('A4');

        // Set column widths
        $columnWidths = [
            'A' => 8,
            'B' => 18,
            'C' => 18,
            'D' => 20,
            'E' => 18,
            'F' => 25,
            'G' => 10,
            'H' => 12,
            'I' => 15,
            'J' => 22,
            'K' => 15,
            'L' => 18,
            'M' => 15,
            'N' => 18,
            'O' => 15,
            'P' => 18,
            'Q' => 22,
            'R' => 15,
            'S' => 15,
            'T' => 12
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    protected function applyYearlyHeaderStyle($sheet, $row)
    {
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];

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

            // Apply colors based on column
            switch ($column) {
                case 'A':
                case 'B':
                case 'C':
                case 'D':
                case 'E':
                case 'F':
                case 'G':
                case 'H':
                case 'I':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E1C1E8']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'J':
                case 'K':
                case 'L':
                case 'M':
                case 'N':
                case 'O':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '96D6B8']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'P':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C8AFC']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'Q':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1BF72C']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'R':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCF2C0']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'S':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E06458']];
                    $styleConfig['font']['color'] = ['rgb' => '000000'];
                    break;
                case 'T':
                    $styleConfig['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCF2C0']];
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

    protected function applyAlternateRowColor($row)
    {
        if ($row % 2 == 0) {
            $this->sheet->getStyle('A' . $row . ':AI' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);
        }
    }

    protected function addToYearlyShipmentSheet(array $data, $rowIndex)
    {
        $yearlySheet = $this->spreadsheet->getSheetByName($this->getYearlySheetName());
        if (!$yearlySheet) {
            $yearlySheet = new Worksheet($this->spreadsheet, $this->getYearlySheetName());
            $this->spreadsheet->addSheet($yearlySheet);
            $this->setupYearlyShipmentSheet($yearlySheet);
        }

        // Get the last row
        $lastRow = $yearlySheet->getHighestRow();
        if ($lastRow < 3) {
            $lastRow = 3;
        }
        $newRow = $lastRow + 1;

        // Calculate values
        $inboundCost = floatval($data['inbound_cost'] ?? 0);
        $serviceFee = floatval($data['service_fee'] ?? 0);
        $overweight = floatval($data['overweight'] ?? 0);
        $others = floatval($data['others'] ?? 0);
        $discount = floatval($data['discount'] ?? 0);
        $withholdingTax = floatval($data['withholding_tax'] ?? 0);
        $amountToBePaid = ($inboundCost + $serviceFee + $overweight + $others) - ($discount + $withholdingTax);
        $balance = 0;

        // Write data
        $yearlySheet->setCellValue('A' . $newRow, $rowIndex);
        $yearlySheet->setCellValue('B' . $newRow, $data['container_code'] ?? '');
        $yearlySheet->setCellValue('C' . $newRow, isset($data['china_recieved_date']) ? date('m/d/Y', strtotime($data['china_recieved_date'])) : date('m/d/Y'));
        $yearlySheet->setCellValue('D' . $newRow, $data['order_reference'] ?? '');
        $yearlySheet->setCellValue('E' . $newRow, 'AKPH-' . ($data['client_code'] ?? ''));
        $yearlySheet->setCellValue('F' . $newRow, ($data['product_name'] ?? '') . ' ' . ($data['product_code'] ?? ''));
        $yearlySheet->setCellValue('G' . $newRow, $data['pkgs'] ?? '');
        $yearlySheet->setCellValue('H' . $newRow, $data['total_cbm'] ?? '');
        $yearlySheet->setCellValue('I' . $newRow, $data['applied_rate'] ?? '');
        $yearlySheet->setCellValue('J' . $newRow, $data['soa_number'] ?? '');
        $yearlySheet->setCellValue('K' . $newRow, $serviceFee);
        $yearlySheet->setCellValue('L' . $newRow, $inboundCost);
        $yearlySheet->setCellValue('M' . $newRow, $overweight);
        $yearlySheet->setCellValue('N' . $newRow, $data['initial_billing'] ?? 0);
        $yearlySheet->setCellValue('O' . $newRow, $others);
        $yearlySheet->setCellValue('P' . $newRow, $withholdingTax);
        $yearlySheet->setCellValue('Q' . $newRow, $amountToBePaid);
        $yearlySheet->setCellValue('R' . $newRow, $data['actual_payment'] ?? 0);
        $yearlySheet->setCellValue('S' . $newRow, $balance);
        $yearlySheet->setCellValue('T' . $newRow, $data['status'] ?? 'Pending');

        // Format currency columns
        $currencyColumns = ['K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
        foreach ($currencyColumns as $col) {
            $yearlySheet->getStyle($col . $newRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        }

        $yearlySheet->getRowDimension($newRow)->setRowHeight(20);

        // Apply alternating row colors
        if ($newRow % 2 == 0) {
            $yearlySheet->getStyle('A' . $newRow . ':T' . $newRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]
            ]);
        }
    }

    public function addEntry(array $data)
    {
        try {
            Log::info('Adding entry to Excel', $data);
            $this->ensureDirectoryExists();

            // Get the current date being posted
            $currentDate = isset($data['created_at']) ? date('Y-m-d', strtotime($data['created_at'])) : date('Y-m-d');
            $displayDate = isset($data['created_at']) ? date('F d, Y', strtotime($data['created_at'])) : date('F d, Y');

            // Get the last row with data
            $lastRow = $this->sheet->getHighestRow();
            if ($lastRow < 2) {
                $lastRow = 2;
            }

            $newRow = $lastRow + 1;

            // Check if we need to add a date header
            $lastDateRow = $this->findLastDateRow();
            $lastDate = $this->getLastStoredDate($lastDateRow);

            // If this is a new date (different from last entry), add date header
            if ($lastDate != $currentDate && $lastDate !== null) {
                // Insert a new row for date header
                $this->sheet->insertNewRowBefore($newRow);
                $this->addDateHeaderRow($newRow, $displayDate);
                $newRow++; // Increment row because we inserted a row
            } elseif ($lastDate === null && $newRow > 3) {
                // First entry, add date header
                $this->addDateHeaderRow($newRow, $displayDate);
                $newRow++;
            }

            Log::info('Writing to row: ' . $newRow);

            $existingEntries = $newRow - 3;
            $rowIndex = max($existingEntries, 1);

            // Calculate derived values
            $inboundCost = floatval($data['inbound_cost'] ?? 0);
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $overweight = floatval($data['overweight'] ?? 0);
            $others = floatval($data['others'] ?? 0);
            $discount = floatval($data['discount'] ?? 0);
            $withholdingTax = floatval($data['withholding_tax'] ?? 0);
            $amountToBePaid = ($inboundCost + $serviceFee + $overweight + $others) - ($discount + $withholdingTax);
            $balance = 0;

            // Write data to DAILY sheet
            $this->writeDataToRow($newRow, $data, $rowIndex, $inboundCost, $serviceFee, $overweight, $others, $discount, $withholdingTax, $amountToBePaid, $balance);

            // Format currency columns
            $currencyColumns = ['M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'AD'];
            foreach ($currencyColumns as $col) {
                $this->sheet->getStyle($col . $newRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
            }

            $this->applyCellStyle($newRow, false);
            $this->applyAlternateRowColor($newRow);
            $this->sheet->getRowDimension($newRow)->setRowHeight(20);

            // Add to yearly shipment sheet
            $this->addToYearlyShipmentSheet($data, $rowIndex);

            // Save the file
            $writer = new Xlsx($this->spreadsheet);
            $writer->save($this->filePath);

            Log::info('Excel file saved successfully. Size: ' . filesize($this->filePath) . ' bytes');

            return ['row' => $newRow, 'file' => $this->filePath, 'index' => $rowIndex];
        } catch (\Exception $e) {
            Log::error('Error in ExcelWriterService::addEntry: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    protected function findLastDateRow()
    {
        $highestRow = $this->sheet->getHighestRow();
        for ($row = $highestRow; $row >= 3; $row--) {
            $cellValue = $this->sheet->getCell('Z' . $row)->getValue();
            // Check if this looks like a date header (has format like "January 1, 2024")
            if (!empty($cellValue) && preg_match('/[A-Za-z]+\s\d{1,2},\s\d{4}/', $cellValue)) {
                return $row;
            }
        }
        return null;
    }

    protected function getLastStoredDate($dateRow)
    {
        if ($dateRow) {
            $dateStr = $this->sheet->getCell('Z' . $dateRow)->getValue();
            return date('Y-m-d', strtotime($dateStr));
        }
        return null;
    }

    protected function addDateHeaderRow($row, $displayDate)
    {
        // Insert the date in column Z (DATE POSTED column)
        $this->sheet->setCellValue('Z' . $row, $displayDate);

        // Merge cells for the date header (optional - from A to Z or just leave as is)
        // $this->sheet->mergeCells('A' . $row . ':Z' . $row);

        // Style the date header
        $this->sheet->getStyle('Z' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'] // Gray background
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Also style columns A-Y for the same row if you want the whole row gray
        for ($col = 'A'; $col !== 'Z'; $col++) {
            $this->sheet->getStyle($col . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3']
                ]
            ]);
        }

        $this->sheet->getRowDimension($row)->setRowHeight(25);
    }

    protected function writeDataToRow($row, $data, $rowIndex, $inboundCost, $serviceFee, $overweight, $others, $discount, $withholdingTax, $amountToBePaid, $balance)
    {
        $productName = $data['product_name'] ?? '';
        $productCode = $data['product_code'] ?? $data['code'] ?? '';
        $productValue = trim($productName . ($productName && $productCode ? ' - ' : '') . $productCode);

        $this->sheet->setCellValue('A' . $row, $rowIndex);
        $this->sheet->setCellValue('B' . $row, $data['container_code'] ?? '');
        $this->sheet->setCellValue('C' . $row, isset($data['china_recieved_date']) ? date('m/d/Y', strtotime($data['china_recieved_date'])) : date('m/d/Y'));
        $this->sheet->setCellValue('D' . $row, $data['order_reference'] ?? '');
        $this->sheet->setCellValue('E' . $row, 'AKPH-' . ($data['client_code'] ?? ''));
        $this->sheet->setCellValue('F' . $row, $productValue);
        $this->sheet->setCellValue('G' . $row, $data['pkgs'] ?? '');
        $this->sheet->setCellValue('H' . $row, $data['total_cbm'] ?? '');
        $this->sheet->setCellValue('I' . $row, $data['weight'] ?? '');
        $this->sheet->setCellValue('J' . $row, $data['applied_rate'] ?? '');
        $this->sheet->setCellValue('K' . $row, $data['soa_number'] ?? '');
        $this->sheet->setCellValue('L' . $row, $data['client_name'] ?? '');
        $this->sheet->setCellValue('M' . $row, $amountToBePaid);
        $this->sheet->setCellValue('N' . $row, $data['initial_billing'] ?? 0);
        $this->sheet->setCellValue('O' . $row, $withholdingTax);
        $this->sheet->setCellValue('P' . $row, $inboundCost);
        $this->sheet->setCellValue('Q' . $row, $serviceFee);
        $this->sheet->setCellValue('R' . $row, $overweight);
        $this->sheet->setCellValue('S' . $row, $discount);
        $this->sheet->setCellValue('T' . $row, $others);
        $this->sheet->setCellValue('U' . $row, $amountToBePaid);
        $this->sheet->setCellValue('V' . $row, $balance);
        $this->sheet->setCellValue('W' . $row, $data['depositing_bank_name'] ?? '');
        $this->sheet->setCellValue('X' . $row, $data['payment_reference_number'] ?? '');
        $this->sheet->setCellValue('Y' . $row, $data['status'] ?? 'Pending');
        $this->sheet->setCellValue('Z' . $row, isset($data['created_at']) ? date('m/d/Y', strtotime($data['created_at'])) : date('m/d/Y'));
        $this->sheet->setCellValue('AA' . $row, $data['deposit_date'] ?? '');
        $this->sheet->setCellValue('AB' . $row, isset($data['created_at']) ? date('h:i:s A', strtotime($data['created_at'])) : date('h:i:s A'));
        $this->sheet->setCellValue('AC' . $row, 'AKPH-' . ($data['client_code'] ?? ''));
        $this->sheet->setCellValue('AD' . $row, $amountToBePaid);
        $this->sheet->setCellValue('AE' . $row, $data['payment_method_name'] ?? '');
        $this->sheet->setCellValue('AF' . $row, $data['purpose'] ?? 'FREIGHT PAYMENT');
        $this->sheet->setCellValue('AG' . $row, $data['agent_name'] ?? '');
        $this->sheet->setCellValue('AH' . $row, $data['container_code'] ?? '');
        $this->sheet->setCellValue('AI' . $row, $data['soa_code'] ?? '');
    }

    public function download()
    {
        if (!file_exists($this->filePath)) {
            Log::error('Excel file not found for download: ' . $this->filePath);
            abort(404, 'Excel file not found');
        }

        return response()->download($this->filePath, '2026_CASH_RECEIPT_BOOK_' . date('Y-m-d') . '.xlsx');
    }
}
