<?php

namespace App\Services\ExcelWriter\Traits;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait BankTransactionTrait
{
    protected Spreadsheet $spreadsheet;

    public function setBankTransactionSpreadsheet(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    protected function setupBankTransactionSheet(Worksheet $sheet)
    {
        $currentYear = date('Y');

        $sheet->setTitle($this->getBankTransactionSheetName());

        $headers = [
            'A' => '#',
            'B' => 'DATE POSTED',
            'C' => 'DEPOSIT DATE',
            'D' => 'TIME',
            'E' => 'CLIENT CODE',
            'F' => 'DEPOSITNG BANK',
            'G' => 'TOTAL PHP',
            'H' => 'RECEIVING BANK',
            'I' => 'PAYMENT REFERENCE NUMBER',
            'J' => 'PURPOSE',
            'K' => 'AGENT',
            'L' => 'SHIPMENT REF #',
            'M' => 'SOA CODE',
            'N' => 'G CONFIRM',
            'O' => 'DATE POSTED IN BANK',
            'Q' => 'HANDLER',
            'R' => 'BDO(4849)',
            'S' => 'UNIONBANK (2119)',
            'T' => 'ANGKAT BUS (9280)',
            'U' => 'GCASH (0021)',
        ];

        $columnWidths = [
            'A' => 8,
            'B' => 18,
            'C' => 18,
            'D' => 20,
            'E' => 18,
            'F' => 60,
            'G' => 10,
            'H' => 12,
            'I' => 15,
            'J' => 22,
            'K' => 60,
            'L' => 18,
            'M' => 15,
            'N' => 18,
            'O' => 15,
            'P' => 18,
            'Q' => 22,
            'R' => 15,
            'S' => 15,
            'T' => 12,
            'U' => 12,
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $headerRow = 2;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $this->applyBankTransactionHeaderStyle($sheet, $headerRow);

        // Freeze the header row and the first column
        $sheet->freezePane('A3');
    }

    protected function addToBankTransactionSheet(array $data, int $rowIndex, float $amountToBePaid)
    {
        $bankTransactionSheet = $this->spreadsheet->getSheetByName($this->getBankTransactionSheetName());
        if (!$bankTransactionSheet) {
            $bankTransactionSheet = new Worksheet($this->spreadsheet, $this->getBankTransactionSheetName());
            $this->spreadsheet->addSheet($bankTransactionSheet);
            $this->setupBankTransactionSheet($bankTransactionSheet);
        }

        $lastRow = $bankTransactionSheet->getHighestRow();
        if ($lastRow < 2) {
            $lastRow = 2;  // Headers are at row 2, so start data from row 3
        }

        $newRow = $lastRow + 1;

        // Write common data
        $bankTransactionSheet->setCellValue('A' . $newRow, $rowIndex);
        $bankTransactionSheet->setCellValue('B' . $newRow, isset($data['created_at']) ? date('Y-m-d', strtotime($data['created_at'])) : '');
        $bankTransactionSheet->setCellValue('C' . $newRow, isset($data['deposit_date']) ? date('Y-m-d', strtotime($data['deposit_date'])) : '');
        $bankTransactionSheet->setCellValue('D' . $newRow, isset($data['created_at']) ? date('H:i:s', strtotime($data['created_at'])) : '');
        $bankTransactionSheet->setCellValue('E' . $newRow, $data['client_code'] ?? '');
        $bankTransactionSheet->setCellValue('F' . $newRow, $data['depositing_bank_name'] ?? '');
        $bankTransactionSheet->setCellValue('G' . $newRow, $amountToBePaid);
        $bankTransactionSheet->setCellValue('H' . $newRow, $data['receiving_bank'] ?? '');
        $bankTransactionSheet->setCellValue('I' . $newRow, $data['payment_reference_number'] ?? '');
        $bankTransactionSheet->setCellValue('J' . $newRow, $data['purpose'] ?? '');
        $bankTransactionSheet->setCellValue('K' . $newRow, $data['agent_name'] ?? '');
        $bankTransactionSheet->setCellValue('L' . $newRow, $data['order_reference'] ?? '');
        $bankTransactionSheet->setCellValue('M' . $newRow, $data['soa_code'] ?? '');

        // Switch-case for receiving bank placement (using general names, no codes)
        $receivingBank = strtoupper(trim($data['receiving_bank'] ?? ''));
        $totalPhp = $amountToBePaid;

        switch (true) {
            case (strpos($receivingBank, 'BDO (4849)') !== false):
                // Place in BDO column
                $bankTransactionSheet->setCellValue('Q' . $newRow, $totalPhp);
                break;

            case (strpos($receivingBank, 'UNIONBANK (2119)') !== false || strpos($receivingBank, 'UNION BANK') !== false):
                // Place in UNIONBANK column
                $bankTransactionSheet->setCellValue('R' . $newRow, $totalPhp);
                break;

            case (strpos($receivingBank, 'ANGKAT BUS (9280)') !== false):
                // Place in ANGKAT BUS column
                $bankTransactionSheet->setCellValue('S' . $newRow, $totalPhp);
                break;

            case (strpos($receivingBank, 'GCASH') !== false || strpos($receivingBank, 'G-CASH') !== false):
                // Place in GCASH column
                $bankTransactionSheet->setCellValue('T' . $newRow, $totalPhp);
                break;

            default:
                // Optional: Log unknown bank
                Log::info("Unknown receiving bank: {$receivingBank}");
                break;
        }

        // Format currency columns
        $bankTransactionSheet->getStyle('G' . $newRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        $bankColumns = ['Q', 'R', 'S', 'T'];
        foreach ($bankColumns as $col) {
            $bankTransactionSheet->getStyle($col . $newRow)->getNumberFormat()->setFormatCode('');
        }
    }
}
