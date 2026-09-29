<script>
    $(document).on('change', '.search_by_company, .search_by_branch', function() {
        let company_id = $('.search_by_company').val();
        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        let category_selects = $('#expense_category_id, #filter_expense_category');

        if (!company_id) {
            category_selects.each(function() {
                let isFilter = $(this).attr('id') === 'filter_expense_category' || $(this).hasClass('select_filter');
                $(this).html("<option value=''>" + (isFilter ? 'Filter by Expense Category' : 'Select Expense Category') + "</option>");
                if ($(this).hasClass('select2')) { $(this).select2(); }
            });
            $('#expense_subcategory_id, #filter_expense_subcategory').each(function() {
                let isFilter = $(this).attr('id') === 'filter_expense_subcategory' || $(this).hasClass('select_filter');
                $(this).html("<option value=''>" + (isFilter ? 'Filter by Expense SubCategory' : 'Select Expense SubCategory') + "</option>");
                if ($(this).hasClass('select2')) { $(this).select2(); }
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
                        let $cat = $(this);
                        let isFilter = $cat.attr('id') === 'filter_expense_category' || $cat.hasClass('select_filter');
                        let defaultText = isFilter ? 'Filter by Expense Category' : 'Select Expense Category';
                        let selectedCategoryId = $cat.data("selectedcategoryid") || $cat.val() || '';

                        let options = "<option value=''>" + defaultText + "</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (selectedCategoryId == item.id) {
                                    options += "<option value='" + item.id + "' selected>" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                                }
                            });
                        }
                        $cat.html(options);

                        if (selectedCategoryId) {
                            $cat.val(selectedCategoryId).trigger("change");
                        }

                        if ($cat.hasClass('select2')) {
                            $cat.select2();
                        }
                    });
                }
            }
        });
    });

    $(document).on('change', '#expense_category_id, #filter_expense_category', function() {
        let expense_category_id = $(this).val();
        let company_id = $('.search_by_company').val();
        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        let subcat_selects = $('#expense_subcategory_id, #filter_expense_subcategory');

        if (!expense_category_id) {
            subcat_selects.each(function() {
                let isFilter = $(this).attr('id') === 'filter_expense_subcategory' || $(this).hasClass('select_filter');
                $(this).html("<option value=''>" + (isFilter ? 'Filter by Expense SubCategory' : 'Select Expense SubCategory') + "</option>");
                if ($(this).hasClass('select2')) { $(this).select2(); }
            });
            return;
        }

        let branch_id = $('.search_by_branch').val() || '';
        let team_person_id = $('#team_person_id, #filter_team_person').val() || '';

        $.ajax({
            type: 'POST',
            url: '{{ env('API_URL') }}expense/sub-category/list',
            beforeSend: function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            data: {
                expense_category_id: expense_category_id,
                company_id: company_id,
                branch_id: branch_id,
                team_person_id: team_person_id
            },
            success: function(response) {
                if (response.status) {
                    subcat_selects.each(function() {
                        let $sub = $(this);
                        let isFilter = $sub.attr('id') === 'filter_expense_subcategory' || $sub.hasClass('select_filter');
                        let defaultText = isFilter ? 'Filter by Expense SubCategory' : 'Select Expense SubCategory';
                        let selectedSubCategoryId = $sub.data("selectedsubcategoryid") || $sub.val() || '';

                        let options = "<option value=''>" + defaultText + "</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (selectedSubCategoryId == item.id) {
                                    options += "<option value='" + item.id + "' selected>" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                                }
                            });
                        }
                        $sub.html(options);

                        if (selectedSubCategoryId) {
                            $sub.val(selectedSubCategoryId).trigger("change");
                        }

                        if ($sub.hasClass('select2')) {
                            $sub.select2();
                        }
                    });
                }
            }
        });
    });

    $(document).on('change', '.search_by_branch', function() {
        if ($('#expense_category_id').val() || $('#filter_expense_category').val()) {
            $('#expense_category_id, #filter_expense_category').trigger("change");
        }
    });

    $(document).on('change', '#team_person_id, #filter_team_person', function() {
        if ($('#expense_category_id').val() || $('#filter_expense_category').val()) {
            $('#expense_category_id, #filter_expense_category').trigger("change");
        }
    });

    // Trigger initial load
    $(document).ready(function() {
        if ($('.search_by_company').val() || $('meta[name="company_id"]').attr('value')) {
            $('.search_by_company').trigger("change");
        }
    });
</script>
