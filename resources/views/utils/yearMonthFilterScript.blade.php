{{-- Common JavaScript for Custom Month Picker --}}
{{-- Include this when useCustomMonthPicker is true --}}
@php
    $monthId = $monthId ?? 'filter_month';
    $minYear = $minYear ?? date('Y') - 2;
    $maxYear = $maxYear ?? date('Y');
@endphp

<script>
    // Custom Month Picker Implementation for {{ $monthId }}
    $(function() {
        var currentDate = moment();
        var currentMonth = currentDate.format('YYYY-MM');
        var currentYear = currentDate.year();
        var currentMonthNum = currentDate.month() + 1;

        // Calculate restrictions
        var minYear = {{ $minYear }};
        var maxYear = {{ $maxYear }} > 2025 ? 2025 : {{ $maxYear }};
        var maxMonth = {{ $maxYear }} > 2025 ? 12 : currentMonthNum;

        var selectedYear = currentYear;
        var selectedMonth = currentMonthNum;

        // Initialize display
        updateMonthDisplay{{ $monthId }}();
        updateMonthPicker{{ $monthId }}();

        // Toggle dropdown
        $('#{{ $monthId }}_picker').on('click', function(e) {
            e.stopPropagation();
            $('#{{ $monthId }}_dropdown').toggleClass('show');
            updateMonthPicker{{ $monthId }}();
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-month-picker-wrapper').length) {
                $('#{{ $monthId }}_dropdown').removeClass('show');
            }
        });

        // Year navigation
        $('#{{ $monthId }}_year_display').on('click', function() {
            selectedYear++;
            if (selectedYear > maxYear) {
                selectedYear = minYear;
            }
            selectedMonth = null;
            updateMonthPicker{{ $monthId }}();
        });

        // Month selection
        $(document).on('click', '#{{ $monthId }}_grid .month-btn:not(.disabled)', function() {
            selectedMonth = parseInt($(this).data('month'));
            updateMonthDisplay{{ $monthId }}();
            updateMonthPicker{{ $monthId }}();
            $('#{{ $monthId }}_dropdown').removeClass('show');
        });

        // Clear button
        $('#{{ $monthId }}_clear').on('click', function() {
            $('#{{ $monthId }}_picker').val('');
            $('#{{ $monthId }}_value').val('');
            selectedYear = currentYear;
            selectedMonth = currentMonthNum;
            updateMonthPicker{{ $monthId }}();
        });

        // This month button
        $('#{{ $monthId }}_this_month').on('click', function() {
            selectedYear = currentYear;
            selectedMonth = currentMonthNum;
            updateMonthDisplay{{ $monthId }}();
            $('#{{ $monthId }}_dropdown').removeClass('show');
        });

        // Update month picker display
        function updateMonthPicker{{ $monthId }}() {
            $('#{{ $monthId }}_year_display').text(selectedYear);

            $('#{{ $monthId }}_grid .month-btn').each(function() {
                var monthNum = parseInt($(this).data('month'));
                var isDisabled = false;

                if (selectedYear < minYear) {
                    isDisabled = true;
                } else if (selectedYear === minYear) {
                    var minMonth = moment().subtract(2, 'years').month() + 1;
                    if (monthNum < minMonth) {
                        isDisabled = true;
                    }
                }

                if (selectedYear > maxYear) {
                    isDisabled = true;
                } else if (selectedYear === maxYear) {
                    if (monthNum > maxMonth) {
                        isDisabled = true;
                    }
                }

                if (selectedYear > 2025) {
                    isDisabled = true;
                }

                if (isDisabled) {
                    $(this).addClass('disabled');
                } else {
                    $(this).removeClass('disabled');
                }

                var storedValue = $('#{{ $monthId }}_value').val();
                if (storedValue) {
                    var storedMoment = moment(storedValue + '-01');
                    if (storedMoment.year() === selectedYear && storedMoment.month() + 1 === monthNum) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                } else {
                    $(this).removeClass('selected');
                }
            });
        }

        // Update input display
        function updateMonthDisplay{{ $monthId }}() {
            if (selectedYear && selectedMonth) {
                var monthMoment = moment(selectedYear + '-' + String(selectedMonth).padStart(2, '0') + '-01');
                var displayText = monthMoment.format('MMMM, YYYY');
                $('#{{ $monthId }}_picker').val(displayText);
                $('#{{ $monthId }}_value').val(monthMoment.format('YYYY-MM'));
                updateMonthRangeInfo{{ $monthId }}();
            }
        }

        // Update month range info
        function updateMonthRangeInfo{{ $monthId }}() {
            var monthValue = $('#{{ $monthId }}_value').val();
            if (monthValue) {
                var startDate = moment(monthValue + '-01').format('DD/MM/YYYY');
                var endDate = moment(monthValue + '-01').endOf('month').format('DD/MM/YYYY');
                var monthName = moment(monthValue + '-01').format('MMMM YYYY');
                $('#{{ $monthId }}_range_info').text('Range: ' + startDate + ' to ' + endDate + ' (' + monthName + ')');
            } else {
                $('#{{ $monthId }}_range_info').text('');
            }
        }
    });
</script>
