@if(isset($data) && count($data) > 0)
    <table class="table table-bordered table-striped"
           data-total="{{ $summary['total'] }}"
           data-present="{{ $summary['present'] }}"
           data-absent="{{ $summary['absent'] }}">
        <thead class="table-light">
            <tr>
                <th style="width: 60px;">SR NO</th>
                <th>EMPLOYEE NAME - CODE</th>
                <th>SHIFT NAME</th>
                <th>DESIGNATION</th>
                <th>DEPARTMENT</th>
                <th>PRESENT / ABSENT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <span class="fw-semibold">{{ $row['employee_name'] }}</span>
                        <br>
                        <small class="text-muted">{{ $row['employee_code'] }}</small>
                    </td>
                    <td>{{ $row['shift_name'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>
                        @if($row['status'] === 'Present')
                            <span class="badge bg-label-success">Present</span>
                        @else
                            <span class="badge bg-label-danger">Absent</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light fw-semibold">
            <tr>
                <td colspan="5" class="text-end">
                    Total: {{ $summary['total'] }} &nbsp;|&nbsp;
                    <span class="text-success">Present: {{ $summary['present'] }}</span> &nbsp;|&nbsp;
                    <span class="text-danger">Absent: {{ $summary['absent'] }}</span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@else
    <div class="text-center p-5">
        <div class="mb-3">
            <i class="fs-1 text-muted ti ti-file-search"></i>
        </div>
        <h5>No Data Found</h5>
        <p class="text-muted">No attendance records match the selected filters.</p>
    </div>
@endif
