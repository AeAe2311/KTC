<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ChartHelper
{
    public static function generateFromTableData($results, $category, $report)
    {
        try {
            // Convert to collection if it's an array
            if (is_array($results)) {
                $results = collect($results);
            }
            
            // Check if results is empty
            if (empty($results) || (is_object($results) && method_exists($results, 'count') && $results->count() == 0)) {
                \Log::info('No results to generate chart for', ['category' => $category, 'report' => $report]);
                return null;
            }
            
            \Log::info('Generating chart data', [
                'category' => $category, 
                'report' => $report,
                'results_count' => is_countable($results) ? count($results) : 0
            ]);
            
            switch ($category) {
                case 'both':
                    return self::generateSalesAndExpensesChart($results, $report);
                case 'sales':
                    return self::generateSalesChart($results, $report);
                case 'expenses':
                    return self::generateExpensesChart($results, $report);
                case 'menu':
                    return self::generateMenuChart($results, $report);
                case 'inventory':
                    return self::generateInventoryChart($results, $report);
                case 'waste':
                    return self::generateWasteChart($results, $report);
                case 'remit':
                    return self::generateRemittanceChart($results);
                case 'both':
                    return self::generateSalesExpensesChart($results);
                case 'invmenu':
                    return self::generateInvMenuChart($results, $report);
                default:
                    \Log::warning('Unknown category for chart generation: ' . $category);
                    return null;
            }
        } catch (\Exception $e) {
            \Log::error('Error in ChartHelper::generateFromTableData: ' . $e->getMessage());
            return null;
        }
    }
/**
 * Generate combined sales and expenses chart
 * Displays both sales and expenses data together for comparison
 */
private static function generateSalesAndExpensesChart($data, $report)
{
    try {
        \Log::info('Starting generateSalesAndExpensesChart', [
            'report' => $report,
            'data_count' => is_countable($data) ? count($data) : 'N/A'
        ]);
        
        // This method expects $data to contain both sales and expenses
        // If you have separate datasets, you'll need to modify this
        
        // Group data by date for both sales and expenses
        $groupedByDate = [];
        $dates = [];
        
        // Process each item to extract date and categorize as sales/expenses
        foreach ($data as $item) {
            if (!is_object($item)) {
                continue;
            }
            
            // Extract date - handle different field names
            $dateField = null;
            if (isset($item->date)) {
                $dateField = $item->date;
            } elseif (isset($item->created_at)) {
                $dateField = $item->created_at;
            }
            
            if (!$dateField) {
                continue;
            }
            
            $date = Carbon::parse($dateField)->format('M d, Y');
            
            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }
            
            if (!isset($groupedByDate[$date])) {
                $groupedByDate[$date] = [
                    'sales' => 0,
                    'expenses' => 0,
                    'gross_profit' => 0
                ];
            }
            
            // Determine if this is sales or expenses based on data structure
            if (isset($item->total)) {
                // This is likely a sales transaction
                $groupedByDate[$date]['sales'] += floatval($item->total ?? 0);
            } elseif (isset($item->amount) && !isset($item->total)) {
                // This is likely an expense
                $groupedByDate[$date]['expenses'] += floatval($item->amount ?? 0);
            } elseif (isset($item->stotal) && isset($item->etotal)) {
                // Already processed data from union query
                $groupedByDate[$date]['sales'] += floatval($item->stotal ?? 0);
                $groupedByDate[$date]['expenses'] += floatval($item->etotal ?? 0);
            }
            
            // Calculate gross profit (sales - expenses)
            $groupedByDate[$date]['gross_profit'] = $groupedByDate[$date]['sales'] - $groupedByDate[$date]['expenses'];
        }
        
        if (empty($dates)) {
            \Log::warning('No dates found for sales and expenses chart');
            return null;
        }
        
        // Sort dates chronologically
        usort($dates, function($a, $b) {
            return Carbon::parse($a)->timestamp <=> Carbon::parse($b)->timestamp;
        });
        
        \Log::info('Sales & Expenses chart data prepared', [
            'dates_count' => count($dates),
            'sample_data' => $groupedByDate[$dates[0] ?? ''] ?? 'No data'
        ]);
        
        // Prepare datasets
        $salesData = [];
        $expensesData = [];
        $profitData = [];
        
        foreach ($dates as $date) {
            $salesData[] = $groupedByDate[$date]['sales'] ?? 0;
            $expensesData[] = $groupedByDate[$date]['expenses'] ?? 0;
            $profitData[] = $groupedByDate[$date]['gross_profit'] ?? 0;
        }
        
        // Calculate totals for summary
        $totalSales = array_sum($salesData);
        $totalExpenses = array_sum($expensesData);
        $totalProfit = array_sum($profitData);
        
        \Log::info('Sales & Expenses totals', [
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'total_profit' => $totalProfit
        ]);
        
        // Create chart configurations
        $charts = [];
        
        // Chart 1: Sales vs Expenses Comparison (Dual Axis)
        $charts[] = [
            'reportType' => 'bar',
            'title' => 'Sales vs Expenses Comparison',
            'subtitle' => 'Daily comparison of sales and expenses',
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Sales (₱)',
                    'data' => $salesData,
                    'borderColor' => 'rgb(34, 197, 94)', // Green for sales
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Expenses (₱)',
                    'data' => $expensesData,
                    'borderColor' => 'rgb(239, 68, 68)', // Red for expenses
                    'backgroundColor' => 'rgba(239, 68, 68, 0.6)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1'
                ]
            ],
            'options' => [
                'responsive' => true,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false
                ],
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'title' => [
                            'display' => true,
                            'text' => 'Sales Amount (₱)'
                        ],
                        'ticks' => [
                            'callback' => 'function(value) { return "₱" + value.toLocaleString("en-PH", {minimumFractionDigits: 0, maximumFractionDigits: 0}); }'
                        ]
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'title' => [
                            'display' => true,
                            'text' => 'Expenses Amount (₱)'
                        ],
                        'grid' => [
                            'drawOnChartArea' => false
                        ],
                        'ticks' => [
                            'callback' => 'function(value) { return "₱" + value.toLocaleString("en-PH", {minimumFractionDigits: 0, maximumFractionDigits: 0}); }'
                        ]
                    ]
                ]
            ]
        ];
        
        // Chart 2: Profit/Loss Trend (Line Chart)
        $charts[] = [
            'reportType' => 'line',
            'title' => 'Gross Profit/Loss Trend',
            'subtitle' => 'Daily gross profit (sales - expenses)',
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Gross Profit (₱)',
                    'data' => $profitData,
                    'borderColor' => 'rgb(59, 130, 246)', // Blue for profit
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4
                ]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'ticks' => [
                            'callback' => 'function(value) { return "₱" + value.toLocaleString("en-PH", {minimumFractionDigits: 0, maximumFractionDigits: 0}); }'
                        ]
                    ]
                ]
            ]
        ];
        
        // Chart 3: Sales & Expenses Distribution (Doughnut)
        $charts[] = [
            'reportType' => 'doughnut',
            'title' => 'Sales vs Expenses Distribution',
            'subtitle' => 'Total distribution of sales and expenses',
            'labels' => ['Total Sales', 'Total Expenses', 'Gross Profit'],
            'datasets' => [
                [
                    'label' => 'Amount (₱)',
                    'data' => [
                        $totalSales,
                        $totalExpenses,
                        $totalProfit
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)', // Green for sales
                        'rgba(239, 68, 68, 0.7)', // Red for expenses
                        'rgba(59, 130, 246, 0.7)'  // Blue for profit
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)'
                    ],
                    'borderWidth' => 2,
                ]
            ]
        ];
        
        // Chart 4: Stacked Bar Chart (Sales minus Expenses)
        $charts[] = [
            'reportType' => 'stackedBar',
            'title' => 'Sales vs Expenses (Stacked View)',
            'subtitle' => 'Visualizing the relationship between sales and expenses',
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expensesData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ]
            ],
            'options' => [
                'scales' => [
                    'x' => [
                        'stacked' => true
                    ],
                    'y' => [
                        'stacked' => true,
                        'ticks' => [
                            'callback' => 'function(value) { return "₱" + value.toLocaleString("en-PH", {minimumFractionDigits: 0, maximumFractionDigits: 0}); }'
                        ]
                    ]
                ]
            ]
        ];
        
        \Log::info('Sales & Expenses chart generation completed', [
            'charts_generated' => count($charts)
        ]);
        
        return $charts;
        
    } catch (\Exception $e) {
        \Log::error('Error in generateSalesAndExpensesChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return null;
    }
}
    /**
     * Generate sales chart based on table data
     */

    private static function generateSalesChart($transactions, $report)
    {
        if ($report == 'summary') {
            return self::salesSummaryChart($transactions);
        } else if ($report == 'list' || $report == 'served_list' || $report == 'notServed_list') {
            return self::salesTransactionChart($transactions);
        } else if ($report == 'hoursale') {
            return self::hourlySalesSummaryChart($transactions);
        } else if ($report == 'beverages' || $report == 'food') {
            return self::salesByCategoryChart($transactions, $report);
        } else if ($report == 'zreading') {
            return self::zReadingChart($transactions);
        } else if ($report == 'discount') {
            return self::summaryDiscountChart($transactions);
        } else if ($report == 'server') {
            return self::salebyserverChart($transactions);
        } else if ($report == 'srpwd') {
            return self::srpwddiscountChart($transactions);
        }
        
        \Log::info('No chart available for sales report: ' . $report);
        return null;
    }
   
    /**
     * Generate discount summary chart aligned with discount table data
     */
    private static function summaryDiscountChart($transactions)
    {
        try {
            // Group transactions by date and discount percentage
            $groupedByDate = [];
            $dates = [];
            $discountTypes = [5, 10, 20, 50];
            
            foreach ($transactions as $transaction) {
                if (!is_object($transaction)) {
                    continue;
                }
                
                // Get date from transaction
                $dateField = $transaction->date ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = Carbon::parse($dateField)->format('M d, Y');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                // Initialize date entry if not exists
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'total_transactions' => 0,
                        'total_amount' => 0,
                        'discount_types' => []
                    ];
                    
                    // Initialize all discount types
                    foreach ($discountTypes as $type) {
                        $groupedByDate[$date]['discount_types'][$type] = [
                            'transactions' => 0,
                            'amount' => 0
                        ];
                    }
                }
                
                // Get discount percentage
                $discount = (int)$transaction->lime_discount;
                
                // Check if this is a valid discount type
                if (in_array($discount, $discountTypes)) {
                    $groupedByDate[$date]['total_transactions'] += $transaction->transaction_count ?? 0;
                    $groupedByDate[$date]['total_amount'] += $transaction->ld_value ?? 0;
                    
                    $groupedByDate[$date]['discount_types'][$discount]['transactions'] += $transaction->transaction_count ?? 0;
                    $groupedByDate[$date]['discount_types'][$discount]['amount'] += $transaction->ld_value ?? 0;
                }
            }
            
            if (empty($dates)) {
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return Carbon::parse($a)->timestamp <=> Carbon::parse($b)->timestamp;
            });
            
            // Prepare datasets for discount amounts
            $amountDatasets = [];
            $transactionDatasets = [];
            
            $discountColors = [
                5 => ['border' => 'rgb(59, 130, 246)', 'bg' => 'rgba(59, 130, 246, 0.6)'],  // Blue
                10 => ['border' => 'rgb(34, 197, 94)', 'bg' => 'rgba(34, 197, 94, 0.6)'],   // Green
                20 => ['border' => 'rgb(234, 179, 8)', 'bg' => 'rgba(234, 179, 8, 0.6)'],   // Yellow
                50 => ['border' => 'rgb(239, 68, 68)', 'bg' => 'rgba(239, 68, 68, 0.6)']    // Red
            ];
            
            foreach ($discountTypes as $type) {
                $amountData = [];
                $transactionData = [];
                
                foreach ($dates as $date) {
                    $amountData[] = $groupedByDate[$date]['discount_types'][$type]['amount'] ?? 0;
                    $transactionData[] = $groupedByDate[$date]['discount_types'][$type]['transactions'] ?? 0;
                }
                
                $color = $discountColors[$type] ?? ['border' => 'rgb(201, 203, 207)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $amountDatasets[] = [
                    'label' => "{$type}% Discount Amount",
                    'data' => $amountData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 2,
                    'type' => 'bar',
                ];
                
                $transactionDatasets[] = [
                    'label' => "{$type}% Transactions",
                    'data' => $transactionData,
                    'borderColor' => $color['border'],
                    'borderWidth' => 2,
                    'type' => 'line',
                    'fill' => false,
                    'yAxisID' => 'y1',
                ];
            }

            return [
                [
                    'reportType' => 'bar',
                    'title' => 'Discount Amount by Percentage',
                    'subtitle' => 'Total discount amount per discount type',
                    'labels' => $dates,
                    'datasets' => $amountDatasets,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Amount (₱)'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'reportType' => 'line',
                    'title' => 'Transaction Count by Discount Percentage',
                    'subtitle' => 'Number of transactions per discount type',
                    'labels' => $dates,
                    'datasets' => $transactionDatasets,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Number of Transactions'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'reportType' => 'pie',
                    'title' => 'Discount Distribution by Type',
                    'subtitle' => 'Percentage of total discount amount by discount type',
                    'labels' => array_map(function($type) { return "{$type}% Discount"; }, $discountTypes),
                    'datasets' => [
                        [
                            'label' => 'Discount Amount',
                            'data' => array_map(function($type) use ($groupedByDate) {
                                $total = 0;
                                foreach ($groupedByDate as $dateData) {
                                    $total += $dateData['discount_types'][$type]['amount'] ?? 0;
                                }
                                return $total;
                            }, $discountTypes),
                            'backgroundColor' => array_map(function($type) use ($discountColors) {
                                return $discountColors[$type]['bg'] ?? 'rgba(201, 203, 207, 0.6)';
                            }, $discountTypes),
                            'borderColor' => array_map(function($type) use ($discountColors) {
                                return $discountColors[$type]['border'] ?? 'rgb(201, 203, 207)';
                            }, $discountTypes),
                            'borderWidth' => 1,
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in summaryDiscountChart: ' . $e->getMessage());
            return null;
        }
    }
    // In ChartHelper.php, add this method to the existing class

    /**
     * Generate discount chart from table data
     */
    public static function generateDiscountChart($discounts)
    {
        try {
            \Log::info('Generating discount chart', ['count' => count($discounts)]);
            
            if (empty($discounts)) {
                return null;
            }
            
            $groupedByDate = [];
            $dates = [];
            $discountTypes = [5, 10, 20, 50];
            
            foreach ($discounts as $discount) {
                if (!is_object($discount)) {
                    continue;
                }
                
                $dateField = $discount->date ?? $discount->created_at ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = \Carbon\Carbon::parse($dateField)->format('M d, Y');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                    // Initialize all discount types
                    foreach ($discountTypes as $type) {
                        $groupedByDate[$date]["discount{$type}"] = 0;
                        $groupedByDate[$date]["transactions{$type}"] = 0;
                    }
                }
                
                // Get discount percentage
                $discountPercent = (int)($discount->lime_discount ?? 0);
                
                // Only process valid discount types
                if (in_array($discountPercent, $discountTypes)) {
                    $amount = floatval($discount->ld_value ?? 0);
                    $transactions = intval($discount->transaction_count ?? 0);
                    
                    $groupedByDate[$date]["discount{$discountPercent}"] += $amount;
                    $groupedByDate[$date]["transactions{$discountPercent}"] += $transactions;
                }
            }
            
            if (empty($dates)) {
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return \Carbon\Carbon::parse($a)->timestamp <=> \Carbon\Carbon::parse($b)->timestamp;
            });
            
            \Log::info('Discount chart data prepared', [
                'dates' => $dates,
                'discount_types' => $discountTypes
            ]);
            
            // Prepare datasets for each discount type
            $datasets = [];
            $discountColors = [
                5 => ['border' => 'rgb(54, 162, 235)', 'bg' => 'rgba(54, 162, 235, 0.6)'],
                10 => ['border' => 'rgb(255, 159, 64)', 'bg' => 'rgba(255, 159, 64, 0.6)'],
                20 => ['border' => 'rgb(75, 192, 75)', 'bg' => 'rgba(75, 192, 75, 0.6)'],
                50 => ['border' => 'rgb(153, 102, 255)', 'bg' => 'rgba(153, 102, 255, 0.6)']
            ];
            
            // Create amount datasets
            foreach ($discountTypes as $type) {
                $amountData = [];
                foreach ($dates as $date) {
                    $amountData[] = $groupedByDate[$date]["discount{$type}"] ?? 0;
                }
                
                $color = $discountColors[$type] ?? ['border' => 'rgb(201, 203, 207)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $datasets[] = [
                    'label' => "{$type}% Discount Amount",
                    'data' => $amountData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 2,
                ];
            }
            
            // Create transaction datasets for secondary axis
            $transactionDatasets = [];
            foreach ($discountTypes as $type) {
                $transactionData = [];
                foreach ($dates as $date) {
                    $transactionData[] = $groupedByDate[$date]["transactions{$type}"] ?? 0;
                }
                
                $color = $discountColors[$type] ?? ['border' => 'rgb(201, 203, 207)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $transactionDatasets[] = [
                    'label' => "{$type}% Transactions",
                    'data' => $transactionData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'].'30', // More transparent
                    'borderWidth' => 1,
                    'type' => 'bar',
                    'yAxisID' => 'y1'
                ];
            }
            
            // Return multiple chart configurations
            return [
                [
                    'reportType' => 'stackedBar',
                    'title' => 'Discount Amount by Type',
                    'subtitle' => 'Stacked view of discount amounts per discount percentage',
                    'labels' => $dates,
                    'datasets' => $datasets,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Amount (₱)'
                                ],
                                'stacked' => true
                            ],
                            'x' => [
                                'stacked' => true
                            ]
                        ]
                    ]
                ],
                [
                    'reportType' => 'bar',
                    'title' => 'Discount Transactions by Type',
                    'subtitle' => 'Number of transactions per discount percentage',
                    'labels' => $dates,
                    'datasets' => $transactionDatasets,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Transaction Count'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'reportType' => 'pie',
                    'title' => 'Discount Distribution',
                    'subtitle' => 'Percentage of total discount amount by discount type',
                    'labels' => array_map(function($type) { return "{$type}% Discount"; }, $discountTypes),
                    'datasets' => [
                        [
                            'label' => 'Discount Amount',
                            'data' => array_map(function($type) use ($dates, $groupedByDate) {
                                $total = 0;
                                foreach ($dates as $date) {
                                    $total += $groupedByDate[$date]["discount{$type}"] ?? 0;
                                }
                                return $total;
                            }, $discountTypes),
                            'backgroundColor' => array_map(function($type) use ($discountColors) {
                                return $discountColors[$type]['bg'] ?? 'rgba(201, 203, 207, 0.6)';
                            }, $discountTypes),
                            'borderColor' => array_map(function($type) use ($discountColors) {
                                return $discountColors[$type]['border'] ?? 'rgb(201, 203, 207)';
                            }, $discountTypes),
                            'borderWidth' => 1,
                        ]
                    ]
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in generateDiscountChart: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Generate sales by server chart aligned with server table data
     */
    private static function salebyserverChart($transactions)
    {
        try {
            // Group transactions by server/cashier
            $serverStats = [];
            
            foreach ($transactions as $transaction) {
                if (!is_object($transaction)) {
                    continue;
                }
                
                // Get server/cashier name
                $serverName = '';
                if (isset($transaction->thisCashier) && isset($transaction->thisCashier->name)) {
                    $serverName = $transaction->thisCashier->name;
                } elseif (isset($transaction->cashier_name)) {
                    $serverName = $transaction->cashier_name;
                } elseif (isset($transaction->user) && isset($transaction->user->name)) {
                    $serverName = $transaction->user->name;
                } else {
                    $serverName = 'Unknown';
                }
                
                // Initialize server entry if not exists
                if (!isset($serverStats[$serverName])) {
                    $serverStats[$serverName] = [
                        'total_sales' => 0,
                        'transaction_count' => 0,
                        'average_transaction' => 0
                    ];
                }
                
                // Add to server totals
                $total = floatval($transaction->total ?? 0);
                $serverStats[$serverName]['total_sales'] += $total;
                $serverStats[$serverName]['transaction_count']++;
            }
            
            if (empty($serverStats)) {
                \Log::info('No server data found for chart');
                return null;
            }
            
            // Calculate average transaction value for each server
            foreach ($serverStats as $serverName => &$stats) {
                if ($stats['transaction_count'] > 0) {
                    $stats['average_transaction'] = $stats['total_sales'] / $stats['transaction_count'];
                }
            }
            
            // Sort servers by total sales (descending)
            uasort($serverStats, function($a, $b) {
                return $b['total_sales'] <=> $a['total_sales'];
            });
            
            // Get top 10 servers
            $topServers = array_slice($serverStats, 0, 10, true);
            
            // Prepare data for chart - Option 1: Include transaction count in label
            $serverLabelsWithCount = [];
            $serverLabelsSimple = [];
            $totalSalesData = [];
            $transactionCountData = [];
            $averageTransactionData = [];
            
            foreach ($topServers as $serverName => $stats) {
                // Option 1: Include transaction count in label
                $serverLabelsWithCount[] = sprintf(
                    "%s (%.0f trans)",
                    substr($serverName, 0, 12) . (strlen($serverName) > 12 ? '...' : ''),
                    $stats['transaction_count']
                );
                
                // Option 2: Simple label for display
                $serverLabelsSimple[] = substr($serverName, 0, 15) . (strlen($serverName) > 15 ? '...' : '');
                
                $totalSalesData[] = $stats['total_sales'];
                $transactionCountData[] = $stats['transaction_count'];
                $averageTransactionData[] = $stats['average_transaction'];
            }
            
            \Log::info('Server chart data prepared', [
                'server_count' => count($serverLabelsSimple),
                'sample_label' => $serverLabelsWithCount[0] ?? 'none',
                'sample_sales' => $totalSalesData[0] ?? 'none',
                'sample_trans_count' => $transactionCountData[0] ?? 'none'
            ]);

            // Return main chart with transaction count in labels
            return [
                'title' => 'Top Servers by Sales Performance',
                'subtitle' => 'Sales amount with transaction count in parentheses',
                'labels' => $serverLabelsWithCount, // Using labels with transaction count
                'datasets' => [
                    [
                        'label' => 'Total Sales: ₱ ',
                        'data' => $totalSalesData,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => 'Number of Transactions: ',
                        'data' => $transactionCountData,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                        'borderColor' => 'rgb(34, 197, 94)',
                        'borderWidth' => 1,
                        'yAxisID' => 'y1',
                    ]
                ],
                'options' => [
                    'scales' => [
                        'y' => [
                            'type' => 'linear',
                            'display' => true,
                            'position' => 'left',
                            'title' => [
                                'display' => true,
                                'text' => 'Sales Amount (₱)'
                            ],
                            // Note: PHP cannot define JS functions directly; handle 'ticks' callback in JS frontend if needed
                        ],
                        'y1' => [
                            'type' => 'linear',
                            'display' => true,
                            'position' => 'right',
                            'title' => [
                                'display' => true,
                                'text' => 'Transaction Count'
                            ],
                            'grid' => [
                                'drawOnChartArea' => false
                            ]
                        ]
                    ]
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in salebyserverChart: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Generate SR/PWD discount analysis chart aligned with srpwd table data
     */
    private static function srpwddiscountChart($transactions)
    {
        try {
            \Log::info('Starting srpwddiscountChart generation', [
                'input_count' => count($transactions),
                'is_collection' => $transactions instanceof \Illuminate\Support\Collection,
                'first_item_sample' => $transactions->first() ? [
                    'date' => $transactions->first()->date ?? null,
                    'category' => $transactions->first()->category ?? null,
                    'transaction_count' => $transactions->first()->transaction_count ?? null,
                    'pwd_total' => $transactions->first()->pwd_total ?? null,
                    'customer_with_discount' => $transactions->first()->customer_with_discount ?? null
                ] : 'No data'
            ]);
            
            // Group transactions by discount category and date
            $categoryStats = [];
            $dates = [];
            
            // IMPORTANT: Update categories array to match your actual data
            // Based on your logs, you have 'sr' and 'pwd' in the database
            // Let's use these as the standard categories
            $categories = ['PWD', 'SR', 'ATHLETE', 'SINGLE', 'SENIOR']; // Added SENIOR
            
            $processedCount = 0;
            foreach ($transactions as $transaction) {
                $processedCount++;
                if (!is_object($transaction)) {
                    \Log::warning('Transaction is not an object', ['index' => $processedCount]);
                    continue;
                }
                
                // Get discount category - FIXED: Don't over-normalize
                $category = strtoupper(trim($transaction->category ?? ''));
                \Log::debug('Processing transaction', [
                    'index' => $processedCount,
                    'raw_category' => $transaction->category ?? 'null',
                    'normalized_category' => $category,
                    'date' => $transaction->date ?? 'null'
                ]);
                
                // FIXED: Simplified normalization
                // Map variations to standard categories
                if ($category == 'SOLO PARENT' || $category == 'SINGLE PARENT' || $category == 'SINGLE') {
                    $category = 'SINGLE';
                } 
                // FIXED: Keep SR as SR, don't change to SENIOR
                // elseif ($category == 'SR') {
                //     $category = 'SENIOR'; // REMOVED this line
                // }
                
                // FIXED: Also accept SENIOR if it appears
                if ($category == 'SENIOR') {
                    $category = 'SR'; // Map SENIOR back to SR for consistency
                }
                
                if (!in_array($category, $categories)) {
                    \Log::debug('Skipping unknown category', ['category' => $category]);
                    continue;
                }
                
                // Get date
                $dateField = $transaction->date ?? null;
                if (!$dateField) {
                    \Log::warning('No date field found', ['transaction' => $transaction->number ?? 'unknown']);
                    continue;
                }
                
                try {
                    $date = \Carbon\Carbon::parse($dateField)->format('M d, Y');
                } catch (\Exception $e) {
                    \Log::warning('Invalid date format', ['date_field' => $dateField]);
                    $date = \Carbon\Carbon::now()->format('M d, Y');
                }
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                // Initialize category entry if not exists
                if (!isset($categoryStats[$category])) {
                    $categoryStats[$category] = [
                        'total_transactions' => 0,
                        'total_amount' => 0,
                        'daily_data' => [],
                        'customer_count' => 0
                    ];
                }
                
                // Calculate amount (pwd_total / customer_with_discount)
                $customerCount = $transaction->customer_with_discount ?? 1;
                $amount = $customerCount > 0 ? ($transaction->pwd_total ?? 0) / $customerCount : 0;
                
                $transactionCount = $transaction->transaction_count ?? 0;
                
                // Add to category totals
                $categoryStats[$category]['total_transactions'] += $transactionCount;
                $categoryStats[$category]['total_amount'] += $amount;
                $categoryStats[$category]['customer_count'] += $customerCount;
                
                // Initialize daily entry if not exists
                if (!isset($categoryStats[$category]['daily_data'][$date])) {
                    $categoryStats[$category]['daily_data'][$date] = [
                        'transactions' => 0,
                        'amount' => 0,
                        'customers' => 0
                    ];
                }
                
                $categoryStats[$category]['daily_data'][$date]['transactions'] += $transactionCount;
                $categoryStats[$category]['daily_data'][$date]['amount'] += $amount;
                $categoryStats[$category]['daily_data'][$date]['customers'] += $customerCount;
                
                \Log::debug('Added to category stats', [
                    'category' => $category,
                    'date' => $date,
                    'amount' => $amount,
                    'transaction_count' => $transactionCount,
                    'customer_count' => $customerCount
                ]);
            }
            
            \Log::info('Category stats calculated', [
                'processed_count' => $processedCount,
                'unique_categories' => array_keys($categoryStats),
                'unique_dates' => $dates,
                'category_totals' => array_map(function($stats) {
                    return [
                        'transactions' => $stats['total_transactions'],
                        'amount' => $stats['total_amount'],
                        'customers' => $stats['customer_count']
                    ];
                }, $categoryStats)
            ]);
            
            if (empty($categoryStats)) {
                \Log::warning('No category stats generated - empty categoryStats');
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return \Carbon\Carbon::parse($a)->timestamp <=> \Carbon\Carbon::parse($b)->timestamp;
            });
            
            \Log::info('Sorted dates for chart', ['dates' => $dates, 'count' => count($dates)]);
            
            // Prepare data for category distribution
            $categoryLabels = [];
            $categoryAmountData = [];
            $categoryTransactionData = [];
            $categoryCustomerData = [];
            
            // FIXED: Updated color mapping
            $categoryColors = [
                'PWD' => ['border' => 'rgb(59, 130, 246)', 'bg' => 'rgba(59, 130, 246, 0.6)'],
                'SR' => ['border' => 'rgb(34, 197, 94)', 'bg' => 'rgba(34, 197, 94, 0.6)'],
                'SENIOR' => ['border' => 'rgb(34, 197, 94)', 'bg' => 'rgba(34, 197, 94, 0.6)'], // Added for consistency
                'ATHLETE' => ['border' => 'rgb(234, 179, 8)', 'bg' => 'rgba(234, 179, 8, 0.6)'],
                'SINGLE' => ['border' => 'rgb(239, 68, 68)', 'bg' => 'rgba(239, 68, 68, 0.6)']
            ];
            
            // FIXED: Process all found categories, not just predefined ones
            foreach (array_keys($categoryStats) as $category) {
                if (isset($categoryStats[$category])) {
                    $categoryLabels[] = $category;
                    $categoryAmountData[] = $categoryStats[$category]['total_amount'];
                    $categoryTransactionData[] = $categoryStats[$category]['total_transactions'];
                    $categoryCustomerData[] = $categoryStats[$category]['customer_count'];
                    
                    \Log::debug('Added category to chart', [
                        'category' => $category,
                        'amount' => $categoryStats[$category]['total_amount'],
                        'transactions' => $categoryStats[$category]['total_transactions'],
                        'customers' => $categoryStats[$category]['customer_count']
                    ]);
                }
            }
            
            if (empty($categoryLabels)) {
                \Log::warning('No category labels generated for chart');
                return null;
            }
            
            // FIXED: Also check if we need to handle single-category scenario differently
            if (count($categoryLabels) === 1) {
                \Log::info('Only one category found', ['category' => $categoryLabels[0]]);
                
                // Create a simpler chart for single category
                return [
                    [
                        'reportType' => 'bar',
                        'title' => 'Discount Analysis - ' . $categoryLabels[0],
                        'subtitle' => 'Daily discount amounts and customer counts',
                        'labels' => $dates,
                        'datasets' => [
                            [
                                'label' => 'Discount Amount (₱)',
                                'data' => array_map(function($date) use ($categoryStats, $categoryLabels) {
                                    $category = $categoryLabels[0];
                                    return $categoryStats[$category]['daily_data'][$date]['amount'] ?? 0;
                                }, $dates),
                                'borderColor' => $categoryColors[$categoryLabels[0]]['border'] ?? 'rgb(59, 130, 246)',
                                'backgroundColor' => $categoryColors[$categoryLabels[0]]['bg'] ?? 'rgba(59, 130, 246, 0.6)',
                                'borderWidth' => 2,
                                'yAxisID' => 'y'
                            ],
                            [
                                'label' => 'Customer Count',
                                'data' => array_map(function($date) use ($categoryStats, $categoryLabels) {
                                    $category = $categoryLabels[0];
                                    return $categoryStats[$category]['daily_data'][$date]['customers'] ?? 0;
                                }, $dates),
                                'borderColor' => 'rgb(255, 159, 64)',
                                'backgroundColor' => 'rgba(255, 159, 64, 0.4)',
                                'borderWidth' => 1,
                                'type' => 'bar',
                                'yAxisID' => 'y1'
                            ]
                        ],
                        'options' => [
                            'scales' => [
                                'y' => [
                                    'type' => 'linear',
                                    'display' => true,
                                    'position' => 'left',
                                    'title' => [
                                        'display' => true,
                                        'text' => 'Amount (₱)'
                                    ],
                                    'ticks' => [
                                        'callback' => 'function(value) { return "₱" + value.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2}); }'
                                    ]
                                ],
                                'y1' => [
                                    'type' => 'linear',
                                    'display' => true,
                                    'position' => 'right',
                                    'title' => [
                                        'display' => true,
                                        'text' => 'Customer Count'
                                    ],
                                    'grid' => [
                                        'drawOnChartArea' => false
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'reportType' => 'line',
                        'title' => 'Daily ' . $categoryLabels[0] . ' Discount Trend',
                        'subtitle' => 'Trend of discount amounts over time',
                        'labels' => $dates,
                        'datasets' => [
                            [
                                'label' => $categoryLabels[0] . ' Discount Amount',
                                'data' => array_map(function($date) use ($categoryStats, $categoryLabels) {
                                    $category = $categoryLabels[0];
                                    return $categoryStats[$category]['daily_data'][$date]['amount'] ?? 0;
                                }, $dates),
                                'borderColor' => $categoryColors[$categoryLabels[0]]['border'] ?? 'rgb(59, 130, 246)',
                                'backgroundColor' => str_replace('rgb', 'rgba', $categoryColors[$categoryLabels[0]]['border'] ?? 'rgb(59, 130, 246)') . ', 0.1)',
                                'borderWidth' => 3,
                                'fill' => true,
                                'tension' => 0.4
                            ]
                        ]
                    ]
                ];
            }
            
            // Original multi-category chart logic (kept for reference)
            // Prepare data for daily trends (line chart)
            $dailyAmountDatasets = [];
            $dailyTransactionDatasets = [];
            $dailyCustomerDatasets = [];
            
            foreach (array_keys($categoryStats) as $category) {
                if (!isset($categoryStats[$category])) {
                    continue;
                }
                
                $amountData = [];
                $transactionData = [];
                $customerData = [];
                
                foreach ($dates as $date) {
                    $amountData[] = $categoryStats[$category]['daily_data'][$date]['amount'] ?? 0;
                    $transactionData[] = $categoryStats[$category]['daily_data'][$date]['transactions'] ?? 0;
                    $customerData[] = $categoryStats[$category]['daily_data'][$date]['customers'] ?? 0;
                }
                
                $color = $categoryColors[$category] ?? ['border' => 'rgb(201, 203, 207)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $dailyAmountDatasets[] = [
                    'label' => "{$category} Amount",
                    'data' => $amountData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => str_replace('rgb', 'rgba', $color['border']) . ', 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4
                ];
                
                $dailyTransactionDatasets[] = [
                    'label' => "{$category} Transactions",
                    'data' => $transactionData,
                    'borderColor' => $color['border'],
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                    'borderDash' => [5, 5]
                ];
                
                $dailyCustomerDatasets[] = [
                    'label' => "{$category} Customers",
                    'data' => $customerData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 1,
                    'type' => 'bar',
                    'yAxisID' => 'y1'
                ];
            }

            \Log::info('Chart datasets prepared', [
                'category_labels' => $categoryLabels,
                'category_amount_data' => $categoryAmountData,
                'category_transaction_data' => $categoryTransactionData,
                'daily_amount_datasets_count' => count($dailyAmountDatasets),
                'daily_transaction_datasets_count' => count($dailyTransactionDatasets),
                'date_range_count' => count($dates)
            ]);
            
            $charts = [
                [
                    'reportType' => 'doughnut',
                    'title' => 'Discount Distribution by Category',
                    'subtitle' => 'Percentage of total discount amount by category',
                    'labels' => $categoryLabels,
                    'datasets' => [
                        [
                            'label' => 'Discount Amount',
                            'data' => $categoryAmountData,
                            'backgroundColor' => array_map(function($cat) use ($categoryColors) {
                                return $categoryColors[$cat]['bg'] ?? 'rgba(201, 203, 207, 0.6)';
                            }, $categoryLabels),
                            'borderColor' => array_map(function($cat) use ($categoryColors) {
                                return $categoryColors[$cat]['border'] ?? 'rgb(201, 203, 207)';
                            }, $categoryLabels),
                            'borderWidth' => 2,
                        ]
                    ]
                ],
                [
                    'reportType' => 'bar',
                    'title' => 'Transaction Count by Discount Category',
                    'subtitle' => 'Number of transactions per discount category',
                    'labels' => $categoryLabels,
                    'datasets' => [
                        [
                            'label' => 'Number of Transactions',
                            'data' => $categoryTransactionData,
                            'backgroundColor' => array_map(function($cat) use ($categoryColors) {
                                return $categoryColors[$cat]['bg'] ?? 'rgba(201, 203, 207, 0.6)';
                            }, $categoryLabels),
                            'borderColor' => array_map(function($cat) use ($categoryColors) {
                                return $categoryColors[$cat]['border'] ?? 'rgb(201, 203, 207)';
                            }, $categoryLabels),
                            'borderWidth' => 1,
                        ]
                    ]
                ]
            ];
            
            // Add daily trend charts only if we have multiple dates
            if (count($dates) > 1) {
                $charts[] = [
                    'reportType' => 'line',
                    'title' => 'Daily Discount Amount by Category',
                    'subtitle' => 'Trend of discount amounts over time',
                    'labels' => $dates,
                    'datasets' => $dailyAmountDatasets
                ];
                
                $charts[] = [
                    'reportType' => 'line',
                    'title' => 'Daily Transaction Count by Category',
                    'subtitle' => 'Trend of transaction counts over time',
                    'labels' => $dates,
                    'datasets' => $dailyTransactionDatasets
                ];
                
                $charts[] = [
                    'reportType' => 'bar',
                    'title' => 'Customer Count with Discount by Category',
                    'subtitle' => 'Number of customers receiving discounts',
                    'labels' => $dates,
                    'datasets' => $dailyCustomerDatasets,
                    'options' => [
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Customer Count'
                                ]
                            ]
                        ]
                    ]
                ];
                
                $charts[] = [
                    'reportType' => 'stackedBar',
                    'title' => 'Cumulative Discount Analysis',
                    'subtitle' => 'Stacked view of discounts by category over time',
                    'labels' => $dates,
                    'datasets' => array_map(function($category) use ($categoryStats, $dates, $categoryColors) {
                        if (!isset($categoryStats[$category])) {
                            return null;
                        }
                        
                        $data = [];
                        foreach ($dates as $date) {
                            $data[] = $categoryStats[$category]['daily_data'][$date]['amount'] ?? 0;
                        }
                        
                        $color = $categoryColors[$category] ?? ['border' => 'rgb(201, 203, 207)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                        
                        return [
                            'label' => $category,
                            'data' => $data,
                            'backgroundColor' => $color['bg'],
                            'borderColor' => $color['border'],
                            'borderWidth' => 1,
                        ];
                    }, array_keys($categoryStats))
                ];
            }
            
            \Log::info('SR/PWD chart generation completed', [
                'total_charts_generated' => count($charts),
                'chart_types' => array_map(function($chart) {
                    return $chart['reportType'] ?? 'unknown';
                }, $charts)
            ]);
            
            return $charts;
            
        } catch (\Exception $e) {
            \Log::error('Error in srpwddiscountChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return null;
        }
    }
    /**
     * Generate Z-Reading summary chart
     */
    private static function zReadingChart($zreadings)
    {
        try {
            \Log::info('Starting zReadingChart generation', ['count' => count($zreadings)]);
            
            $groupedByDate = [];
            $dates = [];
            
            foreach ($zreadings as $reading) {
                if (!is_object($reading)) {
                    continue;
                }
                
                // Get date from reading
                $dateField = $reading->date ?? $reading->created_at ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = Carbon::parse($dateField)->format('M d, Y');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'total_sales' => 0,
                        'total_discount' => 0,
                        'vat_amount' => 0,
                        'service_charge' => 0,
                        'net_amount' => 0,
                        'gross_sales' => 0
                    ];
                }
                
                // Add values from your transaction data
                $groupedByDate[$date]['total_sales'] += floatval($reading->total ?? 0);
                $groupedByDate[$date]['total_discount'] += floatval($reading->lime_discount ?? 0);
                $groupedByDate[$date]['vat_amount'] += floatval($reading->vat ?? 0);
                $groupedByDate[$date]['service_charge'] += floatval($reading->service_charge ?? 0);
                
                // Calculate net amount
                $net = ($reading->total ?? 0) - (($reading->vat ?? 0) + ($reading->service_charge ?? 0));
                $groupedByDate[$date]['net_amount'] += $net;
                
                // Gross sales would be total before any deductions
                $groupedByDate[$date]['gross_sales'] += $reading->total ?? 0;
            }
            
            if (empty($dates)) {
                \Log::warning('No dates found for Z-Reading chart');
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return Carbon::parse($a)->timestamp <=> Carbon::parse($b)->timestamp;
            });
            
            \Log::info('Z-Reading chart dates prepared', ['dates' => $dates]);
            
            // Prepare datasets
            $datasets = [
                [
                    'label' => 'Gross Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['gross_sales'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Total Discount',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['total_discount'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(255, 205, 86)',
                    'backgroundColor' => 'rgba(255, 205, 86, 0.6)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Net Amount',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['net_amount'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderWidth' => 2,
                ]
            ];

            $chartData = [
                'title' => 'Z-Reading Summary',
                'labels' => $dates,
                'datasets' => $datasets
            ];
            
            \Log::info('Z-Reading chart data generated', [
                'labels_count' => count($dates),
                'datasets_count' => count($datasets)
            ]);
            
            return $chartData;
            
        } catch (\Exception $e) {
            \Log::error('Error in zReadingChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return null;
        }
    }

    public static function generateZReadingPaymentChart($zreadings, $multiplepayments = null)
    {
        try {
            \Log::info('Starting generateZReadingPaymentChart', ['count' => count($zreadings)]);
            
            // Group transactions by date and payment method
            $groupedByDate = [];
            $dates = [];
            
            // First, process regular transactions with single payment mode
            foreach ($zreadings as $reading) {
                if (!is_object($reading)) {
                    continue;
                }
                
                $dateField = $reading->date ?? $reading->created_at ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = Carbon::parse($dateField)->format('M d, Y');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    // Initialize with all possible payment methods
                    $groupedByDate[$date] = [
                        'CASH' => 0,
                        'GCASH' => 0,
                        'CREDIT CARD' => 0,
                        'BANC NET' => 0,
                        'VISA' => 0,
                        'JCB' => 0,
                        'DINERS' => 0,
                        'AMEX' => 0,
                        'PAYMAYA' => 0,
                        'FOOD PANDA' => 0,
                        'GRAB FOOD' => 0,
                        'UNION PAY' => 0,
                        'GRAB PAY' => 0,
                        'BDO PAY' => 0,
                        'WE CHAT PAY' => 0,
                        'QR PH' => 0,
                        'DOWNPAYMENT' => 0,
                        'GIFT CERT' => 0,
                        'OTHER' => 0
                    ];
                }
                
                // Get payment mode and amount
                $modeOfPayment = strtoupper(trim($reading->mode_of_payment ?? ''));
                $amount = floatval($reading->total ?? 0);
                
                if ($amount <= 0) {
                    continue;
                }
                
                // Map MOP to standard method names
                $methodMapping = [
                    'CASH' => 'CASH',
                    'GCASH' => 'GCASH',
                    'GCASH CREDIT GIVES' => 'GCASH',
                    'CREDIT CARD' => 'CREDIT CARD',
                    'MASTERCARD' => 'CREDIT CARD',
                    'DEBIT' => 'CREDIT CARD',
                    'VISA' => 'VISA',
                    'JCB' => 'JCB',
                    'DINERS' => 'DINERS',
                    'AMEX' => 'AMEX',
                    'AMERICAN EXPRESS' => 'AMEX',
                    'PAYMAYA' => 'PAYMAYA',
                    'BANK TRANSFER' => 'BANC NET',
                    'BANC NET' => 'BANC NET',
                    'UNION PAY' => 'UNION PAY',
                    'UNION BANK' => 'UNION PAY',
                    'GRAB PAY' => 'GRAB PAY',
                    'GRAB FOOD' => 'GRAB FOOD',
                    'FOOD PANDA' => 'FOOD PANDA',
                    'BDO PAY' => 'BDO PAY',
                    'WE CHAT PAY' => 'WE CHAT PAY',
                    'QR PH' => 'QR PH',
                    'DOWN PAYMENT' => 'DOWNPAYMENT',
                    'DOWNPAYMENT' => 'DOWNPAYMENT',
                    'GIFT CERT' => 'GIFT CERT',
                    'GIFT CERTIFICATE' => 'GIFT CERT'
                ];
                
                $methodName = $methodMapping[$modeOfPayment] ?? 'OTHER';
                
                // Add to the appropriate method
                if ($modeOfPayment !== 'MULTIPLE') {
                    if (!isset($groupedByDate[$date][$methodName])) {
                        $groupedByDate[$date][$methodName] = 0;
                    }
                    $groupedByDate[$date][$methodName] += $amount;
                    \Log::debug('Added single payment', [
                        'date' => $date,
                        'mode' => $modeOfPayment,
                        'mapped_to' => $methodName,
                        'amount' => $amount
                    ]);
                }
            }
            
            // Second, process multiple payments if available
            if ($multiplepayments && count($multiplepayments) > 0) {
                \Log::info('Processing multiple payments', ['count' => count($multiplepayments)]);
                
                foreach ($multiplepayments as $payment) {
                    if (!is_object($payment)) {
                        continue;
                    }
                    
                    $dateField = $payment->date ?? $payment->created_at ?? null;
                    if (!$dateField) {
                        continue;
                    }
                    
                    $date = Carbon::parse($dateField)->format('M d, Y');
                    
                    // If this date isn't in our dates array yet, add it
                    if (!in_array($date, $dates)) {
                        $dates[] = $date;
                        // Initialize for this date
                        $groupedByDate[$date] = [
                            'CASH' => 0,
                            'GCASH' => 0,
                            'CREDIT CARD' => 0,
                            'BANC NET' => 0,
                            'VISA' => 0,
                            'JCB' => 0,
                            'DINERS' => 0,
                            'AMEX' => 0,
                            'PAYMAYA' => 0,
                            'FOOD PANDA' => 0,
                            'GRAB FOOD' => 0,
                            'UNION PAY' => 0,
                            'GRAB PAY' => 0,
                            'BDO PAY' => 0,
                            'WE CHAT PAY' => 0,
                            'QR PH' => 0,
                            'DOWNPAYMENT' => 0,
                            'GIFT CERT' => 0,
                            'OTHER' => 0
                        ];
                    }
                    
                    // Ensure this date exists in groupedByDate
                    if (!isset($groupedByDate[$date])) {
                        $groupedByDate[$date] = [
                            'CASH' => 0,
                            'GCASH' => 0,
                            'CREDIT CARD' => 0,
                            'BANC NET' => 0,
                            'VISA' => 0,
                            'JCB' => 0,
                            'DINERS' => 0,
                            'AMEX' => 0,
                            'PAYMAYA' => 0,
                            'FOOD PANDA' => 0,
                            'GRAB FOOD' => 0,
                            'UNION PAY' => 0,
                            'GRAB PAY' => 0,
                            'BDO PAY' => 0,
                            'WE CHAT PAY' => 0,
                            'QR PH' => 0,
                            'DOWNPAYMENT' => 0,
                            'GIFT CERT' => 0,
                            'OTHER' => 0
                        ];
                    }
                    
                    $mop = strtoupper(trim($payment->mop ?? ''));
                    $amount = floatval($payment->amount ?? 0);
                    
                    if ($amount <= 0 || empty($mop)) {
                        continue;
                    }
                    
                    // Map MOP to standard method names
                    $methodMapping = [
                        'CASH' => 'CASH',
                        'GCASH' => 'GCASH',
                        'GCASH CREDIT GIVES' => 'GCASH',
                        'CREDIT CARD' => 'CREDIT CARD',
                        'MASTERCARD' => 'CREDIT CARD',
                        'DEBIT' => 'CREDIT CARD',
                        'VISA' => 'VISA',
                        'JCB' => 'JCB',
                        'DINERS' => 'DINERS',
                        'AMEX' => 'AMEX',
                        'AMERICAN EXPRESS' => 'AMEX',
                        'PAYMAYA' => 'PAYMAYA',
                        'BANK TRANSFER' => 'BANC NET',
                        'BANC NET' => 'BANC NET',
                        'UNION PAY' => 'UNION PAY',
                        'UNION BANK' => 'UNION PAY',
                        'GRAB PAY' => 'GRAB PAY',
                        'GRAB FOOD' => 'GRAB FOOD',
                        'FOOD PANDA' => 'FOOD PANDA',
                        'BDO PAY' => 'BDO PAY',
                        'WE CHAT PAY' => 'WE CHAT PAY',
                        'QR PH' => 'QR PH',
                        'DOWN PAYMENT' => 'DOWNPAYMENT',
                        'DOWNPAYMENT' => 'DOWNPAYMENT',
                        'GIFT CERT' => 'GIFT CERT',
                        'GIFT CERTIFICATE' => 'GIFT CERT'
                    ];
                    
                    $methodName = $methodMapping[$mop] ?? 'OTHER';
                    
                    if (!isset($groupedByDate[$date][$methodName])) {
                        $groupedByDate[$date][$methodName] = 0;
                    }
                    $groupedByDate[$date][$methodName] += $amount;
                    
                    \Log::debug('Added multiple payment', [
                        'date' => $date,
                        'mop' => $mop,
                        'mapped_to' => $methodName,
                        'amount' => $amount
                    ]);
                }
            }
            
            if (empty($dates)) {
                \Log::warning('No dates found for payment chart');
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return Carbon::parse($a)->timestamp <=> Carbon::parse($b)->timestamp;
            });
            
            \Log::info('Payment data by date:', ['grouped_data' => $groupedByDate]);
            
            // Prepare datasets - only include methods that have data
            $datasets = [];
            $paymentColors = [
                'CASH' => 'rgb(75, 192, 192)',
                'GCASH' => 'rgb(54, 162, 235)',
                'CREDIT CARD' => 'rgb(255, 206, 86)',
                'BANC NET' => 'rgb(153, 102, 255)',
                'VISA' => 'rgb(255, 99, 132)',
                'JCB' => 'rgb(255, 159, 64)',
                'DINERS' => 'rgb(201, 203, 207)',
                'AMEX' => 'rgb(255, 99, 71)',
                'PAYMAYA' => 'rgb(50, 205, 50)',
                'FOOD PANDA' => 'rgb(30, 144, 255)',
                'GRAB FOOD' => 'rgb(138, 43, 226)',
                'UNION PAY' => 'rgb(210, 105, 30)',
                'GRAB PAY' => 'rgb(0, 128, 128)',
                'BDO PAY' => 'rgb(128, 0, 128)',
                'WE CHAT PAY' => 'rgb(0, 128, 0)',
                'QR PH' => 'rgb(128, 128, 0)',
                'DOWNPAYMENT' => 'rgb(128, 0, 0)',
                'GIFT CERT' => 'rgb(0, 0, 128)',
                'OTHER' => 'rgb(128, 128, 128)'
            ];
            
            // Define all possible payment methods
            $allMethods = array_keys($paymentColors);
            
            \Log::info('Preparing chart datasets...');
            foreach ($allMethods as $method) {
                $methodData = [];
                $methodTotal = 0;
                
                foreach ($dates as $date) {
                    $value = $groupedByDate[$date][$method] ?? 0;
                    $methodData[] = $value;
                    $methodTotal += $value;
                }
                
                // Only include methods that have data
                if ($methodTotal > 0) {
                    $color = $paymentColors[$method] ?? 'rgb(201, 203, 207)';
                    
                    $datasets[] = [
                        'label' => $method,
                        'data' => $methodData,
                        'borderColor' => $color,
                        'backgroundColor' => str_replace('rgb', 'rgba', $color) . ', 0.6)',
                        'borderWidth' => 2,
                    ];
                    
                    \Log::info('Added dataset', ['method' => $method, 'total' => $methodTotal]);
                }
            }
            
            \Log::info('Datasets prepared', ['count' => count($datasets)]);
            
            // If no payment data found at all
            if (empty($datasets)) {
                \Log::warning('No payment data found for Z-Reading chart');
                
                return [
                    'title' => 'Z-Reading Payment Methods',
                    'labels' => $dates,
                    'datasets' => [
                        [
                            'label' => 'No Payment Data Available',
                            'data' => array_fill(0, count($dates), 0),
                            'borderColor' => 'rgb(201, 203, 207)',
                            'backgroundColor' => 'rgba(201, 203, 207, 0.6)',
                            'borderWidth' => 2,
                        ]
                    ]
                ];
            }

            return [
                'title' => 'Z-Reading Payment Methods',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in generateZReadingPaymentChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Generate Z-Reading deductions chart
     */
    public static function generateZReadingDeductionsChart($zreadings)
    {
        try {
            // Group Z-Readings by date
            $groupedByDate = [];
            $dates = [];
            
            foreach ($zreadings as $reading) {
                if (!is_object($reading)) {
                    continue;
                }
                
                $dateField = $reading->date ?? $reading->created_at ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = Carbon::parse($dateField)->format('M d, Y');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'total_discount' => 0,
                        'pwd' => 0,
                        'vat' => 0,
                        'sc' => 0,
                        'gross_sales' => 0
                    ];
                }
                
                // Add deduction totals from your table structure
                $groupedByDate[$date]['total_discount'] += $reading->lime_discount ?? 0;
                $groupedByDate[$date]['pwd'] += $reading->pwd ?? 0;
                $groupedByDate[$date]['vat'] += $reading->vat ?? 0;
                $groupedByDate[$date]['sc'] += $reading->service_charge ?? 0;
                $groupedByDate[$date]['gross_sales'] += $reading->total ?? 0;
            }
            
            if (empty($dates)) {
                return null;
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return Carbon::parse($a)->timestamp <=> Carbon::parse($b)->timestamp;
            });
            
            // Prepare datasets for deductions
            $datasets = [
                [
                    'label' => 'Gross Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['gross_sales'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Total Discount',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['total_discount'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(255, 205, 86)',
                    'backgroundColor' => 'rgba(255, 205, 86, 0.6)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'PWD/Senior',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['pwd'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Service Charge',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['sc'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(255, 159, 64)',
                    'backgroundColor' => 'rgba(255, 159, 64, 0.6)',
                    'borderWidth' => 2,
                ]
            ];

            return [
                'reportType' => 'zreading_deductions',
                'title' => 'Z-Reading Deductions Analysis',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in generateZReadingDeductionsChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate sales summary chart from table data
     */
    private static function salesSummaryChart($transactions)
    {
        try {
            // Group transactions by date
            $groupedByDate = [];
            $dates = [];
            
            foreach ($transactions as $transaction) {
                if (!is_object($transaction)) {
                    continue;
                }
                
                // Handle different date field names
                $dateField = $transaction->date ?? $transaction->created_at ?? null;
                if (!$dateField) {
                    continue;
                }
                
                $date = Carbon::parse($dateField)->format('M d');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'total' => 0,
                        'discount' => 0,
                        'vat' => 0,
                        'net' => 0,
                        'served_total' => 0,
                        'not_served_total' => 0
                    ];
                }
                
                // Add to totals
                $groupedByDate[$date]['total'] += $transaction->total ?? 0;
                $groupedByDate[$date]['discount'] += $transaction->lime_discount ?? $transaction->pwd ?? 0;
                $groupedByDate[$date]['vat'] += $transaction->vat ?? 0;
                
                // Calculate net (total - vat - service charge)
                $serviceCharge = $transaction->service_charge ?? 0;
                $net = ($transaction->total ?? 0) - (($transaction->vat ?? 0) + $serviceCharge);
                $groupedByDate[$date]['net'] += $net;
                
                // Check if served
                $isServed = isset($transaction->is_audit) ? $transaction->is_audit : 
                           (isset($transaction->order_status) && $transaction->order_status == 'SERVED');
                
                if ($isServed) {
                    $groupedByDate[$date]['served_total'] += $transaction->total ?? 0;
                } else {
                    $groupedByDate[$date]['not_served_total'] += $transaction->total ?? 0;
                }
            }
            
            if (empty($dates)) {
                return null;
            }
            
            sort($dates);
            
            // Prepare datasets
            $datasets = [
                [
                    'label' => 'Total Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['total'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Net Amount',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['net'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Served Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['served_total'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(234, 179, 8)',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.5)',
                    'borderWidth' => 2,
                ],
                                [
                    'label' => 'Not Served Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['not_served_total'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgba(234, 8, 8, 1)',
                    'backgroundColor' => 'rgba(234, 8, 8, 0.5)',
                    'borderWidth' => 2,
                ]
            ];

            return [
                'reportType' => 'sales_summary',
                'title' => 'Daily Sales Summary',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in salesSummaryChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate sales transaction chart
     */
    private static function salesTransactionChart($transactions)
    {
        try {
            // Group transactions by payment method and date
            $groupedByDate = [];
            $dates = [];
            $paymentMethods = [];
            
            foreach ($transactions as $transaction) {
                if (!is_object($transaction)) {
                    continue;
                }
                
                $date = Carbon::parse($transaction->created_at ?? $transaction->date)->format('M d');
                $paymentMethod = strtoupper($transaction->mode_of_payment ?? 'CASH');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!in_array($paymentMethod, $paymentMethods)) {
                    $paymentMethods[] = $paymentMethod;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                }
                
                if (!isset($groupedByDate[$date][$paymentMethod])) {
                    $groupedByDate[$date][$paymentMethod] = 0;
                }
                
                $groupedByDate[$date][$paymentMethod] += $transaction->total ?? 0;
            }
            
            if (empty($dates)) {
                return null;
            }
            
            sort($dates);
            sort($paymentMethods);
            
            // Prepare datasets for each payment method
            $datasets = [];
            $colors = [
                'CASH' => ['border' => 'rgba(54, 162, 235, 1)', 'bg' => 'rgba(54, 162, 235, 0.6)'],
                'GCASH' => ['border' => 'rgba(255, 206, 86, 1)', 'bg' => 'rgba(255, 206, 86, 0.6)'],
                'VISA' => ['border' => 'rgba(75, 192, 192, 1)', 'bg' => 'rgba(75, 192, 192, 0.6)'],
                'MASTERCARD' => ['border' => 'rgba(153, 102, 255, 1)', 'bg' => 'rgba(153, 102, 255, 0.6)'],
                'AMEX' => ['border' => 'rgba(255, 99, 132, 1)', 'bg' => 'rgba(255, 99, 132, 0.6)'],
                'MULTIPLE' => ['border' => 'rgba(255, 159, 64, 1)', 'bg' => 'rgba(255, 159, 64, 0.6)'],
            ];
            
            foreach ($paymentMethods as $method) {
                $methodData = [];
                foreach ($dates as $date) {
                    $methodData[] = $groupedByDate[$date][$method] ?? 0;
                }
                
                $color = $colors[$method] ?? ['border' => 'rgba(201, 203, 207, 1)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $datasets[] = [
                    'label' => $method,
                    'data' => $methodData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 2,
                ];
            }

            return [
                'reportType' => 'sales_transaction',
                'title' => 'Sales by Payment Method',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in salesTransactionChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate hourly sales summary chart
     */
    private static function hourlySalesSummaryChart($transactions)
    {
        try {
            \Log::info('Starting hourlySalesSummaryChart', [
                'count' => count($transactions),
                'sample_data' => $transactions->first() ? get_object_vars($transactions->first()) : 'No data'
            ]);
            
            $groupedByHour = [];
            
            foreach ($transactions as $transaction) {
                if (!is_object($transaction)) {
                    \Log::warning('Transaction is not an object');
                    continue;
                }
                
                // Get the hour from the transaction
                $hour = $transaction->hour ?? null;
                if ($hour === null) {
                    \Log::warning('No hour found in transaction');
                    continue;
                }
                
                // Format hour as two digits
                $hourFormatted = str_pad($hour, 2, '0', STR_PAD_LEFT);
                
                if (!isset($groupedByHour[$hourFormatted])) {
                    $groupedByHour[$hourFormatted] = [
                        'sales' => 0,
                        'transactions' => 0,
                        'discount' => 0
                    ];
                }
                
                // Use hourly_sales field from your query
                $groupedByHour[$hourFormatted]['sales'] += $transaction->hourly_sales ?? 0;
                $groupedByHour[$hourFormatted]['transactions'] += $transaction->hourly_transactions ?? 0;
                $groupedByHour[$hourFormatted]['discount'] += $transaction->hourly_discount ?? 0;
            }
            
            \Log::info('Grouped by hour data', $groupedByHour);
            
            // Fill all 24 hours
            $labels = [];
            $salesData = [];
            $transactionsData = [];
            $discountData = [];
            
            for ($i = 0; $i < 24; $i++) {
                $hour = sprintf('%02d', $i);
                $labels[] = $hour . ':00';
                $salesData[] = $groupedByHour[$hour]['sales'] ?? 0;
                $transactionsData[] = $groupedByHour[$hour]['transactions'] ?? 0;
                $discountData[] = $groupedByHour[$hour]['discount'] ?? 0;
            }

            \Log::info('Hourly chart data prepared', [
                'labels_count' => count($labels),
                'sales_total' => array_sum($salesData),
                'transactions_total' => array_sum($transactionsData),
                'discount_total' => array_sum($discountData)
            ]);

            return [
                [
                    'reportType' => 'line',
                    'title' => 'Hourly Sales Distribution',
                    'subtitle' => 'Sales amount by hour with transaction count',
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Sales Amount: ₱',
                            'data' => $salesData,
                            'borderColor' => 'rgb(59, 130, 246)',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'borderWidth' => 3,
                            'fill' => true,
                            'tension' => 0.4,
                            'yAxisID' => 'y'
                        ],
                        [
                            'label' => 'Number of Transactions: ',
                            'data' => $transactionsData,
                            'borderColor' => 'rgb(34, 197, 94)',
                            'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                            'borderWidth' => 2,
                            'fill' => false,
                            'yAxisID' => 'y1',
                            'type' => 'bar',
                            'order' => 1
                        ]
                    ],
                    'options' => [
                        'responsive' => true,
                        'interaction' => [
                            'mode' => 'index',
                            'intersect' => false
                        ],
                        'scales' => [
                            'y' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'left',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Sales Amount (₱)'
                                ],
                                'ticks' => [
                                    'callback' => 'function(value) { return "₱ " + value.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2}); }'
                                ]
                            ],
                            'y1' => [
                                'type' => 'linear',
                                'display' => true,
                                'position' => 'right',
                                'title' => [
                                    'display' => true,
                                    'text' => 'Number of Transactions'
                                ],
                                'grid' => [
                                    'drawOnChartArea' => false
                                ],
                                'ticks' => [
                                    'stepSize' => 1,
                                    'precision' => 0
                                ]
                            ]
                        ],
                        'plugins' => [
                            'tooltip' => [
                                'callbacks' => [
                                    'label' => 'function(context) {
                                        const datasetLabel = context.dataset.label || "";
                                        let value = context.parsed.y;
                                        let label = "";
                                        
                                        if (datasetLabel.includes("Sales")) {
                                            label = "Sales Amount: ₱ " + value.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                        } else if (datasetLabel.includes("Transaction")) {
                                            label = "Number of Transactions: " + value;
                                        }
                                        return label;
                                    }',
                                    'afterLabel' => 'function(context) {
                                        // Get the index of the hovered point
                                        const index = context.dataIndex;
                                        const hour = context.chart.data.labels[index];
                                        
                                        // Get transaction count for this hour
                                        const transactions = @json($transactionsData);
                                        const sales = @json($salesData);
                                        
                                        if (transactions && transactions[index] !== undefined && sales && sales[index] !== undefined) {
                                            return [
                                                "Hour: " + hour,
                                                "Sales: ₱ " + sales[index].toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                                                "Transactions: " + transactions[index]
                                            ].join("\\n");
                                        }
                                        return "";
                                    }'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'reportType' => 'line',
                    'title' => 'Hourly Discounts',
                    'subtitle' => 'Discount amount by hour',
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Discount Amount (₱)',
                            'data' => $discountData,
                            'borderColor' => 'rgb(239, 68, 68)',
                            'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                            'borderWidth' => 3,
                            'fill' => true,
                            'tension' => 0.4
                        ]
                    ],
                    'options' => [
                        'plugins' => [
                            'tooltip' => [
                                'callbacks' => [
                                    'label' => 'function(context) {
                                        const value = context.parsed.y;
                                        return "Discount Amount: ₱ " + value.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }',
                                    'afterLabel' => 'function(context) {
                                        const index = context.dataIndex;
                                        const hour = context.chart.data.labels[index];
                                        const transactions = @json($transactionsData);
                                        
                                        if (transactions && transactions[index] !== undefined) {
                                            return "Hour: " + hour + "\\nTransactions: " + transactions[index];
                                        }
                                        return "";
                                    }'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in hourlySalesSummaryChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return null;
        }
    }
    /**
     * Generate expenses chart based on table data
     */
    private static function generateExpensesChart($expenses, $report)
    {
        if ($report == 'list' || $report == 'unpaid' || $report == 'cos' || $report == 'ooe') {
            return self::expensesTransactionChart($expenses);
        } else if ($report == 'summary') {
            return self::expensesSummaryChart($expenses);
        }
        
        return null;
    }

    // Fix for expensesTransactionChart method
    private static function expensesTransactionChart($expenses)
    {
        try {
            $groupedByDate = [];
            $dates = []; // Fixed: Changed from $date to $dates
            $categories = ['Cost of Sales', 'Operating Expenses'];
            
            foreach ($expenses as $expense) {
                if (!is_object($expense)) {
                    continue;
                }
                
                $date = Carbon::parse($expense->date ?? $expense->created_at ?? now())->format('M d');
                $category = (isset($expense->exp_type) && $expense->exp_type == 1) ? 'Cost of Sales' : 'Operating Expenses';
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'Cost of Sales' => 0,
                        'Operating Expenses' => 0
                    ];
                }
                
                // Fix: Cast amount to float before addition
                $amount = floatval($expense->amount ?? 0);
                $groupedByDate[$date][$category] += $amount;
            }
            
            if (empty($dates)) {
                \Log::info('No expense data found for chart');
                return null;
            }
            
            sort($dates);
            
            // Prepare datasets
            $datasets = [
                [
                    'label' => 'Cost of Sales',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['Cost of Sales'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Operating Expenses',
                    'data' => array_map(function($date) use ($groupedByDate) {
                        return $groupedByDate[$date]['Operating Expenses'] ?? 0;
                    }, $dates),
                    'borderColor' => 'rgb(234, 179, 8)',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.5)',
                    'borderWidth' => 2,
                ]
            ];

            return [
                'reportType' => 'expenses_transaction',
                'title' => 'Daily Expenses by Category',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in expensesTransactionChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate expenses summary chart
     */
    private static function expensesSummaryChart($expenses)
    {
        try {
            $groupedByDate = [];
            $dates = [];
            $categories = [];

            foreach ($expenses as $expense) {
                if (!is_object($expense)) {
                    continue;
                }
                
                $date = Carbon::parse($expense->date ?? $expense->created_at ?? now())->format('M d');
                $category = (isset($expense->exp_type) && $expense->exp_type == 1) ? 'Cost of Sales' : 'Operating Expenses';
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!in_array($category, $categories)) {
                    $categories[] = $category;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                }
                
                if (!isset($groupedByDate[$date][$category])) {
                    $groupedByDate[$date][$category] = 0;
                }
                
                // Fix: Cast amount to float before addition
                $amount = floatval($expense->amount ?? 0);
                $groupedByDate[$date][$category] += $amount;
            }
            
            if (empty($dates)) {
                \Log::info('No expense data found for summary chart');
                return null;
            }
            
            sort($dates);
            $categories = array_unique($categories);
            
            // Prepare datasets for each category
            $datasets = [];
            $categoryColors = [
                'Cost of Sales' => ['border' => 'rgba(239, 68, 68, 1)', 'bg' => 'rgba(239, 68, 68, 0.6)'],
                'Operating Expenses' => ['border' => 'rgba(234, 179, 8, 1)', 'bg' => 'rgba(234, 179, 8, 0.6)'],
            ];
            
            foreach ($categories as $category) {
                $categoryData = [];
                foreach ($dates as $date) {
                    $categoryData[] = $groupedByDate[$date][$category] ?? 0;
                }
                
                $color = $categoryColors[$category] ?? ['border' => 'rgba(201, 203, 207, 1)', 'bg' => 'rgba(201, 203, 207, 0.6)'];
                
                $datasets[] = [
                    'label' => $category,
                    'data' => $categoryData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 2,
                ];
            }

            return [
                'reportType' => 'expenses_summary',
                'title' => 'Expenses Summary by Category',
                'labels' => $dates,
                'datasets' => $datasets
            ];
        } catch (\Exception $e) {
            \Log::error('Error in expensesSummaryChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate menu chart based on table data
     */
    private static function generateMenuChart($menuItems, $report)
    {
        if ($report == 'rank') {
            return self::menuRankingChart($menuItems);
        } else if ($report == 'stock') {
            return self::menuStockChart($menuItems);
        }
        
        return null;
    }

    /**
     * Generate menu ranking chart
     */
    public static function menuRankingChart($results)
    {
        try {
            $labels = [];
            $quantityData = [];
            $revenueData = [];

            // Sort the results by quantity sold (descending) and get top 10
            $topItems = $results->sortByDesc('quantity')->take(10);

            foreach ($topItems as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $brand = trim($item->brand_name ?? '');
                $menuLabel = ($brand !== '' ? $brand . ' - ' : '') . ($item->name ?? 'Unknown');
                $labels[] = substr($menuLabel, 0, 15) . (strlen($menuLabel) > 15 ? '...' : '');
                $quantityData[] = $item->quantity ?? 0;
                $revenueData[] = ($item->total ?? 0);
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'bar',
                'title' => 'Top 10 Menu Items by Quantity Sold',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Quantity Sold: ',
                        'data' => $quantityData,
                        'yAxisID' => 'y1',
                        'borderColor' => 'rgb(235, 68, 56)',
                        'backgroundColor' => 'rgba(184, 18, 6, 0.7)',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => 'Revenue: ₱',
                        'data' => $revenueData,
                        'yAxisID' => 'y',
                        'borderColor' => 'rgb(82, 211, 7)',
                        'backgroundColor' => 'rgba(52, 173, 4, 0.7)',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in menuRankingChart: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Generate menu stock chart
     */
    private static function menuStockChart($menuItems)
    {
        try {
            $labels = [];
            $stockData = [];

            // Get top 20 items by stock
            $topItems = $menuItems->sortByDesc('quantity')->take(20);

            foreach ($topItems as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $labels[] = substr($item->name ?? 'Unknown', 0, 12) . (strlen($item->name ?? '') > 12 ? '...' : '');
                $stockData[] = $item->quantity ?? 0;
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'bar',
                'title' => 'Menu Stock Levels',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Current Stock',
                        'data' => $stockData,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in menuStockChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate inventory chart based on table data
     */
    private static function generateInventoryChart($inventory, $report)
    {
        \Log::info('generateInventoryChart called', [
            'report' => $report,
            'data_count' => is_countable($inventory) ? count($inventory) : 'N/A',
            'data_type' => gettype($inventory)
        ]);
        
        if ($report == 'logs') {
            $result = self::inventoryLogsChart($inventory);
            \Log::info('inventoryLogsChart returned', [
                'is_null' => is_null($result),
                'is_array' => is_array($result),
                'count' => is_array($result) ? count($result) : 0
            ]);
            return $result;
        } else if ($report == 'stock') {
            $result = self::inventoryStockChart($inventory);
            \Log::info('inventoryStockChart returned', [
                'is_null' => is_null($result),
                'is_array' => is_array($result)
            ]);
            return $result;
        }
        
        \Log::warning('No matching report type', ['report' => $report]);
        return null;
    }

    /**
     * Generate inventory stock chart
     */
    private static function inventoryStockChart($inventory)
    {
        try {
            $labels = [];
            $quantityData = [];

            // Get top 20 items by quantity
            $topItems = $inventory->sortByDesc('quantity')->take(20);

            foreach ($topItems as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $labels[] = substr($item->name ?? 'Unknown', 0, 12) . (strlen($item->name ?? '') > 12 ? '...' : '');
                $quantityData[] = $item->quantity ?? 0;
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'bar',
                'title' => 'Raw Materials Stock Levels',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Quantity',
                        'data' => $quantityData,
                        'borderColor' => 'rgb(139, 92, 246)',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.7)',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in inventoryStockChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate inventory logs chart
     */
    private static function inventoryLogsChart($inventoryLogs)
    {
        try {
            \Log::info('Starting inventoryLogsChart', [
                'total_logs' => count($inventoryLogs),
                'is_collection' => $inventoryLogs instanceof \Illuminate\Support\Collection,
                'has_data' => !empty($inventoryLogs)
            ]);
            
            // Ensure we're working with a collection
            if (!$inventoryLogs instanceof \Illuminate\Support\Collection) {
                $inventoryLogs = collect($inventoryLogs);
            }
            
            if ($inventoryLogs->isEmpty()) {
                \Log::warning('No inventory logs provided');
                return null;
            }
            
            // Group logs by type (IN/OUT) and date
            $groupedByDate = [];
            $dates = [];
            $logTypes = ['IN', 'OUT'];
            
            $processedCount = 0;
            foreach ($inventoryLogs as $log) {
                $processedCount++;
                if (!is_object($log)) {
                    \Log::warning('Log is not an object', ['index' => $processedCount]);
                    continue;
                }
                
                // Get date from log
                $dateField = $log->created_at ?? $log->date ?? null;
                if (!$dateField) {
                    \Log::warning('No date field found', ['log_id' => $log->id ?? 'unknown']);
                    continue;
                }
                
                try {
                    $date = \Carbon\Carbon::parse($dateField)->format('M d, Y');
                } catch (\Exception $e) {
                    \Log::warning('Invalid date format', ['date_field' => $dateField, 'error' => $e->getMessage()]);
                    $date = \Carbon\Carbon::now()->format('M d, Y');
                }
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                // Get quantities
                $quantityBefore = floatval($log->quantity_before ?? 0);
                $quantityAfter = floatval($log->quantity_after ?? 0);
                $quantity = floatval($log->quantity ?? 0);
                
                // Determine log type - FIXED LOGIC
                // Based on your logs, when type is "OUTGOING" but quantity_after > quantity_before, it's actually IN
                $logType = 'OUT'; // default
                
                // Check database type field first
                $dbType = strtoupper(trim($log->type ?? ''));
                if ($dbType == 'INCOMING' || $dbType == 'IN') {
                    $logType = 'IN';
                } elseif ($dbType == 'OUTGOING' || $dbType == 'OUT') {
                    $logType = 'OUT';
                } else {
                    // If type not clear, determine by quantity change
                    if ($quantityAfter > $quantityBefore) {
                        $logType = 'IN';
                    } elseif ($quantityAfter < $quantityBefore) {
                        $logType = 'OUT';
                    }
                }
                
                // Check remarks as additional clue
                if ($logType == 'OUT') {
                    $remarks = strtoupper($log->remarks ?? '');
                    if (str_contains($remarks, 'ADD') || str_contains($remarks, 'RECEIVED') || str_contains($remarks, 'IN')) {
                        $logType = 'IN';
                    }
                }
                
                // Initialize date entry if not exists
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [
                        'IN' => 0,
                        'OUT' => 0,
                        'net' => 0,
                        'item_count' => 0,
                        'transactions' => 0
                    ];
                }
                
                // Calculate actual change (absolute value)
                $quantityChange = abs($quantityAfter - $quantityBefore);
                
                // Special case: if quantity field has value, use it
                if ($quantity != 0 && $quantityChange == 0) {
                    $quantityChange = abs($quantity);
                    // Determine direction from quantity sign or type
                    if ($dbType == 'OUTGOING' || $quantity < 0) {
                        $logType = 'OUT';
                    } elseif ($dbType == 'INCOMING' || $quantity > 0) {
                        $logType = 'IN';
                    }
                }
                
                \Log::debug('Calculating change', [
                    'date' => $date,
                    'log_id' => $log->id ?? 'unknown',
                    'db_type' => $dbType,
                    'final_type' => $logType,
                    'before' => $quantityBefore,
                    'after' => $quantityAfter,
                    'quantity_field' => $quantity,
                    'change' => $quantityChange
                ]);
                
                // Add to appropriate category
                if ($logType == 'IN') {
                    $groupedByDate[$date]['IN'] += $quantityChange;
                    $groupedByDate[$date]['net'] += $quantityChange;
                } else {
                    $groupedByDate[$date]['OUT'] += $quantityChange;
                    $groupedByDate[$date]['net'] -= $quantityChange;
                }
                
                $groupedByDate[$date]['item_count']++;
                $groupedByDate[$date]['transactions']++;
            }
            
            \Log::info('Processed inventory logs', [
                'total' => $inventoryLogs->count(),
                'processed' => $processedCount,
                'unique_dates' => count($dates),
                'grouped_data' => $groupedByDate
            ]);
            
            if (empty($dates)) {
                \Log::warning('No dates found for inventory logs chart');
                // Return minimal chart data for debugging
                return [
                    [
                        'reportType' => 'bar',
                        'title' => 'Inventory Movement (DEBUG)',
                        'subtitle' => 'No data available',
                        'labels' => ['No Data'],
                        'datasets' => [
                            [
                                'label' => 'IN',
                                'data' => [0],
                                'borderColor' => 'rgb(34, 197, 94)',
                                'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                                'borderWidth' => 1,
                            ],
                            [
                                'label' => 'OUT',
                                'data' => [0],
                                'borderColor' => 'rgb(239, 68, 68)',
                                'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                                'borderWidth' => 1,
                            ]
                        ]
                    ]
                ];
            }
            
            // Sort dates chronologically
            usort($dates, function($a, $b) {
                return \Carbon\Carbon::parse($a)->timestamp <=> \Carbon\Carbon::parse($b)->timestamp;
            });
            
            \Log::info('Sorted dates', ['dates' => $dates, 'count' => count($dates)]);
            
            // Prepare datasets
            $inData = [];
            $outData = [];
            $netData = [];
            $transactionData = [];
            
            foreach ($dates as $date) {
                $inData[] = $groupedByDate[$date]['IN'] ?? 0;
                $outData[] = $groupedByDate[$date]['OUT'] ?? 0;
                $netData[] = $groupedByDate[$date]['net'] ?? 0;
                $transactionData[] = $groupedByDate[$date]['transactions'] ?? 0;
            }
            
            \Log::info('Chart data prepared', [
                'in_data' => $inData,
                'out_data' => $outData,
                'net_data' => $netData,
                'transaction_data' => $transactionData
            ]);
            
            // Check if we have any non-zero data
            $totalIn = array_sum($inData);
            $totalOut = array_sum($outData);
            
            \Log::info('Data totals', [
                'total_in' => $totalIn,
                'total_out' => $totalOut
            ]);
            
            // Create charts array
            $charts = [];
            
            // Chart 1: IN vs OUT
            $charts[] = [
                'reportType' => 'bar',
                'title' => 'Raw Materials Movement (IN vs OUT)',
                'subtitle' => 'Daily inventory additions and withdrawals',
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Stock IN',
                        'data' => $inData,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => 'Stock OUT',
                        'data' => $outData,
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                        'borderWidth' => 1,
                    ]
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'title' => [
                                'display' => true,
                                'text' => 'Quantity'
                            ]
                        ],
                        'x' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Date'
                            ]
                        ]
                    ]
                ]
            ];
            
            // Chart 2: Net Movement
            if (count($dates) > 1) {
                $charts[] = [
                    'reportType' => 'line',
                    'title' => 'Net Inventory Movement',
                    'subtitle' => 'Daily net change in inventory (IN - OUT)',
                    'labels' => $dates,
                    'datasets' => [
                        [
                            'label' => 'Net Change',
                            'data' => $netData,
                            'borderColor' => 'rgb(59, 130, 246)',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'borderWidth' => 3,
                            'fill' => true,
                            'tension' => 0.4
                        ]
                    ],
                    'options' => [
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                        'scales' => [
                            'y' => [
                                'title' => [
                                    'display' => true,
                                    'text' => 'Net Quantity'
                                ]
                            ]
                        ]
                    ]
                ];
            }
            
            // Chart 3: Pie Chart (only if we have data)
            if ($totalIn > 0 || $totalOut > 0) {
                $charts[] = [
                    'reportType' => 'pie',
                    'title' => 'Inventory Movement Distribution',
                    'subtitle' => 'Total IN vs OUT quantities',
                    'labels' => ['Stock IN', 'Stock OUT'],
                    'datasets' => [
                        [
                            'label' => 'Total Quantity',
                            'data' => [
                                $totalIn,
                                $totalOut
                            ],
                            'backgroundColor' => [
                                'rgba(34, 197, 94, 0.7)',
                                'rgba(239, 68, 68, 0.7)'
                            ],
                            'borderColor' => [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)'
                            ],
                            'borderWidth' => 1,
                        ]
                    ],
                    'options' => [
                        'responsive' => true,
                        'maintainAspectRatio' => false,
                    ]
                ];
            }
            
            // Chart 4: Transactions
            $charts[] = [
                'reportType' => 'bar',
                'title' => 'Daily Inventory Transactions',
                'subtitle' => 'Number of inventory log entries per day',
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Number of Transactions',
                        'data' => $transactionData,
                        'borderColor' => 'rgb(139, 92, 246)',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.7)',
                        'borderWidth' => 1,
                    ]
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'title' => [
                                'display' => true,
                                'text' => 'Transaction Count'
                            ]
                        ]
                    ]
                ]
            ];
            
            \Log::info('Returning chart data', [
                'chart_count' => count($charts),
                'has_data' => !empty($charts),
                'first_chart' => $charts[0]['title'] ?? 'No chart'
            ]);
            
            return $charts;
            
        } catch (\Exception $e) {
            \Log::error('Error in inventoryLogsChart: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Return error chart for debugging
            return [
                [
                    'reportType' => 'bar',
                    'title' => 'Error Loading Chart',
                    'subtitle' => $e->getMessage(),
                    'labels' => ['Error'],
                    'datasets' => [
                        [
                            'label' => 'Error',
                            'data' => [1],
                            'borderColor' => 'rgb(239, 68, 68)',
                            'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                            'borderWidth' => 1,
                        ]
                    ]
                ]
            ];
        }
    }

    /**
     * Analyze inventory items for chart
     */
    private static function analyzeInventoryItems($inventoryLogs)
    {
        try {
            $itemStats = [];
            $categoryStats = [];
            
            foreach ($inventoryLogs as $log) {
                if (!is_object($log)) {
                    continue;
                }
                
                // Get item name
                $itemName = $log->inv->name ?? 'Unknown';
                
                // Get category if available
                $categoryName = $log->inv->cn ?? 'Uncategorized';
                
                // Calculate quantity change
                $quantityBefore = floatval($log->quantity_before ?? 0);
                $quantityAfter = floatval($log->quantity_after ?? 0);
                $quantityChange = abs($quantityAfter - $quantityBefore);
                
                // Determine if IN or OUT
                $logType = ($quantityAfter > $quantityBefore) ? 'IN' : 'OUT';
                
                // Track item stats
                if (!isset($itemStats[$itemName])) {
                    $itemStats[$itemName] = [
                        'total_in' => 0,
                        'total_out' => 0,
                        'transactions' => 0,
                        'category' => $categoryName
                    ];
                }
                
                if ($logType == 'IN') {
                    $itemStats[$itemName]['total_in'] += $quantityChange;
                } else {
                    $itemStats[$itemName]['total_out'] += $quantityChange;
                }
                $itemStats[$itemName]['transactions']++;
                
                // Track category stats
                if (!isset($categoryStats[$categoryName])) {
                    $categoryStats[$categoryName] = [
                        'total_in' => 0,
                        'total_out' => 0,
                        'items' => 0,
                        'transactions' => 0
                    ];
                }
                
                if ($logType == 'IN') {
                    $categoryStats[$categoryName]['total_in'] += $quantityChange;
                } else {
                    $categoryStats[$categoryName]['total_out'] += $quantityChange;
                }
                $categoryStats[$categoryName]['transactions']++;
            }
            
            return [
                'items' => $itemStats,
                'categories' => $categoryStats
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in analyzeInventoryItems: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate waste chart based on table data
     */
    private static function generateWasteChart($wasteItems, $report)
    {
        if ($report == 'raw') {
            $title = 'Raw Materials Waste';
        } else {
            $title = 'Menu Waste';
        }
        
        return self::wasteCostChart($wasteItems, $title);
    }

    /**
     * Generate waste cost chart
     */
    private static function wasteCostChart($wasteItems, $title)
    {
        try {
            $groupedByDate = [];
            $dates = [];
            
            foreach ($wasteItems as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $date = Carbon::parse($item->created_at ?? $item->date)->format('M d');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = 0;
                }
                
                $groupedByDate[$date] += $item->cost ?? 0;
            }
            
            if (empty($dates)) {
                return null;
            }
            
            sort($dates);
            
            return [
                'reportType' => 'line',
                'title' => $title,
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Waste Cost',
                        'data' => array_map(function($date) use ($groupedByDate) {
                            return $groupedByDate[$date] ?? 0;
                        }, $dates),
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 3,
                        'fill' => true,
                        'tension' => 0.1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in wasteCostChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate remittance chart
     */
    private static function generateRemittanceChart($remittances)
    {
        try {
            $labels = [];
            $data = [];
            
            foreach ($remittances as $remit) {
                if (!is_object($remit)) {
                    continue;
                }
                
                if (isset($remit->name) && $remit->name !== 'Total') {
                    $labels[] = strtoupper($remit->name);
                    $data[] = $remit->amount ?? 0;
                }
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'doughnut',
                'title' => 'Remittance by Payment Method',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Amount',
                        'data' => $data,
                        'backgroundColor' => [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                        ],
                        'borderColor' => [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 159, 64, 1)',
                        ],
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in generateRemittanceChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate sales & expenses comparison chart
     */
    private static function generateSalesExpensesChart($data)
    {
        try {
            // Assuming $data contains both sales and expenses grouped by date
            $dates = [];
            $salesData = [];
            $expensesData = [];
            
            foreach ($data as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $date = Carbon::parse($item->date ?? $item->created_at)->format('M d');
                
                if (!in_array($date, $dates)) {
                    $dates[] = $date;
                }
                
                // This would need to be adapted based on your actual data structure
                // For now, using placeholder logic
                if (isset($item->total)) {
                    $salesData[$date] = ($salesData[$date] ?? 0) + ($item->total ?? 0);
                }
                if (isset($item->amount) && !isset($item->total)) {
                    $expensesData[$date] = ($expensesData[$date] ?? 0) + ($item->amount ?? 0);
                }
            }
            
            if (empty($dates)) {
                return null;
            }
            
            sort($dates);
            
            $salesDataset = array_map(function($date) use ($salesData) {
                return $salesData[$date] ?? 0;
            }, $dates);
            
            $expensesDataset = array_map(function($date) use ($expensesData) {
                return $expensesData[$date] ?? 0;
            }, $dates);

            return [
                'reportType' => 'line',
                'title' => 'Sales vs Expenses Comparison',
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Sales',
                        'data' => $salesDataset,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'borderWidth' => 3,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Expenses',
                        'data' => $expensesDataset,
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderWidth' => 3,
                        'fill' => true,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in generateSalesExpensesChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate invmenu chart
     */
    private static function generateInvMenuChart($items, $report)
    {
        if ($report == 'summary') {
            return self::invMenuSummaryChart($items);
        }
        
        return null;
    }

    /**
     * Generate inventory and menu summary chart
     */
    private static function invMenuSummaryChart($items)
    {
        try {
            $labels = [];
            $stockData = [];
            $typeData = [];

            // Separate menu and inventory items
            $menuItems = [];
            $inventoryItems = [];
            
            foreach ($items as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                if (isset($item->type) && $item->type == 'Menu') {
                    $menuItems[] = $item;
                } else {
                    $inventoryItems[] = $item;
                }
            }
            
            // Get top 10 of each
            $topMenu = array_slice($menuItems, 0, 10);
            $topInventory = array_slice($inventoryItems, 0, 10);
            
            foreach ($topMenu as $item) {
                $labels[] = 'M: ' . substr($item->name ?? 'Unknown', 0, 10);
                $stockData[] = $item->quantity ?? 0;
                $typeData[] = 'Menu';
            }
            
            foreach ($topInventory as $item) {
                $labels[] = 'I: ' . substr($item->name ?? 'Unknown', 0, 10);
                $stockData[] = $item->quantity ?? 0;
                $typeData[] = 'Inventory';
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'bar',
                'title' => 'Top Inventory & Menu Items',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Stock Quantity',
                        'data' => $stockData,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in invMenuSummaryChart: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate sales by category chart (for beverages/food reports)
     */
    private static function salesByCategoryChart($items, $report)
    {
        try {
            $labels = [];
            $quantityData = [];
            $revenueData = [];
            
            $categoryName = $report == 'beverages' ? 'Beverages' : 'Food';
            
            foreach ($items as $item) {
                if (!is_object($item)) {
                    continue;
                }
                
                $labels[] = substr($item->name ?? 'Unknown', 0, 12) . (strlen($item->name ?? '') > 12 ? '...' : '');
                $quantityData[] = $item->quantity ?? 0;
                $revenueData[] = ($item->quantity ?? 0) * ($item->price ?? 0);
            }
            
            if (empty($labels)) {
                return null;
            }

            return [
                'reportType' => 'bar',
                'title' => $categoryName . ' Sales Report',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Quantity Sold',
                        'data' => $quantityData,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.7)',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => 'Revenue (₱)',
                        'data' => $revenueData,
                        'borderColor' => 'rgb(255, 99, 132)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.7)',
                        'borderWidth' => 1,
                    ]
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in salesByCategoryChart: ' . $e->getMessage());
            return null;
        }
    }
}