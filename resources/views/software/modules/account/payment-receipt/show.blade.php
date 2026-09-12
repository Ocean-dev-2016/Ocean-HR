@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Payment Receipt';
    $route = $modules['route'] ?? 'payment-receipt';
    $company = $receipt->company ?? null;
    $employee = $receipt->employee ?? null;
    $branch = $receipt->branch ?? null;
    $department = $receipt->departments ?? null;
    $dateStr = \App\Helpers\Helper::formatCompanyDate($receipt->date, $company, 'Y-m-d') ?? '';
    $amountInWords = \App\Helpers\Helper::convertToRupees($receipt->amount ?? 0);
@endphp

@section('title', $page_title)

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">{{ $page_title }}</h5>
            <div>
                <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary me-2">Back</a>
                <a href="{{ route($route . '.export.pdf', $receipt->id) }}" class="btn btn-outline-primary me-2">Download
                    PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <style>
                    :root {
                        --brand-primary: #e7881f;
                        --brand-dark: #1b1b1a;
                    }

                    .receipt-wrapper {
                        width: 720px;
                        max-width: 100%;
                        margin: 0 auto;
                        border: 2px solid var(--brand-dark);
                        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
                    }

                    .receipt-header {
                        display: flex;
                        align-items: center;
                        padding: 14px 18px;
                        background: #ffd5ad;
                        color: #000000;
                        border-bottom: 4px solid #000000;
                    }

                    .receipt-header .title {
                        font-size: 20px;
                        font-weight: 700;
                    }

                    .dash {
                        border-bottom: 1px dashed #999;
                        display: inline-block;
                        min-width: 400px;
                        padding: 0 6px;
                    }

                    .dash-small {
                        border-bottom: 1px dashed #999;
                        display: inline-block;
                        min-width: 300px;
                        padding: 0 6px;
                    }

                    .amount-box {
                        border: 2px solid var(--brand-primary);
                        padding: 10px;
                    }

                    .signature {
                        text-align: right;
                        margin-top: 40px;
                        color: var(--brand-dark);
                    }

                    @media print {
                        body * {
                            visibility: hidden;
                        }

                        .print-area,
                        .print-area * {
                            visibility: visible;
                        }

                        .print-area {
                            position: absolute;
                            left: 0;
                            right: 0;
                            margin: 0 auto;
                            top: 0;
                        }

                        .btn,
                        .btn-outline-secondary,
                        .btn-outline-primary {
                            display: none !important;
                        }
                    }
                </style>
                <div class="receipt-wrapper print-area">
                    <div class="receipt-header">
                        <div style="flex:1;display:flex;align-items:center;gap:12px;">
                            @if ($company?->company_logo_url)
                                <img src="{{ $company->company_logo_url }}" alt="Logo" style="height:48px;">
                            @endif
                            <div>
                                <div style="font-size:18px;font-weight:700;color:#000000;">{{ $company?->company_name }}
                                </div>
                                <div style="font-size:12px;color:#000000;">{{ $company?->address }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="title">Payment Receipt</div>
                            <div style="font-size:12px;color:#000000;" class="hl">No: {{ $receipt->receipt_no }}</div>
                            <div style="font-size:12px;color:#000000;" class="hl">Date: {{ $dateStr }}</div>
                        </div>
                    </div>

                    <div class="receipt-body" style="padding:16px;">
                        <div style="margin-bottom:8px;">
                            <span style="display:inline-block;width:200px;">Received with thanks from</span>
                            <span class="dash hl">
                                {{ $employee?->employee_code }} - {{ $employee?->full_name }}
                            </span>
                        </div>
                        <div style="margin-bottom:8px;">
                            <span style="display:inline-block;width:200px; ">
                                Amount
                            </span>
                            <span class="dash hl">
                                ₹ {{ number_format($receipt->amount ?? 0, 2) }}
                            </span>
                        </div>

                        <div style="margin-bottom:8px;">
                            <span style="display:inline-block;width:200px;">In word</span>
                            <span class="dash hl">
                                {{ $amountInWords }}
                            </span>
                        </div>

                        <div style="margin-bottom:8px;">
                            <span style="display:inline-block;width:200px;">For</span>
                            <span class="dash hl">
                                {{ $receipt->effect_on_month }} {{ $receipt->effect_of_year }}
                            </span>
                        </div>


                        <div style="margin-bottom:8px;">
                            <span style="display:inline-block;width:200px;">Branch</span>
                            <span class="dash hl">
                                {{ $branch?->name }}
                            </span>
                        </div>


                        <div style="display:flex;gap:16px;margin-top:12px;">
                            <div style="flex:1;">
                                <div style="margin-bottom:6px;">Department: <strong
                                        class="hl">{{ $department?->name }}</strong></div>
                                <div style="margin-bottom:6px;">Payment Mode: <strong
                                        class="hl">{{ ucfirst($receipt->payment_mode) }}</strong></div>
                                <div style="margin-bottom:6px;">Payment Type: <strong
                                        class="hl">{{ \App\Models\PaymentReceipt::$payment_type[$receipt->payment_type] ?? ucfirst($receipt->payment_type) }}</strong>
                                </div>
                                @if ($receipt->payment_type === 'upi' && $receipt->upi_no)
                                    <div style="margin-bottom:6px;">UPI Ref: <strong
                                            class="hl">{{ $receipt->upi_no }}</strong></div>
                                @endif
                                @if ($receipt->payment_type === 'cheque' && $receipt->cheque_no)
                                    <div style="margin-bottom:6px;">Cheque No: <strong
                                            class="hl">{{ $receipt->cheque_no }}</strong></div>
                                @endif
                                @if ($receipt->remark)
                                    <div style="margin-bottom:6px;">Remark: <strong
                                            class="hl">{{ $receipt->remark }}</strong></div>
                                @endif
                            </div>
                            <div style="flex:1;">
                                <div class="amount-box">
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="display:inline-block;width:200px; color:#000000; font-weight:bold;">
                                            Amount
                                        </span>
                                        <span class=" hl" style="color:#000000; font-weight:bold;">
                                            ₹ {{ number_format($receipt->amount ?? 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="signature">
                                    <div>Authorized Signature</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
