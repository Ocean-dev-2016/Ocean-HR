    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Attendance List - Print</title>
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

            h2 {
                text-align: center;
                font-size: 14px;
                margin-bottom: 30px;
                color: #7f8c8d;
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
                    <th colspan="19" class="text-center">
                        <h3>Attendance List - List Printed on
                            {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}</h3>
                    </th>
                </tr>
                <tr>
                    <th>Sr No</th>
                    @if (!$company_id)
                        <th>Company ID</th>
                    @endif
                    <th>Employee ID</th>
                    <th>Shift ID</th>
                    <th>Attendance Date</th>
                    <th>Create Date</th>
                    <th>Punch In/ Out Time</th>

                    <th>Attendance Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendance as $index => $attendances)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if (!$company_id)
                            <td>{{ $attendances->company->company_name ?? '-' }}</td>
                        @endif
                        <td>{{ $attendances->employee->employee_code ?? '-' }} -
                            {{ $attendances->employee->full_name ?? '-' }}
                        </td>
                        <td>{{ $attendances->shift->name ?? '-' }}</td>
                        <td>{{ $attendances->attendance_date ?? '-' }}</td>
                        <td>{{ $attendances->create_date ?? '-' }}</td>
                        <td>{{ $attendances->punch_in_time ?? '-' }}</td>

                        <td>{{ ucfirst($attendances->attendace_type ?? '-') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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
