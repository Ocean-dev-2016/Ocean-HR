    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Leave Type List - Print</title>
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
                    <th colspan="9" class="text-center">
                        <h3>Leave Type List - List Printed on {{ now()->setTimezone('Asia/Kolkata')->format('d M Y H:i A') }}
                        </h3>
                    </th>
                </tr>
                <tr>
                    <th>Sr No</th>
                     @if (!$company_id)
                        <th>Company Name</th>
                    @endif
                    <th>Sort Name</th>
                    <th>Full Name</th>
                    <th>Count</th>
                    <th>Mode</th>
                    <th>Carry Forward</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leave as $index => $leaves)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                         @if (!$company_id)
                            <td>{{ $leaves->company->company_name ?? '-' }}</td>
                        @endif
                        <td>{{ $leaves->sort_name }}</td>
                        <td>{{ $leaves->full_name }}</td>
                        <td>{{ $leaves->count }}</td>
                        <td>{{ $leaves->mode == 1 ? "Company Pay":"Employee Pay" }}</td>
                        <td>{{ $leaves->carry_forward == 1 ? 'Yes' : 'No' }}</td>
                        <td>{{ ucfirst($leaves->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <script>
            function printAndRedirect() {
                window.print();

                window.onafterprint = function () {
                    setTimeout(function () {
                        window.location.href = "{{ route($modules['route'] . '.index') }}";
                    }, 1);
                };
            }
        </script>
    </body>

    </html>
