<script>
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_shift();
    }

    $(document).on('change', '.search_by_company', function() {
        if ($(".search_by_company option:selected").val()) {
            fetch_shift();
        }
    });

    $(document).on('change', '.search_by_branch', function() {
        if ($(".search_by_branch option:selected").val()) {
            fetch_shift();
        }
    });

    function fetch_shift() {
        let company_id = $(".search_by_company option:selected").val();
        let branch_id = $(".search_by_branch option:selected").val();
        const instance = $('.search_by_shift');
        const selected_id = instance.data("selectedshiftid") || '';
        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');
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
                url: '{{ env('API_URL') }}get-shift',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formJson,
                success: function(response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Shift</option>";
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
                    console.error("Shifts fetch failed", err);
                }
            });
        }
    }
</script>
