<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Operations Rate List - Print</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 5px;
            color: #333;
        }

        h3 {
            text-align: center;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 5px;
            border: 1px solid #ddd;
            background-color: #f2f2f2;
        }

        td {
            padding: 5px;
            border: 1px solid #ddd;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <table>
        <thead>
            <tr>
                <th colspan="{{ (!isset($modules['company_id']) || !$modules['company_id']) ? '8' : '7' }}" class="text-center">
                    <h3>Operations Rate List - Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}</h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!isset($modules['company_id']) || !$modules['company_id'])
                    <th>Company Name</th>
                @endif
                <th>Operation</th>
                <th>Name</th>
                <th>Month</th>
                <th>Year</th>
                <th>Total Qty</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @if (!isset($modules['company_id']) || !$modules['company_id'])
                        <td>{{ $row->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $row->operation }}</td>
                    <td>
                        @php
                            $names = $row->multi_names ? explode(', ', $row->multi_names) : [];
                            $displayName = (count($names) > 1) ? 'ALL EMPLOYEE' : ($row->multi_names ?: '-');
                        @endphp
                        {{ $displayName }}
                    </td>
                    <td>{{ date('F', mktime(0, 0, 0, $row->month, 1)) }}</td>
                    <td>{{ $row->year }}</td>
                    <td class="text-center">{{ $row->total_qty }}</td>
                    <td class="text-center">{{ $row->total_amount }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        function printAndRedirect() {
            window.print();
            window.onafterprint = function () {
                setTimeout(function () {
                    window.close();
                }, 1);
            };
        }
    </script>
</body>

</html>
