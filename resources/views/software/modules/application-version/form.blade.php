@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;

@endphp
@section('title', $page_title)


@section('content')
    <div class="px-1">
    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => (isset($edit) && $edit?->id) ? "Edit ".$page_title : "Create ".$page_title , 'url' => ''],
            ],
        ])
        <a class="btn btn-primary waves-effect waves-light text-white" href="{{ url()->previous() }}">
            <i class="menu-icon ti ti-chevrons-left"></i> Back
        </a>
    </div>
</div>
    <div class="card my-3 mb-4">
        {{-- <h5 class="card-header"></h5> --}}
        <div class="card-body">
            <form action="{{ route('application-version.store') }}" method="POST" enctype="multipart/form-data" id="import-form">
                @csrf
                <div class="row">

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Application Version <span class="text-danger">*</span> </label>
                            <input id="version" type="text"
                                class="form-control @error('version') is-invalid @enderror" name="version"
                                value="{{ isset($edit) && $edit?->version ? $edit?->version : old('version') }}"
                                placeholder="Enter Application Version">
                            @error('version')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Application APK File <span class="text-danger">*</span> </label>
                            <input id="apk_file" type="file"
                                class="form-control @error('apk_file') is-invalid @enderror" name="apk_file"
                                value="{{ isset($edit?->apk_file) ? $edit?->apk_file : old('apk_file') }}"/>

                                @if (isset($edit) && $edit?->apk_file && $edit?->apk_file_url)
                                    <a href="{{ $edit?->apk_file_url ?? '#' }}" target="_blank"
                                        class="" for="inputGroupFile02">View File</a>
                                @endif

                            @error('apk_file')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mt-4">
                        <div class="form-group d-flex align-items-center">
                            <label class="form-label me-3 mb-0">Force Update</label>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="is_force_update" name="is_force_update"
                                    value="1" {{ old('is_force_update', $edit->is_force_update ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label mb-0" for="is_force_update">Yes</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Application Update Message</label>
                            <textarea rows="3" cols="3" class="form-control @error('update_message') is-invalid @enderror" name="update_message" placeholder="Enter Application Update Message" id="update_message">{{ isset($edit) && $edit?->update_message ? $edit?->update_message : old('update_message') }}</textarea>
                            @error('update_message')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="divider">
                        <hr />
                    </div>
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">
                            Submit
                        </button>

                    </div>
                </div>
            </form>
        </div>




    </div>
@endsection
@push('page_scripts')
@endpush
