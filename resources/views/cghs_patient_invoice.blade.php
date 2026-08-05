<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            background: #fff;
        }
        .invoice-page {
            width: 100%;
            padding: 90px 42px 42px;
            background-image: url("{{ public_path('assets/images/background.png') }}");
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
        }
        .header-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
        .logo { text-align: center; margin-bottom: 18px; }
        .logo img { width: 155px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #255a9b; padding: 7px; vertical-align: middle; }
        th { background-color: #255a9b; color: #fff; font-weight: bold; }
        .title {
            background: #255a9b;
            color: #fff;
            font-size: 18px;
            text-align: center;
            font-weight: bold;
            letter-spacing: .5px;
            padding: 8px;
        }
        .meta-label { font-weight: bold; color: #111; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-label { font-weight: bold; background: #eef4fb; }
        .no-border { border: none !important; }
        .footer-note { margin-top: 28px; font-size: 11px; color: #555; text-align: center; }
    </style>
</head>
<body>
    <div class="invoice-page">
        <img class="header-image" src="{{ public_path('assets/images/New-Header.png') }}" alt="">
        <div class="logo">
            <img src="{{ public_path('assets/images/logo_n.png') }}" alt="Vraj Dental Clinic">
        </div>

        <table>
            <tr>
                <td colspan="5" class="title">CGHS PATIENT INVOICE</td>
            </tr>
            <tr>
                <td colspan="3"><span class="meta-label">Patient Name:</span> {{ $invoice->patient_name }}</td>
                <td colspan="2"><span class="meta-label">Date:</span> {{ optional($invoice->cghs_date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td colspan="3"><span class="meta-label">Invoice No:</span> {{ $invoice->id }}</td>
                <td colspan="2"><span class="meta-label">CGHS Type:</span> {{ optional($invoice->cghsType)->strCghsName ?? $invoice->cghs_type }}</td>
            </tr>
            <tr>
                <th style="width: 10%;">Sr No</th>
                <th style="width: 45%;">Treatment</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 15%;">Amount</th>
                <th style="width: 15%;">Total</th>
            </tr>
            @forelse ($invoice->details as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->cghs_treatment_name ?? optional($detail->treatment)->cghs_treatment_name }}</td>
                    <td class="text-center">{{ $detail->iQty }}</td>
                    <td class="text-right">{{ number_format((float) $detail->iAmount, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $detail->iQty * (float) $detail->iAmount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No invoice details found.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3" class="no-border"></td>
                <td class="summary-label">Amount</td>
                <td class="text-right">{{ number_format((float) $invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td class="summary-label">Discount</td>
                <td class="text-right">{{ number_format((float) $invoice->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="no-border"></td>
                <td class="summary-label">Total</td>
                <td class="text-right"><strong>{{ number_format((float) $invoice->total_amount, 2) }}</strong></td>
            </tr>
        </table>

        <div class="footer-note">This is a computer-generated CGHS patient invoice.</div>
    </div>
</body>
</html>