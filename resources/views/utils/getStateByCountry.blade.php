<script>
    let stateSearchRequest = null; // Declare globally, outside the event

    $(document).on('change', '.search_by_country', function() {
        let instance = $('.search_by_country');
        let country_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');
        let state_id = instance.attr("data-selectedStateId");
        let select_aall_option = $(".search_by_state").attr("data-show_select_all");
        if (!country_id) {
            country_id = instance.attr("data-selectedCountryId");
        }

        if (!state_id) {
            state_id = $(".search_by_state").attr("data-selectedStateId");
        }
        if (!state_id) {
            state_id = $(".search_by_state").val();
        }

        let state_ids = [];
        if (state_id) {
            state_ids = String(state_id).split(",").map(function(s) {
                return String(s).trim();
            });
        }

        if (country_id) {
            // Abort previous request if any
            if (stateSearchRequest !== null) {
                stateSearchRequest.abort();
            }

            stateSearchRequest = $.ajax({
                type: 'POST',
                url: '{{ env('API_URL') }}get-state',
                'beforeSend': function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                        'content'));
                },
                data: {
                    country_id: country_id
                },
                success: function(response) {
                    if (response.status) {
                        let options = "";
                        if (select_aall_option == "true") {
                            options = "<option value='all'>Select All State</option>";
                        } else {
                            options = "<option value=''>Select State</option>";
                        }
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                let isSelected = false;
                                let itemIdStr = String(item.id);
                                if (state_id && (String(state_id) === itemIdStr || state_ids.includes(itemIdStr))) {
                                    isSelected = true;
                                }
                                options += "<option value='" + item.id + "'" + (isSelected ? " selected" : "") + ">" + item.name + "</option>";
                            });
                        }
                        if ($(".search_by_state").attr('data-append')) {
                            $(".search_by_state").attr('data-selectedCountryId', country_id);
                            let appendTarget = $("." + $(".search_by_state").attr('data-append'));
                            appendTarget.empty().append(options);
                        } else if (instance.parent().parent().hasClass("col-md-6") || instance
                            .parent().parent().hasClass("col-md-4") || instance.parent().parent()
                            .hasClass("col-md-3")) {

                            let select_tag =
                                '<select name="country_id" class="form-select search_by_country';
                            if (is_select2) {
                                select_tag += ' select2 ';
                            }
                            select_tag += '"';
                            if (is_required) {
                                select_tag += ' required ';
                            }
                            select_tag += '>' + options + '</select>';
                            let html = '';
                            if (instance.parent().parent().hasClass("col-md-4")) {
                                html = '<div class="col-md-4 country_div">';
                            } else if (instance.parent().parent().hasClass("col-md-3")) {
                                html = '<div class="col-md-3 country_div">';
                            } else {
                                html = '<div class="col-md-6 country_div">';
                            }
                            html +=
                                '<div class="form-group"> <label for="country_id">Select State ';
                            if (is_required) {
                                html += '<span class="text-danger">*</span>';
                            }
                            html += '</label>';
                            html += select_tag;
                            html += '</div></div>';
                            $(".country_div").remove();
                            instance.parent().parent().after(html);
                        } else if (instance.parent("tr")) {
                            $(".search_by_country").removeClass('d-none').empty().append(options);
                        } else {
                            instance.parent().after(options);
                        }
                        $('.select2').select2();

                        // If state has a selected value, trigger change to load city
                        if ($(".search_by_state").val()) {
                            $(".search_by_state").trigger("change");
                        }
                    }
                }
            });
        }
    });

    $(document).on("change", ".search_by_state", function(e) {
        const $select = $(this);
        let selectedValues = $select.val() || [];

        // Check if "all" was selected
        if (selectedValues.includes("all")) {
            // Get all option values except "all"
            const allValues = $select.find('option[value!="all"]').map(function() {
                return this.value;
            }).get();

            // Set select2 value to all values
            $select.val(allValues).trigger("change.select2");
        }
    });

    $(document).ready(function() {
        let country_id = $('.search_by_country').val() || $('.search_by_country').attr("data-selectedCountryId");
        let state_options = $('.search_by_state').find('option');
        if (country_id && state_options.length <= 1) {
            $('.search_by_country').trigger('change');
        }
    });
</script>
