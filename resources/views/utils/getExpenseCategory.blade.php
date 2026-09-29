<script>
    $(document).ready(function() {
        if ($('meta[name="company_id"]').attr('value') || $('.search_by_company').val()) {
            fetch_expenseCategory();
        }
    });

    $(document).on('change', '.team_person_select', function() {
        let filter_company = $('meta[name="company_id"]').attr('value') || $('.search_by_company').val();
        if ($.trim(filter_company || '') !== '') {
            fetch_expenseCategory();
        }
    });

    $(document).on('change', '.search_by_company', function() {
        fetch_expenseCategory();
    });

    $(document).on('change', '.search_by_branch', function() {
        fetch_expenseCategory();
    });

    function fetch_expenseCategory() {
        let instance = $('.search_by_company');
        let company_id = instance.val();

        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        let category_selects = $('#expense_category_id, #filter_expense_category, .search_by_expense_category, select[name="filter_expense_category"]');

        if (!company_id) {
            category_selects.each(function() {
                let isFilter = $(this).attr('id') === 'filter_expense_category' || $(this).hasClass('select_filter');
                let defaultOptionText = isFilter ? 'Filter by Expense Category' : 'Select Expense Category';
                $(this).html("<option value=''>" + defaultOptionText + "</option>");
                if ($(this).hasClass('select2')) {
                    $(this).select2();
                }
            });
            return;
        }

        let branch_id = $('.search_by_branch').val() || '';

        $.ajax({
            type: 'POST',
            url: '{{ env('API_URL') }}expense/category/list',
            beforeSend: function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            data: {
                company_id: company_id,
                branch_id: branch_id
            },
            success: function(response) {
                if (response.status) {
                    category_selects.each(function() {
                        let $select = $(this);
                        let isFilter = $select.attr('id') === 'filter_expense_category' || $select.hasClass('select_filter');
                        let defaultOptionText = isFilter ? 'Filter by Expense Category' : 'Select Expense Category';
                        let selectedCategoryId = $select.data("selectedcategoryid") || $select.val() || '';

                        let options = "<option value=''>" + defaultOptionText + "</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (selectedCategoryId == item.id) {
                                    options += "<option value='" + item.id + "' selected>" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                                }
                            });
                        }

                        $select.html(options);

                        if ($select.hasClass('select2')) {
                            $select.select2();
                        }
                    });
                }
            },
            error: function(err) {
                console.error("fetch_expenseCategory error:", err);
            }
        });
    }
</script>
