<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Expense SubCategory List - Print</title>
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
                <th colspan="7" class="text-center">
                    <h3>Expense SubCategory List - Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y H:i A') }}
                    </h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!$company_id)
                    <th>Company Name</th>
                @endif
                <th>Name</th>
                <th>Expense Category</th>
                <th>Expense Type</th>
                <th>Expense Details</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ExpenseSubCategory as $index => $subcategory)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if (!$company_id)
                        <td>{{ $subcategory->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $subcategory->name }}</td>
                    <td>{{ $subcategory->expense_category->name ?? '-' }}</td>
                    <td>{{ $subcategory->expense_type }}</td>
                    <td>
                        @if ($subcategory->expense_type == 'General')
                            Min: {{ $subcategory->min_amount ?? '-' }}, Max: {{ $subcategory->max_amount ?? '-' }}
                        @elseif ($subcategory->expense_type == 'KM')
                            Per KM: {{ $subcategory->per_km_rate ?? '-' }}
                        @elseif ($subcategory->expense_type == 'Food')
                            Fix: {{ $subcategory->fix_amount ?? '-' }}, From: {{ $subcategory->from_time ?? '-' }}, To: {{ $subcategory->to_time ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ucfirst($subcategory->status) }}</td>
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
