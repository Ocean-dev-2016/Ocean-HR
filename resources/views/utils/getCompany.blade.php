{{--
===> Auto Select Option
* data-auto_select_option="true" ## false / true
* data-country_id
* data-state_id
* data-city_id
* data-companydata
--}}
<script>
    $(document).ready(function () {
        // console.log("search_by_company L-3");

        let instance = $('.search_by_company');
        let company_id = instance.val();
        let showBranchDiv = instance.attr('showBranch') || false;
        let is_required = instance.attr('required');
        let is_select2 = instance.hasClass('select2');

        if (!company_id) {
            company_id = instance.attr("data-selectedcompanyid");
        }

        console.log('getCompany.blade.php - company_id to select:', company_id, typeof company_id);

        $.ajax({
            type: 'POST',
            url: '{{ url("api/get-companies") }}',
            data: {
                full_detail: true
            },
            beforeSend: function (request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                    'content'));
            },
            success: function (response) {
                if (response.status) {
                    let options = "<option value=''>Select Company</option>";
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function (i, item) {
                            options += "<option value='" + item.id + "'";
                            if (item?.branch_type) {
                                options += " data-branch_type='" + item?.branch_type + "'";
                            }
                            if (item?.country_id) {
                                options += " data-country_id='" + item?.country_id + "'";
                            }
                            if (item?.state_id) {
                                options += " data-state_id='" + item?.state_id + "'";
                            }
                            if (item?.city_id) {
                                options += " data-city_id='" + item?.city_id + "'";
                            }
                            if (company_id && company_id == item?.id) {
                                options += " selected ";
                            }
                            if (item.branch_type != "single" && showBranchDiv != false) {
                                options += " showBranchDiv='branchDiv'";
                                // $(".branchDiv").show();
                            }

                            options += " data-companydata='" + JSON.stringify(item) + "'";

                            options += " >" + item.company_name + "</option>";
                        });
                    }

                    if (instance.attr('data-append')) {
                        if ($(document).find("." + instance.attr('data-append')).length > 0) {
                            $("." + instance.attr('data-append')).empty();
                            $("." + instance.attr('data-append')).append(options);
                        } else {
                            $("." + instance.attr('data-append')).empty();
                            $("." + instance.attr('data-append')).append(options);
                        }
                    } else if (instance.parent().parent().hasClass("col-md-6") || instance.parent()
                        .parent().hasClass("col-md-4") || instance.parent().parent().hasClass(
                            "col-md-3")) {
                        let select_tag =
                            '<select name="company_id" class="form-select search_by_company';
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
                            html = '<div class="col-md-4 company_div">';
                        } else if (instance.parent().parent().hasClass("col-md-3")) {
                            html = '<div class="col-md-3 company_div">';
                        } else {
                            html = '<div class="col-md-6 company_div">';
                        }
                        html += '<div class="form-group"><label for="company_id">Select Company ';
                        if (is_required) {
                            html += '<span class="text-danger">*</span>';
                        }
                        html += '</label>';
                        html += select_tag;
                        html += '</div></div>';

                        $(".company_div").remove();
                        instance.parent().parent().after(html);
                    } else if (instance.parent("tr")) {
                        $(".search_by_company").removeClass('d-none').empty().append(options);
                    } else {
                        instance.parent().after(options);
                    }
                    filter_company = $('select[name="company_id"] option:selected').val();
                    if ($.trim(filter_company || '') !== '') {
                        $('.search_by_company').trigger("change");
                    }

                    $('.select2').select2();
                }
            },
            error: function (err) {
                console.error("Company fetch failed", err);
            }
        });
    });




    // Removed redundant fetch_employee call; getEmployee.blade.php handles its own initialization
    /* 
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_employee();
    }
    */


    $(document).on('change', '.search_by_company', function () {
        let companyInstance = $(".search_by_company option:selected");
        let companyData = companyInstance.attr("data-companydata");
        $(".branchDiv").hide();
        if (companyInstance.attr('showbranchdiv') == 'branchDiv') {
            $("." + companyInstance.attr('showbranchdiv')).show();
        }
        // if (companyInstance.attr('data-branch_type') == 'multiple') {
        //     $("." + companyInstance.attr('showbranchdiv')).show();
        // }

        if ($(".search_by_company").attr("data-auto_select_option") == "true") {
            if (companyInstance.attr('data-country_id') && ($(".search_by_country option:selected").attr(
                "data-selectedcountryid") == null || $(".search_by_country option:selected").attr(
                    "data-selectedcountryid") != undefined)) {
                $(".search_by_country").attr("data-selectedcountryid", companyInstance.attr('data-country_id'));
                $(".search_by_country").attr("data-selectedstateid", companyInstance.attr('data-state_id'));
                $(".search_by_country").val(companyInstance.attr("data-country_id")).trigger("change");
            }
            if (companyInstance.attr('data-state_id') && ($(".search_by_state option:selected").attr(
                "data-selectedcountryid") == null || $(".search_by_state option:selected").attr(
                    "data-selectedstateid") != undefined)) {
                $(".search_by_state").attr("data-selectedstateid", companyInstance.attr('data-state_id'));
                $('.search_by_state').trigger("change");
            }
        }
    });
</script>