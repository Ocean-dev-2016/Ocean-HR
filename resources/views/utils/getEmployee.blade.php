<script>
    $(document).ready(function () {
        if ($('meta[name="company_id"]').attr('value')) {
            fetch_employee();
        }
    });

    $(document).on('change', '.search_by_company', function () {
        if ($(".search_by_company option:selected").val()) {
            fetch_employee();
        }
    });

    $(document).on('change', '.search_by_branch', function () {
        if ($(".search_by_branch option:selected").val()) {
            fetch_employee();
        } else if ($(".search_by_branch").attr('data-forceReload') && $(".search_by_branch").attr('data-forceReload') == "true") {
            fetch_employee();
        }

    });

    function fetch_employee() {
        let company_id = $(".search_by_company option:selected").val();
        let branch_id = $(".search_by_branch option:selected").val();
        const instance = $('.search_by_employee');
        const selected_id = instance.data("selectedemployeeid") || '';
        const is_multiple = instance.prop('multiple');
        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');
        // console.log("LN-18" , selected_id);
        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        let formJson = {};
        formJson = {
            ...formJson,
            company_id: company_id
        };

        if (branch_id) {
            formJson = {
                ...formJson,
                branch_id: branch_id
            };
        }

        const exclude_contractor = instance.data("exclude-contractor") || '';
        if (exclude_contractor) {
            formJson = {
                ...formJson,
                exclude_contractor: exclude_contractor
            };
        }

        if (company_id) {
            // Clear current options to indicate loading/change
            if (is_select2) {
                instance.html("<option value=''>Loading...</option>");
                // Avoid triggering a full table redraw during "Loading" state to prevent crashes
                // instance.trigger('change'); 

            } else {
                instance.html("<option value=''>Loading...</option>");
            }

            $.ajax({
                type: 'POST',
                url: '{{ url("api/get-employee") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formJson,
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Employee</option>";
                        let selectedIds = [];

                        // Handle multiple selection
                        if (is_multiple && selected_id) {
                            selectedIds = selected_id.toString().split(',').map(id => id.trim());
                        }

                        $.each(response.data, function (i, item) {
                            let selected = "";
                            if (is_multiple) {
                                selected = selectedIds.includes(item.id.toString()) ? "selected" : "";
                            } else {
                                selected = selected_id == item.id ? "selected" : "";
                            }
                            const shiftId = item.shift_id || '';
                            // Standardized format used: Surname Firstname Fathername (populated from server as item.full_name)
                            options += `<option value="${item.id}" data-shift-id="${shiftId}" ${selected}>${item.employee_code} - ${item.full_name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    } else {
                        instance.html("<option value=''>No Employees Found</option>");
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function (err) {
                    console.error("Employee fetch failed", err);
                    instance.html("<option value=''>Error loading employees</option>");
                    if (is_select2) {
                        instance.select2();
                    }
                }
            });
        }
    }
</script>