<html>
<body>
<div class="container">
   <div class="row">
       <div class="col-md-8 offset-md-3 mt-5">
           <div class="card">
               <div class="card-header">
                   <table style="width: 100%;">
   <tr>
    <td style="text-align: center;font-size: 32px;font-weight: 600;">{{ $ConcernForm->strConcernFormTitle }}</td>
   </tr>
   <tr>
     <td style="padding-top: 20px;">I <b>{{ $patient['patient'][0]['name_prefix'] }} {{ $patient['patient'][0]['name'] }} </b>son/daughter of ............................aged 1 Y resident of <b>{{ $patient['patient'][0]['address'] }}</b> being under
        the treatment of ....................................... (state here name of doctor/hospital/nursing home) do hereby give
        consent to the performance of medical/surgical /anesthesia/ diagnostic procedure of
        ................................................ (mention nature of procedure / treatment to be performed, etc.) upon
        myself/upon <b>{{ $patient['patient'][0]['age'] }} </b> aged 1 Y who is related to me as ............................... (mention here
        relationship,e.g. son,daughter, father, mother, wife, etc.).</td>
   </tr>

   <tr>
    <td style="padding-top: 20px;">
        {{ $ConcernForm->strConcernFormText }}
    </td>
   </tr>

   <tr>
    <td style="padding-top: 20px;">Place : </td>
   </tr>
   <tr>
    <td>Date : {{$patient['patient'][0]['today']}} </td>
   </tr>
   <tr>
    <td>Time :{{$patient['patient'][0]['time']}}</td>
   </tr>
   <tr>
    <td>Signature ( To be signed by parent /guardian in case of minor):</td>
   </tr>
   <tr>
	<td style=""><img style="text-align:left;width:200px;height:100px;" src="{{ $fileName }}" alt=""></td>
   </tr>
   <tr>
    <td style="padding-top: 20px;">NOTES :-</td>
   </tr>
   <tr>
    <td>1. This Consent Form should be signed before the treatment is started. These formats may be modified as per
        individual requirements</td>
   </tr>
   <tr>
    <td>2. These formats should be in local language and in certain cases it would be prudent to have a proper witness to the
        consent signature.</td>
   </tr>
   <tr>
    <td>3. Informed consent forms for various situations can be made for Nursing Homes / Hospitals.Help of lawyers may
        have to be taken. Detailed forms on medical history can also be maintained. Keep all records safely in order.</td>
   </tr>
   <tr>
    <td>4. It is important to note that written consent should refer to one specific procedure. Obtaining a ‘blanket’ consent on
        admission does not have legal validity.</td>
   </tr>
</table>
               </div>
               
           </div>
       </div>
   </div>
</div>
</body>
</html>