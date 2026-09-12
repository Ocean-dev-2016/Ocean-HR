{{-- Common Year and Month Filter Component --}}
@php
    // Default values
    $currentYear = $currentYear ?? date('Y');
    $currentMonth = $currentMonth ?? date('n');
    $months = $months ?? config('constants.months') ?? [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    
    // Configuration
    $yearId = $yearId ?? 'filter_year';
    $monthId = $monthId ?? 'filter_month';
    $yearName = $yearName ?? 'filter_year';
    $monthName = $monthName ?? 'filter_month';
    $yearLabel = $yearLabel ?? 'Filter by Year';
    $monthLabel = $monthLabel ?? 'Filter by Month';
    $yearRequired = $yearRequired ?? false;
    $monthRequired = $monthRequired ?? false;
    $yearSelected = $yearSelected ?? $currentYear;
    $monthSelected = $monthSelected ?? $currentMonth;
    $yearClass = $yearClass ?? 'form-control select2 select_filter';
    $monthClass = $monthClass ?? 'form-control select2 select_filter';
    $yearColClass = $yearColClass ?? 'col-md-2 col-sm-12 mb-2';
    $monthColClass = $monthColClass ?? 'col-md-2 col-sm-12 mb-2';
    $minYear = $minYear ?? 2020;
    $maxYear = $maxYear ?? date('Y');
    $useCustomMonthPicker = $useCustomMonthPicker ?? false;
@endphp

{{-- Year Filter --}}
<div class="{{ $yearColClass }}">
    <div class="form-group {{ $formGroupClass ?? '' }}">
        <label class="form-label {{ $labelClass ?? '' }}">
            {{ $yearLabel }}
            @if($yearRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
        <select id="{{ $yearId }}" name="{{ $yearName }}" class="{{ $yearClass }}" @if($yearRequired) required @endif>
            <option value="">@if($yearRequired) Select Year @else All Years @endif</option>
            @for ($y = $maxYear; $y >= $minYear; $y--)
                <option value="{{ $y }}" {{ ($yearSelected == $y) ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
    </div>
</div>

{{-- Month Filter --}}
@if($useCustomMonthPicker)
    {{-- Custom Month Picker (like attendance report) --}}
    <div class="{{ $monthColClass }}">
        <div class="form-group {{ $formGroupClass ?? '' }}">
            <label class="form-label {{ $labelClass ?? '' }}">
                {{ $monthLabel }}
                @if($monthRequired)
                    <span class="text-danger">*</span>
                @endif
            </label>
            <div class="custom-month-picker-wrapper">
                <input type="text" name="{{ $monthName }}" id="{{ $monthId }}_picker"
                    class="form-control table_filter month-picker-input" readonly
                    placeholder="Select Month">
                <input type="hidden" id="{{ $monthId }}_value" value="{{ date('Y-m', mktime(0, 0, 0, $monthSelected, 1, $currentYear)) }}">
                <div class="custom-month-picker-dropdown" id="{{ $monthId }}_dropdown">
                    <div class="month-picker-header">
                        <button type="button" class="month-picker-year-btn"
                            id="{{ $monthId }}_year_display">{{ $currentYear }}</button>
                    </div>
                    <div class="month-picker-grid" id="{{ $monthId }}_grid">
                        <button type="button" class="month-btn" data-month="01">Jan</button>
                        <button type="button" class="month-btn" data-month="02">Feb</button>
                        <button type="button" class="month-btn" data-month="03">Mar</button>
                        <button type="button" class="month-btn" data-month="04">Apr</button>
                        <button type="button" class="month-btn" data-month="05">May</button>
                        <button type="button" class="month-btn" data-month="06">Jun</button>
                        <button type="button" class="month-btn" data-month="07">Jul</button>
                        <button type="button" class="month-btn" data-month="08">Aug</button>
                        <button type="button" class="month-btn" data-month="09">Sep</button>
                        <button type="button" class="month-btn" data-month="10">Oct</button>
                        <button type="button" class="month-btn" data-month="11">Nov</button>
                        <button type="button" class="month-btn" data-month="12">Dec</button>
                    </div>
                    <div class="month-picker-footer">
                        <a href="javascript:void(0)" class="month-picker-link"
                            id="{{ $monthId }}_clear">Clear</a>
                        <a href="javascript:void(0)" class="month-picker-link"
                            id="{{ $monthId }}_this_month">This month</a>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted d-none" id="{{ $monthId }}_range_info"></small>
        </div>
    </div>
    
    {{-- JavaScript for Custom Month Picker --}}
    <script>
        $(function() {
            if (typeof moment === 'undefined') {
                console.error('Moment.js is required for custom month picker');
                return;
            }
            
            var currentDate = moment();
            var currentMonth = currentDate.format('YYYY-MM');
            var currentYear = currentDate.year();
            var currentMonthNum = currentDate.month() + 1;

            var minYear = {{ $minYear }};
            var maxYear = {{ $maxYear }} > 2025 ? 2025 : {{ $maxYear }};
            var maxMonth = {{ $maxYear }} > 2025 ? 12 : currentMonthNum;

            var selectedYear = currentYear;
            var selectedMonth = currentMonthNum;

            function updateMonthDisplay{{ $monthId }}() {
                if (selectedYear && selectedMonth) {
                    var monthMoment = moment(selectedYear + '-' + String(selectedMonth).padStart(2, '0') + '-01');
                    var displayText = monthMoment.format('MMMM, YYYY');
                    $('#{{ $monthId }}_picker').val(displayText);
                    $('#{{ $monthId }}_value').val(monthMoment.format('YYYY-MM'));
                }
            }

            function updateMonthPicker{{ $monthId }}() {
                $('#{{ $monthId }}_year_display').text(selectedYear);
                $('#{{ $monthId }}_grid .month-btn').each(function() {
                    var monthNum = parseInt($(this).data('month'));
                    var isDisabled = false;

                    if (selectedYear < minYear || selectedYear > maxYear || selectedYear > 2025) {
                        isDisabled = true;
                    } else if (selectedYear === maxYear && monthNum > maxMonth) {
                        isDisabled = true;
                    }

                    $(this).toggleClass('disabled', isDisabled);
                    
                    var storedValue = $('#{{ $monthId }}_value').val();
                    if (storedValue) {
                        var storedMoment = moment(storedValue + '-01');
                        $(this).toggleClass('selected', storedMoment.year() === selectedYear && storedMoment.month() + 1 === monthNum);
                    }
                });
            }

            updateMonthDisplay{{ $monthId }}();
            updateMonthPicker{{ $monthId }}();

            $('#{{ $monthId }}_picker').on('click', function(e) {
                e.stopPropagation();
                $('#{{ $monthId }}_dropdown').toggleClass('show');
                updateMonthPicker{{ $monthId }}();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.custom-month-picker-wrapper').length) {
                    $('#{{ $monthId }}_dropdown').removeClass('show');
                }
            });

            $('#{{ $monthId }}_year_display').on('click', function() {
                selectedYear++;
                if (selectedYear > maxYear) selectedYear = minYear;
                selectedMonth = null;
                updateMonthPicker{{ $monthId }}();
            });

            $(document).on('click', '#{{ $monthId }}_grid .month-btn:not(.disabled)', function() {
                selectedMonth = parseInt($(this).data('month'));
                updateMonthDisplay{{ $monthId }}();
                updateMonthPicker{{ $monthId }}();
                $('#{{ $monthId }}_dropdown').removeClass('show');
            });

            $('#{{ $monthId }}_clear').on('click', function() {
                $('#{{ $monthId }}_picker').val('');
                $('#{{ $monthId }}_value').val('');
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthPicker{{ $monthId }}();
            });

            $('#{{ $monthId }}_this_month').on('click', function() {
                selectedYear = currentYear;
                selectedMonth = currentMonthNum;
                updateMonthDisplay{{ $monthId }}();
                $('#{{ $monthId }}_dropdown').removeClass('show');
            });
        });
    </script>
@else
    {{-- Simple Month Dropdown --}}
    <div class="{{ $monthColClass }}">
        <div class="form-group {{ $formGroupClass ?? '' }}">
            <label class="form-label {{ $labelClass ?? '' }}">
                {{ $monthLabel }}
                @if($monthRequired)
                    <span class="text-danger">*</span>
                @endif
            </label>
            <select id="{{ $monthId }}" name="{{ $monthName }}" class="{{ $monthClass }}" @if($monthRequired) required @endif>
                @if($monthRequired) <option value="">Select Month</option> @endif
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}" {{ ($monthSelected == $num) ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endif
