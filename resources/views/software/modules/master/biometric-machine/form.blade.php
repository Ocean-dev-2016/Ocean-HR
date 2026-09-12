@extends('software.layout.app')

@php
    $i = 0;
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
@endphp

@section('title', $page_title)

@section('page_leavel_style')
@endsection

@section('content')
    <div class="px-1">
        <div class="d-lg-flex justify-content-lg-between flex-column flex-lg-row">
            @include('software.inlcudes.breadcrumb', [
                'breadcrumbArray' => [
                    ['title' => $page_title, 'url' => route($route . '.index')],
                    [
                        'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                        'url' => '',
                    ],
                ],
                'route' => $route,
                'show_back_btn' => true,
            ])
        </div>
    </div>

    <div class="card my-3 mb-4">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    @if (!$company_id)
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Select Company <span class="text-danger">*</span></label>
                                <select id="company_id"
                                    class="form-control select2 search_by_company @error('company_id') is-invalid @enderror"
                                    name="company_id"
                                    data-selectedCompanyId="{{ old('company_id') ?? ($edit->company_id ?? '') }}">
                                    <option value="">Select Company</option>
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    @else
                        <input type="hidden" class="form-control search_by_company" name="company_id"
                            value="{{ $company_id }}" />
                    @endif

                    {{-- Provider Type --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Provider Type <span class="text-danger">*</span></label>
                            <select id="provider_type" class="form-control select2 @error('provider_type') is-invalid @enderror"
                                name="provider_type" required>
                                <option value="">Select Provider</option>
                                <option value="minop" 
                                    @if(isset($edit) && $edit?->provider_type == 'minop') selected 
                                    @elseif(old('provider_type', 'minop') == 'minop') selected @endif>
                                    Minop (Push-based)
                                </option>
                                <option value="etimeoffice"
                                    @if(isset($edit) && $edit?->provider_type == 'etimeoffice') selected
                                    @elseif(old('provider_type') == 'etimeoffice') selected @endif>
                                    eTimeOffice (Pull-based)
                                </option>
                                <option value="mintra"
                                    @if(isset($edit) && $edit?->provider_type == 'mintra') selected
                                    @elseif(old('provider_type') == 'mintra') selected @endif>
                                    Mintra (Pull-based)
                                </option>
                                <option value="old_crm"
                                    @if(isset($edit) && $edit?->provider_type == 'old_crm') selected
                                    @elseif(old('provider_type') == 'old_crm') selected @endif>
                                    Old CRM (Pull-based)
                                </option>
                            </select>
                            <small class="text-muted">
                                <i class="ti ti-info-circle"></i> 
                                Minop: Receives data. eTimeOffice/Mintra/Old CRM: Fetches data via API.
                            </small>
                            @error('provider_type')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Machine Name --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Machine Name</label>
                            <input id="machine_name" type="text" class="form-control @error('machine_name') is-invalid @enderror"
                                name="machine_name" value="{{ isset($edit) && $edit?->machine_name ? $edit?->machine_name : old('machine_name') }}"
                                placeholder="Enter Machine Name">
                            @error('machine_name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- IP Address --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input id="ip_address" type="text" class="form-control @error('ip_address') is-invalid @enderror"
                                name="ip_address" value="{{ isset($edit) && $edit?->ip_address ? $edit?->ip_address : old('ip_address') }}"
                                placeholder="Enter IP Address (e.g., 192.168.1.1)" required>
                            @error('ip_address')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Port --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Port <span class="text-danger">*</span></label>
                            <input id="port" type="number" class="form-control @error('port') is-invalid @enderror"
                                name="port" value="{{ isset($edit) && $edit?->port ? $edit?->port : old('port') }}"
                                placeholder="Enter Port (1-65535)" min="1" max="65535" required>
                            @error('port')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- API Configuration Section (for pull-based providers) --}}
                    <div id="api_config_section" style="display: none;">
                        <div class="divider">
                            <hr />
                            <h6 class="text-muted">API Configuration (Required for Pull-based Providers)</h6>
                        </div>

                        {{-- API URL --}}
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">API URL <span class="text-danger">*</span></label>
                                <input id="api_url" type="url" 
                                    class="form-control @error('api_url') is-invalid @enderror"
                                    name="api_url" 
                                    value="{{ isset($edit) && $edit?->api_url ? $edit?->api_url : old('api_url', 'https://api.etimeoffice.com/api/') }}"
                                    placeholder="https://api.etimeoffice.com/api/">
                                @error('api_url')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Authentication Type --}}
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Authentication Type <span class="text-danger">*</span></label>
                                <select id="auth_type" class="form-control select2 @error('auth_type') is-invalid @enderror"
                                    name="auth_type" required>
                                    <option value="">Select Auth Type</option>
                                    <option value="basic"
                                        @if(isset($edit) && $edit?->auth_type == 'basic') selected
                                        @elseif(old('auth_type', 'basic') == 'basic') selected @endif>
                                        Basic Auth (Username/Password)
                                    </option>
                                    <option value="bearer_token"
                                        @if(isset($edit) && $edit?->auth_type == 'bearer_token') selected
                                        @elseif(old('auth_type') == 'bearer_token') selected @endif>
                                        Bearer Token
                                    </option>
                                    <option value="api_key"
                                        @if(isset($edit) && $edit?->auth_type == 'api_key') selected
                                        @elseif(old('auth_type') == 'api_key') selected @endif>
                                        API Key
                                    </option>
                                    <option value="custom"
                                        @if(isset($edit) && $edit?->auth_type == 'custom') selected
                                        @elseif(old('auth_type') == 'custom') selected @endif>
                                        Custom Headers
                                    </option>
                                </select>
                                @error('auth_type')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Basic Auth Fields --}}
                        <div id="basic_auth_fields" style="display: none;">
                            {{-- Corporate ID (eTimeOffice only) --}}
                            <div id="corporate_id_field" class="col-md-4 col-sm-12 mb-3" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label">Corporate ID <span class="text-danger">*</span></label>
                                    <input id="corporate_id" type="text" 
                                        class="form-control @error('corporate_id') is-invalid @enderror"
                                        name="corporate_id" 
                                        value="{{ isset($edit) && $edit?->corporate_id ? $edit?->corporate_id : old('corporate_id') }}"
                                        placeholder="Enter Corporate ID">
                                    <small class="text-muted">Required for eTimeOffice provider</small>
                                    @error('corporate_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- API Username --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">API Username <span class="text-danger">*</span></label>
                                    <input id="api_username" type="text" 
                                        class="form-control @error('api_username') is-invalid @enderror"
                                        name="api_username" 
                                        value="{{ isset($edit) && $edit?->api_username ? $edit?->api_username : old('api_username') }}"
                                        placeholder="Enter API Username">
                                    @error('api_username')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- API Password --}}
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">API Password</label>
                                    <div class="input-group">
                                        <input id="api_password" type="password" 
                                            class="form-control @error('api_password') is-invalid @enderror"
                                            name="api_password" 
                                            value=""
                                            placeholder="Enter API Password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle_password">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        @if(isset($edit) && $edit?->api_password)
                                            Leave blank to keep existing password
                                        @else
                                            Optional - can be set later
                                        @endif
                                    </small>
                                    @error('api_password')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Bearer Token Fields --}}
                        <div id="bearer_token_fields" class="col-md-4 col-sm-12 mb-3" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Bearer Token <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input id="bearer_token" type="password" 
                                        class="form-control @error('bearer_token') is-invalid @enderror"
                                        name="bearer_token" 
                                        value="{{ isset($edit) && $edit?->bearer_token ? $edit?->bearer_token : old('bearer_token') }}"
                                        placeholder="Enter Bearer Token">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle_bearer_token">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                @error('bearer_token')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- API Key Fields --}}
                        <div id="api_key_fields" style="display: none;">
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">API Key Name <span class="text-danger">*</span></label>
                                    <input id="api_key_name" type="text" 
                                        class="form-control @error('api_key_name') is-invalid @enderror"
                                        name="api_key_name" 
                                        value="{{ isset($edit) && $edit?->api_key_name ? $edit?->api_key_name : old('api_key_name') }}"
                                        placeholder="e.g., X-API-Key">
                                    @error('api_key_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">API Key Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input id="api_key_value" type="password" 
                                            class="form-control @error('api_key_value') is-invalid @enderror"
                                            name="api_key_value" 
                                            value=""
                                            placeholder="Enter API Key Value">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle_api_key">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        @if(isset($edit) && $edit?->api_key_value)
                                            Leave blank to keep existing key
                                        @endif
                                    </small>
                                    @error('api_key_value')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Custom Headers Fields --}}
                        <div id="custom_headers_fields" class="col-md-12 col-sm-12 mb-3" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Custom Headers (JSON) <span class="text-danger">*</span></label>
                                <textarea id="custom_headers" 
                                    class="form-control @error('custom_headers') is-invalid @enderror"
                                    name="custom_headers" 
                                    rows="4"
                                    placeholder='{"Authorization": "Bearer token", "X-Custom-Header": "value"}'>{{ isset($edit) && $edit?->custom_headers ? (is_array($edit->custom_headers) ? json_encode($edit->custom_headers, JSON_PRETTY_PRINT) : $edit->custom_headers) : old('custom_headers') }}</textarea>
                                <small class="text-muted">Enter custom headers as JSON object</small>
                                @error('custom_headers')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Sync Interval --}}
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Sync Interval (Minutes)</label>
                                <input id="sync_interval_minutes" type="number" 
                                    class="form-control @error('sync_interval_minutes') is-invalid @enderror"
                                    name="sync_interval_minutes" 
                                    value="{{ isset($edit) && $edit?->sync_interval_minutes ? $edit?->sync_interval_minutes : old('sync_interval_minutes', 60) }}"
                                    placeholder="60" min="1" max="1440">
                                <small class="text-muted">How often to sync attendance (default: 60 minutes)</small>
                                @error('sync_interval_minutes')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Test Connection Button --}}
                        <div class="col-md-12 col-sm-12 mb-3">
                            <button type="button" id="test_connection_btn" class="btn btn-info">
                                <i class="ti ti-plug"></i> Test Connection
                            </button>
                            <div id="test_connection_result" class="mt-2" style="display: none;"></div>
                        </div>
                    </div>

                    {{-- Set as Active --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                    @if(isset($edit) && $edit?->is_active) checked
                                    @elseif(old('is_active')) checked @endif>
                                <label class="form-check-label" for="is_active">
                                    Set as Active Machine
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"
                                placeholder="Enter description" rows="3">{{ isset($edit) && $edit?->description ? $edit?->description : old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="divider">
                        <hr />
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
@endsection

@push('page_scripts')
    @if (!$company_id)
        @include('utils.getCompany')
    @endif

    <script>
        // Setup AJAX with CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            }
        });

        $(document).ready(function() {
            // Ensure CSRF token is available
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
            if (!csrfToken) {
                console.error('CSRF token not found!');
            }
            // Initialize provider type on page load
            toggleApiFields();
            toggleAuthFields();

            // Handle provider type change
            $('#provider_type').on('change', function() {
                toggleApiFields();
                toggleAuthFields();
            });

            // Handle auth type change
            $('#auth_type').on('change', function() {
                toggleAuthFields();
            });

            // Toggle password visibility
            $('#toggle_password').on('click', function() {
                const passwordField = $('#api_password');
                const icon = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                }
            });

            // Toggle bearer token visibility
            $('#toggle_bearer_token').on('click', function() {
                const tokenField = $('#bearer_token');
                const icon = $(this).find('i');
                
                if (tokenField.attr('type') === 'password') {
                    tokenField.attr('type', 'text');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                } else {
                    tokenField.attr('type', 'password');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                }
            });

            // Toggle API key visibility
            $('#toggle_api_key').on('click', function() {
                const keyField = $('#api_key_value');
                const icon = $(this).find('i');
                
                if (keyField.attr('type') === 'password') {
                    keyField.attr('type', 'text');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                } else {
                    keyField.attr('type', 'password');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                }
            });

            // Test connection button
            $('#test_connection_btn').on('click', function() {
                const providerType = $('#provider_type').val();
                const machineId = '{{ isset($edit) && $edit?->id ? $edit->id : "" }}';
                
                if (!providerType || (providerType !== 'etimeoffice' && providerType !== 'mintra' && providerType !== 'old_crm')) {
                    showTestResult('error', 'Please select a pull-based provider (eTimeOffice, Mintra, or Old CRM)');
                    return;
                }

                // Validate required fields based on auth type
                const apiUrl = $('#api_url').val();
                const authType = $('#auth_type').val();

                if (!apiUrl) {
                    showTestResult('error', 'API URL is required');
                    return;
                }

                if (!authType) {
                    showTestResult('error', 'Authentication Type is required');
                    return;
                }

                // Validate based on auth type
                if (authType === 'basic') {
                    const apiUsername = $('#api_username').val();
                    if (!apiUsername) {
                        showTestResult('error', 'API Username is required for Basic Auth');
                        return;
                    }
                    if (providerType === 'etimeoffice') {
                        const corporateId = $('#corporate_id').val();
                        if (!corporateId) {
                            showTestResult('error', 'Corporate ID is required for eTimeOffice');
                            return;
                        }
                    }
                } else if (authType === 'bearer_token') {
                    const bearerToken = $('#bearer_token').val();
                    if (!bearerToken) {
                        showTestResult('error', 'Bearer Token is required');
                        return;
                    }
                } else if (authType === 'api_key') {
                    const apiKeyName = $('#api_key_name').val();
                    const apiKeyValue = $('#api_key_value').val();
                    if (!apiKeyName || !apiKeyValue) {
                        showTestResult('error', 'API Key Name and Value are required');
                        return;
                    }
                } else if (authType === 'custom') {
                    const customHeaders = $('#custom_headers').val();
                    if (!customHeaders) {
                        showTestResult('error', 'Custom Headers are required');
                        return;
                    }
                }

                // Show loading state
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<i class="ti ti-loader-2 spin"></i> Testing...');

                // Prepare data based on auth type
                const testData = {
                    provider_type: providerType,
                    api_url: apiUrl,
                    auth_type: authType,
                };

                if (authType === 'basic') {
                    testData.corporate_id = $('#corporate_id').val();
                    testData.api_username = $('#api_username').val();
                    testData.api_password = $('#api_password').val();
                } else if (authType === 'bearer_token') {
                    testData.bearer_token = $('#bearer_token').val();
                } else if (authType === 'api_key') {
                    testData.api_key_name = $('#api_key_name').val();
                    testData.api_key_value = $('#api_key_value').val();
                } else if (authType === 'custom') {
                    testData.custom_headers = $('#custom_headers').val();
                }

                // Make AJAX call to test connection
                const testUrl = machineId && machineId !== '' 
                    ? '{{ route("biometric-machines.test-connection", ":id") }}'.replace(':id', machineId)
                    : '{{ route("biometric-machines.test-connection", 0) }}';
                
                // Get CSRF token
                const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
                
                // Add CSRF token to data as well (Laravel accepts both header and form data)
                testData._token = csrfToken;
                
                $.ajax({
                    url: testUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: testData,
                    success: function(response) {
                        if (response.status) {
                            showTestResult('success', response.message || 'Connection successful!');
                        } else {
                            showTestResult('error', response.message || 'Connection failed');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Connection test failed. Please check your credentials.';
                        showTestResult('error', errorMsg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function toggleApiFields() {
                const providerType = $('#provider_type').val();
                const isPullBased = providerType === 'etimeoffice' || providerType === 'mintra' || providerType === 'old_crm';
                
                if (isPullBased) {
                    $('#api_config_section').show();
                    $('#api_url, #auth_type').prop('required', true);
                } else {
                    $('#api_config_section').hide();
                    $('#api_url, #auth_type').prop('required', false);
                }
            }

            function toggleAuthFields() {
                const providerType = $('#provider_type').val();
                const authType = $('#auth_type').val();
                const isPullBased = providerType === 'etimeoffice' || providerType === 'mintra' || providerType === 'old_crm';
                
                if (!isPullBased) {
                    // Hide all auth fields if not pull-based
                    $('#basic_auth_fields, #bearer_token_fields, #api_key_fields, #custom_headers_fields').hide();
                    return;
                }

                // Hide all auth fields first
                $('#basic_auth_fields').hide();
                $('#bearer_token_fields').hide();
                $('#api_key_fields').hide();
                $('#custom_headers_fields').hide();

                // Remove required from all auth fields
                $('#corporate_id, #api_username, #api_password, #bearer_token, #api_key_name, #api_key_value, #custom_headers')
                    .prop('required', false);

                // Show fields based on auth type
                if (authType === 'basic') {
                    $('#basic_auth_fields').show();
                    $('#api_username').prop('required', true);
                    
                    // Show corporate ID only for eTimeOffice
                    if (providerType === 'etimeoffice') {
                        $('#corporate_id_field').show();
                        $('#corporate_id').prop('required', true);
                    } else {
                        $('#corporate_id_field').hide();
                    }
                    
                    // Password is optional (not required)
                } else if (authType === 'bearer_token') {
                    $('#bearer_token_fields').show();
                    $('#bearer_token').prop('required', true);
                } else if (authType === 'api_key') {
                    $('#api_key_fields').show();
                    $('#api_key_name, #api_key_value').prop('required', true);
                } else if (authType === 'custom') {
                    $('#custom_headers_fields').show();
                    $('#custom_headers').prop('required', true);
                }
            }

            function showTestResult(type, message) {
                const resultDiv = $('#test_connection_result');
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'ti-check-circle' : 'ti-alert-circle';
                
                resultDiv.html(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="ti ${icon} me-2"></i>${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `).show();
            }
        });
    </script>
@endpush

