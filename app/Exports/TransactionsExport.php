<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $results;
    protected $startDate;
    protected $endDate;
    protected $selectedBranch;
    protected $selectedClient;
    protected $branches;
    protected $clients;
    protected $exportedBy;

    public function __construct($results, $startDate, $endDate, $selectedBranch, $selectedClient, $branches, $clients, $exportedBy = null)
    {
        $this->results = $results;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedBranch = $selectedBranch;
        $this->selectedClient = $selectedClient;
        $this->branches = $branches;
        $this->clients = $clients;
        $this->exportedBy = $exportedBy ?? auth()->user()->firstname . ' ' . auth()->user()->lastname ?? 'N/A';
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect();

        // Data rows
        foreach ($this->results as $result) {
            $items = $result->ordered_items ?? collect();
            $clientName = $result->client_name ?? $result->table_name ?? 'N/A';
            
            if ($items->isNotEmpty()) {
                // Group items by menu_name to show breakdown properly
                $groupedItems = $items->groupBy('menu_name');
                $firstRow = true;

                foreach ($groupedItems as $menuName => $group) {
                    $totalQuantity = $group->sum('ordered_quantity');
                    $itemCount = $group->count();
                    $itemCode = $group->first()->item_code ?? 'N/A';
                    $brandName = $group->first()->brand_name ?? '';
                    $bransCode = $group->first()->brand_code ?? '';
                    $discountAmount = $group->sum('ordered_discount_amount');
                    $salesAmount = $group->sum('ordered_amount');
                    $foodCost = $group->sum('ordered_food_cost');
                    $profit = $salesAmount - $foodCost;
                    
                    // Build the description with quantity breakdown like frontend
                    $description = $menuName . ($brandName ? ' - ' . $brandName : '') ."($bransCode)";
                    
                    // Create quantity breakdown string like "2.000/2.000" when there are multiple entries
                    $quantityBreakdown = '';
                    if ($itemCount > 1) {
                        $quantities = [];
                        foreach ($group as $item) {
                            $quantities[] = number_format((float)($item->ordered_quantity ?? 0), 3);
                        }
                        $quantityBreakdown = implode('/', $quantities);
                    }

                    $row = [
                        $firstRow ? date('Y-m-d', strtotime($result->date ?? now())) : '',
                        $firstRow ? $result->nn : '',
                        $firstRow ? $clientName : '',
                        $itemCode,
                        $description,
                        $quantityBreakdown, // BREAKDOWN column (now before QTY)
                        number_format($totalQuantity, 3), // QTY column (now after BREAKDOWN)
                        '₱ ' . number_format($discountAmount, 2),
                        '₱ ' . number_format($salesAmount, 2),
                        '₱ ' . number_format($foodCost, 2),
                        '₱ ' . number_format($profit, 2),
                        $firstRow ? '₱ ' . number_format($result->amount, 2) : '',
                        $firstRow ? ($result->cashier_name ?? 'N/A') : '',
                        $firstRow ? ($result->tstatus ?? 'N/A') : '',
                    ];

                    $data->push($row);
                    $firstRow = false;
                }

                // Add total row for this transaction
                $totalDiscount = $items->sum('ordered_discount_amount');
                $totalSales = $items->sum('ordered_amount');
                $totalFoodCost = $items->sum('ordered_food_cost');
                $totalProfit = $totalSales - $totalFoodCost;
                $totalQuantity = $items->sum('ordered_quantity');

                $data->push([
                    '',
                    '',
                    '',
                    '',
                    'TOTAL:',
                    '', // BREAKDOWN column (empty for total)
                    number_format($totalQuantity, 3), // QTY column
                    '₱ ' . number_format($totalDiscount, 2),
                    '₱ ' . number_format($totalSales, 2),
                    '₱ ' . number_format($totalFoodCost, 2),
                    '₱ ' . number_format($totalProfit, 2),
                    '',
                    '',
                    ''
                ]);
                $data->push([]); // Empty row between transactions
                
            } else {
                // Transaction with no items
                $data->push([
                    date('Y-m-d', strtotime($result->date ?? now())),
                    $result->nn,
                    $clientName,
                    'N/A',
                    'No items found',
                    '', // BREAKDOWN column
                    '', // QTY column
                    '',
                    '',
                    '',
                    '',
                    '₱ ' . number_format($result->amount, 2),
                    $result->cashier_name ?? 'N/A',
                    $result->tstatus ?? 'N/A'
                ]);
                $data->push([]);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'DATE',
            'RECEIPT #',
            'CLIENT',
            'ITEM CODE',
            'DESCRIPTION',
            'BREAKDOWN', // Now before QTY
            'QTY',       // Now after BREAKDOWN
            'DISCOUNT',
            'SALES',
            'UNIT COST',
            'PROFIT',
            'PAYMENT',
            'CASHIER',
            'STATUS'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // This is now handled in the AfterSheet event
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Get client name for header
                $clientName = 'All Clients';
                if ($this->selectedClient != 'all') {
                    $client = $this->clients->firstWhere('id', (int)$this->selectedClient);
                    if ($client) {
                        $clientName = $client->name;
                    }
                }

                // Get branch name for header
                $branchName = 'All Branches';
                if ($this->selectedBranch != 'all') {
                    $branch = $this->branches->firstWhere('branch_code', $this->selectedBranch);
                    if ($branch) {
                        $branchName = $branch->name;
                    }
                }

                // Insert 5 rows at the top for title and info
                $sheet->insertNewRowBefore(1, 5);
                
                // ===== ROW 1: TITLE =====
                $sheet->mergeCells('A1:N1');
                $sheet->setCellValue('A1', 'Sales Detail Report');
                $sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E79'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== ROW 2: BRANCH AND DATE RANGE =====
                $sheet->setCellValue('A2', 'Branch:');
                $sheet->setCellValue('B2', $branchName);
                $sheet->setCellValue('C2', 'Date Range:');
                $sheet->setCellValue('D2', date('Y-m-d', strtotime($this->startDate)) . ' to ' . date('Y-m-d', strtotime($this->endDate)));
                
                // Style row 2
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== ROW 3: CLIENT AND DATE PRINTED =====
                $sheet->setCellValue('A3', 'Client:');
                $sheet->setCellValue('B3', $clientName);
                $sheet->setCellValue('C3', 'Date Printed:');
                $sheet->setCellValue('D3', now()->format('Y-m-d H:i:s'));
                
                // Style row 3
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== ROW 4: EXPORTED BY =====
                $sheet->setCellValue('A4', 'Exported By:');
                $sheet->setCellValue('B4', $this->exportedBy);
                
                // Style row 4
                $sheet->getStyle('A4:B4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== ROW 5: EMPTY SPACER =====
                $sheet->getRowDimension(5)->setRowHeight(10);

                // ===== ROW 6: HEADERS (now shifted down by 5 rows) =====
                $sheet->getStyle('A6:N6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F4E79'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== APPLY BORDERS =====
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Apply borders to all data from row 6 onwards
                $sheet->getStyle('A6:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // ===== STYLE TOTAL ROWS AND BREAKDOWN =====
                for ($row = 7; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('E' . $row)->getValue();
                    
                    // Style TOTAL rows
                    if ($cellValue === 'TOTAL:') {
                        $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E8E8E8'],
                            ],
                        ]);
                    }
                    
                    // Style the BREAKDOWN column (column F) when it has values like "2.000/2.000"
                    $breakdownValue = $sheet->getCell('F' . $row)->getValue();
                    if (!empty($breakdownValue) && strpos($breakdownValue, '/') !== false) {
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => 'FF6B6B'],
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    }
                    
                    // Alternate row colors for data rows
                    if ($row > 6 && $cellValue !== 'TOTAL:' && !empty($sheet->getCell('A' . $row)->getValue())) {
                        $isEvenRow = ($row % 2 == 0);
                        $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $isEvenRow ? 'F5F5F5' : 'FFFFFF'],
                            ],
                        ]);
                    }
                }

                // ===== AUTO-SIZE COLUMNS =====
                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // ===== FREEZE THE HEADER ROW =====
                $sheet->freezePane('A7');
            },
        ];
    }
}