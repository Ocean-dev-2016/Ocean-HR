@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    // dd($modules);
    $maring_bottom = 'mb-3';
@endphp
@section('title', $page_title)


@section('page_leavel_style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" />

    <style>
        #company_map_edit {
            height: 380px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #d9dee3;
            z-index: 1;
        }
        .select2-container {
            display: block !important;
        }

        .ck-editor__editable_inline {
            min-height: 150px;
        }

        .bootstrap-tagsinput {
            width: 100%;
            min-height: 40px;
            padding: 6px 10px;
            line-height: 22px;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            background: #fff;
        }

        .bootstrap-tagsinput .tag {
            margin-right: 2px;
            color: white;
            background-color: #29c3c0;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
    </style>

@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <!-- Default -->
    <div class="row">
        <!-- Default Wizard -->
        <div class="col-12 mb-6">
            <div class="card p-3">
                <h4 class="mb-0">Company name :- {{ $edit?->company_name ?? '' }}</h4>
            </div>
        </div>
        <div class="col-12 mb-6">
            <div class="bs-stepper wizard-numbered mt-2">
                <div class="bs-stepper-header table-responsive">
                    <div class="step" data-target="#company-information">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Company Information</span>
                            </span>
                        </button>
                    </div>

                    <div class="line">
                        <i class="icon-base ti tabler-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#Image-Setting">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Image Setting</span>
                            </span>
                        </button>
                    </div>

                    <div class="line">
                        <i class="icon-base ti tabler-chevron-right"></i>
                    </div>
                    <div class="step" data-target="#General-Setting">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">General Setting</span>
                            </span>
                        </button>
                    </div>

                </div>
                <div class="bs-stepper-content">
                    <form id="CompanyDetailsForm" action="{{ route($route . '.update', [$edit?->id]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @isset($edit)
                            @method('PUT')
                        @endisset
                        <!-- Company Information -->
                        <div id="company-information" class="content">
                            <div class="row g-6 {{ $maring_bottom }}">
                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="gst_no">GST No</label>
                                    <input type="text" id="gst_no" name="gst_no"
                                        class="form-control @error('gst_no') is-invalid @enderror"
                                        value="{{ $edit->gst_no }}" placeholder="GST No" autocomplete="gst_no" />
                                    @error('gst_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="company_name">Company Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="company_name" name="company_name"
                                        class="form-control @error('company_name') is-invalid @enderror"
                                        value="{{ $edit->company_name }}" placeholder="Company Name" />
                                    @error('company_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="person_name">Person Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="person_name" name="person_name"
                                        class="form-control @error('person_name') is-invalid @enderror"
                                        value="{{ $edit->person_name }}" placeholder="Person Name" />
                                    @error('person_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="whatsapp_number">Whatsapp Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="whatsapp_number" name="whatsapp_number"
                                        class="form-control @error('whatsapp_number') is-invalid @enderror"
                                        value="{{ $edit->whatsapp_number }}" maxlength="10"
                                        placeholder="Whatsapp Number" />
                                    @error('whatsapp_number')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="email">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ $edit->email }}" placeholder="Email" />
                                    @error('email')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="pan_card">Pan Card</label>
                                    <input type="text" id="pan_card" name="pan_card"
                                        class="form-control @error('pan_card') is-invalid @enderror"
                                        value="{{ $edit->pan_card }}" placeholder="Pan Card" />
                                    @error('pan_card')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <div class="form-group">
                                        <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                                        <select name="branch_type" id="branch_type"
                                            class="form-control @error('branch_type') is-invalid @enderror select2">
                                            <option value="" disabled selected>Select Branch</option>
                                            @foreach (config('constants.branch_type') as $branch_key => $value)
                                                <option value="{{ $branch_key }}"
                                                    {{ $edit->branch_type == $branch_key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_type')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-8 col-sm-12 {{ $maring_bottom }}"></div>

                                <div class="col-md-6 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="bank_details">Bank Details</label>
                                    <textarea name="bank_details" id="bank_details"
                                        class="ckeditor_common_cls form-control @error('bank_details') is-invalid @enderror" placeholder="Bank Details">{{ $edit->bank_details }}</textarea>
                                    @error('bank_details')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="address">Address</label>
                                    <textarea name="address" id="address"
                                        class="ckeditor_common_cls form-control @error('address') is-invalid @enderror" placeholder="Address">{{ $edit->address }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Office Location & Geofencing Settings --}}
                                <div class="col-12 mt-3 mb-2">
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
                                        <h5 class="mb-0 text-primary"><i class="ti ti-map-pin-filled me-2"></i>Office Location & Geofencing Settings</h5>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="is_geofencing_enabled" name="is_geofencing_enabled" value="1"
                                                {{ old('is_geofencing_enabled', isset($edit) ? $edit->is_geofencing_enabled : 1) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_geofencing_enabled">Enable Geofence Attendance</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="latitude">Latitude <span class="text-danger">*</span></label>
                                    <input type="text" id="latitude_edit" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                                        value="{{ old('latitude', $edit->latitude ?? '') }}" placeholder="e.g. 21.1702" readonly />
                                    @error('latitude')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="longitude">Longitude <span class="text-danger">*</span></label>
                                    <input type="text" id="longitude_edit" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                                        value="{{ old('longitude', $edit->longitude ?? '') }}" placeholder="e.g. 72.8311" readonly />
                                    @error('longitude')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                {{-- Map Search & Map Container --}}
                                <div class="col-12 mb-3">
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-8 col-sm-12">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                                <input type="text" id="search_location_edit" class="form-control" placeholder="Search office address or landmark on Google Map...">
                                                <button type="button" id="btn_search_map_edit" class="btn btn-outline-primary"><i class="ti ti-search me-1"></i>Search</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12 text-md-end">
                                            <button type="button" id="btn_current_location_edit" class="btn btn-outline-success w-100"><i class="ti ti-crosshair me-1"></i>Use Current Location</button>
                                        </div>
                                    </div>

                                    <div id="company_map_edit"></div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="ti ti-info-circle me-1"></i> <strong>Tip:</strong> Click anywhere on the map or drag the marker to pin your exact company office location.
                                    </small>
                                </div>


                                <div class="col-12 d-flex justify-content-between mt-3">
                                    <a href="{{ route('company.index') }}"
                                        class="btn btn-label-secondary btn-prev waves-effect" disabled="">
                                        <i class="menu-icon ti ti-chevrons-left"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Back</span>
                                    </a>
                                    <div>
                                        <button type="button"
                                            class="btn btn-primary btn-next waves-effect waves-light"><span
                                                class="align-middle d-sm-inline-block d-none me-sm-2">Next</span> <i
                                                class="icon-base ti tabler-arrow-right icon-xs"></i></button>
                                        <button type="submit" id="submit"
                                            class="btn btn-success btn-submit waves-effect waves-light">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Image Setting -->
                        <div id="Image-Setting" class="content">
                            <div class="row g-6 {{ $maring_bottom }}">
                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        Company Logo
                                        <small class="text-muted">minimum image size 400 x 300</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="company_logo" name="company_logo"
                                            onchange="previewImage(event, 'companyLogoPreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="companyLogoPreview" src="<?php echo $edit->company_logo && $edit->company_logo_url ? $edit->company_logo_url : '#'; ?>" alt="Image Preview"
                                            style="display: <?php echo $edit->company_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="company_logo_old" value="{{ $edit->company_logo }}">
                                    </div>
                                </div>


                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        White Labeling Logo
                                        <!-- <small class="text-muted">minimum image size 800 x 120</small> -->
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="white_labeling_logo" name="white_labeling_logo"
                                            onchange="previewImage(event, 'whiteLabelingLogoPreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="whiteLabelingLogoPreview" src="<?php echo $edit->white_labeling_logo && $edit->white_labeling_logo_url ? $edit->white_labeling_logo_url : '#'; ?>" alt="Image Preview"
                                            style="display: <?php echo $edit->white_labeling_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="white_labeling_logo_old"
                                            value="{{ $edit->white_labeling_logo }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        Favicon Icon
                                        <small class="text-muted">minimum image size 32 x 32</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="company_favicon" name="company_favicon"
                                            onchange="previewImage(event, 'CompanyFaviconPreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="CompanyFaviconPreview" src="<?php echo $edit->company_favicon && $edit->company_favicon_url ? $edit->company_favicon_url : '#'; ?>" alt="Image Preview"
                                            style="display: <?php echo $edit->company_favicon ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="company_favicon_old"
                                            value="{{ $edit->company_favicon }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        Watermark Logo
                                        <small class="text-muted">(Used as Employee Document Watermark)</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="watermark_logo" name="watermark_logo"
                                            onchange="previewImage(event, 'WatermarkLogoPreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="WatermarkLogoPreview" src="<?php echo $edit->watermark_logo && $edit->watermark_logo_url ? $edit->watermark_logo_url : '#'; ?>" alt="Image Preview"
                                            style="display: <?php echo $edit->watermark_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="watermark_logo_old"
                                            value="{{ $edit->watermark_logo }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        App Logo
                                        <small class="text-muted">minimum image size 512 x 512</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="app_logo" name="app_logo"
                                            onchange="previewImage(event, 'appLogoPreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="appLogoPreview" src="<?php echo $edit->app_logo && $edit->app_logo_url ? $edit->app_logo : '#'; ?>" alt="Image Preview"
                                            style="<?php echo $edit->app_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="app_logo_old" value="{{ $edit->app_logo }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        Salary Slip Header
                                        <small class="text-muted">minimum image size 800 x 120</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="header_image" name="header_image"
                                            onchange="previewImage(event, 'headerimagePreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="headerimagePreview" src="<?php echo $edit->order_header_logo && $edit->order_header_logo_url ? $edit->order_header_logo_url : '#'; ?>" alt="Image Preview"
                                            style="<?php echo $edit->order_header_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="header_image_old"
                                            value="{{ $edit->order_header_logo }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label">
                                        Salary Slip Footer
                                        <small class="text-muted">minimum image size 800 x 100</small>
                                    </label>
                                    <div class="border border-secondary rounded d-flex align-items-center justify-content-center"
                                        style="height: 150px; background-color: #f9f9f9;position: relative">
                                        <input type="file" class="form-control"
                                            style="opacity: 0; height: 150px; width: 100%; position: absolute;"
                                            id="footer_image" name="footer_image"
                                            onchange="previewImage(event, 'footerimagePreview')" accept="image/*">
                                        <p class="text-muted m-0">Drop in your image or click the box to add one!</p>
                                    </div>
                                    <!-- Image Preview Box -->
                                    <div style="margin-top: 10px;">
                                        <img id="footerimagePreview" src="<?php echo $edit->order_footer_logo && $edit->order_footer_logo_url ? $edit->order_footer_logo_url : '#'; ?>" alt="Image Preview"
                                            style="<?php echo $edit->order_footer_logo ? 'block' : 'none'; ?>; width: 200px;  object-fit: cover; border: 1px solid #ddd;"
                                            onerror="this.onerror=null; this.src=''; this.style.display='none';">
                                        <input type="hidden" name="footer_image_old"
                                            value="{{ $edit->order_footer_logo }}">
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button type="button" class="btn btn-label-secondary btn-prev waves-effect">
                                        <i class="icon-base ti tabler-arrow-left icon-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                    </button>
                                    <div>
                                        <button type="button"
                                            class="btn btn-primary btn-next waves-effect waves-light"><span
                                                class="align-middle d-sm-inline-block d-none me-sm-2">Next</span> <i
                                                class="icon-base ti tabler-arrow-right icon-xs"></i></button>
                                        <button type="submit" id="submit"
                                            class="btn btn-success btn-submit waves-effect waves-light">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Setting -->
                        <div id="General-Setting" class="content">
                            <div class="row g-8 {{ $maring_bottom }}">

                                <div class="content-header mt-1 mb-4 {{ $maring_bottom }}">
                                    <h6 class="mb-0">General Settings</h6>
                                </div>
                                <div class="col-md-2 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="mobile_min">Mobile Number Min Length</label>
                                    <input type="number" id="mobile_min" name="mobile_min"
                                        class="form-control @error('mobile_min') is-invalid @enderror"
                                        value="{{ old('mobile_min', $edit->mobile_min ?? '') }}"
                                        placeholder="Enter length" min="0" step="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('mobile_min')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-2 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="mobile_max">Mobile Number Max Length</label>
                                    <input type="number" id="mobile_max" name="mobile_max"
                                        class="form-control @error('mobile_max') is-invalid @enderror"
                                        value="{{ old('mobile_max', $edit->mobile_max ?? '') }}"
                                        placeholder="Enter length" min="0" step="1"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('mobile_max')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-2 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="hra_percentage">HRA Percentage (%)</label>
                                    <input type="number" id="hra_percentage" name="hra_percentage" step="0.01"
                                        min="0" max="100"
                                        class="form-control @error('hra_percentage') is-invalid @enderror"
                                        value="{{ old('hra_percentage', $edit->hra_percentage ?? 40) }}"
                                        placeholder="Enter HRA percentage">
                                    @error('hra_percentage')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <div class="form-group">
                                        <label class="form-label">Employee Code Auto Generation<span
                                                class="text-danger">*</span></label>
                                        <select name="employee_code_auto_generation" id="employee_code_auto_generation"
                                            class="form-control @error('employee_code_auto_generation') is-invalid @enderror select2">
                                            <option value="" disabled selected>Select Employee Code Auto Generation
                                            </option>
                                            @foreach (config('constants.employee_code_auto_generation') as $employee_code_auto_generation_key => $value)
                                                <option value="{{ $employee_code_auto_generation_key }}"
                                                    {{ old('employee_code_auto_generation', $edit->employee_code_auto_generation ?? 'auto') == $employee_code_auto_generation_key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_code_auto_generation')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-8 {{ $maring_bottom }}">
                                <div class="content-header mt-1 mb-4 {{ $maring_bottom }}">
                                    <h6 class="mb-0">Date &amp; Time Format</h6>
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="date_format">Date Format</label>
                                    @php
                                        $selectedDateFormat = old(
                                            'date_format',
                                            $edit->date_format ?? \App\Helpers\Helper::getDefaultDateFormat(),
                                        );
                                    @endphp
                                    <select name="date_format" id="date_format"
                                        class="form-control select2 @error('date_format') is-invalid @enderror">
                                        @foreach ($dateFormatOptions ?? [] as $format => $label)
                                            <option value="{{ $format }}"
                                                {{ $selectedDateFormat === $format ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('date_format')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="time_format">Time Format</label>
                                    @php
                                        $selectedTimeFormat = old(
                                            'time_format',
                                            $edit->time_format ?? \App\Helpers\Helper::getDefaultTimeFormat(),
                                        );
                                    @endphp
                                    <select name="time_format" id="time_format"
                                        class="form-control select2 @error('time_format') is-invalid @enderror">
                                        @foreach ($timeFormatOptions ?? [] as $format => $label)
                                            <option value="{{ $format }}"
                                                {{ $selectedTimeFormat === $format ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('time_format')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="content-header mt-4 mb-4 {{ $maring_bottom }}">
                                    <h6 class="mb-0">Color Theme</h6>
                                    <!-- <small>Enter Your Company Information.</small> -->
                                </div>

                                <!-- Status Bar Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="status_bar_color">Status Bar Color</label>
                                    <input type="color" id="status_bar_color" name="status_bar_color"
                                        class="form-control @error('status_bar_color') is-invalid @enderror"
                                        placeholder="Status Bar Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->status_bar_color }}">
                                    @error('status_bar_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Title Name Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="title_name_color">Title Name Color</label>
                                    <input type="color" id="title_name_color" name="title_name_color"
                                        class="form-control @error('title_name_color') is-invalid @enderror"
                                        placeholder="Title Name Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->title_name_color }}">
                                    @error('title_name_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- All Icon Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="all_icon_color">All Icon Color</label>
                                    <input type="color" id="all_icon_color" name="all_icon_color"
                                        class="form-control @error('all_icon_color') is-invalid @enderror"
                                        placeholder="All Icon Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->all_icon_color }}">
                                    @error('all_icon_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Edittext Title Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="edittext_title_color">Edittext Title Color</label>
                                    <input type="color" id="edittext_title_color" name="edittext_title_color"
                                        class="form-control @error('edittext_title_color') is-invalid @enderror"
                                        placeholder="Edittext Title Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->edittext_title_color }}">
                                    @error('edittext_title_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Screen Background light Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="screen_background_light_color">Screen Background light
                                        Color</label>
                                    <input type="color" id="screen_background_light_color"
                                        name="screen_background_light_color"
                                        class="form-control @error('screen_background_light_color') is-invalid @enderror"
                                        placeholder="Screen Background light Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->screen_background_light_color }}">
                                    @error('screen_background_light_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Screen Background Dark Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="screen_background_dark_color">Screen Background Dark
                                        Color</label>
                                    <input type="color" id="screen_background_dark_color"
                                        name="screen_background_dark_color"
                                        class="form-control @error('screen_background_dark_color') is-invalid @enderror"
                                        placeholder="Screen Background Dark Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->screen_background_dark_color }}">
                                    @error('screen_background_dark_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- All Screen Header Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="all_screen_header_color">All Screen Header
                                        Color</label>
                                    <input type="color" id="all_screen_header_color" name="all_screen_header_color"
                                        class="form-control @error('all_screen_header_color') is-invalid @enderror"
                                        placeholder="All Screen Header Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->all_screen_header_color }}">
                                    @error('all_screen_header_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- All Screen Back Arrow Background Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="all_screen_back_arrow_background_color">All Screen Back
                                        Arrow Background Color</label>
                                    <input type="color" id="all_screen_back_arrow_background_color"
                                        name="all_screen_back_arrow_background_color"
                                        class="form-control @error('all_screen_back_arrow_background_color') is-invalid @enderror"
                                        placeholder="All Screen Back Back Arrow Background Color"
                                        style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->all_screen_back_arrow_background_color }}">
                                    @error('all_screen_back_arrow_background_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- All Screen Back Arrow Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="all_screen_back_arrow_color">All Screen Back Arrow
                                        Color</label>
                                    <input type="color" id="all_screen_back_arrow_color"
                                        name="all_screen_back_arrow_color"
                                        class="form-control @error('all_screen_back_arrow_color') is-invalid @enderror"
                                        placeholder="All Screen Back Arrow Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->all_screen_back_arrow_color }}">
                                    @error('all_screen_back_arrow_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Data List Border Color -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="data_list_border_color">Data List Border Color</label>
                                    <input type="color" id="data_list_border_color" name="data_list_border_color"
                                        class="form-control @error('data_list_border_color') is-invalid @enderror"
                                        placeholder="Data List Border Color" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->data_list_border_color }}">
                                    @error('data_list_border_color')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Login Text Color 1 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="login_text_color_1">Login Text Color 1</label>
                                    <input type="color" id="login_text_color_1" name="login_text_color_1"
                                        class="form-control @error('login_text_color_1') is-invalid @enderror"
                                        placeholder="Login Text Color 1" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->login_text_color_1 }}">
                                    @error('login_text_color_1')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Login Text Color 2 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="login_text_color_2">Login Text Color 1</label>
                                    <input type="color" id="login_text_color_2" name="login_text_color_2"
                                        class="form-control @error('login_text_color_2') is-invalid @enderror"
                                        placeholder="Login Text Color 1" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->login_text_color_2 }}">
                                    @error('login_text_color_2')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Background Shape Color 1 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="background_shape_1">Background Shape 1</label>
                                    <input type="color" id="background_shape_1" name="background_shape_1"
                                        class="form-control @error('background_shape_1') is-invalid @enderror"
                                        placeholder="Background Shape 1" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->background_shape_1 }}">
                                    @error('background_shape_1')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Background Shape Color 2 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="background_shape_2">Background Shape 2</label>
                                    <input type="color" id="background_shape_2" name="background_shape_2"
                                        class="form-control @error('background_shape_2') is-invalid @enderror"
                                        placeholder="Background Shape 2" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->background_shape_2 }}">
                                    @error('background_shape_2')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Background Shape Color 2 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="background_shape_3">Background Shape 3</label>
                                    <input type="color" id="background_shape_3" name="background_shape_3"
                                        class="form-control @error('background_shape_3') is-invalid @enderror"
                                        placeholder="Background Shape 3" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->background_shape_3 }}">
                                    @error('background_shape_3')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Extra Color 1 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="extra_color_1">Extra Color 1</label>
                                    <input type="color" id="extra_color_1" name="extra_color_1"
                                        class="form-control @error('extra_color_1') is-invalid @enderror"
                                        placeholder="Extra Color 1" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->extra_color_1 }}">
                                    @error('extra_color_1')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Extra Color 2 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="extra_color_2">Extra Color 2</label>
                                    <input type="color" id="extra_color_2" name="extra_color_2"
                                        class="form-control @error('extra_color_2') is-invalid @enderror"
                                        placeholder="Extra Color 2" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->extra_color_2 }}">
                                    @error('extra_color_2')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Extra Color 3 -->
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="extra_color_3">Extra Color 3</label>
                                    <input type="color" id="extra_color_3" name="extra_color_3"
                                        class="form-control @error('extra_color_3') is-invalid @enderror"
                                        placeholder="Extra Color 3" style="width: 200px; height: 38px;"
                                        value="{{ $edit?->company_details?->extra_color_3 }}">
                                    @error('extra_color_3')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>



                                <div class="col-sm-12 {{ $maring_bottom }}">
                                    <hr class="">
                                </div>

                                <div class="content-header mt-1 mb-4 {{ $maring_bottom }}">
                                    <h6 class="mb-0">Default Settings</h6>
                                    <!-- <small>Enter Your Company Information.</small> -->
                                </div>
                                <div class="col-md-12 col-sm-12 {{ $maring_bottom }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="defaultCheckCopyright"
                                            {{ $edit->is_copyright_view == 'yes' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="defaultCheckCopyright"> Copyright </label>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="default_password">Default Password </label>
                                    <input type="text" id="default_password" name="default_password"
                                        class="form-control @error('default_password') is-invalid @enderror"
                                        value="{{ $edit->default_password }}" />
                                    @error('default_password')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="reset_password">Reset Password</label>
                                    <input type="text" id="reset_password" name="reset_password"
                                        class="form-control @error('reset_password') is-invalid @enderror"
                                        value="{{ $edit->reset_password }}" />
                                    @error('reset_password')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <div class="form-group">
                                        <label class="form-label">Select Country <span
                                                class="text-danger">*</span></label>
                                        <select name="country_id" id="country_id"
                                            class="form-control @error('country_id') is-invalid @enderror select2">
                                            <option value="" disabled selected>Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ $edit->country_id == $country->id ? 'selected' : '' }}
                                                    data-phonecode="{{ $country->code }}">{{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country_id')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-12 {{ $maring_bottom }}">
                                    <label class="form-label" for="phonecode">Phonecode</label>
                                    <input type="text" id="phonecode" name="phonecode"
                                        class="form-control @error('phonecode') is-invalid @enderror"
                                        value="{{ $edit->phonecode }}" readonly />
                                    @error('phonecode')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-12 d-flex justify-content-between">
                                    <button type="button" class="btn btn-label-secondary btn-prev waves-effect">
                                        <i class="icon-base ti tabler-arrow-left icon-xs me-sm-2 me-0"></i>
                                        <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                    </button>
                                    <button type="submit" id="submit"
                                        class="btn btn-success btn-submit waves-effect waves-light">Submit</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!-- /Default Wizard -->
    </div>
@endsection

@section('page_leavel_script')
    <!-- Vendors JS -->
    <script src="{{ asset('software/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
    <!-- Page JS -->

    <script src="{{ asset('software/js/form-wizard-numbered.js') }}"></script>


    <script src="{{ asset('software/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('software/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.js"></script>
    <!-- <script src="{{ asset('software/js/forms-pickers.js') }}"></script> -->
@endsection

@push('page_scripts')
    <script>
        initializeCKEditor(".ckeditor_common_cls");

        function previewImage(event, previewId) {
            const input = event.target;
            const preview = document.getElementById(previewId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
    </script>

    <script>
        $(document).ready(function() {

            $('#plan_from').flatpickr({
                dateFormat: "Y-m-d",
                minDate: "today",
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        $('#plan_to').flatpickr('set', 'minDate', dateStr);
                    }
                }
            });

            $('#plan_to').flatpickr({
                dateFormat: "Y-m-d",
                minDate: "today",

            });

            $(document).on('change', '#country_id', function() {
                var selectedOption = $(this).find('option:selected');
                var phoneCode = selectedOption.data('phonecode');
                $('#phonecode').val(phoneCode);
            });

        });

        $('#bcc').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // stop form submit

                const input = $(this);
                const value = input.val().trim();

                if (value) {
                    input.tagsinput('add', value);
                    input.val('');
                }

        // Company Map Edit Script
        if ($('#company_map_edit').length) {
            const defaultLat = parseFloat($('#latitude_edit').val()) || 21.1702;
            const defaultLng = parseFloat($('#longitude_edit').val()) || 72.8311;
            const hasLocation = $('#latitude_edit').val() && $('#longitude_edit').val();

            const companyMapEdit = L.map('company_map_edit').setView([defaultLat, defaultLng], hasLocation ? 16 : 12);

            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                attribution: '© Google Maps',
                maxZoom: 20
            }).addTo(companyMapEdit);

            const officeIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            let marker = L.marker([defaultLat, defaultLng], {
                draggable: true,
                icon: officeIcon
            }).addTo(companyMapEdit);

            function updateLocationEdit(lat, lng) {
                $('#latitude_edit').val(lat.toFixed(7));
                $('#longitude_edit').val(lng.toFixed(7));

                marker.setLatLng([lat, lng]);
                marker.bindPopup(`<b>Company Office Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
            }

            if (hasLocation) {
                updateLocationEdit(defaultLat, defaultLng);
            }

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateLocationEdit(pos.lat, pos.lng);
            });

            companyMapEdit.on('click', function(e) {
                updateLocationEdit(e.latlng.lat, e.latlng.lng);
            });

            $('#btn_current_location_edit').on('click', function() {
                if (navigator.geolocation) {
                    const btn = $(this);
                    btn.html('<i class="ti ti-loader ti-spin me-1"></i>Locating...');
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            btn.html('<i class="ti ti-crosshair me-1"></i>Use Current Location');
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            companyMapEdit.setView([lat, lng], 17);
                            updateLocationEdit(lat, lng);
                        },
                        function(error) {
                            btn.html('<i class="ti ti-crosshair me-1"></i>Use Current Location');
                            alert('Unable to retrieve location: ' + error.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            });

            $('#btn_search_map_edit').on('click', function() {
                const query = $('#search_location_edit').val().trim();
                if (!query) return;

                const btn = $(this);
                btn.html('<i class="ti ti-loader ti-spin"></i>');

                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        btn.html('<i class="ti ti-search me-1"></i>Search');
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            companyMapEdit.setView([lat, lng], 17);
                            updateLocationEdit(lat, lng);
                        } else {
                            alert('Location not found. Please try another query or click on the map.');
                        }
                    })
                    .catch(err => {
                        btn.html('<i class="ti ti-search me-1"></i>Search');
                        console.error('Search error:', err);
                    });
            });

            setTimeout(() => { companyMapEdit.invalidateSize(); }, 600);
        }
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
