<script>
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_loan_types();
    }

    $(document).on('change', '.search_by_company', function () {
        if ($(".search_by_company option:selected").val()) {
            fetch_loan_types();
        }
    });

    function fetch_loan_types() {
        let company_id = $(".search_by_company option:selected").val();
        const instance = $('.search_by_loan');
        const selected_id = instance.data("selectedloantypeid") || '';
        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');
        // console.log("LN-18" , selected_id);
        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        if (company_id) {
            $.ajax({
                type: 'POST',
                url: '{{ env("API_URL") }}get-loan-types',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { company_id },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Loan Type</option>";
                        $.each(response.data, function (i, item) {
                            const selected = selected_id == item.id ? "selected" : "";
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function (err) {
                    console.error("Loan Types fetch failed", err);
                }
            });
        }
    }
</script>
