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
        color : white;
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
            <td colspan="10" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="10" style="text-align: center; border: none;"><b>Daily Cash Collection of VGDC</b></td>
    	</tr>
    	<tr>
        	<td colspan="10" style="border: none;">&nbsp;</td>
    	</tr>
    	
    	<tr>
        	<td colspan="10" style="text-align: center; border: none;"><b>Duration:{{ trim($Duration) }}</b></td>
    	</tr>
    	<tr>
        	<td colspan="10" style="border: none;">&nbsp;</td>
    	</tr>
    	
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 15%;">Date</th>
            <th style="text-align: right;">SAMA</th>
            <th style="text-align: right;">HARNI</th>
            <th style="text-align: right;">AJWA</th>
            <th style="text-align: right;">VASNA</th>
            <th style="text-align: right;">RAOPURA</th>
            <th style="text-align: right;">GOTRI</th>
            <!--<th style="text-align: right;">SURAT</th>-->
            <th style="text-align: right;">MANJAL PUR</th>
            <th style="text-align: right;">SUN PHARMA</th>
            <th style="text-align: right;">HARNI SAMA LINK</th>
            <th style="text-align: right;">REMARKS</th>
        </tr>
    	<?php $i = 1; $SamaSavliCashAmountTotal = 0; $HARNICashAmountTotal = 0; $AJWACashAmountTotal = 0; $VASNACashAmountTotal = 0; $RAOPURACashAmountTotal = 0; $GOTRICashAmountTotal = 0;
    	    $SuratCashAmountTotal = 0;
    	    $ManjalpurCashAmountTotal = 0;
    	    $SunPharmaCashAmountTotal = 0;
    	    $HARNILinkCashAmountTotal = 0;
    	?>
		@foreach ($Collection as $Dailycollection)
		<?php $total=0;?>
            <tr>
                <td style="border-top: 1px solid black;text-align: center;">{{ $Dailycollection['payment_date'] }}</td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['SamaSavliCashAmount'] }}
                    <?php $SamaSavliCashAmountTotal+=$Dailycollection['SamaSavliCashAmount']; ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['HARNICashAmount'] }} 
                    <?php $HARNICashAmountTotal += $Dailycollection['HARNICashAmount'];  ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['AJWACashAmount'] }}
                    <?php $AJWACashAmountTotal += $Dailycollection['AJWACashAmount']; ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['VASNACashAmount'] }}
                    <?php $VASNACashAmountTotal += $Dailycollection['VASNACashAmount']; ?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['RAOPURACashAmount'] }}
                    <?php $RAOPURACashAmountTotal += $Dailycollection['RAOPURACashAmount'];?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['GOTRICashAmount'] }}
                    <?php $GOTRICashAmountTotal += $Dailycollection['GOTRICashAmount'];?>
                </td>
                <!--<td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['SuratCashAmount'] ?? 0 }}
                    <?php //$SuratCashAmountTotal += $Dailycollection['SuratCashAmount'] ?? 0;?>
                </td>-->
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['ManjalpurACashAmount'] }}
                    <?php $ManjalpurCashAmountTotal += $Dailycollection['ManjalpurACashAmount'];?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['SunPharmaCashAmount'] }}
                    <?php $SunPharmaCashAmountTotal += $Dailycollection['SunPharmaCashAmount'];?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['HARNILinkCashAmount'] }}
                    <?php $HARNILinkCashAmountTotal += $Dailycollection['HARNILinkCashAmount'];?>
                </td>
                <td style="border-top: 1px solid black;text-align: right;"></td>
           </tr>
           
            <?php $i++; ?>
        @endforeach
         <tr style="background-color: #e5e5e5e5;">
                <th style="">Total</th>
                <th style="text-align: right;">{{ $SamaSavliCashAmountTotal }}</th>
                <th style="text-align: right;">{{ $HARNICashAmountTotal }}</th>
                <th style="text-align: right;">{{ $AJWACashAmountTotal }}</th>
                <th style="text-align: right;">{{ $VASNACashAmountTotal }}</th>
                <th style="text-align: right;">{{ $RAOPURACashAmountTotal }}</th>
                <th style="text-align: right;">{{ $GOTRICashAmountTotal }}</th>
                <!--<th style="text-align: right;">{{ $SuratCashAmountTotal }}</th>-->
                <th style="text-align: right;">{{ $ManjalpurCashAmountTotal }}</th>
                <th style="text-align: right;">{{ $SunPharmaCashAmountTotal }}</th>
                <th style="text-align: right;">{{ $HARNILinkCashAmountTotal }}</th>
                <th style="text-align: center;">-</th>
            </tr>
    </tbody>
    <tbody>
        <tr>
            <td colspan="10" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>