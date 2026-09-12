@php
    if (isset($month) && $month) {
        $monthText = date('F', mktime(0, 0, 0, $month, 10));
    } else {
        $startMonthName = 'April';
        $endMonthName = isset($endDateStr) ? \Carbon\Carbon::parse($endDateStr)->format('F') : 'June';
        $monthText = $startMonthName . ' to ' . $endMonthName;
    }
@endphp

<table class="table table-bordered table-striped align-middle table-hover text-center">
    <thead class="table-light">
        <tr>
            <th rowspan="2" class="align-middle">Employee Code</th>
            <th rowspan="2" class="align-middle">Employee Name</th>
            <th rowspan="2" class="align-middle">Designation</th>
            <th rowspan="2" class="align-middle">Department</th>
            @foreach($leaveTypes as $lt)
                <th colspan="3" class="text-center">{{ $lt->full_name }}</th>
            @endforeach
            <th colspan="3" class="text-center fw-bold">GRAND TOTAL</th>
        </tr>
        <tr>
            @foreach($leaveTypes as $lt)
                <th class="text-center" style="font-size: 0.8rem;">TOTAL {{ strtoupper($lt->sort_name) }}</th>
                <th class="text-center" style="font-size: 0.8rem;">USED {{ strtoupper($lt->sort_name) }}</th>
                <th class="text-center" style="font-size: 0.8rem;">PENDING {{ strtoupper($lt->sort_name) }}</th>
            @endforeach
            <th class="text-center fw-bold" style="font-size: 0.8rem;">TOTAL LEAVES</th>
            <th class="text-center fw-bold" style="font-size: 0.8rem;">USED LEAVES</th>
            <th class="text-center fw-bold" style="font-size: 0.8rem;">PENDING LEAVES</th>
        </tr>
    </thead>
    <tbody>
        @forelse($employees as $emp)
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
                    <td colspan="{{ (count($leaveTypes) * 3) + 3 }}" class="text-center text-danger p-3 fw-bold">
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
                                $assigned = (float) ($earnedCoff[$emp->id] ?? 0);
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
                                $used = (float) ($usedCoff[$emp->id] ?? 0);
                                $pending = (float) ($pendingCoff[$emp->id] ?? 0);
                            } else {
                                if (isset($isMonthWise) && $isMonthWise && $lt->carry_forward == 1) {
                                    $used = (float) ($usedLeavesFyToMonth[$emp->id][$lt->id] ?? 0);
                                } else {
                                    $used = (float) ($usedLeaves[$emp->id][$lt->id] ?? 0);
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
                        <td class="text-center font-monospace">{{ $assigned > 0 ? $assigned : '-' }}</td>
                        <td class="text-center text-danger font-monospace fw-semibold">{{ $used > 0 ? $used : '-' }}</td>
                        <td class="text-center text-success font-monospace fw-semibold">{{ $pending > 0 ? $pending : '-' }}</td>
                    @endforeach
                    <td class="text-center font-monospace fw-bold">{{ $grandTotalAssigned }}</td>
                    <td class="text-center text-danger font-monospace fw-bold">{{ $grandTotalUsed > 0 ? $grandTotalUsed : '-' }}
                    </td>
                    <td class="text-center text-success font-monospace fw-bold">
                        {{ $grandTotalPending > 0 ? $grandTotalPending : '-' }}
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ 4 + (count($leaveTypes) * 3) + 3 }}" class="text-center p-4 text-muted">No records found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>