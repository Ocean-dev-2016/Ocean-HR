    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Employee Education Experience  - Print</title>
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
                        <h3>Employee Education Experience  - List Printed on
                            {{ now()->setTimezone('Asia/Kolkata')->format('d M Y h:i:s A') }}
                        </h3>
                    </th>
                </tr>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Employee Code</th>
                            <th>Company Name</th>
                            <th>Employee Name</th>
                            <th>Degree</th>
                            <th>Previous Company Name</th>
                            <th>Unit Change</th>
                            <th>Document Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeDocuments as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->employee->employee_code ?? '-' }}</td>
                                <td>{{ $item->company->company_name ?? '-' }}</td>
                                <td>{{ $item->employee->full_name ?? '-' }}</td>
                                <td>{{ $item->degree ?? '-' }}</td>
                                <td>{{ $item->company_name ?? '-' }}</td>
                                <td>{{ $item->unit_change ?? '-' }}</td>
                                <td>{{ $item->document_name ?? '-' }}</td>
                                <td>{{ ucfirst($item->status ?? '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No records found</td>
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
