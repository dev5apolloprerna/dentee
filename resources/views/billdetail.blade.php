
<style>
    *{
        margin: 0;
        padding: 0;
    }

    table{
        border-collapse: collapse;
    }

    table td{
        border-collapse: collapse;
        border: 1px solid #255a9b;
        padding: 3px;
    }

    table tr th{
        background-color: #255a9b;
    }

    table tr{
        
    }
</style>
    
	 <!--<table style="width: 100%;border-bottom: 1px solid black;border:none; padding-bottom: 4px;">
        <tr>
            <td rowspan="4"><img src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
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
    </table>-->
	<?php $discountVisible = false; ?>
	    @if($masterOrderDetails['discount'] > 0)
	        <?php 
	        $discountVisible = true; ?>
	    @endif
	<?php $discountVisibleOrderDetails = false; ?>
	@foreach ($BillorderdetailList as $billorderdetailList)
	    @if($billorderdetailList['discount_amount'] > 0)
	        <?php $discountVisibleOrderDetails = true; ?>
	    @endif
	@endforeach
	
	
	
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important;padding: 60px;">
    <thead>
        <img style="width: 100%;position: absolute; top: 0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
    </thead>
    <thead style="text-align:center;width: 100%;">
       <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
       </tr>
    </thead>
    <tbody style="width: 100%;text-align: center;background: #255a9b;">
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" rowspan="1" style="background: #255a9b;font-family: sans-serif;color: white;padding: 5px;font-size: 18px;width: 100%;">BILL</td>
        </tr>
    </tbody>
    <!--<tbody style="width: 100%;">-->
    <!--    <tr>-->
    <!--        <td colspan="3" style="border: none;">&nbsp;</td>-->
    <!--        <td colspan="3" style="border: none;">&nbsp;</td>-->
    <!--    </tr>-->
    <!--</tbody>-->
    <tbody style="width: 100%;">
        <tr>
            <td colspan="3" style="width: 70%;padding: 5px;">Patient Name:- {{ $name_prefix }} {{$patient_name }}</td>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="width: 30%;padding: 5px;">Bill No:- {{$BillorderdetailList[0]['bill_no']}}</td>
        </tr>
        <tr>
            <td colspan="3" style="width: 70%;padding: 5px;">Branch:- : {{$address}}</td>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="width: 30%;padding: 5px;">Bill Date:- :{{$BillorderdetailList[0]['bill_date']}}</td>
        </tr>
    </tbody>
    <tbody style="width: 100%;">
        <tr>
            <td colspan="3" style="border: none;">&nbsp;</td>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border: none;">&nbsp;</td>
        </tr>
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5; font-family: sans-serif;color: white;">
            <th style="width: 10%;">Sr No</th>
            <th style="width: 30%;">Treatment Type</th>
            <th style="width: 20%;">Teeth</th>
            <th style="width: @if($discountVisibleOrderDetails == true) 25% @else 10% @endif;">Cost</th>
            @if($discountVisibleOrderDetails == true)
            <th style="width: 15%;">Discount</th>
            @endif
            <th style="width: 15%;">Net Cost</th>
        </tr>
        <?php $i = 1; ?>
		@foreach ($BillorderdetailList as $billorderdetailList)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $billorderdetailList['treatment_name'] }}</td>
                <td>{{ wordwrap($billorderdetailList['selected_teeth'], 10, "\n",true); }}</td>
                <td> {{ $billorderdetailList['amount'] }}</td>
                @if($discountVisibleOrderDetails == true)
                <td>{{ $billorderdetailList['discount_amount'] }}</td>
                @endif
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
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;">&nbsp;</td>
            <td colspan="2" style="text-align: center;border-top: 1px solid black;">TOTAL</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $grand_amount}}</td>
         </tr>
        <tr>
           <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
           <td colspan="2" style="text-align: start;border-top: 1px solid black;">Grand Total:-</td>
           <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['net_amount']}}</td>
        </tr>
        @if($discountVisible == true)
		<tr>
           <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
           <td colspan="2" style="text-align: start;border-top: 1px solid black;">Discount:-</td>
           <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['discount']}}</td>
        </tr>
        @endif
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
            <td  colspan="2"  style="text-align: start;border-top: 1px solid black;">Paid Amount:-</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['paid_amount']}}</td>
         </tr>
         <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border-top: 1px solid black;text-align: start;text-transform: uppercase;"></td>
            <td colspan="2" style="text-align: start;border-top: 1px solid black;">Balance:-</td>
            <td style="border-top: 1px solid black;text-align: center;">{{ $masterOrderDetails['due_amount']}}</td>
         </tr> 
    </tbody>
	<?php if(count($masterOrderPaymentDetail) > 0) {?>
	<tbody style="width: 100%;">
        <tr>
            <td colspan="3" style="border: none;">&nbsp;</td>
            <td colspan="@if($discountVisibleOrderDetails == true) 3 @else 2 @endif" style="border: none;">&nbsp;</td>
        </tr>
    </tbody>
    <tbody style="width: 50%;">
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="width: 100%;text-align: center;text-transform: uppercase;font-weight: bold; border: none;">Payment details</td>
        </tr>
        <tr style="background-color: #e5e5e5e5;color:white;">
            <th colspan="@if($discountVisibleOrderDetails == true) 2 @else 1 @endif" style="width: 30%;color:white;">Date</th>
            <th style="width: 25%;color:white;">Receipt Number</th>
            <th style="width: 20%;color:white;">Mode of Payment</th>
            <th style="width: 25%;color:white;">Paid Amount INR</th>
        </tr>
        <?php
            $i = 1;
            ?>
			@foreach ($masterOrderPaymentDetail as $MasterOrderPaymentDetail)
            <tr>
                
                <td colspan="2">{{ $MasterOrderPaymentDetail['payment_date'] }}</td>
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
                <td style="border: 1px solid #255a9b !important;">{{ $MasterOrderPaymentDetail['amount'] }}</td>
            </tr>

            <?php $i++; ?>
        @endforeach
    </tbody>
	<?php }?>
    <tbody style="width: 100%;">
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="padding-top: 20px;text-align: start;font-weight: 900; border: none;margin-left:10px;">Notes:-</td>
        </tr>
    </tbody>
    <tbody style="width: 100%;">
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="padding-top: 70px;text-align: right;font-weight: 900; border: none;">
                <img src="https://vgdcapp.vrajdentalclinic.com/assets/images/ujas.png" style="width:100px;"/></td>
        </tr>
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="text-align: right;font-weight: 900; border: none;">Authorized Signature</td>
        </tr>
    </tbody>
	<tbody>
        <tr>
            <td colspan="@if($discountVisibleOrderDetails == true) 6 @else 5 @endif" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/new-footer.png" alt=""></td>
       </tr>
    </tbody>
</table>
    