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
            <td colspan="6" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="6" style="text-align: center; border: none;"><b>FACEBOOK PT RECORD</b></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; border: none;"></td>
            <td colspan="3" style="text-align: right; border: none;">Duration : {{ $Duration }}</td>
    	</tr>
    	<tr>
        	<td colspan="6" style="border: none;">&nbsp;</td>
    	</tr>
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 05%;">Sr No</th>
            <th style="width: 15%;">PT Name</th>
            <th style="width: 20%;">Branch Name</th>
            <th style="width: 20%;">Suggest Treatment</th>
            <th style="width: 20%;">Quotation</th>
            <th style="width: 20%;">Revenue Amount</th>
        </tr>
        <?php $i = 1; //dd($Collection); ?>
    	@foreach ($Collection as $Dailycollection)
		
            <tr>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['PT_Name'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['Branch'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: center;">{{ $Dailycollection['Suggest_Treatment'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Quotation'] }}</td>
                <td style="border-right: 1px solid black;border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Revenue_Amount'] }}</td>
           </tr>
            <?php $i++; ?>
        @endforeach
         <tr style="background-color: #e5e5e5e5;">
                <th style="width: 05%;">Total</th>
                <th style="width: 15%;"></th>
                <th style="width: 20%;"></th>
                <th style="width: 20%;"></th>
                <th style="width: 20%;"></th>
                <th style="width: 20%;">{{ $Total ?? 0 }}</th>
            </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="7" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>