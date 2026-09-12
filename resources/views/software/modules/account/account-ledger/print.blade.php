<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ledger Print - {{ $employee?->full_name ?? 'All Employees' }}</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ✅ General layout */
        body {
            padding: 30px;
            font-size: 14px;
            background-color: #f8f9fa;
            color: #000;
        }

        .header-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .employee-box {
            background-color: #ffffff;
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #5d596c;
        }

        .table thead th {
            background-color: #5d596c !important;
            color: white !important;
            text-align: center;
        }

        .table td,
        .table th {
            vertical-align: middle !important;
        }

        .text-end {
            text-align: right !important;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        /* ✅ Footer totals with soft colors */
        .table-info {
            background-color: #cff4fc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-warning {
            background-color: #fff3cd !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ✅ Force print colors */
        @media print {
            body {
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .employee-box {
                box-shadow: none !important;
                border: 1px solid #000;
            }

            .table thead th {
                background-color: #dfdfe3 !important;
                color: #000 !important;
            }

            .table-info {
                background-color: #cff4fc !important;
            }

            .table-warning {
                background-color: #fff3cd !important;
            }

            a[href]:after {
                content: "";
            }
        }
    </style>
</head>

<body onload="printAndRedirect();">
<div class="report-container mx-auto p-3" style="width: 100%; border: 1px solid rgb(0, 0, 0); padding: 20px;">
    <div class="header-title text-center mb-4">
        <h3>Account Ledger Report</h3>
        <p class="text-muted">For {{ $employee?->full_name ?? 'All Employees' }}</p>
    </div>

    @if ($employee)
        <div class="employee-box mx-auto p-3 border rounded" style="max-width: 800px; background-color: #f8f9fa;">
            <h5 class="mb-3 text-center"><strong>Employee Details</strong></h5>
            <div class="d-flex justify-content-between mb-2">
                <div><strong>Code:</strong> {{ $employee->employee_code ?? '-' }}</div>
                <div><strong>Full Name:</strong> {{ $employee->full_name ?? '-' }}</div>
            </div>
            <div class="d-flex justify-content-between">
                <div><strong>Phone:</strong> {{ $employee->contact_number ?? ($employee->other_number ?? '-') }}</div>
                <div><strong>Address:</strong> {{ $employee->current_address ?? ($employee->permanent_address ?? '-') }}
                </div>
            </div>
        </div>
    @endif

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Date</th>
                <th>Description</th>
                <th class="text-end">Debit (₹)</th>
                <th class="text-end">Credit (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger as $index => $entry)
                @php
                    $mode = strtolower($entry['payment_type'] ?? '');
                    $extraInfo = match ($mode) {
                        'cheque' => 'Cheque No. (' . ($entry['cheque_no'] ?? '-') . ')',
                        'upi' => 'UPI No. (' . ($entry['upi_no'] ?? '-') . ')',
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        default => ucfirst($mode ?: '-'),
                    };
                    $description =
                        ($entry['employee_code'] ?? '-') .
                        ' - ' .
                        ($entry['full_name'] ?? '-') .
                        ' By ' .
                        $extraInfo .
                        ' with Receipt No - ' .
                        ($entry['receipt_no'] ?? '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $entry['date'] }}</td>
                    <td>{{ $description }}</td>
                    <td class="text-end text-danger">{{ number_format($entry['debit'], 2) }}</td>
                    <td class="text-end text-success">{{ number_format($entry['credit'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No ledger entries found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="table-info fw-bold">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end">{{ number_format($total_debit, 2) }}</td>
                <td class="text-end">{{ number_format($total_credit, 2) }}</td>
            </tr>
            <tr class="table-warning fw-bold">
                <td colspan="4" class="text-end">Net Closing Balance</td>
                <td class="text-end">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>



    <script>
        function printAndRedirect() {
            window.print();
            window.onafterprint = function() {
                setTimeout(function() {
                    window.location.href = "{{ route($modules['route'] . '.index') }}";
                }, 1000);
            };
        }
    </script>

</body>

</html>
