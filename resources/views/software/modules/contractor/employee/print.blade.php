<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $modules['title'] ?? 'Contractor Employee List' }} - Print</title>
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
    @php
        $employeeList = $employee ?? $employees ?? [];
    @endphp
    <table>
        <thead>
            <tr>
                <th colspan="25" class="text-center">
                    <h3>{{ $modules['title'] ?? 'Contractor Employee List' }} - List Printed on
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
                <th>Full Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Username</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employeeList as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if (!$company_id)
                        <td>{{ $item->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $item->employee_code ?? '-' }}</td>
                    <td>{{ $item->full_name ?? '-' }}</td>
                    <td>{{ $item->contact_number ?? '-' }}</td>
                    <td>{{ $item->email ?? '-' }}</td>
                    <td>{{ $item->username ?? '-' }}</td>
                    <td>{{ ucfirst($item->status ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ !$company_id ? 8 : 7 }}" class="text-center">No records found</td>
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
