<style>
    *{
        margin: 0;
        padding: 0;
    }

    table{
        border-collapse: collapse;
    }

    table tr{
        
    }
	/*.page-break {*/
	/*	page-break-before: always;*/
	/*}*/
</style>
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important;padding: 60px;">
    <thead>
        <img style="width: 100%;position: absolute; top: 0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
    </thead>
    <thead style="text-align:center;width: 100%;">
       <tr>
            <td colspan="10" style="border:none;"><img style="text-align:center;padding:60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
       </tr>
    </thead>
    <tbody>
        <tr>
			<!--<td colspan="4" style="text-align: left;"><b>Total Amount:{{ $grand_total }}</b></td>-->
			<td colspan="5" style="text-align: left;"><b>Lab Name :{{ $LabworkData->lab_name }}</b></td>
            <td colspan="5" style="text-align: right;"><b>Duration:{{ trim($Duration) }} </b></td>
            
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="9">&nbsp;</td>
		</tr>
		<tr>
            <td colspan="9" style="text-align: center;"><b>Branch:{{ $branchName }} </b></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan="9">&nbsp;</td>
		</tr>
    </tbody>


    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="border: 1px solid black;width: 05%;">Sr No</th>
            <th style="border: 1px solid black;width: 15%;">Order Date</th>
            <!--<th style="border: 1px solid black;width: 05%;">Order <br /> No</th>-->
            <th style="border: 1px solid black;width: 20%;">Patients Name</th>
            <th style="border: 1px solid black;width: 15%;">Product</th>
            <th style="border: 1px solid black;width: 10%;">Teeth</th>
            <th style="border: 1px solid black;width: 05%;">Status</th>
            <th style="border: 1px solid black;width: 05%;">Sub Status</th>
            <th style="border: 1px solid black;width: 05%;">Unit</th>
            <th style="border: 1px solid black;width: 10%;">Rate <br />/Unit</th>
            <th style="border: 1px solid black;width: 10%;">Lab <br /> Amount</th>
            <!--<th style="border: 1px solid black;width: 10%;">Patients <br /> Amount</th>-->
        </tr>
	
		<?php
            $i = 1;
            $lab_price = 0;
            $rate = 0;
            ?>
			@foreach ($labWorkData as $LabWorkData)
            @if($LabWorkData['iLabWorkStatus'] != 5)
            <tr>
                <td style="border: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ date('d-m-Y',strtotime($LabWorkData['order_date'])) }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['patient_name'] }}</td>
                <td style="border: 1px solid black;text-align: center;"> {{ $LabWorkData['product_name'] }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ wordwrap($LabWorkData['teeth'], 10, "\n",true); }}</td>
                <td style="border: 1px solid black;text-align: center;">
                    @if($LabWorkData['iLabWorkStatus'] == 2 || $LabWorkData['iLabWorkStatus'] == 3)
                        {{ 'On Going' }}
                    @else 
                        {{ $LabWorkData['strLabWorkStatus'] }}
                    @endif
                </td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['strLabWorkStatus'] }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['unit'] }}</td>
				<td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['material_price'] }}</td>
				<td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['lab_price'] }}</td>
           </tr>
           <?php
            $lab_price += $LabWorkData['lab_price'];
			//$total_amount += $treatments['total_amount'];
            ?>
           @else
           <tr bgcolor="red">
                <td style="border: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ date('d-m-Y',strtotime($LabWorkData['order_date'])) }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['patient_name'] }}</td>
                <td style="border: 1px solid black;text-align: center;"> {{ $LabWorkData['product_name'] }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ wordwrap($LabWorkData['teeth'], 10, "\n",true); }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['strLabWorkStatus'] }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['strLabWorkStatus'] }}</td>
                <td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['unit'] }}</td>
				<td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['material_price'] }}</td>
				<td style="border: 1px solid black;text-align: center;">{{ $LabWorkData['lab_price'] }}</td>
           </tr>
           @endif
            

            <?php $i++; ?>
			@if($i % 20 == 0)
	        	<!--<div class="page-break"></div>-->
	        @endif
        @endforeach
            <tr style="background-color: #e5e5e5e5;">
                <th style="border: 1px solid black;width: 05%;">Total</th>
                <th style="border: 1px solid black;width: 15%;"></th>
                <th style="border: 1px solid black;width: 20%;"></th>
                <th style="border: 1px solid black;width: 15%;"></th>
                <th style="border: 1px solid black;width: 10%;"></th>
                <th style="border: 1px solid black;width: 05%;"></th>
                <th style="border: 1px solid black;width: 05%;"></th>
                <th style="border: 1px solid black;width: 05%;"></th>
                <th style="border: 1px solid black;width: 10%;"></th>
                <th style="border: 1px solid black;width: 10%;"><?= $lab_price; ?></th>
            </tr>
    </tbody>


    <tbody>
        <tr>
            <td colspan="10" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
       </tr>
    </tbody>
</table>