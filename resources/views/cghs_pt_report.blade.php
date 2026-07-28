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
        border: 1px solid black;
    }

    table tr th{
        background-color: #255a9b;
        color: white;
    }

    table tr{
        
    }
</style>
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important; padding: 60px;">
    <thead>
        <img style="width: 100%;position: absolute;top: 0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
    </thead>
    <thead style="text-align:center;width: 100%;">
        <tr>
            <td colspan="8" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="8" style="text-align: center; border: none;"><b>CGHS PT RECORD</b></td>
    	</tr>
    	<tr>
        	<td colspan="8" style="border: none;">&nbsp;</td>
    	</tr>
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 05%;">Sr No</th>
            <th style="width: 10%;">Month</th>
            <th style="width: 10%;">Branch Name</th>
            <th style="width: 10%;">Group Name</th>
            <th style="width: 15%;">PT Name</th>
            <th style="width: 15%;">Treatment</th>
            <th style="width: 15%;">Due Amount</th>
            <th style="width: 15%;">Paid Amount</th>
            <th style="width: 15%;">Total Amount</th>
        </tr>
        <?php $i = 1; //dd($Collection); ?>
    	@foreach ($Collection as $Dailycollection)
		
            <tr>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['month'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['branchName'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['group_name'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['patientsName'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['treatment_name'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">{{ $Dailycollection['DueAmount'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">{{ $Dailycollection['PaidAmount'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">{{ $Dailycollection['TotalAmount'] }}</td>
           </tr>
            <?php $i++; ?>
        @endforeach
         <tr style="background-color: #e5e5e5e5;">
                <th style="width: 05%;">Total</th>
                <th style="width: 10%;"></th>
                <th style="width: 10%;"></th>
                <th style="width: 15%;"></th>
                <th style="width: 15%;"></th>
                <th style="width: 15%;">{{ $dueAmount ?? 0 }}</th>
                <th style="width: 15%;">{{ $paidAmount ?? 0 }}</th>
                <th style="width: 15%;">{{ $Total ?? 0 }}</th>
            </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="7" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>