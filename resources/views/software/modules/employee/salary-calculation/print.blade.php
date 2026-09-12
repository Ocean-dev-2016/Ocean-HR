<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Calculation Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        tr:hover {
            background-color: #e8ecf3;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            font-weight: 600;
        }
        .earning {
            color: #28a745;
        }
        .deduction {
            color: #dc3545;
        }
        .net-pay {
            color: #17a2b8;
            font-weight: 700;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .summary-row {
            background-color: #f0f0f0 !important;
            font-weight: 600;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
        .btn-print {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .btn-print:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print text-center" style="margin-bottom: 20px;">
            <button onclick="window.print()" class="btn-print">
                <i class="fa fa-print"></i> Print Report
            </button>
        </div>

        <div class="header">
            <h1>Salary Calculation Report</h1>
            <p>Generated on: {{ date('d M Y, h:i A') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Company</th>
                    <th>Branch</th>
                    <th>Period</th>
                    <th class="text-right">CTC</th>
                    <th class="text-right">Present Days</th>
                    <th class="text-right">Total Earning</th>
                    <th class="text-right">Total Deduction</th>
                    <th class="text-right">Net Pay</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalEarning = 0;
                    $totalDeduction = 0;
                    $totalNetPay = 0;
                    $months = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                    ];
                @endphp
                @forelse($salaries as $index => $salary)
                    @php
                        $totalEarning += $salary->total_earning ?? 0;
                        $totalDeduction += $salary->total_deduction ?? 0;
                        $totalNetPay += $salary->net_bank_pay ?? 0;
                        $monthName = $months[$salary->month] ?? $salary->month;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $salary->employee?->employee_code ?? '-' }}</td>
                        <td>{{ $salary->employee?->full_name ?? '-' }}</td>
                        <td>{{ $salary->company?->company_name ?? '-' }}</td>
                        <td>{{ $salary->branch?->branch_name ?? '-' }}</td>
                        <td class="text-center">{{ $monthName }} {{ $salary->year }}</td>
                        <td class="text-right amount">₹ {{ number_format($salary->ctc ?? 0, 2) }}</td>
                        <td class="text-center">{{ $salary->total_present_day ?? 0 }}</td>
                        <td class="text-right amount earning">₹ {{ number_format($salary->total_earning ?? 0, 2) }}</td>
                        <td class="text-right amount deduction">₹ {{ number_format($salary->total_deduction ?? 0, 2) }}</td>
                        <td class="text-right amount net-pay">₹ {{ number_format($salary->net_bank_pay ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No salary records found</td>
                    </tr>
                @endforelse
                @if(count($salaries) > 0)
                    <tr class="summary-row">
                        <td colspan="8" class="text-right"><strong>TOTAL:</strong></td>
                        <td class="text-right amount earning">₹ {{ number_format($totalEarning, 2) }}</td>
                        <td class="text-right amount deduction">₹ {{ number_format($totalDeduction, 2) }}</td>
                        <td class="text-right amount net-pay">₹ {{ number_format($totalNetPay, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p>This is a computer-generated report. No signature required.</p>
            <p>OceanHR - Human Resource Management System</p>
        </div>
    </div>
</body>
</html>

