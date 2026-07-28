<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Expense Voucher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .voucher {
            border: 1px solid #000;
            padding: 10px;
            max-width: 500px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            text-decoration: underline;
        }
        .firm-name {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        .label {
            font-weight: bold;
        }
        .value {
            border-bottom: 1px solid #000;
            min-width: 200px;
            text-align: right;
            padding-left: 10px;
        }
        .signature-row {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-content: center;
        }
        .signature-label {
            font-weight: bold;
            align-self: center;
            /*margin-bottom: 30px;*/
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
        }
    </style>
</head>
<body>
    @foreach($Collection as $data)
        <!--<br />-->
        <!--<br />-->
        <!--<br />-->
    <div class="voucher">
        <!--<br />-->
        <div class="header">Cash Expense Voucher</div>
        
        <div class="firm-name">Firm Name: Vraj Dental Clinics Pvt. Ltd.</div>
        <div class="detail-row">
            <span class="label">Cash Amount:</span>
            <span class="value">{{ number_format($data['amount'], 2) }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">Paid To:</span>
            <span class="value">{{ $data['cash_expense'] }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">Date:</span>
            <span class="value">{{ $data['expense_date'] }}</span>
        </div>
        
        <div class="detail-row">
            <span class="label">Branch Name:</span>
            <span class="value">{{ $data['branch_name'] }}</span>
        </div>
        
        <div class="signature-row">
            <span class="signature-label">Signature:</span>
            <img src="https://vgdcapp.vrajdentalclinic.com/assets/images/ujas.png" style="width:100px;"/>
            <span class="signature-line"></span>
        </div>
    
    <!--<br />-->
    </div>
    <br />
    <!--<br />-->
    <!--<br />-->
    @endforeach
</body>
</html>