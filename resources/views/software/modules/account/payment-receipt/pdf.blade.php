<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        @page { margin: 12px; }
        :root { --brand-primary:#e7881f; --brand-dark:#1b1b1a; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000000; }
        .receipt-wrapper { width:720px; max-width:100%; margin:0 auto; border:2px solid var(--brand-dark); }
        .receipt-header { display: table; width: 100%; background:#ffd5ad; color:#000000; border-bottom:4px solid #000000; }
        .receipt-header .left { display: table-cell; width: 65%; padding: 10px 12px; vertical-align: middle; }
        .receipt-header .right { display: table-cell; width: 35%; padding: 10px 12px; text-align: right; vertical-align: middle; }
        .title { font-size: 20px; font-weight: 700; }
        .dash { border-bottom:1px dashed #999; display:inline-block; min-width:400px; padding:0 6px; }
        .dash-small { border-bottom:1px dashed #999; display:inline-block; min-width:300px; padding:0 6px; }
        .row { padding: 8px 12px; }
        .flex { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; vertical-align: top; }
        .amount-box { border:2px solid var(--brand-primary); padding:10px; }    
        .text-right { text-align: right; }
        .mt-24 { margin-top: 24px; }
        
    </style>
</head>
<body>
    @php
        $company = $receipt->company ?? null;
        $employee = $receipt->employee ?? null;
        $branch = $receipt->branch ?? null;
        $department = $receipt->departments ?? null;
        $dateStr = \App\Helpers\Helper::formatCompanyDate($receipt->date, $company, 'Y-m-d') ?? '';
        $amountInWords = \App\Helpers\Helper::convertToRupees($receipt->amount ?? 0);
    @endphp
    <div class="receipt-wrapper">
        <div class="receipt-header">
            <div class="left">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; vertical-align: middle; width: 20%;">
                        @if($company?->company_logo_url)
                            <img src="{{ $company->company_logo_url }}" alt="Logo" style="height:48px;">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: middle;">
                        <div style="font-size:18px;font-weight:700;color:#000000;">{{ $company?->company_name }}</div>
                        <div style="font-size:12px;color:#000000;">{{ $company?->address }}</div>
                    </div>
                </div>
            </div>  
            <div class="right">
                <div class="title">Payment  Receipt</div>
                <div style="font-size:12px;color:#000000;" class="hl">No: {{ $receipt->receipt_no }}</div>
                <div style="font-size:12px;color:#000000;" class="hl">Date: {{ $dateStr }}</div>
            </div>
        </div>
        <div class="row">
            <span style="display:inline-block;width:200px;">Received with thanks from</span>
            <span class="dash hl">{{ $employee?->employee_code }} - {{ $employee?->full_name }}</span>
        </div>
        <div class="row">
            <span style="display:inline-block;width:200px;"><strong> Amount </strong></span>
            <span class="dash hl">₹ {{ number_format($receipt->amount ?? 0, 2) }}</span>
        </div>
        <div class="row">
            <span style="display:inline-block;width:200px;">In word</span>
            <span class="dash hl">{{ $amountInWords }}</span>
        </div>
        <div class="row">
            <span style="display:inline-block;width:200px;">For</span>
            <span class="dash hl">{{ $receipt->effect_on_month }} {{ $receipt->effect_of_year }}</span>
        </div>
        <div class="row">
            <span style="display:inline-block;width:200px;">Branch</span>
            <span class="dash hl">{{ $branch?->name }}</span>
        </div>
        <div class="row">
            <div class="flex">
                <div class="col">
                    <div>Department: <strong class="hl">{{ $department?->name }}</strong></div>
                    <div>Payment Mode: <strong class="hl">{{ ucfirst($receipt->payment_mode) }}</strong></div>
                    <div>Payment Type: <strong class="hl">{{ \App\Models\PaymentReceipt::$payment_type[$receipt->payment_type] ?? ucfirst($receipt->payment_type) }}</strong></div>
                    @if($receipt->payment_type === 'upi' && $receipt->upi_no)
                        <div>UPI Ref: <strong class="hl">{{ $receipt->upi_no }}</strong></div>
                    @endif
                    @if($receipt->payment_type === 'cheque' && $receipt->cheque_no)
                        <div>Cheque No: <strong class="hl">{{ $receipt->cheque_no }}</strong></div>
                    @endif
                    @if($receipt->remark)
                        <div>Remark: <strong class="hl">{{ $receipt->remark }}</strong></div>
                    @endif
                </div>
                <div class="col">
                    <div class="amount-box">
                        <div style="display: table; width: 100%;">
                            <span style="display:inline-block;width:200px; color:#000000; font-weight:bold;">
                                            Amount
                                        </span>
                                        <span class=" hl" style="color:#000000; font-weight:bold;">
                                            ₹ {{ number_format($receipt->amount ?? 0, 2) }}
                                        </span>
                        </div>
                    </div>
                    <div class="mt-24 text-right">Authorized Signature</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
