@if(isset($data) && count($data) > 0)
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th style="width: 5%;">Sr. No.</th>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Date</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Break Time</th>
                <th>Working Hours</th>
                <th>Late By</th>
                <th>Early Going</th>
                <th>OT Hours</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_code'] }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td class="text-success fw-bold">{!! implode('<br>', $row['in_times']) !!}</td>
                    <td class="text-primary fw-bold">{!! implode('<br>', $row['out_times']) !!}</td>
                    <td>{{ $row['break_time'] }}</td>
                    <td class="fw-bold">{{ $row['total_working_hours'] }}</td>
                    <td class="{{ $row['late_by'] !== '-' ? 'text-warning fw-bold' : '' }}">{{ $row['late_by'] }}</td>
                    <td class="{{ $row['early_going'] !== '-' ? 'text-danger fw-bold' : '' }}">{{ $row['early_going'] }}</td>
                    <td class="{{ $row['ot_hours'] !== '-' ? 'text-info fw-bold' : '' }}">{{ $row['ot_hours'] }}</td>
                    <td>
                        @if($row['status'] == 'Present')
                            <span class="badge bg-label-success">{{ $row['status'] }}</span>
                        @elseif($row['status'] == 'Late')
                            <span class="badge bg-label-warning">{{ $row['status'] }}</span>
                        @elseif($row['status'] == 'Early Going')
                            <span class="badge bg-label-danger">{{ $row['status'] }}</span>
                        @elseif($row['status'] == 'Late & Early Going')
                            <span class="badge bg-label-dark">{{ $row['status'] }}</span>
                        @else
                            <span class="badge bg-label-secondary">{{ $row['status'] }}</span>
                        @endif
                    </td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="text-center p-5">
        <div class="mb-3">
            <i class="fs-1 text-muted ti ti-file-search"></i>
        </div>
        <h5>No Punch-In Records Found</h5>
        <p class="text-muted">No attendance data matches the criteria for the selected period.</p>
    </div>
@endif
