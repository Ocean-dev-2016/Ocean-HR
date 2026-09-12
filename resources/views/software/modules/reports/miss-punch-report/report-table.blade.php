@if(isset($data) && count($data) > 0)
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Department</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Shift Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['employee_code'] }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td class="text-success fw-bold">{{ $row['in_time'] }}</td>
                    <td class="text-danger fw-bold">{{ $row['out_time'] }}</td>
                    <td>
                        {{ $row['shift_name'] }} <br>
                        <small class="text-muted">{{ $row['shift_time'] }}</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="text-center p-5">
        <div class="mb-3">
            <i class="fs-1 text-muted ti ti-file-search"></i>
        </div>
        <h5>No Miss Punch Records Found</h5>
        <p class="text-muted">No employees matches the miss punch criteria for the selected period.</p>
    </div>
@endif
