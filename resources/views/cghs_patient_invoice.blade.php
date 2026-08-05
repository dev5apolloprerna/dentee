
    <style>
        *{
            margin: 1;
            padding: 1;
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
        
        
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        
        table tr th{
            background-color: #255a9b;
            color: white;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
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

    <div class="invoice-page">
        <img class="header-image" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
        <div class="logo">
            <img src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt="Vraj Dental Clinic">
        </div>

        <table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important; padding: 60px;">
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

        <div class="footer-note">
            This is a computer-generated CGHS patient invoice.
            <img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/new-footer.png" alt="">    
        </div>
    </div>
