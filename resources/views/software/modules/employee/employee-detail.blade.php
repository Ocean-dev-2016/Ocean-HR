@extends('software.layout.app')

@php
    $i = 0;

    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $colums = 'col-md-3 col-sm-12 mb-2';
@endphp
@section('title', $page_title)

@section('page_leavel_style')
    <link rel="stylesheet" href="{{ asset('software/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    [
                        'title' => $page_title . ' detail',
                        'url' => '',
                    ],
                ],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <!-- Header -->
    <div class="row mt-1">
        <div class="col-12">
            <div class="card mb-4">
                <div class="user-profile-header-banner text-center">
                    {{-- <img src="https://ui-avatars.com/api/?size=600x200&name=" alt="Banner image" class="rounded-top" /> --}}


                    {{-- <img src="{{ 'https://placehold.co/1200x100/transparent/' . str_replace('#', '', $show?->company?->company_details->color_text_primary_1 ?? 'FFF') . '?text=' . $show?->company?->company_name }}" alt="Banner image" class="rounded-top" /> --}}

                    {{-- <img src="{{ 'https://placehold.co/1200x100/'.str_replace("#","", $show?->company?->company_details->color_text_primary_1 ?? "FFF").'/'.str_replace("#","", $show?->company?->company_details->color_text_primary_1 ?? "FFF").'?text='.$show?->company?->company_name }}" alt="Banner image" class="rounded-top" /> --}}
                    {{-- <img src="{{ 'https://placehold.co/1800x100/'.str_replace("#","", $show?->company?->company_details->color_text_primary_1 ?? "FFF").'/'.str_replace("#","", $show?->company?->company_details->color_text_primary_1 ?? "FFF") }}" alt="Banner image" class="rounded-top" /> --}}
                </div>
                <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4 mt-3">
                    <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        <img src="{{ $show?->employee_photo_url . '&size=128' }}" alt="user image"
                            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                    </div>
                    <div class="flex-grow-1 mt-3 mt-sm-5">
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h4>{{ $show?->full_name }}</h4>
                                <ul
                                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                    @if ($show?->current_role && $show?->current_role?->name)
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-user"></i> {{ $show?->current_role?->name ?? '-' }}
                                        </li>
                                    @endif
                                    @if ($show?->city_id || $show?->state_id || $show?->country_id)
                                        <li class="list-inline-item d-flex gap-1"><i class="ti ti-map-pin"></i>
                                            {{ $show?->city?->name . ',' }}
                                            {{ $show?->state?->name . ',' }}
                                            {{ $show?->country?->name }}
                                        </li>
                                    @endif

                                    @if ($show?->created_at)
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-calendar"></i> Created Data :
                                            {{ \App\Helpers\Helper::convert_date($show?->created_at ?? '', 'Y-m-d H:i:s', 'd/m/Y H:i A') }}
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            {{-- <a href="javascript:void(0)" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Connected
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Header -->

    <div class="row">
        <div class="col-12">
            <div class="nav-align-left mb-4">
                <ul class="nav nav-pills me-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                            aria-selected="true">
                            <i class="ti ti-user me-1"></i> Employee Details
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-employment" aria-controls="navs-pills-left-employment"
                            aria-selected="false">
                            <i class="ti ti-id-badge me-1"></i> Employment Details
                            <span class="badge bg-secondary ms-1">{{ ($show?->employment_details?->count() ?? 0) > 0 ? ($show?->employment_details?->count() ?? 0) : ($show?->employmentDetail ? 1 : 0) }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-assets" aria-controls="navs-pills-left-assets"
                            aria-selected="false">
                            <i class="ti ti-device-laptop me-1"></i> Assign Assets
                            <span class="badge bg-primary ms-1">{{ $show?->employee_asign_assets?->count() ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-increment" aria-controls="navs-pills-left-increment"
                            aria-selected="false">
                            <i class="ti ti-trending-up me-1"></i> Increment Details
                            <span class="badge bg-success ms-1">{{ $show?->increment_details?->count() ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-education" aria-controls="navs-pills-left-education"
                            aria-selected="false">
                            <i class="ti ti-school me-1"></i> Education & Experience
                            <span class="badge bg-info ms-1">{{ $show?->education_experience_details?->count() ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-salary" aria-controls="navs-pills-left-salary"
                            aria-selected="false">
                            <i class="ti ti-currency-rupee me-1"></i> Salary Details
                            <span class="badge bg-warning ms-1">{{ $show?->salary_details?->count() ?? 0 }}</span>
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    {{-- Employee Details Tab --}}
                    <div class="tab-pane fade show active" id="navs-pills-left-profile" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-user me-1"></i> Employee Information</h6>
                            @if(isset($modules['update_permission']) && $modules['update_permission'])
                            <a href="{{ route($route . '.edit', $show->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Employee
                            </a>
                            @endif
                        </div>
                        <small class="card-text text-uppercase">About</small>
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-hexagon-letter-e text-heading"></i><span
                                    class="fw-medium mx-2 text-heading">Employee Code  
                                    :</span> <span>{{ $show?->employee_code ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-fingerprint text-heading"></i><span
                                    class="fw-medium mx-2 text-heading">Biometric User ID
                                    :</span> <span>{{ $show?->biometric_user_id ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-hexagon-letter-s text-heading"></i><span
                                    class="fw-medium mx-2 text-heading">Sur Name
                                    :</span> <span>{{ $show?->first_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-hexagon-letter-f text-heading"></i><span
                                    class="fw-medium mx-2 text-heading">First Name
                                    :</span> <span>{{ $show?->middle_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 text-heading">Father Name
                                    :</span> <span>{{ $show?->father_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-cake text-heading"></i><span class="fw-medium mx-2 text-heading">Date Of
                                    Birth :</span>
                                <span>{{ $show?->date_of_birth ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-hexagon-letter-g text-heading"></i><span
                                    class="fw-medium mx-2 text-heading">Gender :</span>
                                <span>{{ $show?->gender ?? '--' }}</span>
                            </div>
                        </div>
                        <small class="card-text text-uppercase">Contacts</small>
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-mail text-heading"></i><span class="fw-medium mx-2 text-heading">Email
                                    :</span>
                                <span>{{ $show?->email ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <span class="fw-medium mx-2 text-heading">Contact Number :</span>
                                <span>{{ $show?->contact_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <span class="fw-medium mx-2 text-heading">Other Number :</span>
                                <span>{{ $show?->other_number ?? '--' }}</span>
                            </div>
                        </div>

                        <small class="card-text text-uppercase">Address Details</small>
                        <div class="row mb-3">
                            <div class="col-md-6 col-sm-12">
                                <i class="ti ti-home text-heading"></i><span class="fw-medium mx-2 text-heading">Current Address :</span>
                                <span>{{ $show?->current_address ?? '--' }}</span>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <i class="ti ti-map-pin text-heading"></i><span class="fw-medium mx-2 text-heading">Permanent Address :</span>
                                <span>{{ $show?->permanent_address ?? '--' }}</span>
                            </div>
                        </div>

                        <small class="card-text text-uppercase">Document Details</small>
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-id text-heading"></i><span class="fw-medium mx-2 text-heading">Aadhar Card :</span>
                                <span>{{ $show?->aadhar_card_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-id-badge text-heading"></i><span class="fw-medium mx-2 text-heading">PAN Card :</span>
                                <span>{{ $show?->pan_card_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-heart text-heading"></i><span class="fw-medium mx-2 text-heading">Marital Status :</span>
                                <span>{{ ucfirst($show?->marital_status ?? '--') }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-calendar-heart text-heading"></i><span class="fw-medium mx-2 text-heading">Anniversary :</span>
                                <span>{{ $show?->date_of_anniversary ?? '--' }}</span>
                            </div>
                        </div>

                        <small class="card-text text-uppercase">Bank Details</small>
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-building-bank text-heading"></i><span class="fw-medium mx-2 text-heading">Bank Name :</span>
                                <span>{{ $show?->bank_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-credit-card text-heading"></i><span class="fw-medium mx-2 text-heading">Account No :</span>
                                <span>{{ $show?->bank_account_number ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-hash text-heading"></i><span class="fw-medium mx-2 text-heading">IFSC Code :</span>
                                <span>{{ $show?->ifsc_code ?? '--' }}</span>
                            </div>
                        </div>

                        @if(isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
                        <small class="card-text text-uppercase">Login Details</small>
                        <div class="row mb-3">
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-key text-heading"></i><span class="fw-medium mx-2 text-heading">App Key :</span>
                                <span id="app_key_value">{{ $show?->company?->app_key ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 text-heading">Username :</span>
                                <span id="username_value">{{ $show?->username ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <i class="ti ti-lock text-heading"></i><span class="fw-medium mx-2 text-heading">Password :</span>
                                <span id="password_value">{{ $show?->sp ?? '--' }}</span>
                            </div>
                            <div class="col-md-3 col-sm-12 d-flex align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-primary copy-login-details-btn" 
                                    data-app-key="{{ $show?->company?->app_key ?? '' }}"
                                    data-username="{{ $show?->username ?? '' }}"
                                    data-password="{{ $show?->sp ?? '' }}"
                                    title="Copy & Share Login Details">
                                    <i class="ti ti-copy me-1"></i> Copy & Share
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="navs-pills-left-employment" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-id-badge me-1"></i> Employment Details</h6>
                            @if(isset($modules['employment_details_add_permission']) && $modules['employment_details_add_permission'])
                                @if($show?->employmentDetail)
                                    <a href="{{ route('employment-details.edit', $show->employmentDetail->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Employment
                                    </a>
                                @else
                                    <a href="{{ route('employment-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i> Add Employment
                                    </a>
                                @endif
                            @endif
                        </div>
                        @if($show?->employment_details && $show->employment_details->count() > 0)
                        @foreach($show->employment_details as $index => $emp)
                        <div class=" mb-3">
                            <div class=" d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Employment Record #{{ $index + 1 }}
                                    <span class="badge bg-{{ ($emp->status ?? '') == 'active' ? 'success' : 'danger' }} ms-2">
                                        {{ ucfirst($emp->status ?? 'inactive') }}
                                    </span>
                                </h6>
                                
                            </div>
                            <div class="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Role & Department</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Designation Type:</strong> {{ ucfirst($emp->designation_type ?? '-') }}</p>
                                            <p class="mb-1"><strong>Designation:</strong> {{ $emp->designation?->name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Department:</strong> {{ $emp->department?->name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Sub Department:</strong> {{ $emp->subdepartment?->sub_department_name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Process:</strong> {{ $emp->process?->name ?? '--' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Dates & IDs</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Joining Date:</strong> {{ $emp->date_of_joining ? \Carbon\Carbon::parse($emp->date_of_joining)->format('d/m/Y') : '--' }}</p>
                                            <p class="mb-1"><strong>Confirmation Date:</strong> {{ $emp->employment_confirmation_date ? \Carbon\Carbon::parse($emp->employment_confirmation_date)->format('d/m/Y') : '--' }}</p>
                                            <p class="mb-1"><strong>PF No:</strong> {{ $emp->employee_pf_no ?? '--' }}</p>
                                            <p class="mb-1"><strong>UAN No:</strong> {{ $emp->uan_no ?? '--' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Work Settings</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Payment Mode:</strong> {{ strtoupper($emp->payment_mode ?? '--') }}</p>
                                            <p class="mb-1"><strong>Employment Type:</strong> {{ $emp->employee_type?->name ?? strtoupper($emp->employment_type ?? '--') }}</p>
                                            <p class="mb-1"><strong>Shift:</strong> {{ $emp->shiftDetail?->name ?? $emp->shift ?? '--' }}</p>
                                            <p class="mb-1"><strong>Outdoor Attendance:</strong> {{ $emp->outdoor_attendance ?? '--' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @elseif($show?->employmentDetail)
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Role & Department</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Designation Type:</strong> {{ ucfirst($show->employmentDetail->designation_type ?? '-') }}</p>
                                            <p class="mb-1"><strong>Designation:</strong> {{ $show->employmentDetail->designation?->name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Department:</strong> {{ $show->employmentDetail->department?->name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Sub Department:</strong> {{ $show->employmentDetail->subdepartment?->sub_department_name ?? '--' }}</p>
                                            <p class="mb-1"><strong>Process:</strong> {{ $show->employmentDetail->process?->name ?? '--' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Dates & IDs</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Joining Date:</strong> {{ $show->employmentDetail->date_of_joining ? \Carbon\Carbon::parse($show->employmentDetail->date_of_joining)->format('d/m/Y') : '--' }}</p>
                                            <p class="mb-1"><strong>Confirmation Date:</strong> {{ $show->employmentDetail->employment_confirmation_date ? \Carbon\Carbon::parse($show->employmentDetail->employment_confirmation_date)->format('d/m/Y') : '--' }}</p>
                                            <p class="mb-1"><strong>PF No:</strong> {{ $show->employmentDetail->employee_pf_no ?? '--' }}</p>
                                            <p class="mb-1"><strong>UAN No:</strong> {{ $show->employmentDetail->uan_no ?? '--' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Work Settings</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Payment Mode:</strong> {{ strtoupper($show->employmentDetail->payment_mode ?? '--') }}</p>
                                            <p class="mb-1"><strong>Employment Type:</strong> {{ $show->employmentDetail->employee_type?->name ?? strtoupper($show->employmentDetail->employment_type ?? '--') }}</p>
                                            <p class="mb-1"><strong>Shift:</strong> {{ $show->employmentDetail->shiftDetail?->name ?? $show->employmentDetail->shift ?? '--' }}</p>
                                            <p class="mb-1"><strong>Outdoor Attendance:</strong> {{ $show->employmentDetail->outdoor_attendance ?? '--' }}</p>
                                            <p class="mb-1">
                                                <strong>Status:</strong>
                                                <span class="badge bg-{{ ($show->employmentDetail->status ?? '') == 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($show->employmentDetail->status ?? 'inactive') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i> No employment details found for this employee.
                        </div>
                        @endif
                    </div>

                    {{-- Assign Assets Tab --}}
                    <div class="tab-pane fade" id="navs-pills-left-assets" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-device-laptop me-1"></i> Assigned Assets</h6>
                            @if(isset($modules['assign_assets_add_permission']) && $modules['assign_assets_add_permission'])
                            <a href="{{ route('employee-assign-assets.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Add Asset
                            </a>
                            @endif
                        </div>
                        @if($show?->employee_asign_assets && $show->employee_asign_assets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Asset Name</th>
                                        <th>Reference No</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        @if(isset($modules['assign_assets_update_permission']) && $modules['assign_assets_update_permission'])
                                        <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($show->employee_asign_assets as $index => $asset)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $asset->assets?->name ?? '--' }}</td>
                                        <td>{{ $asset->reference_no ?? '--' }}</td>
                                        <td>{{ $asset->date ? \Carbon\Carbon::parse($asset->date)->format('d/m/Y') : '--' }}</td>
                                        <td>{{ $asset->descrption ?? '--' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $asset->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($asset->status) }}
                                            </span>
                                        </td>
                                        @if(isset($modules['assign_assets_update_permission']) && $modules['assign_assets_update_permission'])
                                        <td class="text-center">
                                            <a href="{{ route('employee-assign-assets.edit', $asset->id) }}" 
                                               class="btn btn-sm btn-light btn-icon" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i> No assets assigned to this employee.
                        </div>
                        @endif
                    </div>

                    {{-- Increment Details Tab --}}
                    <div class="tab-pane fade" id="navs-pills-left-increment" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-trending-up me-1"></i> Increment Details</h6>
                            @if(isset($modules['increment_details_add_permission']) && $modules['increment_details_add_permission'])
                            <a href="{{ route('employee-increment-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Add Increment
                            </a>
                            @endif
                        </div>
                        @if($show?->increment_details && $show->increment_details->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Increment Date</th>
                                        <th>Designation</th>
                                        <th>Basic DA</th>
                                        <th>HRA</th>
                                        <th>Conveyance</th>
                                        <th>Medical</th>
                                        <th>Special</th>
                                        <th>PF</th>
                                        <th>Effective From</th>
                                        <th>Status</th>
                                        @if(isset($modules['increment_details_update_permission']) && $modules['increment_details_update_permission'])
                                        <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($show->increment_details as $index => $increment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $increment->icrement_date ?? '--' }}</td>
                                        <td>{{ $increment->designation?->name ?? '--' }}</td>
                                        <td>₹{{ number_format($increment->basic_da ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($increment->hra ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($increment->conveyance_allowance ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($increment->medical_allowance ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($increment->special_allowance ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($increment->pf ?? 0, 2) }}</td>
                                        <td>{{ $increment->effective_month ?? '--' }}/{{ $increment->effective_year ?? '--' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $increment->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($increment->status) }}
                                            </span>
                                        </td>
                                        @if(isset($modules['increment_details_update_permission']) && $modules['increment_details_update_permission'])
                                        <td class="text-center">
                                            <a href="{{ route('employee-increment-details.edit', $increment->id) }}" 
                                               class="btn btn-sm btn-light btn-icon" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i> No increment details found for this employee.
                        </div>
                        @endif
                    </div>

                    {{-- Education & Experience Tab --}}
                    <div class="tab-pane fade" id="navs-pills-left-education" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-school me-1"></i> Education & Experience Details</h6>
                            @if(isset($modules['education_experience_add_permission']) && $modules['education_experience_add_permission'])
                            <a href="{{ route('employee-education-experience.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Add Education/Experience
                            </a>
                            @endif
                        </div>
                        @if($show?->education_experience_details && $show->education_experience_details->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Degree</th>
                                        <th>Institution</th>
                                        <th>Passing Year</th>
                                        <th>Marks/Class</th>
                                        <th>Company</th>
                                        <th>Joining Date</th>
                                        <th>Left Date</th>
                                        <th>Designation</th>
                                        <th>CTC Salary</th>
                                        <th>Status</th>
                                        @if(isset($modules['education_experience_update_permission']) && $modules['education_experience_update_permission'])
                                        <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($show->education_experience_details as $index => $edu)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $edu->degree ?? '--' }}</td>
                                        <td>{{ $edu->institution_name ?? '--' }}</td>
                                        <td>{{ $edu->month_of_passing_year ?? '--' }}</td>
                                        <td>{{ $edu->class_or_mark ?? '--' }}</td>
                                        <td>{{ $edu->company_name ?? '--' }}</td>
                                        <td>{{ $edu->joining_date ?? '--' }}</td>
                                        <td>{{ $edu->left_date ?? '--' }}</td>
                                        <td>{{ $edu->designation ?? '--' }}</td>
                                        <td>{{ $edu->ctc_salary ? '₹' . number_format((int)$edu?->ctc_salary ?? 0, 2) : '--' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $edu->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($edu->status) }}
                                            </span>
                                        </td>
                                        @if(isset($modules['education_experience_update_permission']) && $modules['education_experience_update_permission'])
                                        <td class="text-center">
                                            <a href="{{ route('employee-education-experience.edit', $edu->id) }}" 
                                               class="btn btn-sm btn-light btn-icon" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i> No education or experience details found for this employee.
                        </div>
                        @endif
                    </div>

                    {{-- Salary Details Tab --}}
                    <div class="tab-pane fade" id="navs-pills-left-salary" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ti ti-currency-rupee me-1"></i> Salary Details</h6>
                            @if(isset($modules['salary_details_add_permission']) && $modules['salary_details_add_permission'])
                            <a href="{{ route('employee-wise-salary-details.create', ['employee_id' => $show->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Add Salary Details
                            </a>
                            @endif
                        </div>
                        @if($show?->salary_details && $show->salary_details->count() > 0)
                        @foreach($show->salary_details as $index => $salary)
                        <div class="card mb-3">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Salary Record #{{ $index + 1 }}
                                    <span class="badge bg-{{ $salary->status == 'active' ? 'success' : 'danger' }} ms-2">
                                        {{ ucfirst($salary->status) }}
                                    </span>
                                </h6>
                                @if(isset($modules['salary_details_update_permission']) && $modules['salary_details_update_permission'])
                                <a href="{{ route('employee-wise-salary-details.edit', $salary->id) }}" 
                                   class="btn btn-sm btn-light btn-icon" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Basic Information</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Salary Classification:</strong> {{ $salary->salary_classification ?? '--' }}</p>
                                            <p class="mb-1"><strong>Week Off:</strong> {{ $salary->week_off ?? '--' }}</p>
                                            <p class="mb-1"><strong>Overtime:</strong> {{ $salary->overtime ?? '--' }}</p>
                                            <p class="mb-1"><strong>Leave Eligibility:</strong> {{ $salary->leave_elegiblity ?? '--' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Salary Components</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>Basic DA:</strong> ₹{{ number_format($salary->basic_da ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>HRA:</strong> ₹{{ number_format($salary->hra ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>Conveyance:</strong> ₹{{ number_format($salary->conveyance_allowance ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>Medical:</strong> ₹{{ number_format($salary->medical_allowance ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>Special:</strong> ₹{{ number_format($salary->special_allowance ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>CTC:</strong> <span class="text-success fw-bold">₹{{ number_format((int)$salary?->ctc ?? 0, 2) }}</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-uppercase text-muted">Deductions</small>
                                        <div class="mt-2">
                                            <p class="mb-1"><strong>PF Type:</strong> {{ $salary->pf_type ?? '--' }}</p>
                                            <p class="mb-1"><strong>PF:</strong> ₹{{ number_format((int)$salary?->pf ?? 0, 2) }} ({{ $salary->pf_percentage ?? 0 }}%)</p>
                                            <p class="mb-1"><strong>TDS:</strong> ₹{{ number_format((int)$salary?->tds ?? 0, 2) }} ({{ $salary->tds_percentage ?? 0 }}%)</p>
                                            <p class="mb-1"><strong>PT:</strong> ₹{{ number_format((int)$salary?->pt_amount ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>Insurance:</strong> ₹{{ number_format((int)$salary?->insurance_amount ?? 0, 2) }}</p>
                                            <p class="mb-1"><strong>ESI (Employee):</strong> {{ $salary->esi_employee_side_percentage ?? 0 }}%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i> No salary details found for this employee.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
@endsection

@push('page_scripts')
@if(isset($modules['currentGuard']) && $modules['currentGuard'] === 'admin_software')
<script>
    $(document).on('click', '.copy-login-details-btn', function() {
        var appKey = $(this).data('app-key');
        var username = $(this).data('username');
        var password = $(this).data('password');
        
        var loginDetails = "Login Details:\n" +
            "App Key: " + appKey + "\n" +
            "Username: " + username + "\n" +
            "Password: " + password;
        
        // Copy to clipboard
        navigator.clipboard.writeText(loginDetails).then(function() {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Login details copied to clipboard. You can now share it!',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(function(err) {
            // Fallback for older browsers
            var textArea = document.createElement("textarea");
            textArea.value = loginDetails;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Login details copied to clipboard. You can now share it!',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to copy. Please try again.',
                });
            }
            
            document.body.removeChild(textArea);
        });
    });
</script>
@endif
@endpush
