<script>
    let stateSearchRequest = null; // Declare globally, outside the event

    $(document).on('change', '.search_by_country', function() {
        // console.log("search_by_state L-5");

        let instance = $('.search_by_country');
        let country_id = instance.val();
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');
        let state_id = instance.attr("data-selectedStateId");
        let select_aall_option = $(".search_by_state").attr("data-show_select_all");
        if (!country_id) {
            country_id = instance.attr("data-selectedCountryId");
        }

        let state_ids = [];
        if (!state_id) {
            state_id = $(".search_by_state").attr("data-selectedStateId");
        }

        if (state_id) {
            state_ids = state_id.split(",");
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
                        // console.log("getState 30",response.data, country_id, state_id);
                        let options = "";
                        if (select_aall_option == "true") {
                            options = "<option value='all'>Select All State</option>";
                        } else {
                            options = "<option value=''>Select State</option>";
                        }
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                options += "<option value='" + item.id + "'";
                                if (state_ids.length == 0 && state_id == item.id) {
                                    options += " selected ";
                                } else if (state_ids.length > 0 && state_ids.includes(item
                                        ?.id)) {
                                    options += " selected ";
                                }
                                options += ">" + item.name + "</option>";

                            });
                        }
                        // console.log("getState 33",instance.parent(),);
                        if ($(".search_by_state").attr('data-append')) {
                            $(".search_by_state").attr('data-selectedCountryId', country_id);
                            if ($(document).find("." + $(".search_by_state").attr('data-append'))
                                .length > 0) {
                                $("." + $(".search_by_state").attr('data-append')).empty();
                                $("." + $(".search_by_state").attr('data-append')).append(options);
                            } else {
                                $("." + $(".search_by_state").attr('data-append')).empty();
                                $("." + $(".search_by_state").attr('data-append')).append(options);
                            }
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
                            // console.log("getState 39", instance, instance.parent(), instance.next("th"));
                            $(".search_by_country").removeClass('d-none').empty().append(options);
                            /*
                            // $(".search_by_country").remove();
                            // instance.parent().next("th").remove();
                            // instance.parent().after('<th>'+options+'</th>');
                            */
                        } else {
                            console.log("getState 82", instance.parent());
                            instance.parent().after(options);
                        }
                        $('.select2').select2();
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
</script>
