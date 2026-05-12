<?php

namespace App\Services\ExcelWriter\Traits;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait DataWritingTrait
{

    protected function writeDataToRow(
        int $row, array $data, int $rowIndex, float $inboundCost, float $serviceFee, float $overweight, float $others, float $discount, float $withholdingTax, float $amountToBePaid, float $balance
        )
    {
        $productName = $data['product_name'] ?? '';
        $productCode = $data['product_code'] ?? $data['code'] ?? '';
        $productValue = trim($productName . ($productName && $productCode ? ' - ' : '') . $productCode);

        Log::info("Writing to row {$row}: Product: {$productValue}, Index: {$rowIndex}");

        // Write data to cells
        $this->sheet->setCellValue('A' . $row, $rowIndex);
        $this->sheet->setCellValue('B' . $row, $data['container_code'] ?? '');
        $this->sheet->setCellValue('C' . $row, isset($data['china_received_date']) ? date('m/d/Y', strtotime($data['china_received_date'])) : date('m/d/Y'));
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
        $this->sheet->setCellValue('W' . $row, ($data['depositing_bank_name'] ?? '') . ($data['depositing_bank_code'] ?? ''));
        $this->sheet->setCellValue('X' . $row, $data['payment_reference_number'] ?? '');
        $this->sheet->setCellValue('Y' . $row, $data['status'] ?? 'Pending');
        $this->sheet->setCellValue('Z' . $row, isset($data['created_at']) ? date('m/d/Y', strtotime($data['created_at'])) : date('m/d/Y'));
        $this->sheet->setCellValue('AA' . $row, isset($data['deposit_date']) ? date('m/d/Y', strtotime($data['deposit_date'])) : '');
        $this->sheet->setCellValue('AB' . $row, isset($data['created_at']) ? date('h:i:s A', strtotime($data['created_at'])) : date('h:i:s A'));
        $this->sheet->setCellValue('AC' . $row, 'AKPH-' . ($data['client_code'] ?? ''));
        $this->sheet->setCellValue('AD' . $row, $amountToBePaid);
        
        // Payment method display with null safety
        $paymentMethodName = $data['payment_method_name'] ?? '';
        $paymentMethodCode = $data['payment_method_code'] ?? '';
        $this->sheet->setCellValue('AE' . $row, $paymentMethodName . (($paymentMethodCode) ? ' (' . $paymentMethodCode . ')' : ''));
        
        $this->sheet->setCellValue('AF' . $row, $data['purpose'] ?? 'FREIGHT PAYMENT');
        $this->sheet->setCellValue('AG' . $row, $data['agent_name'] ?? '');
        $this->sheet->setCellValue('AH' . $row, $data['order_reference'] ?? '');
        $this->sheet->setCellValue('AI' . $row, $data['soa_code'] ?? '');

        // Apply styles to ALL data columns (A to AI)
        $allColumns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'
        ];
        
        foreach ($allColumns as $col) {
            $cellCoordinate = $col . $row;
            
            // Center alignment and text wrap
            $this->sheet->getStyle($cellCoordinate)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

            $this->sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
        }
        
        // Apply currency formatting to financial columns
        $currencyColumns = ['M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'AD'];
        foreach ($currencyColumns as $col) {
            $this->sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('₱#,##0.00');
        }
        
    }
}