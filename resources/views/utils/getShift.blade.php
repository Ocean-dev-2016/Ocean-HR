<script>
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_shift();
    }

    $(document).on('change', '.search_by_company', function () {
        if ($(".search_by_company option:selected").val()) {
            fetch_shift();
        }
    });

    function fetch_shift() {
        let company_id = $(".search_by_company option:selected").val();
        const instance = $('.search_by_shift');
        const selected_id = instance.attr("data-selectedshiftid") || '';
        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');

        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        if (company_id) {
            $.ajax({
                type: 'POST',
                url: '{{ env("API_URL") }}get-shift',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { company_id },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Shift</option>";
                        $.each(response.data, function (i, item) {
                            const selected = selected_id+"" == item?.id+"" ? "selected" : "--";
                            options += `<option value="${item.id}" data-shift-id="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        
                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function (err) {
                    console.error("shift fetch failed", err);
                }
            });
        }
    }
</script>
