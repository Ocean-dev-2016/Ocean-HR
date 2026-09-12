<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Expense List - Print</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 5px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e5054;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f2f2f2;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <table>
        <thead>
            <tr>
                <th colspan="10" class="text-center">
                    <h3>Expense List - Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y H:i A') }}
                    </h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!$company_id)
                    <th>Company Name</th>
                @endif
                <th>Team Person</th>
                <th>Expense Category</th>
                <th>Expense SubCategory</th>
                <th>Date</th>
                <th>Request Amount</th>
                <th>Pass Amount</th>
                <th>Reject Amount</th>
                <th>Status</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $index => $expense)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if (!$company_id)
                        <td>{{ $expense->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $expense->employees->full_name ?? '-' }}</td>
                    <td>{{ $expense->expense_category->name ?? '-' }}</td>
                    <td>{{ $expense->expense_sub_category->name ?? '-' }}</td>
                    <td>{{ $expense->date ? $expense->date->format('d-m-Y') : '-' }}</td>
                    <td>{{ number_format($expense->req_amount, 2) }}</td>
                    <td>{{ $expense->pass_amount ? number_format($expense->pass_amount, 2) : '0.00' }}</td>
                    <td>
                        @if ($expense->status == 'reject')
                            {{ number_format($expense->req_amount, 2) }}
                        @elseif($expense->status == 'pass')
                            {{ number_format($expense->req_amount - $expense->pass_amount, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td>{{ ucfirst($expense->status) }}</td>
                    <td>{{ $expense->remark ?? '-' }}</td>
                </tr>
            @endforeach
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
