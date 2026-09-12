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
                <th colspan="8" class="text-center">
                    <h3>{{ $modules['title'] }} - List Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}</h3>
                </th>
            </tr>
            <tr>
                <th>Sr No</th>
                @if (!$modules['company_id']) <th>Company Name</th> @endif
                <th>Employee Name</th>
                <th>Designation Type</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Date of Joining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employeeAssets as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if (!$modules['company_id']) <td>{{ $item->company->company_name ?? '-' }}</td> @endif
                    <td>{{ $item->employee->full_name ?? '-' }}</td>
                    <td>{{ ucfirst($item->designation_type) }}</td>
                    <td>{{ $item->designation->name ?? '-' }}</td>
                    <td>{{ $item->department->name ?? '-' }}</td>
                    <td>{{ $item->date_of_joining ? \Carbon\Carbon::parse($item->date_of_joining)->format('d/m/Y') : '-' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ !$modules['company_id'] ? 8 : 7 }}" class="text-center">No records found</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
