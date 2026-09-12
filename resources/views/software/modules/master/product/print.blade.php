<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Product List - Print</title>
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

        h3 {
            text-align: center;
            margin: 10px 0;
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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <table>
        <thead>
            <tr>
                <th colspan="4" class="text-center">
                    <h3>Product List - Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}</h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!isset($modules['company_id']) || !$modules['company_id'])
                    <th>Company Name</th>
                @endif
                <th>Product Name</th>
                <th>Rate</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @if (!isset($modules['company_id']) || !$modules['company_id'])
                        <td>{{ $product->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $product->name }}</td>
                    <td class="text-center">{{ number_format($product->rate, 2) }}</td>
                    <td>{{ ucfirst($product->status) }}</td>
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
