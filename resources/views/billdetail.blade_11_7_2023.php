
<style>

    table{
        width: 100%;
        /* border: 1px solid black; */
        font-family: sans-serif;
        border-collapse: collapse;
    
    }
    
    table th,
    td{
        padding: 5px;
        border: 1px solid black;
    }
    
    th{
        font-weight: bold;
    }
    </style>
    
	 <table style="width: 100%;border-bottom: 1px solid black;border:none; padding-bottom: 4px;">
        <tr>
            <td rowspan="4"><img src="https://getdemo.in/dentee/assets/images/logo_n.png" alt=""></td>
            <td style="font-weight: bold;">{{ $BillorderdetailList[0]['doctor_name']}}</td>
        </tr>
    
        <tr>
        <td rowspan="3">{{$BillorderdetailList[0]['doctor_address']}}</td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tr>
            <td style="text-align: center; font-weight: 600;font-size:36px; padding-top: 10px;text-transform: uppercase;">Bill</td>
         </tr>
    </table>
    <table style="width: 100%; padding-bottom: 10px;">
        <tr>
            <td>Patient Name : {{ $name_prefix }} {{$patient_name }}({{$case_no}})</td>
        </tr>
        <?php 
		if(!empty($address)){
			?>
			 <tr>
				<td>Address : {{$address}}</td>
			</tr>
			<?php
		}else{
		?>
			<tr>
				<td>Address : - </td>
			</tr>
		<?php }?>
        <tr>
            <td>Bill No :{{$BillorderdetailList[0]['bill_no']}}</td>
        </tr>
        <tr>
            <td>BillDate :{{$BillorderdetailList[0]['bill_date']}}</td>
        </tr>
    </table>
    <table style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 10%;">Sr No</th>
            <th style="width: 30%;">Treatment Type</th>
            <th style="width: 20%;">Teeth</th>
            <th style="width: 10%;">Cost</th>
            <th style="width: 15%;">Discount</th>
            <th style="width: 15%;">Net Cost</th>
        </tr>
    
       <?php
            $i = 1;
            ?>
			@foreach ($BillorderdetailList as $billorderdetailList)
            
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $billorderdetailList['treatment_name'] }}</td>
                <td>{{ $billorderdetailList['selected_teeth'] }}</td>
                <td> {{ $billorderdetailList['amount'] }}</td>
                <td>{{ $billorderdetailList['discount_amount'] }}</td>
                <td>{{ $billorderdetailList['total_amount'] }}</td>
            </tr>
            <?php
           // $net_amount += $treatments['net_amount'];
			//$discount += $treatments['discount'];
			//$total_amount += $treatments['total_amount'];
            ?>

            <?php $i++; ?>
        @endforeach
        <tr>
            <td colspan="3" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;">&nbsp;</td>
            <td colspan="2" style="text-align: center;border-top: 1px solid black;">TOTAL</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $grand_amount}}</td>
         </tr>
        <tr>
           <td colspan="3" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
           <td colspan="2" style="text-align: start;border-top: 1px solid black;">Grand Total:-</td>
           <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['net_amount']}}</td>
        </tr>
		<tr>
           <td colspan="3" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
           <td colspan="2" style="text-align: start;border-top: 1px solid black;">Discount:-</td>
           <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['discount']}}</td>
        </tr>
        <tr>
            <td colspan="3" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
            <td  colspan="2"  style="text-align: start;border-top: 1px solid black;">Paid Amount:-</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['paid_amount']}}</td>
         </tr>
         <tr>
            <td colspan="3" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
            <td colspan="2" style="text-align: start;border-top: 1px solid black;">Balance:-</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['due_amount']}}</td>
         </tr> 
    
    </table>
    <table style="width: 50%;">
        <tr>
            <td colspan="4" style="width: 100%;text-align: center;text-transform: uppercase;font-weight: bold;">Payment details</td>
        </tr>
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 25%;">Date</th>
            <th style="width: 25%;">Receipt Number</th>
            <th style="width: 25%;">Mode of
                Payment</th>
            <th style="width: 25%;">Paid Amount
                INR</th>
        </tr>
        <?php
            $i = 1;
            ?>
			@foreach ($masterOrderPaymentDetail as $MasterOrderPaymentDetail)
            
            <tr>
                <td>{{ $MasterOrderPaymentDetail['created_date'] }}</td>
                <td>RCPT {{ $MasterOrderPaymentDetail['order_payment_detail_id'] }}</td>
                <td>
				 <?php
						if($MasterOrderPaymentDetail['payment_mode'] == 1){
							echo "Cash";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 2){
							echo "Cheque";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 3){
							echo "Card";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 4){
							echo "RTGS";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 5){
							echo "NEFT";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 6){
							echo "Paytm";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 7){
							echo "Coupons";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 8){
							echo "Online";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 9){
							echo "WriteOff";
						}else if ($MasterOrderPaymentDetail['payment_mode'] == 10){
							echo "GooglePay";
						}
            ?>
				
				
				</td>
                <td>{{ $MasterOrderPaymentDetail['amount'] }}</td>
            </tr>

            <?php $i++; ?>
        @endforeach
        </table>
        <table style="width: 100%;">
            <td style="padding-top: 20px;text-align: start;font-weight: 900; border: none;">Notes:-</td>
            </table>
    <table style="width: 100%;">
    <td style="padding-top: 70px;text-align: right;font-weight: 900; border: none;">Authorized Signature</td>
    </table>
    