<!DOCTYPE html>
<html>
<head>
    <title>Employee Leave Summary Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        }
        .text-success { color: green; }
        .text-danger { color: red; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        if (isset($month) && $month) {
            $monthText = date('F', mktime(0, 0, 0, $month, 10));
        } else {
            $startMonthName = 'April';
            $endMonthName = isset($endDateStr) ? \Carbon\Carbon::parse($endDateStr)->format('F') : 'June';
            $monthText = $startMonthName . ' to ' . $endMonthName;
        }
    @endphp
    @if(!empty($is_excel))
        @php
            $totalCols = (count($leaveTypes) * 3) + 7;
        @endphp
        <table>
            @if($company)
                <tr>
                    <td colspan="3" style="text-align: center; vertical-align: middle;">
                        @if($company->company_logo && file_exists(public_path($company->company_logo)))
                            <img src="{{ public_path($company->company_logo) }}" width="180" height="80" style="width: 180px; height: 80px;" alt="Logo">
                        @endif
                    </td>
                    <td colspan="{{ $totalCols - 3 }}" style="text-align: center; font-size: 16px; font-weight: bold; vertical-align: middle;">
                        {{ $company->company_name }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; font-size: 11px; font-weight: bold; vertical-align: middle;">
                        Month: {{ $monthText }} | Year: {{ $year }}-{{ $year + 1 }}
                    </td>
                    <td colspan="9" style="text-align: center; font-size: 11px; font-weight: bold; vertical-align: middle;">
                        Department: {{ $selected_department ?? 'All' }}
                    </td>
                    <td colspan="{{ $totalCols - 12 }}" style="text-align: center; font-size: 11px; font-weight: bold; vertical-align: middle;">
                        Designation: {{ $selected_designation ?? 'All' }}
                    </td>
                </tr>

            @endif
    @else
        <div class="header">
            <h2>{{ $company->company_name ?? 'Employee' }} - Leave Summary Report</h2>
            <p>Year: {{ $year }}-{{ $year + 1 }} @if(isset($month) && $month) | Month: {{ date('F', mktime(0, 0, 0, $month, 10)) }} @endif</p>
        </div>
        <table>
    @endif
        <thead>
            <tr>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Employee Name</th>

                <th rowspan="2">Designation</th>
                <th rowspan="2">Department</th>
                @foreach($leaveTypes as $lt)
                    <th colspan="3">{{ $lt->full_name }}</th>
                @endforeach
                <th colspan="3">GRAND TOTAL</th>
            </tr>
            <tr>
                @foreach($leaveTypes as $lt)
                    <th>TOTAL {{ strtoupper($lt->sort_name) }}</th>
                    <th>USED {{ strtoupper($lt->sort_name) }}</th>
                    <th>PENDING {{ strtoupper($lt->sort_name) }}</th>
                @endforeach
                <th>TOTAL LEAVES</th>
                <th>USED LEAVES</th>
                <th>PENDING LEAVES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
                @php
                    $doj = $emp->employmentDetail?->date_of_joining;
                    $joinedAfterPeriod = false;
                    if ($doj && isset($endDateStr)) {
                        if (\Carbon\Carbon::parse($doj)->startOfDay()->gt(\Carbon\Carbon::parse($endDateStr)->endOfDay())) {
                            $joinedAfterPeriod = true;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $emp->employee_code ?: '-' }}</td>
                    <td>{{ $emp->full_name }}</td>

                    <td>{{ $emp->employmentDetail?->designation?->name ?? '-' }}</td>
                    <td>{{ $emp->employmentDetail?->department?->name ?? '-' }}</td>
                    @if($joinedAfterPeriod)
                        <td colspan="{{ (count($leaveTypes) * 3) + 3 }}" class="text-center font-bold text-danger">
                            Not Found
                        </td>
                    @else
                        @php
                            $grandTotalAssigned = 0;
                            $grandTotalUsed = 0;
                            $grandTotalPending = 0;
                        @endphp
                        @foreach($leaveTypes as $lt)
                            @php
                                $isCOff = (strtolower($lt->sort_name) === 'c-off' || strtolower($lt->sort_name) === 'coff' || strtolower($lt->full_name) === 'compensatory off');
                                if ($isCOff) {
                                    $assigned = (float)($earnedCoff[$emp->id] ?? 0);
                                } else {
                                    $ratio = $activeMonthsRatio[$emp->id] ?? 1.0;
                                    if (isset($isMonthWise) && $isMonthWise) {
                                        $assigned = (float) $emp->getAccruedLeaveCountForReport($lt->id, $year, $month, $ratio);
                                    } else {
                                        $periodEnd = \Carbon\Carbon::parse($endDateStr)->endOfDay();
                                        $assigned = (float) $emp->getAccruedLeaveCountForReport(
                                            $lt->id,
                                            (int) $periodEnd->year,
                                            (int) $periodEnd->month,
                                            1.0
                                        );
                                    }
                                }
                                if ($isCOff) {
                                    $used = (float)($usedCoff[$emp->id] ?? 0);
                                    $pending = (float)($pendingCoff[$emp->id] ?? 0);
                                } else {
                                    if (isset($isMonthWise) && $isMonthWise && $lt->carry_forward == 1) {
                                        $used = (float) ($usedLeavesFyToMonth[$emp->id][$lt->id] ?? 0);
                                    } else {
                                        $used = (float)($usedLeaves[$emp->id][$lt->id] ?? 0);
                                    }
                                    $pending = $assigned - $used;
                                    if ($pending < 0) {
                                        $pending = 0;
                                    }
                                }
                                    if (strtolower($lt->sort_name) === 'pl') {
                                        $grandTotalAssigned += $assigned;
                                        $grandTotalUsed += $used;
                                        $grandTotalPending += $pending;
                                    }
                            @endphp
                            <td class="text-center">{{ $assigned > 0 ? $assigned : '-' }}</td>
                            <td class="text-center text-danger">{{ $used > 0 ? $used : '-' }}</td>
                            <td class="text-center text-success">{{ $pending > 0 ? $pending : '-' }}</td>
                        @endforeach
                        <td class="text-center font-bold">{{ $grandTotalAssigned }}</td>
                        <td class="text-center text-danger font-bold">{{ $grandTotalUsed > 0 ? $grandTotalUsed : '-' }}</td>
                        <td class="text-center text-success font-bold">{{ $grandTotalPending > 0 ? $grandTotalPending : '-' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if(empty($is_excel))
    <script>
        window.print();
    </script>
    @endif
</body>
</html>
