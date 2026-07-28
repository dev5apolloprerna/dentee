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
    /*.page-break {*/
    /*    page-break-after: always;*/
    /*}*/
</style>
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important;padding: 60px;">
	<thead>
	    <img style="width: 100%;position: absolute;top: 0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
	</thead>
	<thead style="text-align:center;width: 100%;">
	   <tr>
	   	    <td colspan="6" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
	   </tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="3" style="text-align: left;"><b>Total Amount: {{ $grand_total }}</b></td>
	        <td colspan="3" style="text-align: right;"><b>Duration: {{ trim($Duration) }}</b></td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="6" style="text-align: center;"><b>Branch: {{ $branchName }} </b></td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="3">&nbsp;</td>
		</tr>
	</tbody>
	<tbody style="width: 100%;border: 1px solid black;">
	    <tr style="background-color: #e5e5e5e5;">
    		<th style="width: 05%;">Sr No</th>
    		<th style="width: 15%;">Date</th>
    		<th style="width: 10%;">Patient Name</th>
    		<!-- <th style="width: 10%;">O/N</th> -->
    		<th style="width: 10%;">Work Done</th>
    		<th style="width: 15%;">Amount</th>
    		<th style="width: 10%;">Mode</th>
	    </tr>
		<?php $i = 1; ?>
		@foreach ($treatmentData as $TreatmentData)
		    <tr>
    			<td style="border-top: 1px solid black;text-align: center;">{{ $i }}</td>
    			<td style="border-top: 1px solid black;text-align: center;">{{ $TreatmentData['Date'] }}</td>
    			<td style="border-top: 1px solid black;text-align: center;"> {{ $TreatmentData['patientsName'] }}</td>
    			<!-- <td style="border-top: 1px solid black;text-align: center;"> {{ $TreatmentData['OldOrNow'] }}</td> -->
    			<td style="border-top: 1px solid black;text-align: center;"> {{ $TreatmentData['workdone'] }}</td>
    			<td style="border-top: 1px solid black;text-align: center;">{{ $TreatmentData['amount'] }}</td>
    			<td style="border-top: 1px solid black;text-align: center;">{{ $TreatmentData['payment_mode'] }}</td>
		   </tr>
		<?php $i++; ?>
		@endforeach
	</tbody>
	<!--<div class="page-break"></div>-->
    <tbody>
        <tr>
            <td colspan="6" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
       </tr>
    </tbody>
</table>
