<script>
    let requestToFetchBranch = null; // Declare globally, outside the event
    if ($('meta[name="branch_type"]').attr('value') && $('meta[name="branch_type"]').attr('value') == 'multiple') {
        fetch_branch();
        $(".branchDiv").show();
    }
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_branch();
    }

    $(document).on('change', '.search_by_company', function() {
        if ($(".search_by_company option:selected").val()) {
            fetch_branch();
        }
    });

    function fetch_branch() {
        let company_id = $(".search_by_company option:selected").val();
        const instance = $('.search_by_branch');
        const selected_id = instance.data("selectedbranchid") || '';
        const is_required = instance.attr('required');
        const is_select2 = instance.hasClass('select2');

        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }


        // Get branch_type from selected company option
        let branch_type = $(".search_by_company option:selected").attr('data-branch_type');
        if (!branch_type) {
            branch_type = $('meta[name="branch_type"]').attr('value');
        }

        // If branch_type is not "multiple", return directly without calling API
        if (branch_type && branch_type !== 'multiple') {
            // Clear branch dropdown and hide branch div
            instance.html("<option value=''>Select Branch</option>");
            if (is_select2) {
                instance.select2();
            }
            $(".branchDiv").hide();
            return;
        }


        // Only call API if branch_type is "multiple" or not set (for backward compatibility)
        if (company_id && branch_type && branch_type === 'multiple') {
            if (requestToFetchBranch !== null) {
                console.log("getBranch Previous request aborting");
                requestToFetchBranch.abort();
            }
            console.log("LN-39", company_id, branch_type);

            requestToFetchBranch = $.ajax({
                type: 'POST',
                url: '{{ env('API_URL') }}get-branch',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'company_id': company_id
                },
                success: function(response) {
                    if (response.status && Array.isArray(response.data)) {
                        let options = "<option value=''>Select Branch</option>";
                        $.each(response.data, function(i, item) {
                            const selected = selected_id == item.id ? "selected" : "";
                            options +=
                                `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });

                        instance.html(options);
                        if (is_select2) {
                            instance.select2();
                        }
                    }
                },
                error: function(err) {
                    console.error("Branch fetch failed", err);
                }
            });
        }
    }
</script>
