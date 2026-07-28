
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
    }

    table tr th{
        background-color: #255a9b;
    }

    table tr{
        
    }
</style>

<table style="width: 100%;background-image: url(https://getdemo.in/dentee/assets/images/background.png);!important">
<thead>
    <img style="width: 100%;position: absolute;
    top: 0px;" src="https://getdemo.in/dentee/assets/images/New-Header.png" alt="">
</thead>
<thead style="text-align:center;width: 100%;">
   <tr>
    <td style="border: none;"><img style="text-align:center; padding: 60px;" src="https://getdemo.in/dentee/assets/images/logo_n.png" alt=""></td>
   </tr>
</thead>
<table style="width: 100%;text-align: center;background: #255a9b;">
    <tr>
        <td rowspan="1" style="background: #255a9b;
    font-family: sans-serif;
    color: white;
    padding: 5px;
    font-size: 18px;width: 100%;">QUOTATION</td>
    </tr>
</table>
<table style="width: 100%;">
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
</table>
<table style="width: 100%;">
    <tr>
        <td style="width: 80%;padding: 5px;">Patient Name:- {{ $Treatments[0]['name_prefix']}} {{$Treatments[0]['patient_name'] }}({{$Treatments[0]['case_no']}})</td>
        <td style="width: 20%;padding: 5px;">Date:-{{ $Treatments[0]['treatment_date']}}</td>
    </tr>
    <tr>
		 <?php 
		if(!empty($Treatments[0]['patient_address'])){
			?>
			 
				<td colspan="2" style="width: 80%;padding: 5px;">Address:- {{$Treatments[0]['patient_address']}}</td>
	
			<?php
		}else{
		?>
				<td colspan="2" style="width: 80%;padding: 5px;">Address : - </td>
			
		<?php }?>
       
    </tr>
</table>
<table style="width: 100%;">
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
</table>
<!--<table style="width: 100%;padding-bottom: 0px;">
    <tr>
        <td rowspan="4" style="width:50%;border:none;border-top:1px solid black;border-left:1px solid black;">
		<img src="https://getdemo.in/dentee/assets/images/logo_n.png" alt=""></td>
        <td style="font-weight: bold;width:50%;">{{ $Treatments[0]['doctor_name']}}</td>
    </tr>

	<tr>
    <td rowspan="3" style="width:50%;">{{$Treatments[0]['address']}}</td>
    </tr>
    
</table>
<table style="width: 100%;">
    <tr>
        <td style="text-align: center; font-weight: 600;font-size:36px; padding-top: 10px;text-transform: uppercase;">quotation</td>
     </tr>
</table>-->
<!--<table style="width: 100%;">
    <tr>
        <td>Date:- {{ $Treatments[0]['treatment_date']}}</td>
    </tr>
    <tr>
        <td>Patient Name : {{ $Treatments[0]['name_prefix']}} {{$Treatments[0]['patient_name'] }}({{$Treatments[0]['case_no']}})</td>
    </tr>
   <?php 
		if(!empty($Treatments[0]['patient_address'])){
			?>
			 <tr>
				<td>Address : {{$Treatments[0]['patient_address']}}</td>
			</tr>
			<?php
		}else{
		?>
			<tr>
				<td>Address : - </td>
			</tr>
		<?php }?>
</table>-->


<table style="width: 100%;">
    <tbody>
    <tr>
        <th style="width: 10%;color: white;font-family: sans-serif;padding: 5px;">Sr No</th>
        <th style="width: 30%;color: white;font-family: sans-serif;padding: 5px;">Treatment Type</th>
        <th style="width: 10%;color: white;font-family: sans-serif;padding: 5px;">Unit</th>
        <th style="width: 20%;color: white;font-family: sans-serif;padding: 5px;">Amount</th>
        <th style="width: 15%;color: white;font-family: sans-serif;padding: 5px;">Discount</th>
        <th style="width: 15%;color: white;font-family: sans-serif;padding: 5px;">Total</th>
    </tr>

<?php
            $i = 1;
            ?>
@foreach ($Treatments as $treatments)
            
            <tr>
                <td style="text-align: center;padding: 3px;">{{ $i }}</td>
                <td style="text-align: center;padding: 3px;">{{ $treatments['treatment_name'] }}</td>
                <td style="text-align: center;padding: 3px;">{{ $treatments['selected_teeth_count'] }}</td>
                <td style="text-align: center;padding: 3px;"> {{ $treatments['amount'] }}</td>
                <td style="text-align: center;padding: 3px;">{{ $treatments['discount_amount'] }}</td>
                <td style="text-align: center;padding: 3px;">{{ $treatments['net_amount'] }}</td>
            </tr>
            <?php
           // $net_amount += $treatments['net_amount'];
			//$discount += $treatments['discount'];
			//$total_amount += $treatments['total_amount'];
            ?>

            <?php $i++; ?>
        @endforeach
	 
	 <tr>
       <td colspan="3" style="text-align: start;text-transform: uppercase;"></td>
       <td colspan="2" style="text-align: start;padding: 3px;">NET TOTAL:-</td>
       <td colspan="2" style="text-align: center;padding: 3px;">{{$netAmount}}</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: start;text-transform: uppercase;"></td>
        <td colspan="2" style="text-align: start;padding: 3px;">TOTAL DISCOUNT:-</td>
        <td colspan="2" style="text-align: center;padding: 3px;">{{$discountAmount}}</td>
     </tr>
     <tr>
        <td colspan="3" style="text-align: start;text-transform: uppercase;"></td>
        <td colspan="2" style="text-align: start;padding: 3px;">FINAL AMOUNT:-</td>
        <td colspan="2" style="text-align: center;padding: 3px;">{{$totalAmount}}</td>
     </tr>
</tbody>
</table>
<table style="text-align: end; width: 100%;">
<tr>
        <td style="font-weight: bold;border:none;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border:none;font-family: sans-serif;padding:10px;">This is software generated quotation,can be differ if there will be </td>
    </tr>
    <tr>
        <td style="border:none;font-family: sans-serif;padding:10px;">any changes in treatment plan.</td>
    </tr>
    <tr>
        <td style="border:none;font-family: sans-serif;padding:10px;">This quotation is valid for 15 days from issue date.</td>
    </tr>
    <tr>
        <td style="font-weight: bold;border:none;">&nbsp;</td>
    </tr>
    <tr>
        <td style="font-weight: bold;border:none;font-family:sans-serif;text-align: right;padding:10px;">Authorized Signature</td>
    </tr>
</table>

<table>
    <tr>
        <td style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;" src="https://getdemo.in/dentee/assets/images/new-footer.png" alt=""></td>
       </tr>
</table>
</table>
