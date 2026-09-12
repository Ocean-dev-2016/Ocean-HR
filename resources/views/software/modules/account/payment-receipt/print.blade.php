<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - Print</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 5px;
            color: #333;
        }

        h3 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <table>
        <thead>
            <tr>
                <th colspan="100%">
                    <h3 style="margin: 5px 0;">
                        Payment Receipt List - Printed on
                        {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                    </h3>
                </th>
            </tr>
            <tr>
                <th>Sr No.</th>
                <th>Company</th>
                <th>Branch / Department / Employee</th>
                <th>Effect Month & Year</th>
                <th>Date</th>
                <th>Payment Mode</th>
                <th>Amount</th>
                <th>Payment Type</th>
                <th>Receipt No.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receipt_list as $index => $receipt)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $receipt->company->company_name ?? '-' }}</td>
                    <td>
                        {{-- Prefer branch > department > employee --}}
                        @if (!empty($receipt->branch->branch_name))
                            {{ $receipt->branch->branch_name }}
                        @elseif (!empty($receipt->department->name))
                            {{ $receipt->department->name }}
                        @elseif (!empty($receipt->employee))
                            {{ $receipt->employee->full_name ?? '-' }}
                            @if (!empty($receipt->employee->employee_code))
                                ({{ $receipt->employee->employee_code }})
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $receipt->effect_on_month ?? '-' }}
                        {{ $receipt->effect_of_year ? ' / ' . $receipt->effect_of_year : '' }}
                    </td>
                    <td>{{ $receipt->date ? \Carbon\Carbon::parse($receipt->date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ ucfirst($receipt->payment_mode ?? '-') }}</td>
                    <td>{{ number_format($receipt->amount ?? 0, 2) }}</td>
                    <td>{{ ucfirst($receipt->payment_type ?? '-') }}</td>
                    <td>{{ $receipt->receipt_no ?? '-' }}</td>
                    <td>{{ ucfirst($receipt->status ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <script>
        function printAndRedirect() {
            window.print();

            window.onafterprint = function() {
                setTimeout(function() {
                    window.location.href = "{{ route($modules['route'] . '.index') }}";
                }, 1);
            };
        }
    </script>
</body>

</html>
