<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonus List - {{ config('app.name', 'Ocean HR') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            background-color: #f8fafc;
            line-height: 1.4;
            padding: 20px;
        }

        .print-container {
            max-width: 1050px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Top Action Bar (Hidden in Print) */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* Report Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2px solid #2563eb;
        }

        .company-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .report-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-info {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }

        .meta-info strong {
            color: #334155;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #334155;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        tbody td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:hover {
            background-color: #f1f5f9;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 9.5px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-active {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-inactive {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .report-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #94a3b8;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 10px;
            }

            .print-container {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            thead th {
                background-color: #1e293b !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tbody tr:nth-child(even) {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Top Action Bar -->
        <div class="action-bar">
            <div>
                <a href="{{ route($modules['route'] . '.index') }}" class="btn btn-secondary">
                    ← Back to Bonus
                </a>
            </div>
            <div>
                <button onclick="window.print();" class="btn btn-primary">
                    🖨️ Print Document
                </button>
            </div>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <div>
                <h1 class="company-title">
                    @if(!empty($company_id) && isset($bonus[0]->company))
                        {{ $bonus[0]->company->company_name }}
                    @else
                        {{ config('app.name', 'Ocean HR') }}
                    @endif
                </h1>
                <div class="report-subtitle">Bonus List Report</div>
            </div>
            <div class="meta-info">
                <div>Printed On: <strong>{{ now()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</strong></div>
                <div>Total Records: <strong>{{ count($bonus) }}</strong></div>
            </div>
        </div>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">Sr No</th>
                    @if (!$company_id)
                        <th>Company Name</th>
                    @endif
                    <th>Branch</th>
                    <th>Employee Name</th>
                    <th class="text-center">Year</th>
                    <th class="text-center">Month</th>
                    <th class="text-end">Amount</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bonus as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        @if (!$company_id)
                            <td>{{ $item->company_name ?? $item->company->company_name ?? '-' }}</td>
                        @endif
                        <td>{{ $item->branch_name ?? '-' }}</td>
                        <td>
                            {{ $item->employeeRelation
                                ? ($item->employeeRelation->employee_code . ' - ' . $item->employeeRelation->full_name)
                                : '-' }}
                        </td>
                        <td class="text-center">{{ $item->year }}</td>
                        <td class="text-center">{{ $item->month }}</td>
                        <td class="text-end"><strong>₹{{ number_format((float)$item->amount, 2) }}</strong></td>
                        <td class="text-center">
                            @if(strtolower($item->status) === 'active')
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (!$company_id ? 8 : 7) }}" class="text-center" style="padding: 25px; color: #64748b;">
                            No bonus records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="report-footer">
            <div>{{ config('app.name', 'Ocean HRMS') }} — Confidential Report</div>
            <div>Generated automatically by Ocean HR System</div>
        </div>
    </div>
</body>
</html>
