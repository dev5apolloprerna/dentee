<html>
<head>
    <title> {{ $ConcernForm->strConcernFormTitle }}</title>
    
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.css">    
    
    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"/>     -->
    <link type="text/css" href="{{ asset('assets/assets/jquery-ui.css') }}" rel="stylesheet">
    
    <!--<link rel="stylesheet" type="text/css" href="http://keith-wood.name/css/jquery.signature.css">-->
    <link type="text/css" href="{{ asset('assets/assets/jquery.signature.css') }}" rel="stylesheet">
    <style>
        .kbw-signature { width: 100%; height: 200px;}
        #sig canvas{ width: 100% !important; height: auto;}
    </style>  
</head>
<body>
<div class="container">
   <div class="row">
       <div class="col-md-8 offset-md-3 mt-5">
           <div class="card">
               <div class="card-header">
			   @if ($message = Session::get('success'))
                        <div class="alert alert-success  alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">×</button>  
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif
                   <table style="width: 100%;">
   <tr>
    <td style="text-align: center;font-size: 32px;font-weight: 600;">{{ $ConcernForm->strConcernFormTitle }}</td>
   </tr>
   <tr>
    <td style="padding-top: 20px;">I <b> {{ $patient['patient'][0]['name_prefix'] }}  {{ $patient['patient'][0]['name'] }} </b>son/daughter of ............................resident of <b>{{ $patient['patient'][0]['address'] }}</b> being under
        the treatment of ....................................... (state here name of doctor/hospital/nursing home) do hereby give
        consent to the performance of medical/surgical /anesthesia/ diagnostic procedure of
        ................................................ (mention nature of procedure / treatment to be performed, etc.) upon
        myself/upon aged <b>{{ $patient['patient'][0]['age'] }} </b> who is related to me as ............................... (mention here
        relationship,e.g. son,daughter, father, mother, wife, etc.).</td>
   </tr>

   <tr>
    <td style="padding-top: 20px;">
        {{ $ConcernForm->strConcernFormText }}
    </td>
   </tr>

   <tr>
    <td style="padding-top: 20px;">Place :-------- </td>
   </tr>
   <tr>
    <td>Date : {{$patient['patient'][0]['today']}} </td>
   </tr>
   <tr>
    <td>Signature ( To be signed by parent /guardian in case of minor):</td>
   </tr>
   <tr>
    <td>Time :{{$patient['patient'][0]['time']}}</td>
   </tr>
   <tr>
    <td>
	<div class="card-body">
                    
					<form method="POST" action="{{ route('patient.upload') }}">
					
					<input type="hidden" id="patient_id" name="patient_id" value="{{$patient['patient'][0]['patient_id']}}">
					<input type="hidden" id="iConcernFormId" name="iConcernFormId" value="{{ $ConcernForm->iConcernFormId }}">
					<input type="hidden" id="PatientsConcernFormId" name="PatientsConcernFormId" value="{{ $PatientsConcernFormId }}">
                        @csrf
                        <div class="col-md-12">
                            <label class="" for="">Draw Signature:</label>
                            <br/>
                            <div id="sig"></div>
                            <br><br>
                            <button id="clear" class="btn btn-danger">Clear Signature</button>
                            <button class="btn btn-success">Save</button>
                            <textarea id="signature" name="signed" style="display: none"></textarea>
                        </div>
                    </form>
               </div>
	</td>
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
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> 

<!--<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="{{ asset('assets/assets/jquery-ui.min.js') }}"></script>
<!--<script type="text/javascript" src="http://keith-wood.name/js/jquery.signature.js"></script>-->
<script type="text/javascript" src="{{ asset('assets/assets/jquery.signature.js') }}"></script>

<script type="text/javascript">
    // var sig = $('#sig').signature({syncField: '#signature', syncFormat: 'PNG'});
    // $('#clear').click(function(e) {
    //     e.preventDefault();
    //     sig.signature('clear');
    //     $("#signature").val('');
    // });
</script>
<script>
        /*$(document).ready(function() {
            var sig = $('#sig').signature({syncField: '#signature', syncFormat: 'PNG'});
            $('#clear').click(function(e) {
                e.preventDefault();
                sig.signature('clear');
                $("#signature64").val('');
            });

            // Add touch support
            $('#sig').on('touchstart touchmove', function(e){
                e.preventDefault();
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                var simulatedEvent = document.createEvent("MouseEvent");
                simulatedEvent.initMouseEvent({
                    touchstart: "mousedown",
                    touchmove: "mousemove",
                    touchend: "mouseup"
                }[e.type], true, true, window, 1,
                    touch.screenX, touch.screenY,
                    touch.clientX, touch.clientY, false,
                    false, false, false, 0, null);
                touch.target.dispatchEvent(simulatedEvent);
            });
        });*/
        $(document).ready(function() {
    var sig = $('#sig').signature({syncField: '#signature', syncFormat: 'PNG'});
    
    $('#clear').click(function(e) {
        e.preventDefault();
        sig.signature('clear');
        $("#signature64").val('');
    });

    // Add touch support
    var isDrawing = false;
    var touchTarget = $('#sig')[0]; // Get the DOM element for touch events

    touchTarget.addEventListener('touchstart', function(e) {
        e.preventDefault();
        var touch = e.touches[0];
        var simulatedEvent = document.createEvent('MouseEvent');
        simulatedEvent.initMouseEvent('mousedown', true, true, window, 1,
            touch.screenX, touch.screenY,
            touch.clientX, touch.clientY, false,
            false, false, false, 0, null);
        touch.target.dispatchEvent(simulatedEvent);
        isDrawing = true;
    }, false);

    touchTarget.addEventListener('touchmove', function(e) {
        if (!isDrawing) return;
        e.preventDefault();
        var touch = e.touches[0];
        var simulatedEvent = document.createEvent('MouseEvent');
        simulatedEvent.initMouseEvent('mousemove', true, true, window, 1,
            touch.screenX, touch.screenY,
            touch.clientX, touch.clientY, false,
            false, false, false, 0, null);
        touch.target.dispatchEvent(simulatedEvent);
    }, false);

    touchTarget.addEventListener('touchend', function(e) {
        isDrawing = false;
        var simulatedEvent = document.createEvent('MouseEvent');
        simulatedEvent.initMouseEvent('mouseup', true, true, window, 1,
            e.changedTouches[0].screenX, e.changedTouches[0].screenY,
            e.changedTouches[0].clientX, e.changedTouches[0].clientY, false,
            false, false, false, 0, null);
        touchTarget.dispatchEvent(simulatedEvent);
    }, false);
});
    </script>
</body>
</html>