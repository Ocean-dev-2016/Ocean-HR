<script>
    // Run fetch only when department or subdepartment is selected
    $(document).on('change', '.search_by_company, .search_by_department, .search_by_subdepartment', function () {
        fetch_process();
    });

    function fetch_process() {
        const company_id = $(".search_by_company option:selected").val() || $('meta[name="company_id"]').attr('value');
        const department_id = $(".search_by_department option:selected").val();
        const sub_department_id = $(".search_by_subdepartment option:selected").val();

        const instance = $('.search_by_process');
        const selected_id = instance.data("selectedprocessid") || '';
        const is_select2 = instance.hasClass('select2');

        // 🧠 IMPORTANT:
        // Only fetch if a department OR subdepartment is selected
        if (!department_id && !sub_department_id) {
            // Clear old process options
            instance.html('<option value="">Select Process</option>');
            if (is_select2) instance.select2();
            return;
        }

        // Proceed only if company_id is available
        if (company_id) {
            $.ajax({
                type: 'POST',
                url: '{{ env("API_URL") }}get-process',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { company_id, department_id, sub_department_id },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Process</option>";
                        $.each(response.data, function (i, item) {
                            const selected = selected_id == item.id ? "selected" : "";
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                            instance.trigger("change");
                        }
                    }
                },
                error: function (err) {
                    console.error("Process fetch failed", err);
                }
            });
        }
    }
</script>
