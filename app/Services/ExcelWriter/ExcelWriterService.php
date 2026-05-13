<?php

namespace App\Services\ExcelWriter;

use App\Services\ExcelWriter\Traits\BankTransactionTrait;
use App\Services\ExcelWriter\Traits\DataWritingTrait;
use App\Services\ExcelWriter\Traits\DateHeaderTrait;
use App\Services\ExcelWriter\Traits\SheetSetupTrait;
use App\Services\ExcelWriter\Traits\StylingTrait;
use App\Services\ExcelWriter\Traits\YearlySheetTrait;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelWriterService
{
    use SheetSetupTrait, StylingTrait, DataWritingTrait, DateHeaderTrait, YearlySheetTrait, BankTransactionTrait;

    protected string $filePath;
    protected Spreadsheet $spreadsheet;
    protected Worksheet $sheet;

    public function setSheet(Worksheet $sheet)
    {
        $this->sheet = $sheet;
    }

    public function __construct()
    {
        $this->filePath = storage_path('app/excel_forms.xlsx');
        $this->ensureDirectoryExists();
        Log::info('Excel file path: ' . $this->filePath);
        $this->initializeExcel();

        // CRITICAL: Set the sheet reference in all traits
        $this->setSheetReference($this->sheet);
        $this->setSpreadsheetReference($this->spreadsheet);
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

            // Verify sheets exist after loading
            $this->ensureSheetsExist();
        } else {
            Log::info('Creating new Excel file');
            $this->spreadsheet = new Spreadsheet();
            $this->createSheets();
            $writer = new Xlsx($this->spreadsheet);
            $writer->save($this->filePath);
            Log::info('New Excel file created and saved at: ' . $this->filePath);
        }

        $this->sheet = $this->spreadsheet->getSheetByName('DAILY');
        if (!$this->sheet) {
            Log::error('DAILY sheet not found, using active sheet');
            $this->sheet = $this->spreadsheet->getActiveSheet();
            $this->sheet->setTitle('DAILY');
        }

        Log::info('Sheet initialized: ' . $this->sheet->getTitle() . ' (Height: ' . $this->sheet->getHighestRow() . ')');
    }

    protected function ensureSheetsExist()
    {
        // Check if DAILY sheet exists
        $dailySheet = $this->spreadsheet->getSheetByName('DAILY');
        if (!$dailySheet) {
            Log::warning('DAILY sheet does not exist in loaded file, checking sheets...');
            $dailySheet = $this->spreadsheet->getActiveSheet();
            if ($dailySheet->getTitle() !== 'DAILY') {
                $dailySheet->setTitle('DAILY');
                Log::info('Renamed active sheet to DAILY');
            }
        }

        // Ensure YEARLY and BANK sheets exist
        if (!$this->spreadsheet->getSheetByName($this->getYearlySheetName())) {
            Log::info('Creating missing YEARLY sheet');
            $yearlySheet = new Worksheet($this->spreadsheet, $this->getYearlySheetName());
            $this->spreadsheet->addSheet($yearlySheet);
            $this->setupYearlyShipmentSheet($yearlySheet);
        }

        if (!$this->spreadsheet->getSheetByName($this->getBankTransactionSheetName())) {
            Log::info('Creating missing BANK TRANSACTION sheet');
            $bankSheet = new Worksheet($this->spreadsheet, $this->getBankTransactionSheetName());
            $this->spreadsheet->addSheet($bankSheet);
            $this->setupBankTransactionSheet($bankSheet);
        }
    }

    protected function setSheetReference(Worksheet $sheet)
    {
        $this->sheet = $sheet;
        // Use the primary setSheet method from StylingTrait
        $this->setSheet($sheet);

        // If you need to set sheet on other traits individually:
        if (method_exists($this, 'setDataWritingSheet')) {
            $this->setDataWritingSheet($sheet);
        }
        if (method_exists($this, 'setDateHeaderSheet')) {
            $this->setDateHeaderSheet($sheet);
        }
        if (method_exists($this, 'setYearlySheet')) {
            $this->setYearlySheet($sheet);
        }
    }

    protected function setSpreadsheetReference(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
        if (method_exists($this, 'setSpreadsheet')) {
            $this->setSpreadsheet($spreadsheet);
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

        $bankTransactionSheet = new Worksheet($this->spreadsheet, $this->getBankTransactionSheetName());
        $this->spreadsheet->addSheet($bankTransactionSheet);
        $this->setupBankTransactionSheet($bankTransactionSheet);
    }

    public function addEntry(array $data)
    {
        try {
            Log::info('Adding entry to Excel', $data);
            $this->ensureDirectoryExists();

            $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
            Log::info("Processing quantity: {$quantity} item(s)");

            $currentDate = isset($data['created_at']) ? date('Y-m-d', strtotime($data['created_at'])) : date('Y-m-d');
            $displayDate = isset($data['created_at']) ? date('F d, Y', strtotime($data['created_at'])) : date('F d, Y');

            $lastRow = $this->sheet->getHighestRow();
            if ($lastRow < 2) {
                $lastRow = 2;
            }

            $lastIndex = 0;
            if ($lastRow >= 3) {
                $lastIndexValue = $this->sheet->getCell('A' . $lastRow)->getValue();
                $lastIndex = is_numeric($lastIndexValue) ? (int)$lastIndexValue : ($lastRow - 2);
            } else {
                $lastIndex = $lastRow - 2;
            }

            $newRow = $lastRow + 1;
            $startRow = $newRow;

            $lastDateRow = $this->findLastDateRow();
            $lastDate = $this->getLastStoredDate($lastDateRow);

            Log::info("Date check - lastDateRow: {$lastDateRow}, lastDate: {$lastDate}, currentDate: {$currentDate}, newRow: {$newRow}, lastRow: {$lastRow}");

            // For FIRST entry ever (no date headers exist and we're adding first data row)
            if ($lastDate === null && $lastRow == 2) {
                Log::info("Adding FIRST date header at row {$newRow}");
                $this->sheet->insertNewRowBefore($newRow);
                $this->addDateHeaderRow($newRow, $displayDate);
                $newRow++;
                $startRow++;
            }
            // For new date (different from last entry)
            elseif ($lastDate !== null && $lastDate != $currentDate) {
                Log::info("Adding NEW date header at row {$newRow} (date changed from {$lastDate} to {$currentDate})");
                $this->sheet->insertNewRowBefore($newRow);
                $this->addDateHeaderRow($newRow, $displayDate);
                $newRow++;
                $startRow++;
            }

            $inboundCost = floatval($data['inbound_cost'] ?? 0);
            $serviceFee = floatval($data['service_fee'] ?? 0);
            $overweight = floatval($data['overweight'] ?? 0);
            $others = floatval($data['others'] ?? 0);
            $discount = floatval($data['discount'] ?? 0);
            $withholdingTax = floatval($data['withholding_tax'] ?? 0);
            $amountToBePaid = ($inboundCost + $serviceFee + $overweight + $others) - ($discount + $withholdingTax);
            $balance = 0;

            $firstRowIndex = $lastIndex + 1;

            for ($q = 0; $q < $quantity; $q++) {
                $currentRow = $newRow + $q;
                $rowIndex = $lastIndex + 1 + $q;

                Log::info("Writing item " . ($q + 1) . " of {$quantity} to row: {$currentRow} with index: {$rowIndex}");

                $this->writeDataToRow($currentRow, $data, $rowIndex, $inboundCost, $serviceFee, $overweight, $others, $discount, $withholdingTax, $amountToBePaid, $balance);

                $currencyColumns = ['M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'AD'];
                foreach ($currencyColumns as $col) {
                    $this->sheet->getStyle($col . $currentRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
                }

                $this->applyCellStyle($currentRow, false);
                $this->applyAlternateRowColor($currentRow);
                $this->sheet->getRowDimension($currentRow)->setRowHeight(20);

                $this->addToYearlyShipmentSheet($data, $rowIndex);

                // Add to bank transaction sheet (aggregates amounts)
                $this->addToBankTransactionSheet($data, $rowIndex, $amountToBePaid);
            }

            // CRITICAL: Flush the bank transaction sheet to write aggregated amounts
            $this->flushBankTransactionSheet();

            $writer = new Xlsx($this->spreadsheet);
            $writer->save($this->filePath);

            Log::info("Excel file saved successfully with {$quantity} entries. Size: " . filesize($this->filePath) . ' bytes');

            $verifyRow = $newRow + $quantity - 1;
            $verifyValue = $this->sheet->getCell('A' . $verifyRow)->getValue();
            Log::info("Verification - Last row index value: " . $verifyValue);

            return [
                'row' => $newRow + $quantity - 1,
                'file' => $this->filePath,
                'index' => $firstRowIndex,
                'quantity' => $quantity,
                'last_index' => $lastIndex + $quantity
            ];
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
