<!DOCTYPE html>
<html>
<head>
    <title>Detailed Attendance Report - {{ $monthYear->format('F Y') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 3px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .employee-col { text-align: left; white-space: nowrap; width: 150px; }
        .text-center { text-align: center; }
        
        .status-P { color: green; font-weight: bold; }
        .status-A { color: red; font-weight: bold; }
        .status-WO { color: grey; }
        .status-H { color: purple; }
        .status-L { color: orange; }
        .status-HD { color: orange; font-weight: bold;}
        
        .weekend-cell { background-color: #f0f0f0; }

        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Detailed Attendance Report - {{ $monthYear->format('F Y') }}</h2>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">Sr</th>
                <th rowspan="2" class="employee-col">Employee Name</th>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <th>{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</th>
                @endfor
                <th colspan="7">Summary</th>
            </tr>
            <tr>
                <!-- Days Subheader (empty but structural) -->
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <!-- Maybe simplify header or keep day nums only -->
                @endfor
                
                <!-- Summary Subheaders -->
                <th style="width: 30px;">P</th>
                <th style="width: 30px;">WO</th>
                <th style="width: 30px;">HD</th>
                <th style="width: 30px;">L</th>
                <th style="width: 30px;">A</th>
                <th style="width: 30px;">H</th>
                <th style="width: 50px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-col">{!! $employee->name !!}</td>
                    
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateStr = sprintf('%04d-%02d-%02d', $monthYear->year, $monthYear->month, $day);
                            // Ensure attendances is a collection
                            $attendance = collect($employee->attendances)->firstWhere('attendance_date', $dateStr);
                            $dayDate = \Carbon\Carbon::parse($dateStr);
                            $cellClass = $dayDate->isWeekend() ? 'weekend-cell' : '';
                            $content = '-';
                            
                            if ($attendance) {
                                // Handle both object and array, though usually object in view
                                $type = is_array($attendance) ? $attendance['attendance_type'] : $attendance->attendance_type;
                                $type = strtolower($type);
                                
                                $timeIn = is_array($attendance) ? ($attendance['time_in']??'') : ($attendance->time_in??'');
                                $timeOut = is_array($attendance) ? ($attendance['time_out']??'') : ($attendance->time_out??'');
                                $wh = is_array($attendance) ? ($attendance['working_hours']??'') : ($attendance->working_hours??'');

                                if ($type === 'present') {
                                    $content = "P<br><span style='font-size:8px;'>{$timeIn}-{$timeOut}</span>";
                                    $cellClass = 'status-P';
                                } elseif ($type === 'absent') {
                                    $content = 'A';
                                    $cellClass = 'status-A';
                                } elseif ($type === 'leave') {
                                    $shortName = is_array($attendance) ? ($attendance['leave_type_short_name'] ?? 'L') : ($attendance->leave_type_short_name ?? 'L');
                                    $content = $shortName;
                                    $cellClass = 'status-L';
                                } elseif ($type === 'half_day') {
                                    $content = "HD<br><span style='font-size:8px;'>{$timeIn}-{$timeOut}</span>";
                                    $cellClass = 'status-HD';
                                } elseif ($type === 'holiday') {
                                    $content = 'H';
                                    $cellClass = 'status-H';
                                } elseif ($type === 'week_off') {
                                    $content = 'WO';
                                    $cellClass = 'status-WO';
                                }
                            }
                        @endphp
                        <td class="{{ $cellClass }}">{!! $content !!}</td>
                    @endfor
                    
                    <!-- Summary Data (Assuming calculated fields exist on employee object) -->
                    <!-- Since detail report usually creates summary in view or controller, let's use what we have or placeholder -->
                    <!-- The ReportDesign view calculates these client side or server side for display? 
                         Actually ReportDesign relies on $employee->total_working_hours. 
                         Let's try to show the same summary columns as the HTML report.
                    -->
                    
                    @php
                       // Re-calculate summary for print if not readily available as totals
                       // Or just show total working hours as per design? 
                       // The design has: P, WO, HD, L, A, H, DC, TotalHours
                       // Let's rely on standard attendance counts which might not be pre-calculated in $employee object 
                       // unless getAttendanceReport provided them.
                       // Based on exportExcel, data has these counts.
                       
                       // Let's use simple logic similar to muster for counts if avail
                       $counts = ['present'=>0, 'wo'=>0, 'hd'=>0, 'leave'=>0, 'absent'=>0, 'holiday'=>0];
                       foreach($employee->attendances as $att) {
                           $t = strtolower(is_array($att)?$att['attendance_type']:$att->attendance_type);
                           if($t=='present' || $t=='week_off_working') $counts['present']++;
                           elseif($t=='week_off') $counts['wo']++;
                           elseif($t=='half_day') $counts['hd']++;
                           elseif($t=='leave') $counts['leave']++;
                           elseif($t=='absent') $counts['absent']++;
                           elseif($t=='holiday') $counts['holiday']++;
                       }
                       $totalHours = is_array($employee) ? ($employee['total_working_hours']??'') : ($employee->total_working_hours??'');
                    @endphp
                    
                    <td>{{ $counts['present'] }}</td>
                    <td>{{ $counts['wo'] }}</td>
                    <td>{{ $counts['hd'] }}</td>
                    <td>{{ $counts['leave'] }}</td>
                    <td>{{ $counts['absent'] }}</td>
                    <td>{{ $counts['holiday'] }}</td>
                    <td>{{ $totalHours }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
