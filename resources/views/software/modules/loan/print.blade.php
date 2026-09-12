    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Department List - Print</title>
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
                    <th colspan="14" class="text-center">
                        <h3>
                            Loan Master List - List Printed on
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
                    <th>Loan Type</th>
                    <th>Loan Amount</th>
                    <th>Balance Amount</th>
                    <th>EMI Amount</th>
                    <th>Total Installments</th>
                    <th>Remaining Installments</th>
                    <th>Interest Type</th>
                    <th>Interest Rate</th>
                    <th>Loan Date</th>
                    <th>Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loans as $index => $loan)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        @if (!$company_id)
                            <td>{{ $loan->company->company_name ?? '-' }}</td>
                        @endif
                      <td>{{ $loan->employee->employee_code ?? '-' }} -
                            {{ $loan->employee->full_name ?? '-' }}
                        </td>
                        <td>{{ $loan->loan_type->name ?? '-' }}</td>
                        <td>{{ $loan->loan_amount }}</td>
                        <td>{{ $loan->balance_amount }}</td>
                        <td>{{ $loan->emi_amount ?? '-' }}</td>
                        <td>{{ $loan->total_installments }}</td>
                        <td>{{ $loan->remaining_installments }}</td>
                        <td>{{ ucfirst($loan->interest_type) }}</td>
                        <td>{{ $loan->interest_rate }}</td>
                        <td>{{ \Carbon\Carbon::parse($loan->loan_date)->format('d-m-Y') }}</td>
                        <td>{{ ucfirst($loan->status) }}</td>
                        <td>{{ $loan->remark ?? '-' }}</td>
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
