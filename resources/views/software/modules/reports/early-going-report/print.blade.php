<!DOCTYPE html>
<html>
<head>
    <title>Early Going Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .text-success { color: green; }
        .text-danger { color: red; }
        .text-warning { color: orange; }
        .text-muted { color: #6c757d; font-size: 10px; }
    </style>
</head>
<body>
    @if(!isset($is_excel))
    <div class="header">
        <h2>Early Going Report</h2>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Department</th>
                <th>Out Time</th>
                <th>Expected Out Time</th>
                <th>Early By</th>
                <th>Shift Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['employee_code'] }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td class="text-danger">{{ $row['out_time'] }}</td>
                    <td class="text-success">{{ $row['expected_out_time'] }}</td>
                    <td class="text-warning">{{ $row['early_by'] }}</td>
                    <td>
                        {{ $row['shift_name'] }}
                        @if(!isset($is_excel))
                            <br><small class="text-muted">{{ $row['shift_time'] }}</small>
                        @else
                             - {{ $row['shift_time'] }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if(!isset($is_excel))
    <script>
        window.print();
    </script>
    @endif
</body>
</html>
