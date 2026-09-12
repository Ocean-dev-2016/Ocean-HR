@extends('software.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        {{ $employee->full_name }} - {{ $employee->employee_code }} - {{ $employee->branch->name ?? '' }} - {{ $employee->employmentDetail->designation->name ?? '' }}
                    </h5>
                    <div class="mt-2">
                        <span class="fw-bold">Attendance Details</span> | {{ $date->format('d-M-Y') }} | {{ $date->format('l') }} | 
                        <span class="fw-bold text-primary">
                            @if($dayData && isset($dayData->attendance_type))
                                {{ strtoupper($dayData->attendance_type) }}
                            @else
                                -
                            @endif
                        </span>
                        @if(isset($employee->employmentDetail) && $employee->employmentDetail->shift_name)
                             | <span class="badge bg-danger">{{ $employee->employmentDetail->shift_name }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Top stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Total Working Hour</label>
                            <input type="text" class="form-control" value="{{ $dayData->working_hours ?? '00:00:00' }}" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Pending Hour</label>
                            <input type="text" class="form-control" value="00:00:00" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Total OT Hour</label>
                            <input type="text" class="form-control" value="{{ $dayData->extra_working_hours ?? '00:00:00' }}" readonly style="background-color: #e9ecef;">
                            <small class="text-danger fw-bold">Round Off OT: {{ $dayData->round_off_ot ?? '00:00:00' }}</small>
                        </div>
                    </div>

                    <!-- Adjustment Section -->
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Adjustment Hour</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="00:00:00" value="00:00:00">
                                <button class="btn btn-primary">Update</button>
                            </div>
                        </div>
                       <div class="col-md-4">
                            <label class="form-label fw-bold">Additional OT Hour</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="00:00:00" value="00:00:00">
                                <button class="btn btn-secondary">Update</button>
                            </div>
                        </div>
                    </div>
                    <div class="text-danger fw-bold mb-4 small">
                        Adjustments or credits given to employees who work fewer hours than scheduled, ensuring they are fairly treated for shortfalls in their working hours
                    </div>

                    <!-- Punches Table -->
                    <div class="table-responsive mb-5">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr.No</th>
                                    <th>DateTime</th>
                                    <th>IN/OUT</th>
                                    <th>Entry Type</th>

                                    <th>Last Updated Date</th>
                                    <th>Added/Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rawPunches as $index => $punch)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($punch->attendance_date . ' ' . $punch->punch_in_time)->format('d-M-Y | l | h:i:s A') }}</td>
                                    <td>{{ strtoupper($punch->attendace_type) }}</td> <!-- adjust column name if needed -->
                                    <td>{{ $punch->entry_type ?? 'PANEL MANUAL ENTRY' }}</td>

                                    <td>{{ $punch->updated_at ? $punch->updated_at->format('d-M-Y | l | h:i:s A') : '-' }}</td>
                                    <td>{{ $punch->updated_by ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No punches found for this day.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Monthly Summary -->
                    <h5 class="fw-bold text-secondary mb-3">Monthly Attendance Details | {{ $date->format('M-Y') }}</h5>
                    
                    @php
                        // Extract stats from monthlyStats
                        // monthlyStats['attendances'] contains list of day objects with type
                        $totalPresent = $monthlyStats['total_present_days'] ?? 0;
                        $totalLeave = ($monthlyStats['paid_leave_days'] ?? 0) + ($monthlyStats['unpaid_leave_days'] ?? 0);
                        $totalHalfDays = $monthlyStats['total_half_days'] ?? 0;
                        $totalWeekOff = ($monthlyStats['paid_week_off_days'] ?? 0) + ($monthlyStats['week_off_working_days'] ?? 0);
                        $totalHoliday = $monthlyStats['paid_holiday_days'] ?? 0;
                        $payableDays = $monthlyStats['payable_days'] ?? 0;
                        $totalWorkingHours = $monthlyStats['total_working_hours'] ?? '00:00:00';
                        $totalOT = $monthlyStats['total_overtime'] ?? '00:00:00';
                    @endphp

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Present Days</div>
                                <div class="fs-5 fw-bold">{{ $totalPresent }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Week Off</div>
                                <div class="fs-5 fw-bold">{{ $totalWeekOff }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Half Days</div>
                                <div class="fs-5 fw-bold">{{ $totalHalfDays }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Leaves</div>
                                <div class="fs-5 fw-bold">{{ $totalLeave }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Holiday</div>
                                <div class="fs-5 fw-bold">{{ $totalHoliday }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Payable Days</div>
                                <div class="fs-5 fw-bold">{{ $payableDays }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Sandwich Applied</div>
                                <div class="fs-5 fw-bold">0</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Absent Days</div>
                                <div class="fs-5 fw-bold">{{ $totalAbsent ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Total Working Hour</div>
                                <div class="fs-5 fw-bold">{{ $totalWorkingHours }}</div>
                            </div>
                        </div>
                         <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Total Pending Hour</div>
                                <div class="fs-5 fw-bold">00:00:00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="border rounded p-3 bg-white h-100">
                                <div class="text-muted small">Total OT</div>
                                <div class="fs-5 fw-bold text-success">{{ $totalOT }}</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
