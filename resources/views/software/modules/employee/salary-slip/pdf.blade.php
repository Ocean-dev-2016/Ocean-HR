<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip - {{ $monthName ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 8px; }
        .salary-slip-single { border: 1px solid #333; background: #fafafa; margin-bottom: 18px; page-break-inside: avoid; }
        .salary-header { display: table; width: 100%; border-bottom: 1px solid #333; }
        .salary-header .left-block { display: table-cell; width: 28%; padding: 8px; vertical-align: middle; border-right: 1px solid #333; text-align: center; }
        .salary-header .center-block { display: table-cell; width: 50%; padding: 8px; text-align: center; border-right: 1px solid #333; }
        .salary-header .center-block h4 { margin: 0 0 4px 0; font-size: 16px; }
        .salary-header .divider { width: 50px; border-bottom: 1px solid #000; margin: 4px auto; }
        /* .salary-header .right-block { display: table-cell; width: 22%; padding: 8px; text-align: center; font-weight: bold; background: #f5f0e6; } */
        .salary-header .right-block { display: table-cell; width: 22%; padding: 8px; text-align: center; font-weight: bold; }
        .employee-info { display: table; width: 100%; border-bottom: 1px solid #333; }
        .employee-info .col { display: table-cell; width: 50%; padding: 8px; vertical-align: top; }
        .employee-info .col:first-child { border-right: 1px solid #333; }
        .employee-info p { margin: 3px 0; }
        .attendance-summary { padding: 6px; border-bottom: 1px solid #ddd; }
        .slip-table { width: 100%; border-collapse: collapse; }
        .slip-table th, .slip-table td { border: 1px solid #333; padding: 5px 6px; text-align: left; }
        .slip-table th { background: #e8e4dc; font-weight: bold; }
        .slip-table .text-end { text-align: right; }
        .slip-table tfoot .row-total th { background: #e8e4dc; }
        .slip-table tfoot .row-net { background: #f5f0e6; font-size: 12px; }
        .slip-footer { padding: 5px 8px; font-size: 9px; color: #666; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    @foreach($slips as $slip)
        @include('software.modules.employee.salary-slip.partials.slip-content', [
            'salary' => $slip['salary'],
            'company' => $slip['company'],
            'employee' => $slip['employee'],
            'employment' => $slip['employment'],
            'netPayInWords' => $slip['netPayInWords'],
        ])
    @endforeach
</body>
</html>
