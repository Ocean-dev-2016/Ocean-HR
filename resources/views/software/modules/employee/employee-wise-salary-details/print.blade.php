<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Employee Wise Salary Details - Print</title>
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
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 8px;
            border: 1px solid #ddd;
        }

        th.text-center {
            text-align: center;
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
                <th colspan="25" class="text-center">
                    <h3>Employee Wise Salary Details - List Printed on
                        {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                    </h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!$company_id)
                    <th>Company Name</th>
                @endif
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Salary Classification</th>
                <th>PF Type</th>
                <th>Salary Calculation Count Month</th>
                <th>CTC</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employeeSalaryDetails as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if (!$company_id)
                        <td>{{ $item->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $item->employee->employee_code ?? '-' }}</td>
                    <td>{{ $item->employee->full_name ?? '-' }}</td>
                    <td>{{ $item->salary_classification ?? '-' }}</td>
                    <td>{{ $item->pf_type ?? '-' }}</td>
                    <td>{{ $item->salary_calculation_month_count ?? '-' }}</td>
                    <td>{{ $item->ctc ?? 0 }}</td>
                    <td>{{ ucfirst($item->status ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ !$company_id ? 9 : 8 }}" class="text-center">No records found</td>
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
