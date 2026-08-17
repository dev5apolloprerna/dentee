<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CGHS Patient Invoice #{{ $invoice->id }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            color: #111;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .page-background {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: -10;
            background-color: #fff;
            background-image: url("https://vgdcapp.vrajdentalclinic.com/assets/images/background.png");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: -1;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: -1;
        }

        .invoice-content {
            padding: 48px 60px 105px;
        }

        .clinic-header {
            margin-bottom: 12px;
            text-align: center;
        }

        .clinic-header img {
            width: 100%;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .title td {
            padding: 6px;
            color: #fff;
            background: #255a9b;
            border: 1px solid #255a9b;
            font-size: 18px;
            text-align: center;
            text-transform: uppercase;
        }

        .patient-details {
            margin-top: 18px;
            margin-bottom: 18px;
        }

        .patient-details td {
            padding: 6px;
            border: 1px solid #255a9b;
        }

        .label {
            font-weight: bold;
        }

        .items thead {
            display: table-header-group;
        }

        .items tr {
            page-break-inside: avoid;
        }

        .items th {
            padding: 6px;
            color: #fff;
            background: #255a9b;
            border: 1px solid #255a9b;
            font-size: 11px;
        }

        .items td {
            padding: 6px;
            border: 1px solid #255a9b;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 52%;
            margin: 0 0 0 auto;
            page-break-inside: avoid;
        }

        .totals td {
            padding: 6px;
            border: 1px solid #255a9b;
        }

        .totals .total-label {
            font-weight: bold;
        }

        .totals .grand-total td {
            font-weight: bold;
        }

        .note {
            margin-top: 28px;
            line-height: 1.7;
        }

        .approval {
            margin-top: 30px;
            page-break-inside: avoid;
            text-align: right;
        }

        .approval img {
            width: 145px;
            max-height: 100px;
            object-fit: contain;
        }

        .signature {
            font-weight: bold;
        }

        .proofreading {
            margin-top: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    {{-- Use the same artwork as treatmentinvoice.blade.php so both PDFs have identical branding. --}}
    <div class="page-background"></div>
    <img class="page-header" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
    <img class="page-footer" src="https://vgdcapp.vrajdentalclinic.com/assets/images/new-footer.png" alt="">
    <main class="invoice-content">
        <div class="clinic-header">
            <img src="{{ public_path('assets/images/cghs-invoice-header.svg') }}" alt="Vraj Dental Clinics Pvt. Ltd., GF, Ashouk House 2, Inside Sansthavasahat Gate, Raopura. Mobile: 9558772226. Email: vrajgroupofdental@mail.com">
        </div>
        <table class="title">
            <tr>
                <td>CGHS Patient Invoice</td>
            </tr>
        </table>

        <table class="patient-details">
            <tr>
                <td style="width: 65%;">
                    <span class="label">Patient Name:-</span>
                    {{ $invoice->patient_name ?: '—' }}
                </td>
                <td style="width: 35%;">
                    <span class="label">Date:-</span>
                    {{ $invoice->cghs_date ? $invoice->cghs_date->format('d-F-Y') : '—' }}
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Invoice No:-</span>
                    {{ $invoice->id }}
                </td>
                <td>
                    <span class="label">CGHS Type:-</span>
                    {{ optional($invoice->cghsType)->strCghsName ?? $invoice->cghs_type ?? '—' }}
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 10%;">Sr No</th>
                    <th style="width: 42%;">Treatment Type</th>
                    <th style="width: 12%;">Unit</th>
                    <th style="width: 18%;">Amount</th>
                    <th style="width: 18%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoice->details as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $detail->cghs_treatment_name ?? optional($detail->treatment)->cghs_treatment_name ?? '—' }}</td>
                    <td class="text-center">{{ $detail->iQty }}</td>
                    <td class="text-center">{{ number_format((float) $detail->iAmount, 2) }}</td>
                    <td class="text-center">{{ number_format((float) $detail->iQty * (float) $detail->iAmount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No invoice details found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="total-label">NET TOTAL:-</td>
                <td class="text-center">{{ number_format((float) $invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">TOTAL DISCOUNT:-</td>
                <td class="text-center">{{ number_format((float) $invoice->discount_amount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>FINAL AMOUNT:-</td>
                <td class="text-center">{{ number_format((float) $invoice->total_amount, 2) }}</td>
            </tr>
        </table>
        <div class="note">This is a software-generated CGHS patient invoice.</div>
        <div class="approval">
            <img src="https://vgdcapp.vrajdentalclinic.com/assets/images/stamp.png" alt="Vraj Dental Clinics stamp and signature">
            <div class="signature">Authorized Signature &amp; Stamp</div>
            <div class="proofreading">Invoice prepared and proofread by maker.</div>
        </div>
    </main>
</body>

</html>