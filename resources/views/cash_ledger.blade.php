<style>
    /**{
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
        
    }*/
    *{
        margin: 1;
        padding: 1;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: auto;
    }
    
    table tr th{
        background-color: #255a9b;
        color: white;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    thead {
        display: table-header-group;
    }
    
    tfoot {
        display: table-footer-group;
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
            <td colspan="6" style="text-align: center; border: none;"><b>Cash Ledger of VGDC</b></td>
    	</tr>
    	<tr>
        	<td colspan="6" style="border: none;">&nbsp;</td>
    	</tr>
    	<tr>
        	<td colspan="3" style="text-align: left; border: none;"><b>Duration:{{ trim($Duration) }}</b></td>
        	<td colspan="3" style="text-align: right; border: none;"><b>Branch: {{ trim($branch['branch_name'] ?? "-") }}</b></td>
    	</tr>
    	<!--<tr>-->
     <!--   	<td colspan="6" style="text-align: center; border: none;"><b>Branch: {{ trim($branch['branch_name'] ?? "-") }}</b></td>-->
    	<!--</tr>-->
    	<tr>
        	<td colspan="6" style="border: none;">&nbsp;</td>
    	</tr>
    	
    </tbody>
    <tbody style="width: 100%;border: 1px solid black;">
        <tr style="background-color: #e5e5e5e5;">
            <th style="width: 10%;">Sr No</th>
            <th style="width: 15%;">Date</th>
            <!--<th style="width: 15%;">Branches</th>-->
            <th style="width: 15%;text-align: right;">Case <br />Collection</th>
            <th style="width: 15%;text-align: right;">Cash <br />Expense</th>
            <th style="width: 15%;text-align: right;">Cash <br />Pickup</th>
            <th style="width: 15%;text-align: right;">Cash <br />on Hand</th>
        </tr>
    	<?php $i = 1; $GrandTotal = 0; $CashTotal = 0; $ChequeTotal = 0; $CardTotal = 0; $OnlineTotal = 0; ?>
		@foreach ($Collection as $Dailycollection)
		<?php $total=0; ?>
            <tr>
                <td style="border-top: 1px solid black;text-align: center;">{{ $i }}</td>
                <td style="border-top: 1px solid black;text-align: center;">{{ date('d-m-Y',strtotime($Dailycollection['transaction_date'])) }}</td>
                <!--<td style="border-top: 1px solid black;text-align: center;">{{ $Dailycollection['branch_name'] }}</td>-->
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['cash_collection'] }} </td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['cash_expense'] }}</td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['cash_pickup'] }}</td>
                <td style="border-top: 1px solid black;text-align: right;">{{ $Dailycollection['cash_on_hand'] }}</td>
           </tr>
           
            <?php $i++; ?>
        @endforeach
    </tbody>
    <tbody>
        <tr>
            <td colspan="6" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
</table>