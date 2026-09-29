<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form List - Print</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 8mm;
            }
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            margin: 10px;
            color: #212529;
            background: #fff;
        }

        .header-box {
            text-align: center;
            margin-bottom: 12px;
        }

        .header-box h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #1a202c;
            text-transform: uppercase;
        }

        .header-box p {
            font-size: 11px;
            margin: 0;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 600;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            font-size: 11.5px;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: #fcfdfd;
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .badge-status {
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            font-size: 11px;
        }
    </style>
</head>

<body onload="printAndRedirect();">
    <div class="header-box">
        <h2>Request Form List</h2>
        <p>Printed on: {{ now()->setTimezone('Asia/Kolkata')->format('d M Y, h:i:s A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">Sr No</th>
                @if (empty($company_id))
                    <th class="text-start">Company Name</th>
                @endif
                <th class="text-start">Request From</th>
                <th class="text-start">Request To</th>
                <th class="text-start">Description</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requestForms as $index => $requestForm)
                @php
                    $fromEmp = $requestForm->requestFromEmployee;
                    $fromEmpText = $fromEmp ? ($fromEmp->employee_code . ' / ' . ($fromEmp->full_name ?? ($fromEmp->first_name . ' ' . $fromEmp->last_name))) : '-';

                    $toEmp = $requestForm->requestToEmployee;
                    $toEmpText = $toEmp ? ($toEmp->employee_code . ' / ' . ($toEmp->full_name ?? ($toEmp->first_name . ' ' . $toEmp->last_name))) : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    @if (empty($company_id))
                        <td>{{ $requestForm->company->company_name ?? '-' }}</td>
                    @endif
                    <td>{{ $fromEmpText }}</td>
                    <td>{{ $toEmpText }}</td>
                    <td>{{ $requestForm->request_description ?? '-' }}</td>
                    <td class="text-center">{{ ucfirst($requestForm->status ?? 'Active') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ empty($company_id) ? 6 : 5 }}" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No Request Form records found.
                    </td>
                </tr>
            @endforelse
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
