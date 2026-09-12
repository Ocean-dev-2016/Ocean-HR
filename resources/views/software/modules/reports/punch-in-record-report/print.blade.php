<!DOCTYPE html>
<html>
<head>
    <title>Punch-In Record Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            margin: 0;
            padding: 10px;
            background-color: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Force Landscape layout for printing */
        @page {
            size: landscape;
            margin: 8mm 10mm;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        /* Report Header Styling */
        .report-header {
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }
        
        .company-name {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .report-title {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-top: 4px;
            letter-spacing: 0.2px;
        }
        
        .report-meta {
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }
        
        .meta-group {
            display: inline-block;
        }
        
        .meta-item {
            margin-right: 15px;
        }
        
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        
        th {
            background-color: #f8fafc !important;
            color: #475569;
            font-weight: 600;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        /* Column Width Allocations */
        .col-sr { width: 3%; text-align: center; }
        .col-code { width: 7%; }
        .col-name { width: 17%; }
        .col-dept { width: 9%; }
        .col-date { width: 7%; text-align: center; }
        .col-in { width: 8%; text-align: center; }
        .col-out { width: 8%; text-align: center; }
        .col-break { width: 6%; text-align: center; }
        .col-work { width: 7%; text-align: center; font-weight: 700; }
        .col-late { width: 5%; text-align: center; }
        .col-early { width: 6%; text-align: center; }
        .col-ot { width: 5%; text-align: center; }
        .col-status { width: 9%; text-align: center; }
        .col-remarks { width: 13%; }
        
        /* Alternating Rows */
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Time Colors */
        .text-success {
            color: #16a34a !important;
            font-weight: 600;
        }
        
        .text-primary {
            color: #2563eb !important;
            font-weight: 600;
        }
        
        .text-warning {
            color: #d97706 !important;
            font-weight: 600;
        }
        
        .text-danger {
            color: #dc2626 !important;
            font-weight: 600;
        }
        
        .text-info {
            color: #0d9488 !important;
            font-weight: 600;
        }
        
        /* StatusBadges for print */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9.5px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        .badge-present {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0;
        }
        
        .badge-late {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a;
        }
        
        .badge-early {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca;
        }
        
        .badge-late-early {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0;
        }
        
        .badge-absent {
            background-color: #fef2f2 !important;
            color: #991b1b !important;
            border: 1px solid #fee2e2;
        }
    </style>
</head>
<body>
    @if(!isset($is_excel))
    <div class="report-header">
        <div class="company-name">{{ isset($company) ? $company->company_name : 'OceanHR' }}</div>
        <div class="report-title">PUNCH-IN RECORD REPORT</div>
        <div class="report-meta">
            <div class="meta-group">
                <span class="meta-item"><b>Period:</b> {{ $period ?? 'Selected Month' }}</span>
                @if(isset($employee) && $employee)
                    <span class="meta-item"><b>Employee:</b> {{ $employee->employee_code }} - {{ $employee->full_name }}</span>
                @endif
            </div>
            <div class="meta-group">
                <span><b>Generated on:</b> {{ date('d-m-Y H:i A') }}</span>
            </div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="col-sr">Sr. No.</th>
                <th class="col-code">Employee ID</th>
                <th class="col-name">Employee Name</th>
                <th class="col-dept">Department</th>
                <th class="col-date">Date</th>
                <th class="col-in">In Time</th>
                <th class="col-out">Out Time</th>
                <th class="col-break">Break Time</th>
                <th class="col-work">Working Hours</th>
                <th class="col-late">Late By</th>
                <th class="col-early">Early Going</th>
                <th class="col-ot">OT Hours</th>
                <th class="col-status">Status</th>
                <th class="col-remarks">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="col-sr" style="text-align: center;">{{ $loop->iteration }}</td>
                    <td class="col-code">{{ $row['employee_code'] }}</td>
                    <td class="col-name">{{ $row['employee_name'] }}</td>
                    <td class="col-dept">{{ $row['department'] }}</td>
                    <td class="col-date" style="text-align: center;">{{ $row['date'] }}</td>
                    
                    @if(isset($is_excel))
                        <td class="col-in text-success" style="text-align: center;">{{ implode(', ', $row['in_times']) }}</td>
                        <td class="col-out text-primary" style="text-align: center;">{{ implode(', ', $row['out_times']) }}</td>
                    @else
                        <td class="col-in text-success" style="text-align: center;">{!! implode('<br>', $row['in_times']) !!}</td>
                        <td class="col-out text-primary" style="text-align: center;">{!! implode('<br>', $row['out_times']) !!}</td>
                    @endif
                    
                    <td class="col-break" style="text-align: center;">{{ $row['break_time'] }}</td>
                    <td class="col-work" style="text-align: center;">{{ $row['total_working_hours'] }}</td>
                    <td class="col-late text-warning" style="text-align: center;">{{ $row['late_by'] }}</td>
                    <td class="col-early text-danger" style="text-align: center;">{{ $row['early_going'] }}</td>
                    <td class="col-ot text-info" style="text-align: center;">{{ $row['ot_hours'] }}</td>
                    
                    <td class="col-status" style="text-align: center;">
                        @if(isset($is_excel))
                            {{ $row['status'] }}
                        @else
                            @if($row['status'] == 'Present')
                                <span class="badge badge-present">{{ $row['status'] }}</span>
                            @elseif($row['status'] == 'Late')
                                <span class="badge badge-late">{{ $row['status'] }}</span>
                            @elseif($row['status'] == 'Early Going')
                                <span class="badge badge-early">{{ $row['status'] }}</span>
                            @elseif($row['status'] == 'Late & Early Going')
                                <span class="badge badge-late-early">{{ $row['status'] }}</span>
                            @else
                                <span class="badge badge-absent">{{ $row['status'] }}</span>
                            @endif
                        @endif
                    </td>
                    
                    <td class="col-remarks">{{ $row['remarks'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if(!isset($is_excel))
    <script>
        // Use a timeout to ensure CSS fonts and layouts are fully loaded before rendering print dialog
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
    @endif
</body>
</html>
