<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $monthName ?? $month ?? '' }} {{ $year ?? '' }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 10px; }
        .salary-slip-single { border: 1px solid #333; background: #fafafa; margin-bottom: 20px; page-break-inside: avoid; }
        .salary-header { display: flex; width: 100%; border-bottom: 1px solid #333; }
        .salary-header .left-block { width: 28%; padding: 10px; display: flex; align-items: center; justify-content: center; border-right: 1px solid #333; }
        .salary-header .left-block img { max-height: 70px; max-width: 100%; }
        .salary-header .center-block { flex: 1; text-align: center; padding: 10px; border-right: 1px solid #333; }
        .salary-header .center-block h4 { margin: 0 0 4px 0; font-size: 18px; }
        .salary-header .divider { width: 60px; border-bottom: 1px solid #000; margin: 4px auto; }
        .salary-header .right-block { width: 22%; padding: 10px; text-align: center; font-weight: bold; }
        /* .salary-header .right-block { width: 22%; padding: 10px; text-align: center; font-weight: bold; background: #f5f0e6; } */
        .salary-header .left-block h2 { margin: 0; font-size: 18px; }
        .employee-info { display: flex; border-bottom: 1px solid #333; }
        .employee-info .col { flex: 1; padding: 10px; }
        .employee-info .col:first-child { border-right: 1px solid #333; }
        .employee-info p { margin: 4px 0; }
        .attendance-summary { padding: 8px; border-bottom: 1px solid #ddd; }
        .slip-table { width: 100%; border-collapse: collapse; }
        .slip-table th, .slip-table td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        .slip-table th { background: #e8e4dc; font-weight: bold; }
        .slip-table .text-end { text-align: right; }
        .slip-table tfoot .row-total th { background: #e8e4dc; }
        .slip-table tfoot .row-net { background: #f5f0e6; font-size: 13px; }
        .slip-footer { padding: 6px 10px; font-size: 10px; color: #666; border-top: 1px solid #ddd; }
        .no-records { text-align: center; padding: 40px; color: #666; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    @if(isset($slips) && $slips->isNotEmpty())
        @foreach($slips as $slip)
            @include('software.modules.employee.salary-slip.partials.slip-content', [
                'salary' => $slip['salary'],
                'company' => $slip['company'],
                'employee' => $slip['employee'],
                'employment' => $slip['employment'],
                'netPayInWords' => $slip['netPayInWords'],
            ])
        @endforeach
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" style="display: inline-block; background-color: #0d6efd; color: #fff; padding: 8px 18px; border-radius: 4px; border: none; text-decoration: none; font-weight: 500; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); transition: background 0.2s; cursor: pointer;">Print</button>
            <a href="{{ route('salary-slip.index') }}" style="display: inline-block; background-color: #ffc107; color: #212529; padding: 8px 18px; border-radius: 4px; border: none; text-decoration: none; font-weight: 500; font-size: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); transition: background 0.2s;">Back to Salary Slip</a>
        </div>
    @else
        <div class="no-records">No salary record found for the selected period.</div>
    @endif
</body>
</html>
