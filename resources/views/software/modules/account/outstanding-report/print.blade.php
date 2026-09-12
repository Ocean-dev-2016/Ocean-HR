<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Outstanding Report - Print</title>
    <style>
        @media print {
            @page { size: A4; margin: 5mm; }
        }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 5px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 8px; border: 1px solid #ddd; }
        th.text-center { text-align: center; }
        td { padding: 8px; border: 1px solid #ddd; }
    </style>
    </head>
<body onload="printAndRedirect();">
    <table>
        <thead>
            <tr>
                <th colspan="4" class="text-center">
                    <h3>Outstanding Report - Printed on
                        {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                    </h3>
                    <div>Period: {{ $startDate->format('d-m-Y') }} to {{ $endDate->format('d-m-Y') }}</div>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                <th>Employee</th>
                <th>Phone</th>
                <th>Closing Balance (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['mobile_number'] }}</td>
                    <td class="text-end">{{ number_format($row['closing_balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No records found</td>
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
