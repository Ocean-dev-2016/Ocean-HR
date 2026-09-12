<!doctype html>

<html lang="en" class="light-style layout-wide customizer-hide layout-navbar-fixed layout-menu-fixed " dir="ltr"
    data-theme="theme-default" data-bs-theme="light" data-assets-path="{{ asset('/') }}"
    data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title> @yield('title') | {{ env('APP_NAME', 'Ocean Infotech') }}</title>

    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="currentGuard" value="{{ isset($currentGuard) && !empty($currentGuard) ? $currentGuard : '' }}" />
    {{-- When Companny id foudn in admin time set using yield --}}
    @if (trim($__env->yieldContent('company_id')))
        <meta name="company_id" value="{{ trim($__env->yieldContent('company_id')) ?? '' }}" />
    @elseif (isset($modules['company_id']) && !empty($modules['company_id']))
        <meta name="company_id" value="{{ $modules['company_id'] ?? '' }}" />
    @else
        <meta name="company_id" value="{{ $authenticateUserDetails?->company_id ?? '' }}" />
    @endif

    @php
        $metaBranchType = isset($branch_type) && !empty($branch_type) ? $branch_type : null;
        if (empty($metaBranchType)) {
            // Try to get company_id from various sources
            $metaCompanyId = trim($__env->yieldContent('company_id'))
                ?: ($modules['company_id'] ?? $authenticateUserDetails?->company_id ?? null);

            if ($metaCompanyId) {
                $metaCompany = \App\Models\Company::find($metaCompanyId);
                $metaBranchType = $metaCompany?->branch_type;
            }
        }
    @endphp
    <meta name="branch_type" value="{{ $metaBranchType ?? '' }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('software/img/ring.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('software/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('software/vendor/css/rtl/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('software/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    {{--
    <link rel="stylesheet" href="{{ asset('software/css/demo.css') }}" /> --}}

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <style>
        /* Global Toastr Fix & Bootstrap 5 Conflict Prevention */
        #toast-container {
            z-index: 999999 !important;
        }
        #toast-container > div.toast {
            opacity: 1 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
            border-radius: 12px !important;
            padding: 14px 20px 14px 50px !important;
            min-width: 320px !important;
            max-width: 480px !important;
            width: auto !important;
            display: block !important;
            overflow: visible !important;
            border: none !important;
        }
        #toast-container > div.toast-error {
            background-color: #ea5455 !important;
            color: #ffffff !important;
        }
        #toast-container > div.toast-success {
            background-color: #28c76f !important;
            color: #ffffff !important;
        }
        #toast-container > div.toast-info {
            background-color: #00cfdd !important;
            color: #ffffff !important;
        }
        #toast-container > div.toast-warning {
            background-color: #ff9f43 !important;
            color: #ffffff !important;
        }
        #toast-container div.toast-message {
            color: #ffffff !important;
            font-size: 0.925rem !important;
            font-weight: 600 !important;
            line-height: 1.4 !important;
            display: block !important;
            word-break: break-word !important;
        }
        #toast-container div.toast-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            margin-bottom: 4px !important;
            display: block !important;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('software/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/apex-charts/apex-charts.css') }}" />



    <link rel="stylesheet" href="{{ asset('software/vendor/libs/select2/select2.css') }}" />

    {{--
    <link rel="stylesheet"
        href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" /> --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <!-- Daterangepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">


    <!-- Page Leavel -->
    @yield('page_leavel_style')

    @stack('push_style')

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('software/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    {{--
    <script src="{{ asset('software/vendor/js/template-customizer.js') }}"></script> --}}
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('software/js/config.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('software/custom/custom.css') }}" />
    <style>
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #f8f7fa;
            /* border-top: 1px solid #e0e0e0; */
            font-size: 13px;
            z-index: 1000;
        }

        .footer a {
            text-decoration: none;
        }
    </style>
</head>

<body style="--bs-scrollbar-width: 0px;">
    @if (!Auth::guard('admin_software')?->check() && !Auth::guard('employees')?->check())
        @yield('content')
    @else
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                @include('software.inlcudes.sidebar')

                <!-- Layout container -->
                <div class="layout-page">
                    <!-- Navbar -->
                    @include('software.inlcudes.navbar')
                    <!-- / Navbar -->

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        <!-- Content -->
                        <div class="container-fuild flex-grow-1 container-p-y container-p-x">
                            @yield('content')
                        </div>
                    </div>
                </div>
                <!-- / Layout page -->

                <!-- Overlay -->
                <div class="layout-overlay layout-menu-toggle"></div>
                {{-- Global Loader removed - using inline loaders instead --}}



                <!-- Loader Wrapper -->
                {{-- <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                    background: rgba(255, 255, 255, 0.7); z-index: 9999; display: flex;
                    align-items: center; justify-content: center;">

                    <!-- Loader Content -->
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">

                        <!-- 3 Bouncing Dots -->
                        <div class="dots-loader">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div> --}}

                <!-- Loader Styles -->


                <!-- Drag Target Area To SlideIn Menu On Small Screens -->
                <div class="drag-target"></div>
            </div>
        </div>
        <footer class="footer text-center py-3">
            <p class="mb-0 text-muted">
                © 2025
                {{ $authenticateUserDetails->company?->company_name ?? 'Ocean Infotech' }}. All Rights Reserved. |
                Developed by
                <a href="https://oceaninfotechcrm.com/" target="_blank" class="fw-bold text-primary">
                    Ocean Infotech
                </a>
            </p>
        </footer>
    @endif

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <!----- punchIn / punch out code start -->
    <div id="navigator_location_info" data-navigator-location-info=""></div>
    <div id="navigator_browser_info" data-navigator-browser-info=""></div>

    <div class="buy-now">
        @if (isset($punchType) && !empty($punchType) && ($punchType == 'in' || $punchType == 'out') && $mainAttendanceAddBtn)
            @php $punchText = $punchType == 'in' ? 'Punch In' : 'Punch Out'; @endphp
            <a class="btn btn-primary btn-buy-now-primary waves-effect waves-light btn-lg"
                data-punch-type="{{ $punchType }}" data-punch-text="{{ $punchText }}" id="punch_in_out_btn"
                href="javascript:;">
                <span class="d-none d-lg-inline punch_txt_dv">{{ $punchText }}</span>
            </a>
        @endif
    </div>
    <!----- punchIn / punch out code end -->

    <!---plan expired modal display before 10 days ago start-->
    @if (
            session('show_plan_expiry_modal') &&
            isset($is_plan_expired) &&
            $is_plan_expired == true &&
            isset($mainPlanExpiryDate) &&
            !empty($mainPlanExpiryDate)
        )
        <!-- Trigger Button -->
        <script>
            window.onload = function () {
                // $('#planExpiryModal').modal('show');
            };
        </script>

        <!-- Modal -->
        <div class="modal fade" id="planExpiryModal" tabindex="-1" aria-labelledby="planExpiryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalCenterTitle" style="color: white;">Plan Expiry Alert</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Your current plan is expiring soon!</strong></p>
                        <p><strong>Plan Name: {{ $mainPlanName ?? '' }}</strong></p>
                        <p><strong>Expiry Date:
                                {{ \Carbon\Carbon::parse($mainPlanExpiryDate)->format('d-m-Y') }}</strong></p>
                        <p>Please renew your plan to continue using all features without interruption.</p>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

        @php
            session()->forget('show_plan_expiry_modal');
        @endphp
    @endif
    <!---plan expired modal display before 10 days ago end-->

    <script src="{{ asset('software/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
    <script src="{{ asset('software/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('software/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('software/vendor/js/menu.js') }}"></script>

    <script src="{{ asset('software/vendor/libs/select2/select2.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    {{--
    <script src="{{ asset('software/vendor/libs/@form-validation/popular.js') }}"></script> --}}
    {{--
    <script src="{{ asset('software/vendor/libs/@form-validation/bootstrap5.js') }}"></script> --}}
    {{--
    <script src="{{ asset('software/vendor/libs/@form-validation/auto-focus.js') }}"></script> --}}
    <script src="{{ asset('software/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('software/js/forms-selects.js') }} "></script>
    <script src="{{ asset('software/vendor/libs/apex-charts/apexcharts.js') }} "></script>
    <script src="{{ asset('software/vendor/libs/chartjs/chartjs.js') }} "></script>

    {{--
    <script src="{{ asset('software/js/dashboards-crm.js') }} "></script> --}}
    {{--
    <script src="{{ asset('software/js/charts-chartjs.js') }} "></script> --}}

    {{--
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script> --}}
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    <!-- Daterangepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{--
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script> --}}

    <script src="https://cdn.ckeditor.com/ckeditor5/35.3.2/super-build/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>

    <script>
        $('.select2').select2();
        let punchinout_url = "{{ route('permissions.punchinout') }}";
        let dashboard_url = "{{ route('software.dashboard') }}";
    </script>

    @yield('page_leavel_script')

    <!-- Main JS -->
    <script src="{{ asset('software/js/main.js') }}"></script>

    <script src="{{ asset('software/custom/custom.js') }}?v=1.1"></script>
    <!-- Page JS -->
    <script>
        $(function () {
            if ($('.my_daterangepicker').length > 0) {
                // var start = moment(); // Today
                var start = moment().startOf('month'); // Today
                var end = moment().endOf('month'); // Today
                var endOfYear = new Date();

                var future_date = $('.my_daterangepicker').attr("data-future-date");
                if (future_date == "yes") {
                    var endOfYear = moment().endOf('year');
                }

                $('.my_daterangepicker').daterangepicker({
                    startDate: start,
                    endDate: end,
                    maxDate: endOfYear,
                    autoUpdateInput: true, // Automatically fills the input field
                    locale: {
                        format: 'DD/MM/YYYY'
                    },
                    ranges: {
                        'Today': [moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    }
                }, function (start, end, label) {
                    $('.my_daterangepicker').val(start.format('DD/MM/YYYY') + ' to ' + end.format(
                        'DD/MM/YYYY'));

                    // Optional: do something based on label
                    // console.log("Selected range: " + label, start.format('DD/MM/YYYY'), end.format(
                    //     'DD/MM/YYYY'), $(this), $('.my_daterangepicker'));
                });

                // Set default value in input
                // $('.my_daterangepicker').val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
            } else {
                // console.log('❌ my_daterangepicker does NOT exist.');
            }

        });

        /** Date Picker */
        if ($('.datepicker').length > 0) {

            $(".datepicker").datepicker({
                dateFormat: 'dd/mm/yy', // Set format to 06/06/2025
                defaultDate: new Date(), // Pre-select today's date
                /*
                showButtonPanel: true,
                beforeShow: function(input) {
                    setTimeout(function() {
                        var buttonPane = $(input)
                            .datepicker("widget")
                            .find(".ui-datepicker-buttonpane");

                            var btn = $(
                                '<button type="button" class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all">Clear</button>'
                            );
                            btn.unbind("click").bind("click", function() {
                                $.datepicker._clearDate(input);
                            });

                            btn.appendTo(buttonPane);
                    }, 1);
                }
                    */
            });

            // If input is empty, set default date to today
            $(".datepicker").each(function () {
                if (!$(this).val()) {
                    $(this).datepicker("setDate", new Date());
                }
            });

            // Convert value from yyyy-mm-dd to dd/mm/yyyy for datepicker inputs
            $(".datepicker").each(function () {
                const val = $(this).val();
                if (val.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const [year, month, day] = val.split("-");
                    $(this).val(`${day}/${month}/${year}`);
                }
            });
        }

        $('.select2').select2();
    </script>
    <script>
        $(document).ready(function () {

            // Initialize date range picker
            function initDateRangePicker(selector) {
                if ($(selector).length > 0) {
                    $(selector).daterangepicker({
                        autoUpdateInput: false,
                        locale: {
                            format: 'DD/MM/YYYY',
                            cancelLabel: 'Clear'
                        },
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                                .subtract(1, 'month').endOf('month')
                            ]
                        }
                    }, function (start, end) {
                        $(selector).val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
                        dtable.draw(); // redraw table immediately when date is selected
                    });

                    $(selector).on('cancel.daterangepicker', function () {
                        $(this).val('');
                        dtable.draw(); // redraw table on clear
                    });
                }
            }

            initDateRangePicker('.employee_daterangepicker');


            $("#cilory_filter").click(function () {
                // Clear all text inputs
                $('.search, .employee_daterangepicker').val('');

                // Reset all select2 dropdowns to default
                $('.select_filter').val('').trigger('change'); // this resets dropdowns to placeholder

                // Reset status dropdown separately if needed
                $('#status_filter').val('all').trigger('change');

                // Redraw your datatable if you have one
                if (typeof dtable !== 'undefined') {
                    dtable.draw();
                }
            });
            // // Filter change triggers DataTable redraw
            // $('.table_filter, .select_filter').on('change', function() {
            //     dtable.draw();
            // });
            // $('input[name="search"]').keyup(function() {
            //     dtable.draw();
            // });
            // $('#status_filter').on('change', function() {
            //     dtable.draw();
            // });


        });
    </script>

    <script>
        // $(document).ready(function() {
        //     if ($('#yajra-datatables').length > 0) {


        //         $(document).on('preXhr.dt', function(e, settings, data) {
        //             $('#loader').show();
        //         });

        //         $(document).on('xhr.dt', function(e, settings, json, xhr) {
        //             $('#loader').hide();
        //         });

        //     } else {
        //         $('#loader').hide();
        //     }
        // });
    </script>
    {{-- Global AJAX loader removed - using inline loaders instead --}}

    {{-- Include Inline Loader Utility --}}
    @include('utils.inline-loader')

    @stack('page_scripts')
    @include('utils.getBranch')
    {{-- Filtte Button --}}
    <script>
        $(document).ready(function () {
            // Check if filter_show element exists and has data-filter="true"
            if ($('#filter_show').length && $('#filter_show').data('filter') === true) {
                $('#filter_section').show();
            } else {
                $('#filter_section').hide();
            }

            $("#show_filter").click(function () {
                if ($('#filter_section').is(':hidden')) {
                    $('#filter_section').show();
                } else {
                    $('#filter_section').hide();
                }
            });
        });
    </script>
    @include('utils.toastr')
    @include('utils.updatePunchInOut')
    <script>
        function loadNotifications() {
            return;
            const dropdown = document.getElementById('notif-dropdown');
            dropdown.classList.toggle('hidden');

            $.ajax({
                url: "{{ route('notification.get_notification') }}",
                type: "POST",
                datatype: 'json',
                global: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status == true) {
                        const list = document.getElementById('notif-list');
                        list.innerHTML = '';

                        var resData = response.data;
                        if (resData && resData.length > 0) {
                            resData.forEach(notif => {

                                const hrefUrl = notif.module_url && notif.module_url !== '' ? notif
                                    .module_url : '#';
                                list.innerHTML += `<a href="${hrefUrl}" target="_blank" class="list-group-item list-group-item-action d-flex gap-3 align-items-start border-0">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="tf-icons ${notif.menu_icon}" style="margin-right: 0.3rem;font-size: 1.375rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 fw-semibold text-dark">${notif.title}</h6>
                                        <small class="text-muted">${notif.created_at}</small>
                                    </div>
                                    <div class="text-muted small">
                                        ${notif.body}
                                    </div>
                                </div>
                            </a>`;
                            });
                            document.getElementById('notif-count').innerText = resData.length;
                        } else {
                            list.innerHTML += '<div class="text-center text-muted py-3">No notifications</div>';
                        }

                    }
                }
            });
        }

        $(document).on('click', '#notif-mark-read', function (event) {
            event.stopPropagation();
            $.ajax({
                url: "{{ route('notification.clear') }}",
                type: "POST",
                datatype: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status == true) {
                        const list = document.getElementById('notif-list');
                        list.innerHTML =
                            '<div class="text-center text-muted py-3">No notifications</div>';

                        $("#notif-count").html('');
                    }
                }
            });
        });

        $(document).ready(function () {
            loadNotifications();
            setInterval(loadNotifications, 120000); // 2 minutes
        });
        document.getElementById('notif-bell').addEventListener('click', loadNotifications);
    </script>
    <script>
        const userIsLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        // if(userIsLoggedIn == true && ($('meta[name="currentGuard"]').attr('value') == 'employee') && ($('meta[name="parent_type_id"]').attr('value') != '')){
        //     var locationData = $('#navigator_location_info').attr("data-navigator-location-info");
        //     var parsedLocation = locationData ? JSON.parse(locationData) : {};

        //     if (!parsedLocation.latitude || !parsedLocation.longitude) {
        //         $('body').css({
        //             'opacity': '0.5',
        //             'pointer-events': 'none',
        //         });
        //     }
        // }
    </script>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>

    <script>
        const firebaseConfig = @json($firebaseConfig);
        firebase.initializeApp(firebaseConfig);

        const messaging = firebase.messaging();

        function startFCM() {
            navigator.serviceWorker.register("/firebase-messaging-sw.js")
                .then((registration) => {
                    messaging.useServiceWorker(registration);

                    messaging.requestPermission()
                        .then(() => messaging.getToken({
                            vapidKey: firebaseConfig.vapidKey
                        }))
                        .then((token) => {
                            const storedToken = localStorage.getItem('fcm_token');
                            if (storedToken !== token) {
                                // Token is new or updated
                                fetch('{{ route('store.token') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        token: token
                                    })
                                }).then(() => {
                                    localStorage.setItem('fcm_token', token);
                                    console.log('FCM Token stored.');
                                }).catch((err) => {
                                    console.error('Error storing FCM token:', err);
                                });
                            }
                        })
                        .catch((err) => {
                            console.error('Permission denied or error:', err);
                        });
                })
                .catch((err) => {
                    console.error('Service worker registration failed:', err);
                });
        }

        // Start only once after login
        if (!localStorage.getItem('fcm_token')) {
            if (userIsLoggedIn == true) {
                startFCM();
            }
        }

        // $(document).on('click', '.logout-btn', function (event) {
        //     event.preventDefault();
        //     localStorage.removeItem('fcm_token');
        //     $('#logout-form').submit();
        // });


        // Display notification when message is received
        messaging.onMessage((payload) => {
            new Notification(payload.notification.title, {
                body: payload.notification.body,
                icon: payload.notification.icon ?? '/firebase-logo.png',
            });
        });
    </script>
</body>

</html>