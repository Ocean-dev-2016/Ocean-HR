<script>
    if ($('meta[name="company_id"]').attr('value') && $('meta[name="department_id"]').attr('value')) {
        fetch_subdepartment();
    }

    $(document).on('change', '.search_by_company, .search_by_department', function() {
        let company_id = $(".search_by_company option:selected").val();
        let department_id = $(".search_by_department option:selected").val();

        // if not found, fallback to subdepartment attribute
        if (!department_id) {
            const instance = $('.search_by_subdepartment');
            department_id = instance.attr("data-selectedsubdepartmentid") || '';
        }

        if (department_id) {
            fetch_subdepartment();
        }
        console.log("LN-23",  department_id);
    });

    function fetch_subdepartment() {
        let company_id = $(".search_by_company option:selected").val();


        const instance = $('.search_by_subdepartment');
        const selected_id = instance.attr("data-selectedsubdepartmentid") || '';

        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');

        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }
        let department_id = $(".search_by_department option:selected").val();

     
        if (!department_id) {
            const instance_main = $('.search_by_department');
            department_id = instance_main.attr("data-selecteddepartmentid") || '';
        }


        if (company_id && department_id) {
            $.ajax({
                type: 'POST',
                url: '{{ env('API_URL') }}get-subdepartment',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    company_id: company_id,
                    department_id: department_id
                },
                success: function(response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Sub Department</option>";
                        $.each(response.data, function(i, item) {
                            const selected = selected_id == item.id ? "selected" : "";
                            options +=
                                `<option value="${item.id}" ${selected}>${item.sub_department_name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function(err) {
                    console.error("Sub Department fetch failed", err);
                }
            });
        }
    }
</script>
