<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Report - {{ $date }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
        }
        .header h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 12px;
            color: #555;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 14px;
            font-size: 12px;
        }
        .summary span {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .summary .total   { background: #e8f0fe; color: #1a56db; }
        .summary .present { background: #def7ec; color: #057a55; }
        .summary .absent  { background: #fde8e8; color: #c81e1e; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        tr:nth-child(even) { background-color: #fafafa; }
        .badge-present { color: #057a55; font-weight: bold; }
        .badge-absent  { color: #c81e1e; font-weight: bold; }
        .text-muted    { color: #888; font-size: 10px; }
        tfoot td {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        @if(isset($company) && $company)
            <h2>{{ $company->company_name }}</h2>
        @endif
        <h2>Daily Attendance Report</h2>
        <p>Date: {{ $date }}</p>
    </div>

    <div class="summary">
        <span class="total">Total: {{ $summary['total'] }}</span>
        <span class="present">Present: {{ $summary['present'] }}</span>
        <span class="absent">Absent: {{ $summary['absent'] }}</span>
    </div>

    @if(count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>SR NO</th>
                    <th>Employee Name</th>
                    <th>Code</th>
                    <th>Shift</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Working Hrs</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['employee_name'] }}</td>
                        <td>{{ $row['employee_code'] }}</td>
                        <td>{{ $row['shift_name'] }}</td>
                        <td>{{ $row['designation'] }}</td>
                        <td>{{ $row['department'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['in_time'] }}</td>
                        <td>{{ $row['out_time'] }}</td>
                        <td>{{ $row['working_hours'] }}</td>
                        <td class="{{ $row['status'] === 'Present' ? 'badge-present' : 'badge-absent' }}">
                            {{ $row['status'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="10" style="text-align:right;">
                        Total: {{ $summary['total'] }} &nbsp;|&nbsp;
                        Present: {{ $summary['present'] }} &nbsp;|&nbsp;
                        Absent: {{ $summary['absent'] }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align:center; padding: 30px; color: #888;">No attendance records found for the selected filters.</p>
    @endif

    <script>window.print();</script>
</body>
</html>
