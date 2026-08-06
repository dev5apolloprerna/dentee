<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CGHS Patient Invoice #{{ $invoice->id }}</title>
    <style>
        @page {
            margin: 112px 38px 82px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #243447;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .page-header {
            position: fixed;
            top: -112px;
            left: -38px;
            right: -38px;
            height: 94px;
            color: #fff;
            background: #255a9b;
            border-bottom: 6px solid #79bce8;
        }

        .header-accent {
            position: absolute;
            right: 0;
            top: 0;
            width: 33%;
            height: 94px;
            background: #163f72;
        }

        .brand-mark {
            position: absolute;
            top: 15px;
            left: 38px;
            width: 56px;
            height: 56px;
            border: 2px solid #fff;
            border-radius: 50%;
            text-align: center;
            font-size: 19px;
            font-weight: bold;
            line-height: 56px;
        }

        .brand-copy {
            position: absolute;
            top: 18px;
            left: 106px;
        }

        .brand-name {
            font-size: 21px;
            font-weight: bold;
            letter-spacing: .3px;
        }

        .brand-tagline {
            margin-top: 2px;
            color: #dbeeff;
            font-size: 9px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .document-label {
            position: absolute;
            top: 26px;
            right: 38px;
            z-index: 2;
            text-align: right;
        }

        .document-label strong {
            display: block;
            font-size: 15px;
            letter-spacing: .8px;
        }

        .document-label span {
            color: #dbeeff;
            font-size: 9px;
        }

        .page-footer {
            position: fixed;
            bottom: -82px;
            left: -38px;
            right: -38px;
            height: 58px;
            padding: 13px 38px 0;
            color: #fff;
            background: #255a9b;
            border-top: 5px solid #79bce8;
            font-size: 8px;
        }

        .footer-left {
            width: 67%;
        }

        .footer-right {
            position: absolute;
            top: 13px;
            right: 38px;
            text-align: right;
        }

        .page-number:after {
            content: counter(page);
        }

        .watermark {
            position: fixed;
            top: 34%;
            left: 12%;
            width: 76%;
            color: #edf4fa;
            font-size: 74px;
            font-weight: bold;
            letter-spacing: 5px;
            text-align: center;
            transform: rotate(-32deg);
            z-index: -1000;
        }

        .invoice-heading {
            width: 100%;
            margin-bottom: 18px;
        }

        .invoice-heading td {
            vertical-align: top;
        }

        .invoice-heading h1 {
            margin: 0 0 4px;
            color: #255a9b;
            font-size: 24px;
            letter-spacing: .5px;
        }

        .invoice-heading .subtitle {
            color: #66788a;
            font-size: 9px;
            text-transform: uppercase;
        }

        .invoice-number {
            padding-top: 4px;
            text-align: right;
        }

        .invoice-number strong {
            display: block;
            color: #255a9b;
            font-size: 16px;
        }

        .patient-card {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #cbd9e6;
            border-left: 5px solid #255a9b;
            background: #f7fafc;
            border-collapse: collapse;
        }

        .patient-card td {
            width: 50%;
            padding: 8px 10px;
            border-bottom: 1px solid #e3ebf2;
        }

        .patient-card tr:last-child td {
            border-bottom: 0;
        }

        .patient-card td+td {
            border-left: 1px solid #e3ebf2;
        }

        .label {
            display: block;
            margin-bottom: 2px;
            color: #718398;
            font-size: 8px;
            text-transform: uppercase;
        }

        .value {
            color: #1b2d40;
            font-size: 11px;
            font-weight: bold;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items thead {
            display: table-header-group;
        }

        .items tr {
            page-break-inside: avoid;
        }

        .items th {
            padding: 8px 7px;
            color: #fff;
            background: #255a9b;
            border: 1px solid #255a9b;
            font-size: 9px;
            text-transform: uppercase;
        }

        .items td {
            padding: 8px 7px;
            border: 1px solid #d5e0ea;
        }

        .items tbody tr:nth-child(even) {
            background: #f7fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 42%;
            margin: 16px 0 0 auto;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .totals td {
            padding: 7px 9px;
            border-bottom: 1px solid #d5e0ea;
        }

        .totals .grand-total td {
            color: #fff;
            background: #255a9b;
            border: 0;
            font-size: 12px;
            font-weight: bold;
        }

        .invoice-note {
            margin-top: 28px;
            color: #657789;
            font-size: 9px;
        }

        .signature {
            margin-top: 34px;
            text-align: right;
            page-break-inside: avoid;
        }

        .signature-line {
            display: inline-block;
            width: 150px;
            padding-top: 7px;
            border-top: 1px solid #8293a5;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="header-accent"></div>
        <div class="brand-mark">VD</div>
        <div class="brand-copy">
            <div class="brand-name">Vraj Dental Clinic</div>
            <div class="brand-tagline">Advanced and compassionate dental care</div>
        </div>
        <div class="document-label">
            <strong>CGHS INVOICE</strong>
            <span>Patient billing document</span>
        </div>
    </div>
    <div class="page-footer">
        <div class="footer-left">
            <strong>VRAJ DENTAL CLINIC</strong><br>
            This is a computer-generated invoice and does not require a physical signature.
        </div>
        <div class="footer-right">CGHS Invoice #{{ $invoice->id }} &nbsp; | &nbsp; Page <span class="page-number"></span></div>
    </div>
    <div class="watermark">VRAJ DENTAL</div>

    <table class="invoice-heading">
        <tr>
            <td>
                <h1>Patient Invoice</h1>
                <div class="subtitle">Central Government Health Scheme</div>
            </td>
            <td class="invoice-number">
                <span class="label">Invoice number</span>
                <strong>#{{ $invoice->id }}</strong>
            </td>
        </tr>
    </table>

    <table class="patient-card">
        <tr>
            <td><span class="label">Patient name</span><span class="value">{{ $invoice->patient_name ?: '—' }}</span></td>
            <td><span class="label">Invoice date</span><span class="value">{{ $invoice->cghs_date ? $invoice->cghs_date->format('d M Y') : '—' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">CGHS category</span><span class="value">{{ optional($invoice->cghsType)->strCghsName ?? $invoice->cghs_type ?? '—' }}</span></td>
            <td><span class="label">Patient / beneficiary ID</span><span class="value">{{ $invoice->patient_id ?: '—' }}</span></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 9%;">Sr. No.</th>
                <th style="width: 43%; text-align: left;">Treatment / Procedure</th>
                <th style="width: 12%;">Qty.</th>
                <th style="width: 18%; text-align: right;">Rate (INR)</th>
                <th style="width: 18%; text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->details as $detail)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $detail->cghs_treatment_name ?? optional($detail->treatment)->cghs_treatment_name ?? '—' }}</td>
                <td class="text-center">{{ $detail->iQty }}</td>
                <td class="text-right">{{ number_format((float) $detail->iAmount, 2) }}</td>
                <td class="text-right">{{ number_format((float) $detail->iQty * (float) $detail->iAmount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No treatment details were added to this invoice.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">INR {{ number_format((float) $invoice->amount, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="text-right">INR {{ number_format((float) $invoice->discount_amount, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Amount payable</td>
            <td class="text-right">INR {{ number_format((float) $invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="invoice-note"><strong>Note:</strong> Please retain this invoice for your CGHS reimbursement and treatment records.</div>
    <div class="signature"><span class="signature-line">Authorized Signatory</span></div>
</body>

</html>