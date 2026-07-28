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
            <td colspan="7" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="7" style="text-align: center; border: none;"><b>Medicine Collection of VGDC</b></td>
    	</tr>
    	<tr>
        	<td colspan="7" style="border: none;">&nbsp;</td>
    	</tr>
    	
    	<tr>
        	<td colspan="7" style="text-align: center; border: none;"><b>Duration:{{ trim($Duration) }}</b></td>
    	</tr>
    	<tr>
        	<td colspan="7" style="border: none;">&nbsp;</td>
    	</tr>
    	
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 05%;">Sr No</th>
            <th style="width: 15%;">Branches</th>
            <th style="width: 15%;text-align: right;">Cash</th>
            <th style="width: 10%;text-align: right;">Cheque</th>
            <th style="width: 10%;text-align: right;">Card</th>
            <th style="width: 10%;text-align: right;">Online</th>
            <th style="width: 10%;text-align: right;">Total</th>
        </tr>
    	<?php $i = 1; $GrandTotal = 0; $CashTotal = 0; $ChequeTotal = 0; $CardTotal = 0; $OnlineTotal = 0; ?>
		@foreach ($Collection as $Dailycollection)
		<?php $total=0;?>
            <tr>
                <td style="border-top: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border-top: 1px solid black;text-align: center;">{{ $Dailycollection['branch_name'] }}</td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Cash'] }} 
                    <?php $GrandTotal += $Dailycollection['Cash']; $total+= $Dailycollection['Cash']; $CashTotal+=$Dailycollection['Cash'] ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Cheque'] }}
                    <?php $GrandTotal += $Dailycollection['Cheque']; $total+= $Dailycollection['Cheque']; $ChequeTotal+=$Dailycollection['Cheque'] ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Card'] }}
                    <?php $GrandTotal += $Dailycollection['Card']; $total+= $Dailycollection['Card']; $CardTotal+=$Dailycollection['Card'] ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['Online'] }}
                    <?php $GrandTotal += $Dailycollection['Online']; $total+= $Dailycollection['Online']; $OnlineTotal+=$Dailycollection['Online'] ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $total }}</td>
           </tr>
           
            <?php $i++; ?>
        @endforeach
         <tr style="background-color: #e5e5e5e5;">
                <th style="width: 05%;">Total</th>
                <th style="width: 15%;"></th>
                <th style="width: 15%;text-align: right;">{{ $CashTotal }}</th>
                <th style="width: 10%;text-align: right;">{{ $ChequeTotal }}</th>
                <th style="width: 10%;text-align: right;">{{ $CardTotal }}</th>
                <th style="width: 10%;text-align: right;">{{ $OnlineTotal }}</th>
                <th style="width: 10%;text-align: right;">{{ $GrandTotal }}</th>
            </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="7" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>