<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CashExpenseExport extends DefaultValueBinder implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithCustomValueBinder
{
    private array $expenses;

    public function __construct(array $expenses)
    {
        $this->expenses = $expenses;
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Paid To',
            'Amount',
            'Expense Date',
            'Branch Name',
            'Description',
        ];
    }


    public function array(): array
    {
        $rows = [];
        $srNo = 1;

        foreach ($this->expenses as $expense) {
            $rows[] = [
                $srNo++,
                $expense['cash_expense'],
                (float) $expense['amount'],
                $expense['expense_date'],
                $expense['branch_name'],
                $expense['strDescription'],
            ];
        }

        return $rows;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
