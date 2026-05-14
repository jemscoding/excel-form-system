<?php

namespace App\Services\ExcelWriter\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait BankTransactionTrait
{
    protected Spreadsheet $spreadsheet;

    // Store totals for each bank
    protected array $bankTotals = [
        'BDO' => 0,
        'UNIONBANK' => 0,
        'ANGKAT' => 0,
        'GCASH' => 0,
        'GRAND_TOTAL' => 0,
    ];

    // Track suffix counts for order references
    protected array $suffixCounts = [];

    // Store all entries to write at once
    protected array $entries = [];

    public function setBankTransactionSpreadsheet(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    protected function setupBankTransactionSheet(Worksheet $sheet)
    {
        $currentYear = date('Y');

        $sheet->setTitle($this->getBankTransactionSheetName());

        // Row 1: Title and Notes
        $sheet->setCellValue('B1', $currentYear . ' BANK TRANSACTIONS');
        $sheet->mergeCells('B1:E1');
        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['size' => 17, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->setCellValue('F1', 'NOTE: DON\'T EDIT ALL LINES HAS FORMULAS');
        $sheet->mergeCells('F1:I1');
        $sheet->getStyle('F1')->applyFromArray([
            'font' => ['size' => 13, 'bold' => true, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
        ]);

        $sheet->setCellValue('J1', $currentYear . ' BANK TRANSACTION');
        $sheet->getStyle('J1')->getFont()->setUnderline(true);
        $sheet->getStyle('J1')->getFont()->setBold(true);
        $sheet->getStyle('J1')->applyFromArray([
            'font' => ['size' => 13, 'color' => ['rgb' => '000BB0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('J1')->getAlignment()->setWrapText(true);

        // ============ ROW 2: SUMMARY TOTALS ROW ============
        $summaryRow = 2;
        

        // Grand Total for TOTAL PHP column (G)
        $sheet->setCellValue('G' . $summaryRow, '₱0.00');
        $sheet->getStyle('G' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '006100']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('G' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        // Total for BDO column (Q)
        $sheet->setCellValue('Q' . $summaryRow, '₱0.00');
        $sheet->getStyle('Q' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '97DB97']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('Q' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        // Total for UNIONBANK column (R)
        $sheet->setCellValue('R' . $summaryRow, '₱0.00');
        $sheet->getStyle('R' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '97DB97']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('R' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        // Total for ANGKAT column (S)
        $sheet->setCellValue('S' . $summaryRow, '₱0.00');
        $sheet->getStyle('S' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '97DB97']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('S' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        // Total for GCASH column (T)
        $sheet->setCellValue('T' . $summaryRow, '₱0.00');
        $sheet->getStyle('T' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '97DB97']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('T' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

        // Set row height for summary row
        $sheet->getRowDimension($summaryRow)->setRowHeight(25);

        // ============ ROW 3: HEADERS ============
        $headers = [
            'A' => '#',
            'B' => 'DATE POSTED',
            'C' => 'DEPOSIT DATE',
            'D' => 'TIME',
            'E' => 'CLIENT CODE',
            'F' => 'DEPOSITING BANK',
            'G' => 'TOTAL PHP',
            'H' => 'RECEIVING BANK',
            'I' => 'PAYMENT REFERENCE NUMBER',
            'J' => 'PURPOSE',
            'K' => 'AGENT',
            'L' => 'SHIPMENT REF #',
            'M' => 'SOA CODE',
            'N' => 'G CONFIRM',
            'O' => 'DATE POSTED IN BANK',
            'Q' => 'BDO(4849)',
            'R' => 'UNIONBANK (2119)',
            'S' => 'ANGKAT BUS (9280)',
            'T' => 'GCASH',
        ];

        $columnWidths = [
            'A' => 3, 'B' => 18, 'C' => 18, 'D' => 20, 'E' => 18,
            'F' => 30, 'G' => 18, 'H' => 22, 'I' => 60, 'J' => 20,
            'K' => 25, 'L' => 30, 'M' => 18, 'N' => 15, 'O' => 40,
            'P' => 3, 'Q' => 22, 'R' => 22, 'S' => 22, 'T' => 22,
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $headerRow = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $this->applyBankTransactionHeaderStyle($sheet, $headerRow);

        // Freeze the header row (row 3)
        $sheet->freezePane('A4');
    }

    /**
     * Get the next suffix for an order reference (A, B, C, etc.)
     */
    protected function getNextSuffix(string $orderReference): string
    {
        if (!isset($this->suffixCounts[$orderReference])) {
            $this->suffixCounts[$orderReference] = 0;
        }
        
        $count = $this->suffixCounts[$orderReference];
        $this->suffixCounts[$orderReference]++;
        
        return chr(65 + ($count));
    }

    /**
     * Write all entries to the bank transaction sheet
     */
    protected function writeEntriesToSheet(): void
    {
        $bankTransactionSheet = $this->spreadsheet->getSheetByName($this->getBankTransactionSheetName());
        if (!$bankTransactionSheet) {
            $bankTransactionSheet = new Worksheet($this->spreadsheet, $this->getBankTransactionSheetName());
            $this->spreadsheet->addSheet($bankTransactionSheet);
            $this->setupBankTransactionSheet($bankTransactionSheet);
        }

        $lastRow = $bankTransactionSheet->getHighestRow();
        if ($lastRow < 3) {
            $lastRow = 3;
        }

        $currentRow = $lastRow + 1;
        $rowIndex = $lastRow - 2;

        // ============ READ EXISTING TOTALS FROM SHEET ============
        $summaryRow = 2;
        
        // Read existing totals from the sheet
        $existingGrandTotal = floatval(str_replace('₱', '', str_replace(',', '', $bankTransactionSheet->getCell('G' . $summaryRow)->getValue())));
        $existingBDO = floatval(str_replace('₱', '', str_replace(',', '', $bankTransactionSheet->getCell('Q' . $summaryRow)->getValue())));
        $existingUnionbank = floatval(str_replace('₱', '', str_replace(',', '', $bankTransactionSheet->getCell('R' . $summaryRow)->getValue())));
        $existingAngkat = floatval(str_replace('₱', '', str_replace(',', '', $bankTransactionSheet->getCell('S' . $summaryRow)->getValue())));
        $existingGcash = floatval(str_replace('₱', '', str_replace(',', '', $bankTransactionSheet->getCell('T' . $summaryRow)->getValue())));

        // Initialize totals with existing values
        $this->bankTotals = [
            'BDO' => $existingBDO,
            'UNIONBANK' => $existingUnionbank,
            'ANGKAT' => $existingAngkat,
            'GCASH' => $existingGcash,
            'GRAND_TOTAL' => $existingGrandTotal,
        ];

        // Reset suffix counts for each flush (but keep existing data's suffixes)
        $this->suffixCounts = [];

        foreach ($this->entries as $entry) {
            $data = $entry['data'];
            $amount = $entry['amount'];
            $orderReference = $data['order_reference'] ?? '';
            
            // Get suffix for this order reference (A, B, C, etc. for multiple items)
            $suffix = $this->getNextSuffix($orderReference);
            $shipmentRef = $orderReference . $suffix;

            // Write each entry as a separate row
            $bankTransactionSheet->setCellValue('A' . $currentRow, $rowIndex);
            $bankTransactionSheet->setCellValue('B' . $currentRow, isset($data['created_at']) ? date('m/d/Y', strtotime($data['created_at'])) : '');
            $bankTransactionSheet->setCellValue('C' . $currentRow, isset($data['deposit_date']) ? date('m/d/Y', strtotime($data['deposit_date'])) : '');
            $bankTransactionSheet->setCellValue('D' . $currentRow, isset($data['created_at']) ? date('h:i:s A', strtotime($data['created_at'])) : '');
            $bankTransactionSheet->setCellValue('E' . $currentRow, 'AKPH-' . ($data['client_code'] ?? ''));
            $bankTransactionSheet->setCellValue('F' . $currentRow, $data['depositing_bank_name'] ?? '');
            $bankTransactionSheet->setCellValue('G' . $currentRow, $amount);

            $receivingBank = $data['payment_method_name'] ?? '';
            $receivingBankCode = $data['payment_method_code'] ?? '';
            $bankTransactionSheet->setCellValue('H' . $currentRow, $receivingBank . ($receivingBankCode ? ' (' . $receivingBankCode . ')' : ''));

            $bankTransactionSheet->setCellValue('I' . $currentRow, $data['payment_reference_number'] ?? '');
            $bankTransactionSheet->setCellValue('J' . $currentRow, $data['purpose'] ?? '');
            $bankTransactionSheet->setCellValue('K' . $currentRow, $data['agent_name'] ?? '');
            $bankTransactionSheet->setCellValue('L' . $currentRow, $shipmentRef);
            $bankTransactionSheet->setCellValue('M' . $currentRow, $data['soa_code'] ?? '');

            // Place amount in appropriate bank column and update totals
            $bankPlaced = $this->placeAmountInBankColumn($bankTransactionSheet, $currentRow, $receivingBank, $amount);
            
            // Update running totals (add to existing)
            switch ($bankPlaced) {
                case 'BDO':
                    $this->bankTotals['BDO'] += $amount;
                    break;
                case 'UNIONBANK':
                    $this->bankTotals['UNIONBANK'] += $amount;
                    break;
                case 'ANGKAT':
                    $this->bankTotals['ANGKAT'] += $amount;
                    break;
                case 'GCASH':
                    $this->bankTotals['GCASH'] += $amount;
                    break;
            }
            $this->bankTotals['GRAND_TOTAL'] += $amount;

            // Format currency
            $bankTransactionSheet->getStyle('G' . $currentRow)->getNumberFormat()->setFormatCode('₱#,##0.00');

            // Apply styling to the current row
            $bankColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'Q', 'R', 'S', 'T'];
            foreach ($bankColumns as $col) {
                $cellCoordinate = $col . $currentRow;
                $bankTransactionSheet->getStyle($cellCoordinate)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $bankTransactionSheet->getStyle($cellCoordinate)->getFont()->setBold(true);
            }

            // Set row height
            $bankTransactionSheet->getRowDimension($currentRow)->setRowHeight(15);

            $currentRow++;
            $rowIndex++;
        }

        // Update the summary row (row 2) with accumulated totals
        $bankTransactionSheet->setCellValue('G' . $summaryRow, $this->bankTotals['GRAND_TOTAL']);
        $bankTransactionSheet->getStyle('G' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        
        $bankTransactionSheet->setCellValue('Q' . $summaryRow, $this->bankTotals['BDO']);
        $bankTransactionSheet->getStyle('Q' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        
        $bankTransactionSheet->setCellValue('R' . $summaryRow, $this->bankTotals['UNIONBANK']);
        $bankTransactionSheet->getStyle('R' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        
        $bankTransactionSheet->setCellValue('S' . $summaryRow, $this->bankTotals['ANGKAT']);
        $bankTransactionSheet->getStyle('S' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
        
        $bankTransactionSheet->setCellValue('T' . $summaryRow, $this->bankTotals['GCASH']);
        $bankTransactionSheet->getStyle('T' . $summaryRow)->getNumberFormat()->setFormatCode('₱#,##0.00');
    }

    /**
     * Place amount in the appropriate bank column and return which bank was used
     */
    protected function placeAmountInBankColumn(Worksheet $sheet, int $row, string $receivingBank, float $amount): string
    {
        $receivingBankUpper = strtoupper(trim($receivingBank));

        switch (true) {
            case (strpos($receivingBankUpper, 'BDO') !== false):
                $sheet->setCellValue('Q' . $row, $amount);
                $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode('₱#,##0.00');
                return 'BDO';
            case (strpos($receivingBankUpper, 'UNIONBANK') !== false || strpos($receivingBankUpper, 'UNION BANK') !== false):
                $sheet->setCellValue('R' . $row, $amount);
                $sheet->getStyle('R' . $row)->getNumberFormat()->setFormatCode('₱#,##0.00');
                return 'UNIONBANK';
            case (strpos($receivingBankUpper, 'ANGKAT') !== false):
                $sheet->setCellValue('S' . $row, $amount);
                $sheet->getStyle('S' . $row)->getNumberFormat()->setFormatCode('₱#,##0.00');
                return 'ANGKAT';
            case (strpos($receivingBankUpper, 'GCASH') !== false || strpos($receivingBankUpper, 'G-CASH') !== false):
                $sheet->setCellValue('T' . $row, $amount);
                $sheet->getStyle('T' . $row)->getNumberFormat()->setFormatCode('₱#,##0.00');
                return 'GCASH';
            default:
                return 'UNKNOWN';
        }
    }

    protected function addToBankTransactionSheet(array $data, int $rowIndex, float $amountToBePaid)
    {
        // Store each entry individually (no aggregation)
        $this->entries[] = [
            'data' => $data,
            'amount' => $amountToBePaid,
            'row_index' => $rowIndex
        ];
    }

    /**
     * Call this method after all entries are added to write all entries
     */
    public function flushBankTransactionSheet(): void
    {
        if (!empty($this->entries)) {
            $this->writeEntriesToSheet();
            $this->entries = [];
            $this->suffixCounts = [];
        }
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function clearEntries(): void
    {
        $this->entries = [];
        $this->suffixCounts = [];
    }

    public function getBankTotals(): array
    {
        return $this->bankTotals;
    }
}