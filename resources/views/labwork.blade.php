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
	.center {
  margin-left: auto;
  margin-right: auto;
}
</style>
<table style="width: 100%;background-image: url(https://vgdcapp.vrajdentalclinic.com/assets/images/background.png);!important">
<thead>
    <img style="width: 100%;position: absolute;
    top: 0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/New-Header.png" alt="">
</thead>
<thead style="text-align:center;width: 100%;">
   <tr>
    <td style="border: none;"><img style="text-align:center; padding: 60px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images/logo_n.png" alt=""></td>
   </tr>
</thead>
<table width="100%";style="margin-left:auto;margin-right: auto;">
    <tr>
        <td><b>Date:-</b></td>
        <td>{{ $labWorkData['date']}}</td>
    </tr>
    <tr>
        <td><b>Patient Name:-</b></td>
        <td>{{ $labWorkData['patient_name']}}</td>
    </tr>

    <tr>
        <td><b>Branch  Name:-</b></td>
        <td>{{ $labWorkData['branch_name']}}</td>
    </tr>

    <tr>
        <td><b>Dentist Name:-</b></td>
        <td>{{ $labWorkData['dentist_name']}}</td>
    </tr>

    <tr>
        <td><b>Branch Phone No:-</b></td>
    <td>{{ $labWorkData['branch_phone']}}</td>
    </tr>

    <tr>
        <td><b>Teeth:-</b></td>
        <td>{{ $labWorkData['teeth']}}</td>
    </tr>

    <tr>
        <td><b>Product name:-</b></td>
        <td>{{ $labWorkData['product_name']}}</td>
    </tr>

    <tr>
        <td><b>Shade:-</b></td>
		<td>{{ $labWorkData['shade']}}</td>
    </tr>

       
		<tr>
			<td><b>Note:-</b></td>
        <td>
            {{ $labWorkData['notes']}}
        </td>
    </tr>

</table>
<table>
    <tr>
        <td style="border: none;"><img style="text-align:center;width: 100%;position: absolute;bottom:0px;" src="https://vgdcapp.vrajdentalclinic.com/assets/images//new-footer.png" alt=""></td>
       </tr>
</table>