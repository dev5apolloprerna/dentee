<style>
    *{
        margin: 0;
        padding: 0;
    }
    table{
        /*width: 100%;*/
        /* border: 1px solid black; */
        font-family: sans-serif;
        border-collapse: collapse;
    
    }
    
    table th,
    td{
        padding: 5px;
        border: 1px solid black;
    }
    
    th{
        font-weight: bold;
    }
</style>
    
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important;padding: 60px;">
	<thead>
		<img style="width: 100%;position: absolute;top: 0px; left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
	</thead>
	<thead style="text-align:center;width: 100%;">
        <tr>
            <td colspan="6" style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
        </tr>
    </thead>
    <thead style="width: 100%;text-align: center;background: #255a9b;">
        <tr>
			<td colspan="6" rowspan="1" style="background: #255a9b;font-family: sans-serif;color: white;padding: 5px;font-size: 18px;width: 100%;text-transform: uppercase;">
				Prescription
			</td>
         </tr>
    </thead>
    <tbody style="width: 100%; padding-bottom: 10px;">

        <tr>
            <td colspan="6">Patient Name : {{ $name_prefix }} {{$patient_name }}({{$case_no}})</td>
        </tr>
        <tr>
            <td colspan="6">Date:- {{ $prescriptionDate }}</td>
        </tr>
        
        <tr>
            <td colspan="6">Age : {{ $age }} | {{ $genderName }}</td>
        </tr>
    </tbody>
    <tbody style="width: 100%; border: none;">
        <tr style="border: none;">
            <td colspan="6"  style="border: none;"></td>
        </tr>
        <tr style="border: none;">
            <td colspan="6"  style="border: none;"></td>
        </tr>
    </tbody>
	<tbody style="width: 100%;border: 1px solid black;">
	    <tr style="background: #255a9b; font-family: sans-serif;color: white;">
            <th style="width: 10%;">Sr No</th>
            <th style="width: 30%;">Medicine Name</th>
            <th style="width: 10%;">Dosage</th>
            <th style="width: 20%;">Duration</th>
            <th style="width: 15%;">Frequency</th>
            <th style="width: 15%;">No of medicine</th>
        </tr>
        <?php $i = 1; ?>
		@foreach ($medicineDataList as $MedicineDataList)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $MedicineDataList['medicine_name'] }} <br> Note : - {{ $MedicineDataList['notes'] }}</td>
                <td>{{ $MedicineDataList['dosage'] }}</td>
                <td>{{ $MedicineDataList['duration'] }}</td>
                <td>{{ $MedicineDataList['frequency_name'] }}</td>
                <td>{{ $MedicineDataList['numberofMedicine'] }}</td>
            </tr>
            <?php $i++; ?>
        @endforeach
		<!--<tr>
            <td style="border-top: 1px solid black;text-align: center;"></td>
            <td style="border-top: 1px solid black;text-align: center;">Prescription Note : - {{ $note }}</td>
            <td style="border-top: 1px solid black;text-align: center;"></td>
            <td style="border-top: 1px solid black;text-align: center;"></td>
            <td style="border-top: 1px solid black;text-align: center;"></td>
            <td style="border-top: 1px solid black;text-align: center;"></td>
        </tr>-->
    
    </tbody>
    <tbody style="width: 100%;">
        <tr>
            <td colspan="6" style="padding-top: 70px;font-weight: 900; border: none;text-align: right;">Authorized Signature</td>
        </tr>
    </tbody>
	<tbody>
        <tr>
            <td colspan="6" style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;left:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
        </tr>
    </tbody>
	
</table>