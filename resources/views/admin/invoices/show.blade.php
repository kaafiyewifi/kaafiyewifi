@extends('layouts.admin')

@section('title', 'Invoice Details')
@section('page_title', 'Invoice Details')

@section('content')
@php
    $customerName = $invoice->full_name ?? $invoice->name ?? 'Unknown Customer';
    $invoiceCode = 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
    $planName = $subscription->plan_name ?? 'Internet Subscription';

    $downloadSpeed = $subscription->download_speed ?? null;
    $downloadUnit = $subscription->download_unit ?? 'Mbps';
    $uploadSpeed = $subscription->upload_speed ?? null;
    $uploadUnit = $subscription->upload_unit ?? 'Mbps';

    $issueDate = !empty($invoice->created_at) ? \Carbon\Carbon::parse($invoice->created_at)->format('n/j/Y') : now()->format('n/j/Y');
    $dueDate = !empty($invoice->paid_at) ? \Carbon\Carbon::parse($invoice->paid_at)->format('n/j/Y') : $issueDate;

    $companyName = config('app.name', 'Kaafiye');
    $companyAddress = 'Mogadishu, Somalia';
    $companyEmail = 'support@kaafiye.online';

    $amount = number_format((float) ($invoice->amount ?? 0), 2);

    $speedText = '—';
    if ($downloadSpeed || $uploadSpeed) {
        if ($downloadSpeed && $uploadSpeed) {
            $speedText = $downloadSpeed . ' ' . $downloadUnit . ' / ' . $uploadSpeed . ' ' . $uploadUnit;
        } elseif ($downloadSpeed) {
            $speedText = $downloadSpeed . ' ' . $downloadUnit;
        } else {
            $speedText = $uploadSpeed . ' ' . $uploadUnit;
        }
    }

    $referenceText = $invoiceCode;
@endphp

<style>
    .invoice-print-sheet {
        background: #f8fafc;
        min-height: 100%;
    }

    .invoice-paper {
        width: 100%;
        max-width: 820px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        padding: 30px 32px 26px;
        color: #111827;
        overflow-x: hidden;
    }

    .invoice-signature {
        font-family: "Brush Script MT", "Segoe Script", cursive;
        font-size: 40px;
        line-height: 1;
        color: #111111;
        transform: rotate(-4deg);
        display: inline-block;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 10px 14px;
        border: 1px solid #d9d9d9;
    }

    .invoice-table th {
        background: #f3f4f6;
        font-weight: 700;
        text-transform: uppercase;
    }

    .invoice-total-table td {
        padding: 10px 16px;
        border: 1px solid #d9d9d9;
        font-weight: 700;
    }

    .invoice-total-table .grand-total td {
        background: #ef1b1b;
        color: #ffffff;
    }

    @media (max-width: 640px) {
        .invoice-paper {
            padding: 18px 16px 20px;
        }

        .invoice-signature {
            font-size: 30px;
        }
    }

    @media print {
        @page {
            size: A5 portrait;
            margin: 0;
        }

        html,
        body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body * {
            visibility: hidden !important;
        }

        #print-invoice-area,
        #print-invoice-area * {
            visibility: visible !important;
        }

        #print-invoice-area {
            position: absolute !important;
            inset: 0 !important;
            width: 148mm !important;
            min-height: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            overflow: visible !important;
        }

        .invoice-paper {
            width: 148mm !important;
            min-height: 210mm !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 10mm 9mm 9mm !important;
            border: 0 !important;
            box-shadow: none !important;
            background: #ffffff !important;
            page-break-after: avoid !important;
        }

        .no-print,
        aside,
        nav,
        header,
        footer {
            display: none !important;
        }
    }
</style>

<div class="space-y-6 overflow-x-hidden">
    <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white break-words">{{ $invoiceCode }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Invoice details and payment receipt</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <a
                href="{{ route('admin.invoices.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                Back
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center rounded-xl bg-[#ff5437] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#e94b32]"
            >
                Print Invoice
            </button>
        </div>
    </div>

    <div id="print-invoice-area" class="invoice-print-sheet">
        <div class="invoice-paper">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="pt-1">
                    <h1 class="text-[20px] font-semibold tracking-wide text-black">INVOICE</h1>
                </div>

                <div class="text-left sm:text-right">
                    <div class="text-[15px] font-bold text-black">{{ $companyName }}</div>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-8 sm:mt-10 md:flex-row md:items-start md:justify-between">
                <div class="max-w-full md:max-w-[52%]">
                    <p class="text-[13px] leading-6 text-[#444]">
                        <span class="font-bold text-black">{{ $companyName }}</span>,
                        {{ $companyAddress }}
                    </p>

                    <div class="mt-5">
                        <div class="text-[15px] font-bold uppercase text-black">Bill To</div>
                        <div class="mt-2 space-y-1 text-[14px] leading-7 text-black break-words">
                            <div class="font-semibold">{{ $customerName }}</div>
                            <div>{{ $invoice->phone ?? '—' }}</div>
                            <div>{{ $invoice->username ?? '—' }}</div>
                            <div>Somalia</div>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-full text-[14px] md:min-w-[220px] md:max-w-[260px]">
                    <div class="grid grid-cols-[110px_1fr] gap-y-2">
                        <div class="font-semibold text-[#333]">Invoice No.:</div>
                        <div class="text-right font-bold text-black break-words">{{ $invoiceCode }}</div>

                        <div class="font-semibold text-[#333]">Issue date:</div>
                        <div class="text-right font-bold text-black">{{ $issueDate }}</div>

                        <div class="font-semibold text-[#333]">Due date:</div>
                        <div class="text-right font-bold text-black">{{ $dueDate }}</div>
                    </div>

                    <div class="mt-6 grid grid-cols-[110px_1fr] gap-y-2">
                        <div class="font-semibold text-[#333]">Reference:</div>
                        <div class="text-right font-bold text-black break-words">{{ $referenceText }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-8 sm:mt-10 overflow-x-auto">
                <table class="invoice-table w-full min-w-[620px] border-collapse text-[14px] text-black">
                    <thead>
                        <tr>
                            <th class="text-left">Description</th>
                            <th class="w-[170px] text-center">Speed</th>
                            <th class="w-[150px] text-right">Unit Price ($)</th>
                            <th class="w-[150px] text-right">Amount ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="font-medium">{{ $planName }}</div>
                            </td>
                            <td class="text-center">{{ $speedText }}</td>
                            <td class="text-right">{{ $amount }}</td>
                            <td class="text-right">{{ $amount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-start sm:justify-end overflow-x-auto">
                <table class="invoice-total-table w-full max-w-full sm:max-w-[360px] border-collapse text-[14px]">
                    <tr>
                        <td class="text-left text-[#555]">TOTAL (USD):</td>
                        <td class="text-right text-[#555]">${{ $amount }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="text-left">TOTAL DUE (USD)</td>
                        <td class="text-right">${{ $amount }}</td>
                    </tr>
                </table>
            </div>

            <div class="mt-8 flex justify-start sm:justify-end">
                <div class="text-left sm:text-right">
                    <div class="text-[14px] font-semibold text-black">Issued by, signature:</div>
                    <div class="mt-5">
                        <span class="invoice-signature">{{ $companyName }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-12 border-t border-[#d9d9d9] pt-5 text-[13px] leading-6 text-black break-words">
                <span class="font-bold">{{ $companyName }}</span>,
                {{ $companyAddress }}
                <span class="ml-0 sm:ml-3 font-bold">Email:</span>
                {{ $companyEmail }}
            </div>
        </div>
    </div>
</div>
@endsection