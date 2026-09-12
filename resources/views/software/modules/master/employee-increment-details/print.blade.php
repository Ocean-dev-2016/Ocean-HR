    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Employee Increment Details List - Print</title>
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
                    <th colspan="20" class="text-center">
                        <h3>
                            Employee Increment Details List - List Printed on
                            {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                        </h3>
                    </th>
                </tr>
                <tr>
                    <th>Sr No</th>
                    @if (!$company_id)
                        <th>Company Name</th>
                    @endif

                    <th>Employee</th>
                    <th>Increment Date</th>
                    <th>Basic + DA</th>
                    <th>HRA</th>
                    <th>Conveyance Allowance</th>
                    <th>Medical Allowance</th>
                    <th>Special Allowance</th>
                    <th>PF</th>
                    <th>Effective Month</th>
                    <th>Effective Year</th>
                    <th>Designation</th>
                    <th>Per Day Salary</th>
                    <th>Per Hour Salary</th>
                    <th>Remark</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assetsallocation as $index => $assetsallocations)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if (!$company_id)
                            <td>{{ $assetsallocations->company->company_name ?? '-' }}</td>
                        @endif
                       <td>{{ $assetsallocations->employee->employee_code ?? '-' }} -
                                    {{ $assetsallocations->employee->full_name ?? '-' }}
                                </td>
                        <td>{{ $assetsallocations->icrement_date ? \Carbon\Carbon::parse($assetsallocations->icrement_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $assetsallocations->basic_da ?? '-' }}</td>
                        <td>{{ $assetsallocations->hra ?? '-' }}</td>
                        <td>{{ $assetsallocations->conveyance_allowance ?? '-' }}</td>
                        <td>{{ $assetsallocations->medical_allowance ?? '-' }}</td>
                        <td>{{ $assetsallocations->special_allowance ?? '-' }}</td>
                        <td>{{ $assetsallocations->pf ?? '-' }}</td>
                        <td>{{ $assetsallocations->effective_month ?? '-' }}</td>
                        <td>{{ $assetsallocations->effective_year ?? '-' }}</td>
                        <td>{{ $assetsallocations->designation->name ?? '-' }}</td>
                        <td>{{ $assetsallocations->per_day_salary ?? '-' }}</td>
                        <td>{{ $assetsallocations->per_hour_salary ?? '-' }}</td>
                        <td>{{ $assetsallocations->remark ?? '-' }}</td>
                        <td>{{ ucfirst($assetsallocations->status) }}</td>
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
