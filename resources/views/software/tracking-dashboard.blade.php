@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $authLoginUserDetail = isset($modules['authLoginUserDetail']) ? $modules['authLoginUserDetail'] : null;
    $loginUserId = isset($authLoginUserDetail?->id) ? $authLoginUserDetail?->id : null;
    $parent_type_id = isset($modules['parent_type_id']) ? $modules['parent_type_id'] : null;
    // dd($modules);
@endphp
@section('title', $page_title)

@section('page_leavel_style')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('content')
    <div class="card">
        {{-- <div class="card-header">
        <h5 class="card-title">Tracking Dashboard</h5>
    </div> --}}
        <div class="card-body">
            <div class="row">
                @if (!$company_id)
                    <div class="col-md-4 col-sm-6 mb-3">
                        <!-- Company -->
                        <div class="form-group">
                            <label class="form-label fw-bold">Select Company <span class="text-danger">*</span></label>
                            <select id="company_id"
                                class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                name="company_id" data-selectedCompanyId="{{ old('company_id', $edit->company_id ?? '') }}">
                                <option value="">Select Company</option>
                            </select>
                            @error('company_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                @endif
                <div class="col-md-3 col-sm-6 mb-3">
                    <!-- Team Person -->
                    <div class="form-group">
                        <label class="form-label fw-bold">Team Person <span class="text-danger">*</span></label>
                        <select id="team_person_ids"
                            class="form-control select2 team_person_select @error('team_person_id') is-invalid @enderror"
                            name="team_person_id"
                            data-selectedids="{{ old('team_person_id', $edit->team_person_id ?? '') }}">
                            <option value="">Select Team Person</option>
                        </select>
                        @error('team_person_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <label for="date" class="form-label fw-bold">Date</label>
                    <input type="date" id="date" class="form-control" value="2025-04-02">
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <label for="date" class="form-label fw-bold"></label>
                    <button class="btn btn-primary w-100" id="search_route_button">Route</button>
                </div>
                {{-- <div class="col-md-2 col-sm-6 mb-3">
                <label for="date" class="form-label fw-bold"></label>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="sync_pin">
                    <label class="form-check-label text-uppercase text-primary" for="sync_pin">Sync Pin</label>
                </div>
            </div> --}}
            </div>
            <div class="row">
                <div class="mt-3" id="team_person_details" style="display: none;">
                    <p class="mb-1"><strong><span id="team_person_name"></span></strong></p>
                    <p class="mb-1 text-muted" id="team_person_contact"></p>
                    <p class="mb-1">App Version: <span id="app_version"></span></p>
                    {{-- <p class="mb-1">Total Orders: <span id="total_orders"></span></p>
                <p class="mb-1">Total Quotation: <span id="total_quotation"></span></p>
                <p class="mb-1">Total Expense: <span id="total_expense_pass_amount"></span> / <span id="total_expense_report_amount"></span></p>
                <p class="mb-1">Total Followups: <span id="total_followups"></span></p> --}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div id="map" style="min-height: 800px;"></div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <!-- Pin Details -->
                    <div id="pin_details" style="display: none;">
                        <h6 class="text-primary">Pin Details</h6>
                        {{-- <div class="divider divider-dashed">
                        <div class="divider-text"><strong>Pin Details</strong></div>
                    </div> --}}

                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('software/img/green-pin.png') }}" width="30" alt="green-pin"
                                class="me-2">
                            <p class="mb-0">Attendance In</p>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('software/img/red-pin.png') }}" width="30" alt="red-pin" class="me-2">
                            <p class="mb-0">Attendance Out</p>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('software/img/blue-pin.png') }}" width="30" alt="blue-pin"
                                class="me-2">
                            <p class="mb-0">Team Person Activity</p>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('software/img/black-pin.png') }}" width="30" alt="black-pin"
                                class="me-2">
                            <p class="mb-0">Traking Sync</p>
                        </div>
                    </div>

                    <h6 class="text-primary">Attendance Details</h6>
                    <div id="routes" class="list-group" style="max-height: 500px; overflow-x: auto;">
                        Please Select Team Person To View Tracking History
                    </div>
                </div>
            </div>

        </div>
        <!-- Leaflet JS -->
    </div>
@endsection


@push('page_scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.motion/dist/leaflet.motion.min.js"></script>
    <script src="{{ asset('software/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    @include('utils.getCompany')
    @include('utils.getEmployee')

    <script>
        flatpickr("#date", {
            dateFormat: "d-m-Y", // Example: 2025-05-13
            defaultDate: new Date()
        });

        var map = L.map('map').setView([20.5937, 78.9629], 5); // Initial Map Setup
        L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            attribution: '© Google Maps'
        }).addTo(map);

        var startIcon = L.icon({
            iconUrl: "{{ asset('software/img/green-pin.png') }}",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        var customIconstop = L.icon({
            iconUrl: "{{ asset('software/img/red-pin.png') }}",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        var customIconTrakingSync = L.icon({
            iconUrl: "{{ asset('software/img/black-pin.png') }}",
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        var animatedIcon = L.icon({
            iconUrl: "{{ asset('software/img/user.png') }}",
            iconSize: [35, 35],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        var markers = [];
        var polyline;
        var motionLine;

        function animateMarker() {

            var animatedMarker;
            var currentStep = 0;
            var totalSteps = 0;
            var animationSpeed = 1000; // Adjust animation speed
            if (currentStep >= totalSteps) return;
            animatedMarker.setLatLng(pathCoordinates[currentStep]);
            currentStep++;
            setTimeout(animateMarker, animationSpeed);
        }


        function stopMarkerAnimation() {
            clearTimeout(animationTimeout);
            if (animatedMarker) {
                map.removeLayer(animatedMarker);
                animatedMarker = null;
            }
        }
    </script>
@endpush
