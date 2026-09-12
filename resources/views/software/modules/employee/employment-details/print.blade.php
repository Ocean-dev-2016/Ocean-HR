    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Employee Details  - Print</title>
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
                    <th colspan="10" class="text-center">
                        <h3>Employee Details  - List Printed on
                            {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                        </h3>
                    </th>
                </tr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            @if (!$company_id)
                                <th>Company Name</th>
                            @endif
                            <th>Employee Name</th>


                            <th>Designation Type</th>

                            <th>Designation</th>
                            <th>Department</th>
                              <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeAssets as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                @if (!$company_id)
                                    <td>{{ $item->company->company_name ?? '-' }}</td>
                                @endif
                                <td>{{ $item->employee->middle_name?? '-' }}</td>

                                <td>{{ $item->designation_type ?? '-' }}</td>

                                <td>{{ $item->designation->name ?? '-' }}</td>

                                <td>{{ $item->department->name ?? '-' }}</td>
                                <td>{{ $item->date_of_joining ? \Carbon\Carbon::parse($item->date_of_joining)->format('d/m/Y') : '-' }}</td>

                                <td>{{ ucfirst($item->status ?? '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ !$company_id ? 8 : 7 }}" class="text-center">No records found</td>
                            </tr>
                        @endforelse
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
