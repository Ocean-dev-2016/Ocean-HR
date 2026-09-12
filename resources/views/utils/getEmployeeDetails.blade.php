<script>
$(document).ready(function () {

    function fetch_employee_details() {
        let company_id    = $(".search_by_company").val() || $('meta[name="company_id"]').attr('value');
        let department_id = $(".search_by_department").val();

        // fallback to preselected data attribute if not found
        if (!department_id) {
            department_id = $('.search_by_department').attr("data-selecteddepartmentid") || '';
        }

        const instance    = $('.search_by_employeedetails');
        const selected_id = instance.attr("data-selectedemployeeid") || '';
        const is_select2  = instance.hasClass('select2');

        if (!company_id || !department_id) {
            instance.html("<option value=''>Select Employee</option>").trigger('change');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '{{ env('API_URL') }}get-employedetails',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { company_id, department_id },
            success: function (response) {
                let options = "<option value=''>Select Employee</option>";
                if (response.status && Array.isArray(response.data)) {
                    $.each(response.data, function (i, item) {
                        const selected = selected_id == item.id ? "selected" : "";
                        // Standardized format used: Surname Firstname Fathername (populated from server as item.full_name)
                        options += `<option value="${item.id}" ${selected}>${item.employee_code} - ${item.full_name}</option>`;
                    });
                }
                instance.html(options);

                // Initialize or refresh select2
                if (is_select2) {
                    if (instance.hasClass('select2-hidden-accessible')) {
                        instance.val(selected_id).trigger('change');
                    } else {
                        instance.select2();
                    }
                }
            },
            error: function (err) {
                console.error("Employee fetch failed", err);
            }
        });
    }

    // Trigger fetch on page load ONLY if company + department exist (Edit Mode)
    const company_val = $(".search_by_company").val() || $('meta[name="company_id"]').attr('value');
    const department_val = $(".search_by_department").val() || $('.search_by_department').attr("data-selecteddepartmentid");

    if (company_val && department_val) {
        fetch_employee_details();
    }

    // Trigger fetch on department change (Add/Edit)
    $(document).on('change', '.search_by_company, .search_by_department', function() {
        fetch_employee_details();
    });

});
</script>
