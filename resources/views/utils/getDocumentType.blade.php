<script>
    if ($('meta[name="company_id"]').attr('value')) {
        fetch_documentType();
    }


    $(document).on('change', '.search_by_company', function() {
        fetch_documentType();
    });

    function fetch_documentType() {
        let instance = $('.search_by_company');
        let company_id = instance.val();

        if (!company_id && $('meta[name="company_id"]').attr('value')) {
            company_id = $('meta[name="company_id"]').attr('value');
        }

        if (!company_id) {
            $('.search_by_document_type_id').html("<option value=''>Select Document Type</option>");
            return;
        }

        $.ajax({
            type: 'POST',
            url: '{{ env('API_URL') }}get-document-type',
            beforeSend: function(request) {
                request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            data: { company_id: company_id },
            success: function(response) {
                if (response.status) {
                    let options = "<option value=''>Select Document Type</option>";

                    $.each(response.data, function(i, item) {
                        options += "<option value='" + item.id + "'>" + item.document_type_name + "</option>";
                    });

                    // Apply to all dropdowns with this class
                    $('.search_by_document_type_id').each(function() {
                        let selected = $(this).data('selecteddocumenttypeid');
                        let dropdownOptions = options;

                        // Preselect if needed
                        if (selected) {
                            dropdownOptions = "<option value=''>Select Document Type</option>";
                            $.each(response.data, function(i, item) {
                                if (selected == item.id) {
                                    dropdownOptions += "<option value='" + item.id + "' selected>" + item.document_type_name + "</option>";
                                } else {
                                    dropdownOptions += "<option value='" + item.id + "'>" + item.document_type_name + "</option>";
                                }
                            });
                        }

                        $(this).html(dropdownOptions);

                        if ($(this).hasClass('select2')) {
                            $(this).select2();
                        }
                    });
                }
            }
        });
    }



    $('.search_by_company').trigger("change");
</script>
