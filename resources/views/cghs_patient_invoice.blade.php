<style>
    * { margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #255a9b; padding: 6px; }
    th { background-color: #255a9b; color: #fff; }
    .no-border { border: none; }
    .title { background: #255a9b; color: #fff; font-size: 18px; text-align: center; font-weight: bold; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
</style>

<table>
    <tr>
        <td colspan="5" class="title">CGHS PATIENT INVOICE</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Patient Name:</strong> {{ $invoice->patient_name }}</td>
        <td colspan="2"><strong>Date:</strong> {{ optional($invoice->cghs_date)->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td colspan="3"><strong>Invoice No:</strong> {{ $invoice->id }}</td>
        <td colspan="2"><strong>CGHS Type:</strong> {{ optional($invoice->cghsType)->strCghsName ?? $invoice->cghs_type }}</td>
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
        <td><strong>Amount</strong></td>
        <td class="text-right">{{ number_format((float) $invoice->amount, 2) }}</td>
    </tr>
    <tr>
        <td colspan="3" class="no-border"></td>
        <td><strong>Discount</strong></td>
        <td class="text-right">{{ number_format((float) $invoice->discount_amount, 2) }}</td>
    </tr>
    <tr>
        <td colspan="3" class="no-border"></td>
        <td><strong>Total</strong></td>
        <td class="text-right">{{ number_format((float) $invoice->total_amount, 2) }}</td>
    </tr>
</table>