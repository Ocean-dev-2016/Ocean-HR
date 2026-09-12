<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $modules['title'] }} - Print</title>
    <style>
        @media print {
            @page { size: A4; margin: 5mm; }
        }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 5px; color: #333; }
        h1 { text-align: center; font-size: 24px; font-weight: bold; text-transform: uppercase; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .text-center { text-align: center; }
    </style>
</head>
<body onload="window.print();">
    <table>
        <thead>
            <tr>
                <th colspan="6" class="text-center">
                    <h3>{{ $modules['title'] }} - List Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}</h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                <th>Employee Code</th>
                <th>Full Name</th>
                <th>Contact Number</th>
                <th>Username</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->employee_code }}</td>
                    <td>{{ $item->full_name }}</td>
                    <td>{{ $item->contact_number }}</td>
                    <td>{{ $item->username }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No records found</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
