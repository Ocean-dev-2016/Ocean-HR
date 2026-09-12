<script>
    $(document).on('click', '.ajaxCall', function() {
        var _url = $(this).attr("data-url");
        var _confirm_message = $(this).attr("data-confirm_message");
        var _icon = $(this).attr("data-icon") ?? 'warning';
        var dtable_reload = $(this).attr("data-dtable") ?? 'false';

        Swal.fire({
            title: "Are you sure?",
            text: _confirm_message,
            icon: _icon,
            customClass: {
                confirmButton: 'btn btn-success waves-effect waves-light',
                cancelButton: "btn btn-danger waves-effect waves-light",
            },
            confirmButtonText: "Yes, Confirm",
            cancelButtonText: "No, cancel please!",
            showCancelButton: true,
            reverseButtons: true
        }).then((isConfirmed) => {
            var _token = '{{ csrf_token() }}';
            if (isConfirmed.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: _url,
                    data: {
                        _token: _token,
                    },
                    success: function(data) {

                        Swal.fire({
                            title: "Complete!",
                            icon: "success",
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light',
                            },
                        });
                        if (dtable_reload == 'true') {
                            // window.location.reload();
                            $("#yajra-datatables").DataTable().ajax.reload();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "Safe!",
                            text: "Something Went wrong deleted failed.",
                            icon: "error",
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light',
                            },
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: "Safe!",
                    text: "Data is safe :)",
                    icon: "error",
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light',
                    },
                });
            }
        });
    });
</script>
