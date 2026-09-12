<!DOCTYPE html>
<html>
<head>
    <title>Attendance Muster Report - {{ $monthYear->format('F Y') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 2px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .employee-col { text-align: left; white-space: nowrap; }
        .text-center { text-align: center; }
        
        /* Status Colors for Print - meaningful if color printing */
        .status-P { color: green; font-weight: bold; }
        .status-A { color: red; font-weight: bold; }
        .status-WO { color: grey; }
        .status-H { color: purple; }
        .status-L { color: blue; }
        .status-HD { color: orange; }

        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Attendance Muster Report - {{ $monthYear->format('F Y') }}</h2>
    
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">Sr</th>
                <th style="width: 150px;">Employee Name</th>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <th style="width: 20px;">{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</th>
                @endfor
                <th style="width: 25px;">P</th>
                <th style="width: 25px;">A</th>
                <th style="width: 25px;">PL</th>
                <th style="width: 25px;">SL</th>
                <th style="width: 25px;">DL</th>
                <th style="width: 25px;">C-off</th>
                <th style="width: 25px;">LWP</th>
                <th style="width: 25px;">H</th>
                <th style="width: 25px;">WO</th>
                <th style="width: 30px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                 $grandSumP=0; $grandSumA=0; $grandSumPL=0; $grandSumSL=0; $grandSumDL=0; $grandSumCOff=0; $grandSumLWP=0; $grandSumH=0; $grandSumWO=0; $grandSumTotal=0;
            @endphp
            @foreach ($employees as $index => $employeeData)
                @php
                     // Depending on how data is passed, it might be object or array. 
                     // Support both as we convert in controller.
                     $name = is_array($employeeData) ? $employeeData['name'] : $employeeData->name;
                     $attendances = is_array($employeeData) ? $employeeData['attendances'] : $employeeData->attendances;
                     
                     // Helper map
                     $attMap = [];
                     foreach($attendances as $att) {
                          $d = is_array($att) ? $att['attendance_date'] : $att->attendance_date;
                          $parts = explode('-', $d);
                          if(count($parts)>=3) $attMap[(int)$parts[2]] = $att;
                     }
                     
                     $sumP=0; $sumA=0; $sumPL=0; $sumSL=0; $sumDL=0; $sumCOff=0; $sumLWP=0; $sumH=0; $sumWO=0; $sumHD=0;
                @endphp
                <tr>
                     <td>{{ $index + 1 }}</td>
                     <td class="employee-col">{!! $name !!}</td>
                     
                     @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $att = $attMap[$day] ?? null;
                            $code = '-';
                            $class = '';
                            
                            if ($att) {
                                $type = is_array($att) ? $att['attendance_type'] : $att->attendance_type;
                                if ($type === 'present') { $code = 'P'; $class='status-P'; $sumP++; }
                                elseif ($type === 'absent') {
                                    $punchCount = is_array($att) ? (isset($att['punch_count']) ? $att['punch_count'] : 0) : (isset($att->punch_count) ? $att->punch_count : 0);
                                    if ($punchCount > 0) {
                                        $code = 'MP'; $class='status-HD'; $sumP += 0.5; $sumLWP += 0.5;
                                    } else {
                                        $code = 'LWP'; $class='status-A'; $sumLWP++;
                                    }
                                }
                                elseif ($type === 'half_day') { $code = 'HD'; $class='status-HD'; $sumP += 0.5; $sumLWP += 0.5; $sumHD++; }
                                elseif ($type === 'leave') { 
                                    $halfDay = is_array($att) ? ($att['half_day'] ?? null) : ($att->half_day ?? null);
                                    $shortName = is_array($att) ? ($att['leave_type_short_name'] ?? 'L') : ($att->leave_type_short_name ?? 'L');
                                    $shortNameUpper = strtoupper(trim($shortName));
                                    
                                    $leaveKey = 'LWP';
                                    if ($shortNameUpper === 'PL' || $shortNameUpper === 'PRIVILEGE LEAVE') {
                                        $leaveKey = 'PL';
                                    } elseif ($shortNameUpper === 'SL' || $shortNameUpper === 'SICK LEAVE') {
                                        $leaveKey = 'SL';
                                    } elseif ($shortNameUpper === 'DL' || $shortNameUpper === 'DUTY LEAVE') {
                                        $leaveKey = 'DL';
                                    } elseif (in_array($shortNameUpper, ['C-OFF', 'COFF', 'COMP-OFF', 'COMPOFF'])) {
                                        $leaveKey = 'C-off';
                                    } elseif ($shortNameUpper === 'LWP' || $shortNameUpper === 'LEAVE WITHOUT PAY') {
                                        $leaveKey = 'LWP';
                                    }

                                    if($halfDay) {
                                        $code = $halfDay === 'firsthalf' ? $shortName.'(FH)' : $shortName.'(SH)';
                                        if ($leaveKey === 'PL') $sumPL += 0.5;
                                        elseif ($leaveKey === 'SL') $sumSL += 0.5;
                                        elseif ($leaveKey === 'DL') $sumDL += 0.5;
                                        elseif ($leaveKey === 'C-off') $sumCOff += 0.5;
                                        else $sumLWP += 0.5;
                                        $sumP += 0.5;
                                    } else {
                                        $code = $shortName;
                                        if ($leaveKey === 'PL') $sumPL++;
                                        elseif ($leaveKey === 'SL') $sumSL++;
                                        elseif ($leaveKey === 'DL') $sumDL++;
                                        elseif ($leaveKey === 'C-off') $sumCOff++;
                                        else $sumLWP++;
                                    }
                                    $class='status-L';
                                }
                                elseif ($type === 'holiday') { $code = 'H'; $class='status-H'; $sumH++; }
                                elseif ($type === 'week_off') { $code = 'WO'; $class='status-WO'; $sumWO++; }
                                elseif ($type === 'week_off_working') { $code = 'WO(P)'; $class='status-P'; $sumP++; }
                            } else {
                                // Simple check for past 'A'
                               try {
                                     $dObj = \Carbon\Carbon::createFromDate($monthYear->year, $monthYear->month, $day);
                                     if ($dObj->isPast() && !$dObj->isFuture()) {
                                         $code = 'LWP'; $class='status-A'; $sumLWP++;
                                     }
                                 } catch(\Exception $e) {}
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $code }}</td>
                     @endfor
                     
                     @php
                         $sumTotal = $sumP + $sumA + $sumPL + $sumSL + $sumDL + $sumCOff + $sumLWP + $sumH + $sumWO;
                     @endphp
                     <td>{{ $sumP }}</td>
                     <td>{{ $sumA }}</td>
                     <td>{{ $sumPL }}</td>
                     <td>{{ $sumSL }}</td>
                     <td>{{ $sumDL }}</td>
                     <td>{{ $sumCOff }}</td>
                     <td>{{ $sumLWP }}</td>
                     <td>{{ $sumH }}</td>
                     <td>{{ $sumWO }}</td>
                     <td style="font-weight: bold;">{{ $sumTotal }}</td>
                </tr>
                @php
                    $grandSumP += $sumP;
                    $grandSumA += $sumA;
                    $grandSumPL += $sumPL;
                    $grandSumSL += $sumSL;
                    $grandSumDL += $sumDL;
                    $grandSumCOff += $sumCOff;
                    $grandSumLWP += $sumLWP;
                    $grandSumH += $sumH;
                    $grandSumWO += $sumWO;
                    $grandSumTotal += $sumTotal;
                @endphp
            @endforeach
            @if (count($employees) > 0)
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td></td>
                    <td class="employee-col">Total Sum</td>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        <td></td>
                    @endfor
                    <td>{{ $grandSumP }}</td>
                    <td>{{ $grandSumA }}</td>
                    <td>{{ $grandSumPL }}</td>
                    <td>{{ $grandSumSL }}</td>
                    <td>{{ $grandSumDL }}</td>
                    <td>{{ $grandSumCOff }}</td>
                    <td>{{ $grandSumLWP }}</td>
                    <td>{{ $grandSumH }}</td>
                    <td>{{ $grandSumWO }}</td>
                    <td>{{ $grandSumTotal }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
