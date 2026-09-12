<script>
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_employee_type();
    }

    $(document).on('change', '.search_by_company', function() {
        if ($(".search_by_company option:selected").val()) {
            fetch_employee_type();
        }
    });

    $(document).on('change', '.search_by_branch', function() {
        if ($(".search_by_branch option:selected").val()) {
            fetch_employee_type();
        }
    });

    function fetch_employee_type() {
        let company_id = $(".search_by_company option:selected").val();
        let branch_id = $(".search_by_branch option:selected").val();
        const instance = $('.search_by_employee_type');
        const selected_id = instance.data("selectedemployeetypeid") || '';
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

        if (company_id) {
            $.ajax({
                type: 'POST',
                url: '{{ env('API_URL') }}get-employee-type',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formJson,
                success: function(response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Employee Type</option>";
                        $.each(response.data, function(i, item) {
                            const selected = selected_id == item.id ? "selected" : "";
                            options +=`<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function(err) {
                    console.error("Employee Type fetch failed", err);
                }
            });
        }
    }
</script>
