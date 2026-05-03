<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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
        Log::info('Excel file path: ' . $this->filePath);
        $this->initializeExcel();
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

        $summarySheet = new Worksheet($this->spreadsheet, 'SUMMARY');
        $this->spreadsheet->addSheet($summarySheet);
        $this->setupSummarySheet($summarySheet);
    }

    protected function setupDailySheet($sheet)
    {
        // Set headers at row 5
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

        $row = 2;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
        }
        
        // Add note row
        $sheet->setCellValue('D2', 'NOTE: From Container Code to Weight - copy data from AKPH 2025 Sheet MNL WH Tab');
        $sheet->mergeCells('D2:K2');
    }

    protected function setupSummarySheet($sheet)
    {
        $sheet->setCellValue('A1', 'SUMMARY REPORT');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);

        $headers = ['Date', 'Total Entries', 'Total Amount', 'Average Amount', 'Min Amount', 'Max Amount'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(chr(65 + $col) . '3', $header);
        }
    }

    public function addEntry(array $data)
    {
        try {
            Log::info('Adding entry to Excel', $data);
            
            // Get the last row with data
            $lastRow = $this->sheet->getHighestRow();
            
            // If only headers exist (row 5), start from row 6
            if ($lastRow < 5) {
                $lastRow = 5;
            }
            
            $newRow = $lastRow + 1;
            Log::info('Writing to row: ' . $newRow);

            // Get the next index number
            $existingEntries = $newRow - 6;
            $rowIndex = max($existingEntries + 1, 1);

            // Calculate derived values
            $inboundCost = floatval($data['inbound_cost'] ?? 0);
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $overweight = floatval($data['overweight'] ?? 0);
            $others = floatval($data['others'] ?? 0);
            $discount = floatval($data['discount'] ?? 0);
            $withholdingTax = floatval($data['withholding_tax'] ?? 0);

            // Calculate amount to be paid
            $amountToBePaid = ($inboundCost + $serviceFee + $overweight + $others) - ($discount + $withholdingTax);
            $balance = 0;

            // Write data to Excel
            $this->sheet->setCellValue('A' . $newRow, $rowIndex);
            $this->sheet->setCellValue('B' . $newRow, $data['container_code'] ?? '');
            $this->sheet->setCellValue('C' . $newRow, $data['china_received_date'] ?? '');
            $this->sheet->setCellValue('D' . $newRow, $data['order_reference'] ?? '');
            $this->sheet->setCellValue('E' . $newRow, $data['client_code'] ?? '');
            $this->sheet->setCellValue('F' . $newRow, $data['product_name'] ?? $data['product_description'] ?? '');
            $this->sheet->setCellValue('G' . $newRow, $data['pkgs'] ?? '');
            $this->sheet->setCellValue('H' . $newRow, $data['total_cbm'] ?? '');
            $this->sheet->setCellValue('I' . $newRow, $data['weight'] ?? '');
            $this->sheet->setCellValue('J' . $newRow, $data['applied_rate'] ?? '');
            $this->sheet->setCellValue('K' . $newRow, $data['soa_number'] ?? '');
            $this->sheet->setCellValue('L' . $newRow, $data['client_name'] ?? '');
            $this->sheet->setCellValue('M' . $newRow, $amountToBePaid);
            $this->sheet->setCellValue('N' . $newRow, $data['initial_billing'] ?? 0);
            $this->sheet->setCellValue('O' . $newRow, $withholdingTax);
            $this->sheet->setCellValue('P' . $newRow, $inboundCost);
            $this->sheet->setCellValue('Q' . $newRow, $serviceFee);
            $this->sheet->setCellValue('R' . $newRow, $overweight);
            $this->sheet->setCellValue('S' . $newRow, $discount);
            $this->sheet->setCellValue('T' . $newRow, $others);
            $this->sheet->setCellValue('U' . $newRow, $amountToBePaid);
            $this->sheet->setCellValue('V' . $newRow, $balance);
            $this->sheet->setCellValue('W' . $newRow, $data['depositing_bank_name'] ?? '');
            $this->sheet->setCellValue('X' . $newRow, $data['payment_reference_number'] ?? '');
            $this->sheet->setCellValue('Y' . $newRow, $data['status'] ?? 'Pending');
            $this->sheet->setCellValue('Z' . $newRow, $data['created_at'] ?? '');
            $this->sheet->setCellValue('AA' . $newRow, $data['deposit_date'] ?? '');
            $this->sheet->setCellValue('AB' . $newRow, $data['created_at'] ?? '');
            $this->sheet->setCellValue('AC' . $newRow, $data['client_code'] ?? '');
            $this->sheet->setCellValue('AD' . $newRow, $amountToBePaid);
            $this->sheet->setCellValue('AE' . $newRow, $data['payment_method_name'] ?? '');
            $this->sheet->setCellValue('AF' . $newRow, $data['purpose'] ?? 'FREIGHT PAYMENT');
            $this->sheet->setCellValue('AG' . $newRow, $data['agent_name'] ?? '');
            $this->sheet->setCellValue('AH' . $newRow, $data['container_code'] ?? '');
            $this->sheet->setCellValue('AI' . $newRow, $data['soa_code'] ?? '');

            // Save the file
            $writer = new Xlsx($this->spreadsheet);
            $writer->save($this->filePath);
            
            Log::info('Excel file saved successfully at: ' . $this->filePath);

            return ['row' => $newRow, 'file' => $this->filePath, 'index' => $rowIndex];
            
        } catch (\Exception $e) {
            Log::error('Error in ExcelWriterService::addEntry: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
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