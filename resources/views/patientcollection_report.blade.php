<style>
    *{
        margin: 0;
        padding: 0;
    }

    table{
        border-collapse: collapse;
    }
/* 
    table td{
        border-collapse: collapse;
        border: 1px solid #255a9b;
    } */

    /* table tr th{
        background-color: #255a9b;
    } */

    table tr{
        
    }
</style>
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important; padding: 60px;">
    <thead>
        <img style="width: 100%;position: absolute;top: 0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
    </thead>
    <thead style="text-align:center;width: 100%;">
        <tr>
            <td colspan="5" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody>
        <tr>
        	<td colspan="3" style="text-align: left;"><b>Total Amount:{{ $grand_total }}</b></td>
            <td colspan="2" style="text-align: right; border: none;"><b>Duration:{{ trim($Duration) }}</b></td>
    	</tr>
    	<tr>
        	<td colspan="5">&nbsp;</td>
    	</tr>
    	
    	<tr>
        	<td colspan="5" style="text-align: center; border: none;"><b>Branch:{{ $branchName }}</b></td>
    	</tr>
    	<tr>
        	<td colspan="5">&nbsp;</td>
    	</tr>
    	
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 05%;border-left: 1px solid black;">Sr No</th>
            <th style="width: 15%;border-left: 1px solid black;">Patient Name</th>
            <!-- <th style="width: 10%;border-left: 1px solid black;">Payment Mode</th> -->
            <th style="width: 15%;border-left: 1px solid black;">Phone Number</th>
            <!-- <th style="width: 15%;">Order Date</th> -->
            <th style="width: 15%;border-left: 1px solid black;">Amount</th>
            <th style="width: 30%;border-left: 1px solid black;">Notes</th>
            <!-- <th style="width: 15%;">Branch Name</th> -->
        </tr>
    	<?php $i = 1; ?>
		@foreach ($dailyCollection as $Dailycollection)
            <tr>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;"> {{ $Dailycollection['patient_name'] }}</td>
                <!-- <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $Dailycollection['payment_mode'] }}</td> -->
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $Dailycollection['phone_number'] }}</td>
                <!-- <td style="border-top: 1px solid black;text-align: center;">{{ date('d-m-Y',strtotime($Dailycollection['order_date'])) }}</td> -->
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $Dailycollection['amount'] }}</td>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;"></td>
                <!-- <td style="border-top: 1px solid black;text-align: center;">{{ $Dailycollection['branch_name'] }}</td> -->
           </tr>
            <?php
                // $net_amount += $treatments['net_amount'];
    			//$discount += $treatments['discount'];
    			//$total_amount += $treatments['total_amount'];
            ?>
            <?php $i++; ?>
        @endforeach
    </tbody>
    @if (!empty($treatmentCollections))
    <tbody>
        <tr>
            <td colspan="5" style="padding-top: 20px;text-align: center;border: none;"><b>Treatment-wise Collection</b></td>
        </tr>
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 10%;border-left: 1px solid black;">Sr No</th>
            <th colspan="2" style="width: 50%;border-left: 1px solid black;">Treatment</th>
            <th style="width: 20%;border-left: 1px solid black;">Quantity</th>
            <th style="width: 20%;border-left: 1px solid black;">Collected Amount</th>
        </tr>
        @foreach ($treatmentCollections as $index => $treatment)
            <tr>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $index + 1 }}</td>
                <td colspan="2" style="border-top: 1px solid black;border-left: 1px solid black;">{{ $treatment['treatment_name'] }}</td>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: center;">{{ $treatment['quantity'] }}</td>
                <td style="border-top: 1px solid black;border-left: 1px solid black;text-align: right;">{{ number_format($treatment['amount'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    @endif
    <tbody>
        <tr>
            <td colspan="5" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>