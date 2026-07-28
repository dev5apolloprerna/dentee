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
        border: 1px solid #000000;
        padding: 5px;
    }
</style>
    
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important;padding: 60px;">
	<thead>
		<img style="width: 100%;position: absolute;top: 0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
	</thead>
    <thead style="text-align:center;width: 100%;">
        <tr>
            <td colspan="2" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <tbody style="width: 80%;margin-left: auto;margin-right: auto;text-align: center;background: #255a9b;">
        <tr>
            <td colspan="2" rowspan="1" style="background: #255a9b;font-family: sans-serif;color: white;padding: 5px;font-size: 18px;width: 100%;">Clinical Notes</td>
        </tr>
    </tbody>
    <tbody style="width: 80%;margin-left: auto;margin-right: auto;">
		<tr>
			<td style="width: 30%;">Patient Name</td>
			<td>{{ $name_prefix }} {{$patient_name }}({{$case_no}})</td>
		</tr>
		<tr>
			<td style="width: 30%;">Age</td>
			<td>{{ $age }}</td>
		</tr>
		<tr>
			<td style="width: 30%;">Sex</td>
			<td>{{ $genderName }}</td>
		</tr>
	</tbody>
	<tbody style="width: 80%;margin-left: auto;margin-right: auto;">
		<tr>
			<td style="width: 20%;text-align: center;font-size: 18px;font-weight: 600;color: #005d97;font-family: sans-serif">Date:-</td>
			<td style="text-align: center;font-size: 18px;font-weight: 600;color: #005d97;font-family: sans-serif;">Notes</td>
		</tr>
		 <?php $i = 1; ?>
			@foreach ($note as $Note)
            <tr>
                <td>{{ $Note['note_date'] }}</td>
                <td>{{ $Note['note'] }}</td>
            </tr>
            <?php $i++; ?>
        @endforeach
	</tbody>
    <tbody style="width: 100%;">
        
        <td colspan="2" style="padding-top: 70px;font-weight: 900; border: none;text-align: right;">
            <img style="width: 25%;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/stamp.png" alt="">
            <br />
            Authorized Signature
            </td>
    </tbody>
	<tbody>
		<tr>
			<td colspan="2" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
       </tr>
	</tbody>
	
</table>