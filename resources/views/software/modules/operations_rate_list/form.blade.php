@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $company_id = isset($modules['company_id']) ? $modules['company_id'] : null;
    $is_edit = isset($edit) ? true : false;
    $op       = old('operation') ?? ($is_edit ? ($edit->operation ?? '') : '');
    $is_lathe = ($op === 'Lathe Employee wise');
    $is_core  = ($op === 'CORE');
    $srNoOpsArr = [];
    $showSrNo = in_array($op, $srNoOpsArr);
    $totalAsLabelOpsArr = ['CLEANING', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'];
    $isTotalLabel = in_array($op, $totalAsLabelOpsArr);
    $is_repair_op = in_array($op, ['BUFF', 'RRL', 'FLEXIBLE', 'ASS-2', 'COATING', 'FOUNDRY']);
    $is_foundry_op = ($op === 'FOUNDRY');
    $show_rej_rate = in_array($op, ['BUFF', 'RRL', 'FLEXIBLE', 'ASS-2', 'COATING']);
    $is_ot_op     = in_array($op, ['RRL', 'FLEXIBLE']);
    $date_colspan = 31;
    if ($is_ot_op) {
        $date_colspan = 93;
    } elseif ($is_repair_op) {
        $date_colspan = 62;
    }
    $hideGradeOpsArr = ['ASS-2', 'BUTTERFLY', 'FOUNDRY', 'CORE'];
    $showGrade = !in_array($op, $hideGradeOpsArr);
    $footer_colspan = 2; // Action + Name
    if ($showSrNo) $footer_colspan++;
    if ($showGrade) $footer_colspan++;
    $globalEmployeeOpsArr = ['Lathe Employee wise', 'CLEANING', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'];

    // Ensure variables always exist (edit mode passes them too)
    $contractEmployees  = $contractEmployees  ?? collect();
    $contractProcesses  = $contractProcesses  ?? collect();
    $products           = $products           ?? collect();
    $grades             = $grades             ?? collect();
@endphp

@section('title', $page_title . ($is_edit ? ' - Edit' : ' - Create'))

@section('page_leavel_style')
    <style>
        .grid-input {
            width: 65px; text-align: center; padding: 2px 4px; font-size: 0.78rem;
        }
        /* Hide spin buttons for number inputs */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type=number] {
            -moz-appearance: textfield !important;
            appearance: none !important;
        }

        .table-responsive { overflow-x: auto; position: relative; }
        .operation-table { border-collapse: separate; border-spacing: 0; }
        .operation-table th, .operation-table td {
            min-width: 55px; font-size: 0.78rem; padding: 4px; vertical-align: middle;
        }
        .sticky-col {
            position: sticky; left: 0; background-color: #f8f9fa !important; z-index: 4;
        }
        .sticky-col-2 {
            position: sticky; left: 62px; background-color: #f8f9fa !important; z-index: 4; min-width: 280px;
        }
        .sticky-col-3 {
            position: sticky; left: 342px; background-color: #f8f9fa !important; z-index: 4; min-width: 140px;
        }
        .sticky-footer-label {
            position: sticky; left: 0; background-color: #f8f9fa !important; z-index: 4;
        }
        .operation-table tbody .sticky-col,
        .operation-table tbody .sticky-col-2,
        .operation-table tbody .sticky-col-3 {
            background-color: #fff !important;
            z-index: 3;
        }
        .operation-table thead .sticky-col,
        .operation-table thead .sticky-col-2,
        .operation-table thead .sticky-col-3,
        .operation-table tfoot .sticky-footer-label {
            box-shadow: 1px 0 0 #d9dee3;
        }
        td:nth-child(3) { min-width: 280px; }
        .select2-hidden-accessible { display: none !important; visibility: hidden !important; }
        .select2-container { display: block !important; }

        /* Week-off column styling (Wednesday) - completely hidden */
        .week-off-col { display: none !important; }
        .week-off-header { display: none !important; }
        .day-beyond-month { display: none !important; }
    </style>
@endsection

@section('content')
<div class="px-1">
    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-start align-items-lg-center">
        @include('software.inlcudes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => $is_edit ? 'Edit' : 'Create', 'url' => ''],
            ],
            'route'           => $route,
            'show_add_btn'    => false,
            'show_filter_btn' => false,
            'show_back_btn'   => false,
        ])
        <a class="btn btn-primary waves-effect waves-light text-white btn-sm"
            href="{{ route($route . '.index') }}">
            <i class="menu-icon ti ti-chevrons-left"></i> Back
        </a>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                    <form action="{{ $is_edit ? route($route . '.update', $edit->id) : route($route . '.store') }}"
                        method="POST" id="operation_rate_form" novalidate>
                    @csrf
                    @if ($is_edit) @method('PUT') @endif

                    {{-- ===== Top filters ===== --}}
                    <div class="row mb-4">

                        {{-- Company --}}
                        @if (!$company_id)
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Company</label>
                                <select name="company_id" id="company_id" class="form-select select2"
                                        required {{ $is_edit ? 'disabled' : '' }}>
                                    <option value="">Select Company</option>
                                    @foreach (\App\Models\Company::all() as $comp)
                                        <option value="{{ $comp->id }}"
                                            {{ (old('company_id', isset($edit) ? $edit->company_id : '')) == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($is_edit)
                                    <input type="hidden" name="company_id" value="{{ $edit->company_id }}">
                                @endif
                            </div>
                        @else
                            <input type="hidden" name="company_id" value="{{ $company_id }}" id="company_id">
                        @endif

                        {{-- Operation --}}
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Operation</label>
                            <select name="operation" id="operation_select" class="form-select select2"
                                    required {{ $is_edit ? 'disabled' : '' }}>
                                <option value="">Select Operation</option>
                                @foreach ($operations as $op)
                                    <option value="{{ $op }}"
                                        {{ (old('operation', isset($edit) ? $edit->operation : '')) == $op ? 'selected' : '' }}>
                                        {{ $op }}
                                    </option>
                                @endforeach
                            </select>
                            @if($is_edit)
                                <input type="hidden" name="operation" value="{{ $edit->operation }}">
                            @endif
                        </div>

                        {{-- Top Name selector --}}
                        <div class="col-md-3 mb-2" id="top_name_wrap" style="display: none;">
                            <label class="form-label" id="top_name_label">Name</label>
                            <select name="global_employee_id" id="global_employee_id" class="form-control select2">
                                <option value="">Select Name</option>
                                @foreach($contractEmployees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ (old('global_employee_id', (isset($edit) && in_array($edit->operation, $globalEmployeeOpsArr) ? $edit->employee_id : ''))) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->employee_code }} - {{ $emp->proper_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Month --}}
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Month</label>
                            <select name="month" id="month_select" class="form-select select2"
                                    required {{ $is_edit ? 'disabled' : '' }}>
                                <option value="">Select Month</option>
                                @foreach (config('constants.months') as $m_num => $m_name)
                                    <option value="{{ $m_num }}"
                                        {{ (old('month', isset($edit) ? $edit->month : date('n'))) == $m_num ? 'selected' : '' }}>
                                        {{ $m_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($is_edit)
                                <input type="hidden" name="month" value="{{ $edit->month }}">
                            @endif
                        </div>

                        {{-- Year --}}
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Year</label>
                            <select name="year" id="year_select" class="form-select select2"
                                    required {{ $is_edit ? 'disabled' : '' }}>
                                <option value="">Select Year</option>
                                @for ($y = date('Y') - 5; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}"
                                        {{ (old('year', isset($edit) ? $edit->year : date('Y'))) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                            @if($is_edit)
                                <input type="hidden" name="year" value="{{ $edit->year }}">
                            @endif
                        </div>

                        {{-- Add button --}}
                        <div class="col-md-3 d-flex align-items-end mb-2" id="btn_add_row">
                            <button type="button" id="add_product_to_list" class="btn btn-primary w-100">
                                <i class="ti ti-plus"></i> <span id="add_btn_label">Add Product</span>
                            </button>
                        </div>
                    </div>

                    {{-- ===== Grid table ===== --}}
                    <div class="table-responsive">
                        <table class="table table-bordered operation-table" id="items_table">
                            <thead class="table-light text-center">
                                <tr>
                                    <th rowspan="3" class="sticky-col align-middle">Action</th>
                                    <th rowspan="3" class="sticky-col align-middle sr-no-col" id="col_header_sr_no" style="min-width: 50px; {{ $showSrNo ? '' : 'display: none;' }}">SR NO</th>
                                    <th rowspan="3" class="sticky-col-2 align-middle" id="col_header_name" style="{{ $is_lathe ? 'display: none;' : '' }}">
                                        @if($is_core) NAME @elseif($isTotalLabel) PRODUCT NAME @else Product Name @endif
                                    </th>
                                    <th rowspan="3" class="align-middle sticky-col-3" id="col_header_grade" style="{{ $showGrade ? '' : 'display: none;' }}">
                                        @if($is_lathe) Contract Process @elseif($is_core) ITEM @elseif($isTotalLabel) GRADE @else Grade @endif
                                    </th>
                                    @if($is_lathe)
                                        <th rowspan="3" class="align-middle" id="th_rate">RATE</th>
                                        <th colspan="{{ $date_colspan }}" class="text-center" id="th_date_group">DATE</th>
                                        <th rowspan="3" class="align-middle" id="th_total_qty">{{ $isTotalLabel ? 'TOTAL' : 'TOTAL QTY' }}</th>
                                        <th rowspan="3" class="align-middle" id="th_total_r" style="display: none;">TOTAL R</th>
                                    @else
                                        <th colspan="{{ $date_colspan }}" class="text-center" id="th_date_group">DATE</th>
                                        <th rowspan="3" class="align-middle" id="th_total_qty">{{ $is_repair_op ? 'TOTAL QTY' : ($isTotalLabel ? 'TOTAL' : 'TOTAL QTY') }}</th>
                                        <th rowspan="3" class="align-middle" id="th_total_r" style="{{ $is_repair_op ? '' : 'display: none;' }}">TOTAL R</th>
                                        <th rowspan="3" class="align-middle" id="th_rate">RATE</th>
                                        <th rowspan="3" class="align-middle" id="th_ot_rate" style="{{ $is_ot_op ? '' : 'display: none;' }}">OT RATE</th>
                                        <th rowspan="3" class="align-middle" id="th_rej_rate" style="{{ $show_rej_rate ? '' : 'display: none;' }}">REJ. RATE</th>
                                    @endif
                                    <th rowspan="3" class="align-middle" id="th_amount">
                                        @if($is_lathe) AMOUNT @elseif($is_core) AMT @else TOTAL AMT @endif
                                    </th>
                                </tr>
                                <tr id="row_header_2">
                                    @for ($i = 1; $i <= 31; $i++)
                                        @php
                                            $day_colspan = 1;
                                            if ($is_ot_op) $day_colspan = 3;
                                            elseif ($is_repair_op) $day_colspan = 2;
                                            $day_rowspan = ($day_colspan > 1) ? 1 : 2;
                                        @endphp
                                        <th colspan="{{ $day_colspan }}" rowspan="{{ $day_rowspan }}" class="day-header" id="day_header_{{ $i }}" data-day="{{ $i }}">
                                            {{ $i }}
                                        </th>
                                    @endfor
                                </tr>
                                <tr id="row_header_3" style="{{ ($is_repair_op || $is_ot_op) ? 'background-color: #f1f1f1;' : 'display: none;' }}">
                                    @for ($i = 1; $i <= 31; $i++)
                                        <th class="day-sub-label day-label-main" id="day_label_{{ $i }}" style="font-size: 0.65rem; font-weight: bold; {{ ($is_repair_op || $is_ot_op) ? '' : 'display: none;' }}">
                                            @if($is_foundry_op) DAY @elseif($is_ot_op || $is_repair_op) Qty @endif
                                        </th>
                                        <th class="day-sub-label r-col-header" id="r_header_{{ $i }}" data-day="{{ $i }}" style="font-size: 0.65rem; font-weight: bold; {{ $is_repair_op ? '' : 'display: none;' }}">
                                            {{ $is_foundry_op ? 'NIGHT' : 'R' }}
                                        </th>
                                        <th class="day-sub-label ot-col-header" id="ot_header_{{ $i }}" data-day="{{ $i }}" style="font-size: 0.65rem; font-weight: bold; {{ $is_ot_op ? '' : 'display: none;' }}">OT</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $display_items = [];
                                    if (old('items')) {
                                        foreach (old('items') as $idx => $old_v) {
                                            $display_items[] = (object) $old_v;
                                        }
                                    } elseif (isset($edits) && count($edits) > 0) {
                                        $display_items = $edits;
                                    }
                                @endphp

                                @if (count($display_items) > 0)
                                    @foreach($display_items as $index => $item_edit)
                                        <tr class="item-row" data-row-index="{{ $index }}">
                                            <td class="sticky-col">
                                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                            <td class="sticky-col align-middle text-center sr-no-cell" style="{{ $showSrNo ? '' : 'display: none;' }}">{{ $index + 1 }}</td>
                                            <td class="sticky-col-2 td-primary-select" style="{{ $is_lathe ? 'display: none;' : '' }}">
                                                @if($is_lathe)
                                                    <select name="items[{{ $index }}][employee_id]"
                                                            class="form-control select2 primary-select">
                                                        <option value="">Select Operator</option>
                                                        @foreach($contractEmployees as $emp)
                                                            <option value="{{ $emp->id }}"
                                                                {{ ($item_edit->employee_id ?? '') == $emp->id ? 'selected' : '' }}>
                                                                {{ $emp->employee_code }} - {{ $emp->proper_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <select name="items[{{ $index }}][product_id]"
                                                            class="form-control select2 primary-select">
                                                        <option value="">Select Product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-rate="{{ $product->rate }}" data-ot-rate="{{ $product->ot_text }}" data-rejection-rate="{{ $product->rejection_rate }}"
                                                                {{ ($item_edit->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            <td class="td-secondary-select sticky-col-3" style="{{ $showGrade ? '' : 'display: none;' }}">
                                                @if($is_lathe)
                                                    <select name="items[{{ $index }}][product_id]"
                                                            class="form-control select2 secondary-select">
                                                        <option value="">Select Process</option>
                                                        @foreach ($contractProcesses as $cp)
                                                            <option value="{{ $cp->id }}"
                                                                data-rate="{{ $cp->rate }}"
                                                                data-ot-rate="{{ $cp->ot_rate }}"
                                                                data-rejection-rate="{{ $cp->rejection_rate }}"
                                                                {{ $cp->id == ($item_edit->product_id ?? ($item_edit->contract_process_id ?? '')) ? 'selected' : '' }}>
                                                                {{ $cp->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($is_core)
                                                    <select name="items[{{ $index }}][grade_id]"
                                                            class="form-control select2 secondary-select">
                                                        <option value="">Select Grade</option>
                                                        @foreach($grades as $grade)
                                                            <option value="{{ $grade->id }}"
                                                                {{ ($item_edit->grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                                                                {{ $grade->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <select name="items[{{ $index }}][grade_id]"
                                                            class="form-control select2 secondary-select">
                                                        <option value="">Select Grade</option>
                                                        @foreach($grades as $grade)
                                                            <option value="{{ $grade->id }}"
                                                                {{ ($item_edit->grade_id ?? '') == $grade->id ? 'selected' : '' }}>
                                                                {{ $grade->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>

                                            @if($is_lathe)
                                                <td class="td-rate">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][rate]"
                                                        class="form-control grid-input rate"
                                                        value="{{ number_format((float)($item_edit->rate ?? 0), 2, '.', '') }}" readonly style="background-color: #f8f9fa;">
                                                </td>
                                            @endif

                                            @for ($i = 1; $i <= 31; $i++)
                                                <td class="day-cell" data-day="{{ $i }}">
                                                    <input type="number" step="0.01"
                                                        name="items[{{ $index }}][day_{{ $i }}]"
                                                        class="form-control grid-input day-qty"
                                                        value="{{ (isset($item_edit->{'day_'.$i}) && $item_edit->{'day_'.$i} !== null && (float)$item_edit->{'day_'.$i} != 0) ? (float)$item_edit->{'day_'.$i} : '' }}">
                                                </td>
                                                <td class="r-cell" data-day="{{ $i }}" style="{{ $is_repair_op ? '' : 'display: none;' }}">
                                                    <input type="number" step="0.01"
                                                        name="items[{{ $index }}][day_{{ $i }}_r]"
                                                        class="form-control grid-input day-qty-r"
                                                        value="{{ (isset($item_edit->{'day_'.$i.'_r'}) && $item_edit->{'day_'.$i.'_r'} !== null && (float)$item_edit->{'day_'.$i.'_r'} != 0) ? (float)$item_edit->{'day_'.$i.'_r'} : '' }}">
                                                </td>
                                                <td class="ot-cell" data-day="{{ $i }}" style="{{ $is_ot_op ? '' : 'display: none;' }}">
                                                    <input type="number" step="0.01"
                                                        name="items[{{ $index }}][day_{{ $i }}_ot]"
                                                        class="form-control grid-input day-qty-ot"
                                                        value="{{ (isset($item_edit->{'day_'.$i.'_ot'}) && $item_edit->{'day_'.$i.'_ot'} !== null && (float)$item_edit->{'day_'.$i.'_ot'} != 0) ? (float)$item_edit->{'day_'.$i.'_ot'} : '' }}">
                                                </td>
                                            @endfor

                                            <td class="td-total-qty">
                                                <input type="text" name="items[{{ $index }}][total_qty]"
                                                    class="form-control grid-input total-qty"
                                                    value="{{ (float)($item_edit->total_qty ?? 0) }}" readonly>
                                            </td>

                                            <td class="td-total-r" style="{{ $is_repair_op ? '' : 'display: none;' }}">
                                                <input type="text" name="items[{{ $index }}][total_r]"
                                                    class="form-control grid-input total-r"
                                                    value="{{ (float)($item_edit->total_r ?? 0) }}" readonly>
                                            </td>

                                            @if(!$is_lathe)
                                                <td class="td-rate">
                                                    <input type="number" step="0.01" name="items[{{ $index }}][rate]"
                                                        class="form-control grid-input rate"
                                                        value="{{ number_format((float)($item_edit->rate ?? 0), 2, '.', '') }}" readonly style="background-color: #f8f9fa;">
                                                </td>
                                                <td class="td-ot-rate" style="{{ $is_ot_op ? '' : 'display: none;' }}">
                                                    <input type="number" step="0.01" class="form-control grid-input row-ot-rate"
                                                        value="{{ number_format((float)($item_edit->product?->ot_text ?? (isset($item_edit->product_id) ? (\App\Models\Product::find($item_edit->product_id)?->ot_text ?? 0) : 0)), 2, '.', '') }}" readonly style="background-color: #f8f9fa;">
                                                </td>
                                                <td class="td-rej-rate" style="{{ $show_rej_rate ? '' : 'display: none;' }}">
                                                    <input type="number" step="0.01" class="form-control grid-input row-rejection-rate"
                                                        value="{{ number_format((float)($item_edit->product?->rejection_rate ?? (isset($item_edit->product_id) ? (\App\Models\Product::find($item_edit->product_id)?->rejection_rate ?? 0) : 0)), 2, '.', '') }}" readonly style="background-color: #f8f9fa;">
                                                </td>
                                            @endif

                                            <td class="td-amount">
                                                <input type="text" name="items[{{ $index }}][total_amount]"
                                                    class="form-control grid-input total-amount"
                                                    value="{{ number_format((float)($item_edit->total_amount ?? 0), 2, '.', '') }}" readonly>
                                            </td>
                                        </tr>
                                    @endforeach

                                @else
                                    {{-- Create mode: single default row --}}
                                    <tr class="item-row" data-row-index="0">
                                        <td class="sticky-col">
                                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                        <td class="sticky-col align-middle text-center sr-no-cell" style="{{ $showSrNo ? '' : 'display: none;' }}">1</td>
                                        <td class="sticky-col-2 td-primary-select" style="{{ $is_lathe ? 'display: none;' : '' }}">
                                            <select name="items[0][product_id]"
                                                    class="form-control select2 primary-select">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-rate="{{ $product->rate }}" data-ot-rate="{{ $product->ot_text }}" data-rejection-rate="{{ $product->rejection_rate }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="td-secondary-select sticky-col-3" style="{{ $showGrade ? '' : 'display: none;' }}">
                                            <select name="items[0][grade_id]"
                                                    class="form-control select2 secondary-select">
                                                <option value="">Select Grade</option>
                                                @foreach($grades as $grade)
                                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        
                                        @if($is_lathe)
                                            <td class="td-rate"><input type="number" step="0.01" name="items[0][rate]" class="form-control grid-input rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>
                                        @endif

                                        @for ($i = 1; $i <= 31; $i++)
                                            <td class="day-cell" data-day="{{ $i }}">
                                                <input type="number" step="0.01"
                                                    name="items[0][day_{{ $i }}]"
                                                    class="form-control grid-input day-qty" value="">
                                            </td>
                                            <td class="r-cell" data-day="{{ $i }}" style="{{ $is_repair_op ? '' : 'display: none;' }}">
                                                <input type="number" step="0.01"
                                                    name="items[0][day_{{ $i }}_r]"
                                                    class="form-control grid-input day-qty-r" value="">
                                            </td>
                                            <td class="ot-cell" data-day="{{ $i }}" style="{{ $is_ot_op ? '' : 'display: none;' }}">
                                                <input type="number" step="0.01"
                                                    name="items[0][day_{{ $i }}_ot]"
                                                    class="form-control grid-input day-qty-ot" value="">
                                            </td>
                                        @endfor
                                        
                                        <td class="td-total-qty">
                                            <input type="text" name="items[0][total_qty]"
                                                class="form-control grid-input total-qty" value="0" readonly>
                                        </td>

                                        <td class="td-total-r" style="{{ $is_repair_op ? '' : 'display: none;' }}">
                                            <input type="text" name="items[0][total_r]"
                                                class="form-control grid-input total-r" value="0" readonly>
                                        </td>

                                        @if(!$is_lathe)
                                            <td class="td-rate"><input type="number" step="0.01" name="items[0][rate]" class="form-control grid-input rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>
                                            <td class="td-ot-rate" style="{{ $is_ot_op ? '' : 'display: none;' }}"><input type="number" step="0.01" class="form-control grid-input row-ot-rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>
                                            <td class="td-rej-rate" style="{{ $show_rej_rate ? '' : 'display: none;' }}"><input type="number" step="0.01" class="form-control grid-input row-rejection-rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>
                                        @endif

                                        <td class="td-amount">
                                            <input type="text" name="items[0][total_amount]"
                                                class="form-control grid-input total-amount" value="0.00" readonly>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="table-light">
                                    <th colspan="{{ $footer_colspan }}" class="text-end sticky-footer-label" id="tf_grand_total_label">Grand Total</th>
                                    
                                    @if($is_lathe) <th id="tf_rate" style="display: none;"></th> @endif
                                    @for ($i = 1; $i <= 31; $i++)
                                        <th class="day-footer" data-day="{{ $i }}"></th>
                                        <th class="r-footer" data-day="{{ $i }}" style="{{ $is_repair_op ? '' : 'display: none;' }}"></th>
                                        <th class="ot-footer" data-day="{{ $i }}" style="{{ $is_ot_op ? '' : 'display: none;' }}"></th>
                                    @endfor
                                    <th id="tf_total_qty" class="text-center">0</th>
                                    <th id="tf_total_r" class="text-center" style="{{ $is_repair_op ? '' : 'display: none;' }}">0</th>
                                    @if(!$is_lathe) 
                                        <th id="tf_rate"></th> 
                                        <th id="tf_ot_rate" style="{{ $is_ot_op ? '' : 'display: none;' }}"></th>
                                        <th id="tf_rej_rate" style="{{ $show_rej_rate ? '' : 'display: none;' }}"></th>
                                    @endif
                                    
                                    <th id="tf_amount" class="text-center">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-success">
                            {{ $is_edit ? 'Update Records' : 'Save Records' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
$(document).ready(function () {

    // ===========================
    // PHP data passed to JS
    // ===========================
    @php
        $jsProducts  = $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'rate' => $p->rate, 'ot_rate' => $p->ot_text, 'rejection_rate' => $p->rejection_rate]);
        $jsGrades    = $grades->map(fn($g) => ['id' => $g->id, 'name' => $g->name]);
        $jsEmployees = $contractEmployees->map(fn($e) => ['id' => $e->id, 'name' => $e->employee_code . ' - ' . $e->proper_name]);
        $jsProcesses = $contractProcesses->map(fn($cp) => ['id' => $cp->id, 'name' => $cp->name, 'rate' => $cp->rate, 'ot_rate' => $cp->ot_text, 'rejection_rate' => $cp->rejection_rate]);
    @endphp

    const phpProducts  = @json($jsProducts);
    const phpGrades    = @json($jsGrades);
    const phpEmployees = @json($jsEmployees);
    const phpProcesses = @json($jsProcesses);
    const phpMonths    = @json(config('constants.months'));

    // Live lists (updated via AJAX on company change)
    let liveProducts  = phpProducts;
    let liveGrades    = phpGrades;
    let liveEmployees = phpEmployees;
    let liveProcesses = phpProcesses;

    let rowIndex    = {{ old('items') ? count(old('items')) : (isset($edits) ? count($edits) : 1) }};
    let isLatheMode = {{ $is_lathe ? 'true' : 'false' }};
    let weekOffDays = [];
    let daysInMonth = 31;

    // Operation Configuration Arrays
    const globalEmployeeOps = ['Lathe Employee wise', 'CLEANING', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'];
    const srNoOps           = [];
    const totalAsLabelOps   = ['CLEANING', 'GRINDING', 'CNC', 'ARGON WELDING', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'];
    const repairOps         = ['BUFF', 'RRL', 'FLEXIBLE', 'ASS-2', 'COATING', 'FOUNDRY'];
    const otRepairOps       = ['RRL', 'FLEXIBLE'];
    const hideGradeOps      = ['ASS-2', 'BUTTERFLY', 'FOUNDRY', 'CORE'];
    const isFoundryOp       = $('#operation_select').val() === 'FOUNDRY';

    const weekOffUrl  = "{{ route($route . '.get-week-off-days') }}";
    const dataUrl     = "{{ route($route . '.get-products-grades') }}";
    const checkDuplicateUrl = "{{ route($route . '.check-duplicate') }}";

    // ===========================
    // Select2 init
    // ===========================
    function initSelect2(ctx) {
        let $els = ctx ? $(ctx).find('.select2') : $('.select2');
        if (!ctx) $els = $('.select2');
        $els.each(function () {
            if ($(this).hasClass('select2-hidden-accessible') || $(this).data('select2')) {
                $(this).select2('destroy');
            }
            $(this).select2({ width: '100%', dropdownAutoWidth: true });
        });
    }
    initSelect2();

    // ===========================
    // Build select HTML helpers
    // ===========================
    function buildOptions(items, placeholder, selectedId) {
        let html = '<option value="">' + placeholder + '</option>';
        items.forEach(function (item) {
            let sel = (selectedId && String(item.id) === String(selectedId)) ? ' selected' : '';
            let rateAttr = (item.rate !== undefined && item.rate !== null) ? ' data-rate="' + item.rate + '"' : '';
            let otAttr   = (item.ot_rate !== undefined && item.ot_rate !== null) ? ' data-ot-rate="' + item.ot_rate + '"' : '';
            let rejAttr  = (item.rejection_rate !== undefined && item.rejection_rate !== null) ? ' data-rejection-rate="' + item.rejection_rate + '"' : '';
            html += '<option value="' + item.id + '"' + sel + rateAttr + otAttr + rejAttr + '>' + item.name + '</option>';
        });
        return html;
    }

    function buildPrimarySelect(fieldName, selectedId) {
        let opVal = $('#operation_select').val();
        if (isLatheMode) {
            let labelName = (opVal === 'GRINDING') ? 'Name' : 'Operator';
            return '<select name="' + fieldName + '" class="form-control select2 primary-select">'
                + buildOptions(liveEmployees, 'Select ' + labelName, selectedId) + '</select>';
        } else {
            return '<select name="' + fieldName + '" class="form-control select2 primary-select">'
                + buildOptions(liveProducts, 'Select Product', selectedId) + '</select>';
        }
    }

    function buildSecondarySelect(fieldName, selectedId) {
        let opVal = $('#operation_select').val();
        if (isLatheMode) {
            return '<select name="' + fieldName + '" class="form-control select2 secondary-select">'
                + buildOptions(liveProcesses, 'Select Process', selectedId) + '</select>';
        } else if (opVal === 'CORE') {
            return '<select name="' + fieldName + '" class="form-control select2 secondary-select">'
                + buildOptions(liveProducts, 'Select Item', selectedId) + '</select>';
        } else {
            return '<select name="' + fieldName + '" class="form-control select2 secondary-select">'
                + buildOptions(liveGrades, 'Select Grade', selectedId) + '</select>';
        }
    }

    // ===========================
    // Rebuild all existing rows when mode changes
    // ===========================
    function rebuildExistingRows() {
        $('#items_table tbody .item-row').each(function (i) {
            let $row   = $(this);
            
            // CAPTURE existing values before destroying
            let existingPrimary = $row.find('.primary-select').val();
            let existingSecondary = $row.find('.secondary-select').val();

            let rIdx   = $row.data('row-index') !== undefined ? $row.data('row-index') : i;
            let opVal = $('#operation_select').val();
            let primaryFld   = (isLatheMode) ? 'items[' + rIdx + '][employee_id]' : 'items[' + rIdx + '][product_id]';
            let secondaryFld = isLatheMode ? 'items[' + rIdx + '][product_id]' : 'items[' + rIdx + '][grade_id]';

            // Destroy select2 first
            $row.find('.primary-select, .secondary-select').each(function () {
                if ($(this).data('select2')) $(this).select2('destroy');
            });

            $row.find('.td-secondary-select').addClass('sticky-col-3');
            $row.find('.td-primary-select').html(buildPrimarySelect(primaryFld, existingPrimary));
            $row.find('.td-secondary-select').html(buildSecondarySelect(secondaryFld, existingSecondary));

            initSelect2($row[0]);
            
            // Trigger change to update rates from data attributes
            if (existingPrimary) $row.find('.primary-select').trigger('change');
            if (existingSecondary) $row.find('.secondary-select').trigger('change');
        });
    }

    // ===========================
    // Grand totals
    // ===========================
    function updateGrandTotals() {
        let grandQty = 0, grandR = 0, grandAmt = 0;

        // Per-day totals in footer
        for (let d = 1; d <= 31; d++) {
            let daySum = 0, dayRSum = 0, dayOtSum = 0;
            $('#items_table tbody .item-row').each(function () {
                let rIdx = $(this).attr('data-row-index');
                daySum += parseFloat($(this).find('input[name="items[' + rIdx + '][day_' + d + ']"]').val()) || 0;
                dayRSum += parseFloat($(this).find('input[name="items[' + rIdx + '][day_' + d + '_r]"]').val()) || 0;
                dayOtSum += parseFloat($(this).find('input[name="items[' + rIdx + '][day_' + d + '_ot]"]').val()) || 0;
            });
            $('.day-footer[data-day="' + d + '"]').text(daySum > 0 ? parseFloat(daySum.toFixed(2)) : '');
            $('.r-footer[data-day="' + d + '"]').text(dayRSum > 0 ? parseFloat(dayRSum.toFixed(2)) : '');
            $('.ot-footer[data-day="' + d + '"]').text(dayOtSum > 0 ? parseFloat(dayOtSum.toFixed(2)) : '');
        }

        $('#items_table tbody .item-row').each(function () {
            grandQty += parseFloat($(this).find('.total-qty').val()) || 0;
            grandR += parseFloat($(this).find('.total-r').val()) || 0;
            grandAmt += parseFloat($(this).find('.total-amount').val()) || 0;
        });
        $('#tf_total_qty').text(parseFloat(grandQty.toFixed(2)));
        $('#tf_total_r').text(parseFloat(grandR.toFixed(2)));
        $('#tf_amount').text(grandAmt.toFixed(2));
    }

    // ===========================
    // Row totals
    // ===========================
    function calculateRowTotals($row) {
        let opVal = $('#operation_select').val();
        let rate = parseFloat($row.find('.rate').val()) || 0;
        let otRate = parseFloat($row.find('.row-ot-rate').val()) || 0;
        let rejRate = parseFloat($row.find('.row-rejection-rate').val()) || 0;
        
        let normalQty = 0;
        $row.find('.day-qty').each(function () {
            if (!$(this).prop('disabled')) {
                normalQty += parseFloat($(this).val()) || 0;
            }
        });

        let rQty = 0;
        if (repairOps.includes(opVal)) {
            $row.find('.day-qty-r').each(function () {
                if (!$(this).prop('disabled')) {
                    rQty += parseFloat($(this).val()) || 0;
                }
            });
        }

        let otQty = 0;
        if (otRepairOps.includes(opVal)) {
            $row.find('.day-qty-ot').each(function () {
                if (!$(this).prop('disabled')) {
                    otQty += parseFloat($(this).val()) || 0;
                }
            });
        }

        let totalQty = normalQty + otQty;
        $row.find('.total-qty').val(parseFloat(totalQty.toFixed(2)));
        $row.find('.total-r').val(parseFloat(rQty.toFixed(2)));

        let totalAmt = 0;
        if (opVal === 'FOUNDRY') {
            totalAmt = (normalQty + rQty) * rate;
        } else if (repairOps.includes(opVal)) {
            // Strictly use the Rejection Rate from Product Master
            totalAmt = (normalQty * rate) + (rQty * rejRate) + (otQty * otRate);
        } else {
            totalAmt = totalQty * rate;
        }

        $row.find('.total-amount').val(totalAmt.toFixed(2));
        updateGrandTotals();
    }

    $(document).on('input', '.day-qty, .day-qty-r, .day-qty-ot, .rate', function () {
        calculateRowTotals($(this).closest('tr'));
    });

    $(document).on('change', '.primary-select, .secondary-select', function () {
        let val = $(this).val();
        let $row = $(this).closest('tr');
        let opVal = $('#operation_select').val();
        
        // 1. Try to get rate from data attributes first (added by buildOptions)
        let $opt = $(this).find('option:selected');
        let selectedRate = $opt.data('rate');
        
        if (selectedRate !== undefined && selectedRate !== null && selectedRate !== "") {
            $row.find('.rate').val(parseFloat(selectedRate).toFixed(2));
            
            let selectedOtRate = $opt.data('ot-rate') || 0;
            $row.find('.row-ot-rate').val(parseFloat(selectedOtRate).toFixed(2));

            let selectedRejRate = $opt.data('rejection-rate') || 0;
            $row.find('.row-rejection-rate').val(parseFloat(selectedRejRate).toFixed(2));

            calculateRowTotals($row);
            return;
        }

        // 2. Fallback: Search in live data arrays if data-rate attribute is missing
        let rate = 0, otRate = 0, rejRate = 0;
        let found = false;

        if (isLatheMode) {
            if ($(this).hasClass('secondary-select')) {
                // Secondary is Process in Lathe mode
                let proc = liveProcesses.find(p => String(p.id) === String(val));
                if (proc) {
                    rate = proc.rate || 0;
                    rejRate = proc.rejection_rate || 0;
                    found = true;
                }
            }
        } else {
            // In non-Lathe modes, product can be in primary OR secondary (e.g. CORE uses secondary for Item)
            let prod = liveProducts.find(p => String(p.id) === String(val));
            if (prod) {
                rate = prod.rate || 0;
                otRate = prod.ot_rate || 0;
                rejRate = prod.rejection_rate || 0;
                found = true;
            }
        }

        if (found) {
            $row.find('.rate').val(parseFloat(rate).toFixed(2));
            $row.find('.row-ot-rate').val(parseFloat(otRate).toFixed(2));
            $row.find('.row-rejection-rate').val(parseFloat(rejRate).toFixed(2));
            calculateRowTotals($row);
        }
    });

    // ===========================
    // Week-off calendar
    // ===========================
    function applyCalendar(month, year) {
        if (!month || !year) return;
        $.get(weekOffUrl, { month: month, year: year }, function (resp) {
            // Ensure array of numbers
            weekOffDays = (resp.week_off_days || []).map(Number);
            daysInMonth = parseInt(resp.days_in_month) || 31;

            let visibleDays = 0;
            for (let d = 1; d <= 31; d++) {
                let $hdr = $('#day_header_' + d);
                let $rhdr = $('#r_header_' + d);
                let $othdr = $('#ot_header_' + d);
                let $cells = $('.day-cell[data-day="' + d + '"]');
                let $rcells = $('.r-cell[data-day="' + d + '"]');
                let $otcells = $('.ot-cell[data-day="' + d + '"]');
                let $ftr = $('.day-footer[data-day="' + d + '"]');
                let $rftr = $('.r-footer[data-day="' + d + '"]');
                let $otftr = $('.ot-footer[data-day="' + d + '"]');

                if (d > daysInMonth) {
                    $hdr.hide().addClass('day-beyond-month');
                    $rhdr.hide().addClass('day-beyond-month');
                    $othdr.hide().addClass('day-beyond-month');
                    $('#day_label_' + d).hide().addClass('day-beyond-month');
                    $cells.hide().addClass('day-beyond-month');
                    $rcells.hide().addClass('day-beyond-month');
                    $otcells.hide().addClass('day-beyond-month');
                    $ftr.hide().addClass('day-beyond-month');
                    $rftr.hide().addClass('day-beyond-month');
                    $otftr.hide().addClass('day-beyond-month');
                    
                    $('#items_table tbody tr').each(function() {
                        let rIdx = $(this).attr('data-row-index');
                        $(this).find('input[name="items[' + rIdx + '][day_' + d + ']"], input[name="items[' + rIdx + '][day_' + d + '_r]"], input[name="items[' + rIdx + '][day_' + d + '_ot]"]').prop('disabled', true);
                    });
                } else {
                    let dayName = getDayName(month, d, year);
                    
                    // Regular day: Show
                    let opVal = $('#operation_select').val();
                    let isRepair = repairOps.includes(opVal);
                    let isOt = otRepairOps.includes(opVal);
                    let isFoundry = (opVal === 'FOUNDRY');

                    let dayColspan = 1;
                    if (isOt) dayColspan = 3;
                    else if (isRepair) dayColspan = 2;
                    let dayRowspan = (dayColspan > 1) ? 1 : 2;

                    $hdr.show().removeClass('day-beyond-month').attr('colspan', dayColspan).attr('rowspan', dayRowspan).text(d);
                    $cells.show().removeClass('day-beyond-month');
                    $ftr.show().removeClass('day-beyond-month');

                    // Show/Hide sub-labels in Row 3
                    let $dayLabel = $('#day_label_' + d);
                    $dayLabel.removeClass('day-beyond-month');
                    if (isRepair || isOt) {
                        $('#row_header_3').show();
                        $dayLabel.show();
                        if (isFoundry) $dayLabel.text('DAY');
                        else $dayLabel.text('Qty');
                    } else {
                        $dayLabel.hide();
                    }
                    
                    if (isRepair) {
                        let rLabel = (opVal === 'FOUNDRY') ? 'NIGHT' : 'R';
                        $rhdr.show().removeClass('day-beyond-month').text(rLabel);
                        $rcells.show().removeClass('day-beyond-month');
                        $rftr.show().removeClass('day-beyond-month');
                    } else {
                        $rhdr.hide();
                        $rcells.hide();
                        $rftr.hide();
                    }

                    if (isOt) {
                        $othdr.show().removeClass('day-beyond-month');
                        $otcells.show().removeClass('day-beyond-month');
                        $otftr.show().removeClass('day-beyond-month');
                    } else {
                        $othdr.hide();
                        $otcells.hide();
                        $otftr.hide();
                    }

                    $('#items_table tbody tr').each(function() {
                         let rIdx = $(this).attr('data-row-index');
                         $(this).find('input[name="items[' + rIdx + '][day_' + d + ']"], input[name="items[' + rIdx + '][day_' + d + '_r]"], input[name="items[' + rIdx + '][day_' + d + '_ot]"]').prop('disabled', false);
                    });
                    visibleDays++;
                }
            }
            let opVal = $('#operation_select').val();
            if (!repairOps.includes(opVal) && !otRepairOps.includes(opVal)) {
                $('#row_header_3').hide();
            }

            let multiplier = 1;
            if (otRepairOps.includes(opVal)) multiplier = 3;
            else if (repairOps.includes(opVal)) multiplier = 2;
            
            let finalColspan = visibleDays * multiplier;
            $('#th_date_group').attr('colspan', finalColspan);
            $('.item-row').each(function () { calculateRowTotals($(this)); });
        });
    }

    function getDayName(month, day, year) {
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days[new Date(year, month - 1, day).getDay()];
    }

    // Triggers
    $('#month_select, #year_select').on('change', function () {
        applyCalendar(parseInt($('#month_select').val()), parseInt($('#year_select').val()));
    });

    // ===========================
    // Prevent future month/year selection and check duplicates
    // ===========================
    const $addBtn = $('#add_product_to_list');
    const $saveBtn = $('button[type="submit"]');

    function validateNotFuture(month, year) {
        if (!month || !year) return true;
        const now = new Date();
        const selYear = parseInt(year);
        const selMonth = parseInt(month);
        const curYear = now.getFullYear();
        const curMonth = now.getMonth() + 1; // JS months 0-based

        if (selYear > curYear) return false;
        if (selYear === curYear && selMonth > curMonth) return false;
        return true;
    }

    function disableFormWithMessage(msg) {
        toastr.error(msg, 'Validation');
        $addBtn.prop('disabled', true);
        $saveBtn.prop('disabled', true);
    }

    function enableForm() {
        $addBtn.prop('disabled', false);
        $saveBtn.prop('disabled', false);
    }

    function checkDuplicate() {
        let companyId = $('#company_id').val() || null;
        let operation = $('#operation_select').val() || null;
        let month = $('#month_select').val() || null;
        let year = $('#year_select').val() || null;
        let employeeId = $('#global_employee_id').val() || null;

        if (!companyId || !operation || !month || !year) {
            enableForm();
            return;
        }

        if (globalEmployeeOps.includes(operation) && !employeeId) {
            enableForm();
            return;
        }

        if (!validateNotFuture(month, year)) {
            disableFormWithMessage('Selected Month/Year is in the future. Please choose current or past month/year.');
            return;
        }

        $.post(checkDuplicateUrl, {
            _token: $('input[name="_token"]').val(),
            id: "{{ $is_edit ? $edit->id : '' }}",
            company_id: companyId,
            operation: operation,
            month: month,
            year: year,
            employee_id: employeeId,
            global_employee_id: employeeId
        }, function (resp) {
            if (resp && resp.exists) {
                let msg = 'Entries already exist for this Operation / Month / Year' + (employeeId ? ' for this Name' : '') + '. Duplicate entries are not allowed.';
                disableFormWithMessage(msg);
            } else {
                enableForm();
            }
        }).fail(function () {
            // On failure, keep form enabled but log
            console.error('Failed to validate duplicates');
            enableForm();
        });
    }

    $('#operation_select, #month_select, #year_select, #company_id, #global_employee_id').on('change', function () {
        // Re-apply calendar and run duplicate check
        applyCalendar(parseInt($('#month_select').val()), parseInt($('#year_select').val()));
        checkDuplicate();
    });

    // Also validate on page load
    // Rebuild month options to hide future months when year == current year
    function rebuildMonthOptions(selectedYear) {
        const $month = $('#month_select');
        const cur = new Date();
        const curYear = cur.getFullYear();
        const curMonth = cur.getMonth() + 1;
        let html = '<option value="">Select Month</option>';
        for (const [num, name] of Object.entries(phpMonths)) {
            const mnum = parseInt(num);
            if (selectedYear && parseInt(selectedYear) === curYear && mnum > curMonth) {
                // skip future months
                continue;
            }
            html += '<option value="' + mnum + '"' + (parseInt($('#month_select').val()) === mnum ? ' selected' : '') + '>' + name + '</option>';
        }
        $month.html(html);
        if ($month.data('select2')) $month.select2('destroy');
        $month.select2({ width: '100%', dropdownAutoWidth: true });
    }

    // When year changes, rebuild month options to hide future months
    $('#year_select').on('change', function () {
        const y = $(this).val();
        rebuildMonthOptions(y);
        applyCalendar(parseInt($('#month_select').val()), parseInt($('#year_select').val()));
        checkDuplicate();
    });

    $(window).on('load', function () { 
        let y = $('#year_select').val();
        let m = $('#month_select').val();
        // ensure month options respect current year on load
        rebuildMonthOptions(y);
        applyCalendar(parseInt(m), parseInt(y));
        checkDuplicate();
    });

    // ===========================
    // Lathe / Normal mode switch
    // ===========================
    function setMode(lathe, rebuildRows) {
        let changed = false;
        if (isLatheMode !== lathe) {
            changed = true;
        }
        isLatheMode = lathe;

        // Visual text updates
        let opVal = $('#operation_select').val();
        
        if (repairOps.includes(opVal)) {
            $('#th_total_r, .td-total-r, #tf_total_r').show();
        } else {
            $('#th_total_r, .td-total-r, #tf_total_r').hide();
        }
        
        if (globalEmployeeOps.includes(opVal)) {
            $('#top_name_wrap').show();
        } else {
            $('#top_name_wrap').hide();
        }

        if (srNoOps.includes(opVal)) {
            $('#col_header_sr_no, .sr-no-cell').show();
        } else {
            $('#col_header_sr_no, .sr-no-cell').hide();
        }

        if (hideGradeOps.includes(opVal)) {
            $('#col_header_grade, .td-secondary-select').hide();
        } else {
            $('#col_header_grade, .td-secondary-select').show();
        }

        $('#col_header_name, .td-primary-select').toggle(!lathe);
        $('#col_header_grade, .td-secondary-select').toggleClass('sticky-col-3', true);
        
        // Adjust sticky position of the next column if Name is hidden
        if (lathe) {
            $('.sticky-col-3').css('left', '62px');
        } else {
            $('.sticky-col-3').css('left', '342px');
        }

        // Update footer colspan dynamically
        let footerColspan = 2; // Action + Name
        if (srNoOps.includes(opVal)) footerColspan++;
        if (!hideGradeOps.includes(opVal)) footerColspan++;
        $('#tf_grand_total_label').attr('colspan', footerColspan);

        if (lathe) {
            $('#col_header_grade').text('Contract Process');
            $('#th_total_qty').text('TOTAL QTY');
            $('#th_amount').text('AMOUNT'); 
        } else if (repairOps.includes(opVal)) {
            $('#col_header_name').text('PRODUCT NAME');
            $('#col_header_grade').text('GRADE');
            $('#th_total_qty').text('TOTAL QTY');
            $('#th_amount').text('TOTAL AMT');
        } else if (totalAsLabelOps.includes(opVal)) {
            $('#col_header_name').text('PRODUCT NAME');
            $('#col_header_grade').text('GRADE');
            $('#th_total_qty').text('TOTAL');
            $('#th_amount').text('TOTAL AMT');
        } else {
            $('#col_header_name').text('Product Name');
            $('#col_header_grade').text('Grade');
            $('#th_total_qty').text('TOTAL QTY');
            $('#th_amount').text('TOTAL AMT');
        }

        // Repair specific R and OT columns
        $('.r-col-header, .r-cell, .r-footer, .ot-col-header, .ot-cell, .ot-footer, .day-label-main').hide();
        $('#row_header_3').hide();
        
        if (repairOps.includes(opVal)) {
            $('.r-col-header:not(.day-beyond-month)').show();
            $('.r-cell:not(.day-beyond-month)').show();
            $('.r-footer:not(.day-beyond-month)').show();
            $('.day-label-main:not(.day-beyond-month)').show();
            $('#row_header_3').show();
            
            if (opVal === 'FOUNDRY') {
                $('.day-label-main').text('DAY');
                $('.r-col-header').text('NIGHT');
                $('#th_rej_rate, .td-rej-rate, #tf_rej_rate').hide();
            } else {
                $('.day-label-main').text('Qty');
                $('.r-col-header').text('R');
                $('#th_rej_rate, .td-rej-rate, #tf_rej_rate').show();
            }
        } else {
            $('#th_rej_rate, .td-rej-rate, #tf_rej_rate').hide();
        }

        if (otRepairOps.includes(opVal)) {
            $('.ot-col-header:not(.day-beyond-month)').show();
            $('.ot-cell:not(.day-beyond-month)').show();
            $('.ot-footer:not(.day-beyond-month)').show();
            $('.day-label-main:not(.day-beyond-month)').show();
            $('#row_header_3').show();
            $('#th_ot_rate, .td-ot-rate, #tf_ot_rate').show();
        } else {
            $('#th_ot_rate, .td-ot-rate, #tf_ot_rate').hide();
        }

        let vDays = $('.day-header:not(.day-beyond-month):not(.week-off-header)').length;
        if (vDays > 0) {
            let mult = 1;
            if (otRepairOps.includes(opVal)) mult = 3;
            else if (repairOps.includes(opVal)) mult = 2;
            $('#th_date_group').attr('colspan', vDays * mult);
        }

        // Move RATE column dynamically
        if (lathe) {
            $('#th_amount').text('AMOUNT'); // Rename TOTAL AMT to AMOUNT
            // Header: Rate before Date
            $('#th_rate').insertBefore('#th_date_group');
            // Rows: Rate before Day 1
            $('#items_table tbody .item-row').each(function() {
                $(this).find('.td-rate').insertBefore($(this).find('.day-cell[data-day="1"]'));
            });
            // Footer: grand total label already covers RATE in lathe mode
            $('#tf_rate').insertBefore('.day-footer[data-day="1"]');
            $('#tf_rate').hide();
        } else {
            $('#th_amount').text('TOTAL AMT'); // Rename to TOTAL AMT
            
            let $afterTotalHeader = repairOps.includes(opVal) ? $('#th_total_r') : $('#th_total_qty');
            $('#th_rate').insertAfter($afterTotalHeader);
            
            $('#items_table tbody .item-row').each(function() {
                let $afterTotalCell = repairOps.includes(opVal) ? $(this).find('.td-total-r') : $(this).find('.td-total-qty');
                $(this).find('.td-rate').insertAfter($afterTotalCell);
            });
            
            let $afterTotalFooter = repairOps.includes(opVal) ? $('#tf_total_r') : $('#tf_total_qty');
            $('#tf_rate').insertAfter($afterTotalFooter);
            $('#tf_rate').show();
        }

        if (rebuildRows && changed) {
            rebuildExistingRows();
        }
    }

    $('#operation_select').on('change', function () {
        let val = $(this).val();
        let lathe = (val === 'Lathe Employee wise');
        setMode(lathe, true);
        loadDataByCompany($('#company_id').val());
    });

    // ===========================
    // Add new row
    // ===========================
    $('#add_product_to_list').on('click', function () {
        let primaryId   = null;
        let secondaryId = null;

        let opVal = $('#operation_select').val();

        let primaryFld   = (isLatheMode) ? 'items[' + rowIndex + '][employee_id]' : 'items[' + rowIndex + '][product_id]';
        let secondaryFld = isLatheMode ? 'items[' + rowIndex + '][product_id]' : 'items[' + rowIndex + '][grade_id]';

        let $row = $('<tr class="item-row" data-row-index="' + rowIndex + '"></tr>');

        // Action cell
        $row.append('<td class="sticky-col"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="ti ti-trash"></i></button></td>');

        // SR NO cell
        let newSrNo = $('#items_table tbody tr').length + 1;
        let showSrNo = srNoOps.includes(opVal) ? '' : 'style="display: none;"';
        $row.append('<td class="sticky-col align-middle text-center sr-no-cell" ' + showSrNo + '>' + newSrNo + '</td>');

        // Primary selector cell
        let primCell = $('<td class="sticky-col-2 td-primary-select"></td>');
        primCell.html(buildPrimarySelect(primaryFld, primaryId));
        $row.append(primCell);

        // Secondary selector cell
        let secCell = $('<td class="td-secondary-select"></td>');
        secCell.addClass('sticky-col-3');
        if (hideGradeOps.includes(opVal)) secCell.hide();
        secCell.html(buildSecondarySelect(secondaryFld, secondaryId));
        $row.append(secCell);

        // RATE, REJ RATE and OT RATE Cell HTML
        let rejRateDisplay = repairOps.includes(opVal) ? '' : 'style="display: none;"';
        let otRateDisplay = otRepairOps.includes(opVal) ? '' : 'style="display: none;"';
        let rateCellHtml = '<td class="td-rate"><input type="number" step="0.01" name="items[' + rowIndex + '][rate]" class="form-control grid-input rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>' +
                           '<td class="td-ot-rate" ' + otRateDisplay + '><input type="number" step="0.01" class="form-control grid-input row-ot-rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>' +
                           '<td class="td-rej-rate" ' + rejRateDisplay + '><input type="number" step="0.01" class="form-control grid-input row-rejection-rate" value="0.00" readonly style="background-color: #f8f9fa;"></td>';

        if (isLatheMode) {
            $row.append(rateCellHtml);
        }

        // Day cells 1-31
        for (let i = 1; i <= 31; i++) {
            let isBeyond  = i > daysInMonth;
            let cls = (isBeyond ? ' day-beyond-month' : '');
            let disabled  = (isBeyond) ? ' disabled' : '';
            
            let rDisplay  = repairOps.includes(opVal) ? '' : 'style="display: none;"';
            let otDisplay = otRepairOps.includes(opVal) ? '' : 'style="display: none;"';
            
            $row.append(
                '<td class="day-cell' + cls + '" data-day="' + i + '">' +
                '<input type="number" step="0.01" name="items[' + rowIndex + '][day_' + i + ']"' +
                ' class="form-control grid-input day-qty" value=""' + disabled + '>' +
                '</td>' +
                '<td class="r-cell' + cls + '" data-day="' + i + '" ' + rDisplay + '>' +
                '<input type="number" step="0.01" name="items[' + rowIndex + '][day_' + i + '_r]"' +
                ' class="form-control grid-input day-qty-r" value=""' + disabled + '>' +
                '</td>' +
                '<td class="ot-cell' + cls + '" data-day="' + i + '" ' + otDisplay + '>' +
                '<input type="number" step="0.01" name="items[' + rowIndex + '][day_' + i + '_ot]"' +
                ' class="form-control grid-input day-qty-ot" value=""' + disabled + '>' +
                '</td>'
            );
        }

        // Total qty and R
        let totalRDisplay = repairOps.includes(opVal) ? '' : 'style="display: none;"';
        $row.append(
            '<td class="td-total-qty"><input type="text" name="items[' + rowIndex + '][total_qty]" class="form-control grid-input total-qty" value="0.00" readonly></td>' +
            '<td class="td-total-r" ' + totalRDisplay + '><input type="text" name="items[' + rowIndex + '][total_r]" class="form-control grid-input total-r" value="0.00" readonly></td>'
        );

        if (!isLatheMode) {
            $row.append(rateCellHtml);
        }

        // Total amount
        $row.append('<td class="td-amount"><input type="text" name="items[' + rowIndex + '][total_amount]" class="form-control grid-input total-amount" value="0.00" readonly></td>');

        $('#items_table tbody').append($row);
        initSelect2($row[0]);

        setMode(isLatheMode, false);

        rowIndex++;
    });

    // ===========================
    // Remove row
    // ===========================
    $(document).on('click', '.remove-row', function () {
        let $row = $(this).closest('tr');
        
        Swal.fire({
            title: "Are you sure?",
            text: "You want to delete this row?",
            icon: "warning",
            customClass: {
                confirmButton: 'btn btn-danger waves-effect waves-light',
                cancelButton: "btn btn-primary waves-effect waves-light",
            },
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            showCancelButton: true,
            reverseButtons: true
        }).then((isConfirmed) => {
            if (isConfirmed.isConfirmed) {
                if ($('#items_table tbody tr').length > 1) {
                    $row.remove();
                    
                    // Recalculate SR NO
                    $('#items_table tbody tr').each(function(index) {
                        $(this).find('.sr-no-cell').text(index + 1);
                    });
                    
                    updateGrandTotals();
                } else {
                    $row.find('.primary-select, .secondary-select').val('').trigger('change.select2');
                    $row.find('.day-qty, .day-qty-r, .day-qty-ot, .rate, .total-qty, .total-r, .total-amount').val(0);
                    updateGrandTotals();
                }
            }
        });
    });

    // ===========================
    // Company change - reload data
    // ===========================
    function loadDataByCompany(companyId) {
        if (!companyId) return;
        let operation = $('#operation_select').val();
        $.get(dataUrl, { company_id: companyId, operation: operation })
        .done(function (resp) {
            console.log("Data fetched successfully:", resp);
            liveProducts  = resp.products          || [];
            liveGrades    = resp.grades            || [];
            liveEmployees = resp.contractEmployees || [];
            liveProcesses = resp.contractProcesses || [];
            rebuildExistingRows();
        })
        .fail(function (xhr, status, error) {
            console.error("Failed to fetch product data:", error);
        });
    }

    $('#company_id').on('change', function () { loadDataByCompany($(this).val()); });

    @if(isset($company_id) && $company_id)
        loadDataByCompany("{{ $company_id }}");
    @endif

    // ===========================
    // Page load init
    // ===========================

    // Apply current mode
    setMode(isLatheMode, false);

    // Apply calendar
    @if($is_edit)
        applyCalendar({{ $edit->month }}, {{ $edit->year }});
    @else
        let _m = parseInt($('#month_select').val()), _y = parseInt($('#year_select').val());
        if (_m && _y) applyCalendar(_m, _y);
    @endif

    // Initial row totals
    $('.item-row').each(function () { calculateRowTotals($(this)); });
    updateGrandTotals();
});
</script>
@endpush
