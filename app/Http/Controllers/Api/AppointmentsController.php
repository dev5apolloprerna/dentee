<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointments;
use App\Models\Branch;
use App\Models\BranchCaseNumber;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Patient;
use App\Services\AuthkeyWhatsAppService;

class AppointmentsController extends Controller
{
    public function addAppointment(Request $request){
		
		if(Auth::user()){
			
			$existAppointments = Appointments::where('appointment_date', '=', $request->appointment_date)->where('appointment_time', '=', $request->appointment_time)
			                    ->where(["branch_id" => $request->branch_id,"doctor_id" => $request->doctor_id,"clinic_id" => $request->clinic_id])->first();
			$isSubmit = $request->isSubmit ?? 0;
			// && $isSubmit==0
			
			if(!empty($existAppointments) && $isSubmit==0){
			    $user = User::where('user_id',$request->doctor_id)->first();
			    $patient = Patient::where('patient_id',$existAppointments->patient_id)->first();
				return response()->json([
					'status' => 'fail',
					'message' => 'Appointment already exist.',
					'doctor_name' => $user->user_name,
					'patient_name' =>  ($patient->name_prefix ?? "") ."  ".($patient->name ?? "")
				]);
			}else{
				$appointment = Appointments::create([
					'patient_id' => $request->patient_id,
					'doctor_id' => $request->doctor_id,
					'clinic_id' => $request->clinic_id,
					'branch_id' => $request->branch_id,
					'treatment_id' => $request->treatment_id,
					'suggested_treatment_id' => $request->suggested_treatment_id,
					'status' => $request->status,
					'notes' => $request->notes,
					'appointment_date' => $request->appointment_date,
					'appointment_time' => $request->appointment_time,
					'patient_reminder' =>$request->patient_reminder,	
					'doctor_reminder' =>$request->doctor_reminder,
					'duration' => $request->duration,
					'status' => $request->status,
				]);
				
				$request->branch_id; // 
				$Branch = Branch::where("branch_id",$request->branch_id)->first();
				if($request->doctor_reminder == 1){
// 				    $key = $_ENV['WHATSAPPKEY'];		
//     				$userData = User::where(["user_id" => $request->doctor_id,"role_id"=> 3])->first();
//     				$patient = Patient::where(["patient_id" => $request->patient_id])->first();
//     				// $msg = "Dear ". $userData->user_name .", Your appointment is confirmed of ". $patient->name_prefix ." ". $patient->name ." for ".date('D ,M d, Y',strtotime($request->appointment_date))."  ". $request->appointment_time ." at Vraj Group Of Dental clini.... Thanks-Dentee";
// 				    $msg ="*Patient Appointment Confirmation - Vraj Group of Dental Clinics* 
				      
// Dear ". $userData->user_name  .",
                        
// We are pleased to confirm your upcoming appointment at Vraj Group of Dental Clinics. Here are the details:
                        
// *Patient Name:* ". $patient->name_prefix ." ". $patient->name ."
// *Appointment Time:* ". $request->appointment_time ."
// *Appointment Date:* ". date('D ,M d, Y',strtotime($request->appointment_date)) ."
// *Branch name:* ". $Branch->branch_name ."
// *Branch address:* ". $Branch->address ."
// *Location link:* ". $Branch->strAddressLink ."";
// 					$users = new User();
// 					$currentUser = Auth::user();

// 					$mobileNo = $userData->mobile_no;
// 					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,"");
                    $userData = User::where(["user_id" => $request->doctor_id,"role_id"=> 3])->first();
                    $mobileNo = $userData->mobile_no;
                    $patient = Patient::where(["patient_id" => $request->patient_id])->first();

					$whatsappService = new AuthkeyWhatsAppService();
					//$wid = "28841"; // template id
					$wid = "30593"; 
					$PatientName = ($patient->name_prefix ?? "") ." ". ($patient->name ?? "");
				// 	$bodyValues = [
				// 		"1" => $userData->user_name,
				// 		"2" => $PatientName,
				// 		"3" => $request->appointment_time,
				// 		"4" => date('D ,M d, Y',strtotime($request->appointment_date)),
				// 		"5" => $Branch->branch_name,
				// 		"6" => $Branch->address,
				// 		"7" => $Branch->strAddressLink
				// 	];
				    $bodyValues = [
						"1" => $userData->user_name,
						"2" => $PatientName,
						"3" => $Branch->branch_name,
						"4" => date('D ,M d, Y',strtotime($request->appointment_date)) ." - ". $request->appointment_time
					];
					$statusofMessage = $whatsappService->sendText($mobileNo, $wid, $bodyValues);
					
					//$statusofMessage = $status->status;
					// $Response = $status->response;
				
    				// 	if($statusofMessage == 1){
    					
    				// 		return response()->json([
    				// 			'status' => 'success',
    				// 			'message' => 'DailyCollection Report sent on your registered mobile number.',
    				// 			'dailycollectionFile' => $dailycollectionFile
    				// 		]);
    						
    				// 	}else{
    						
    				// 		return response()->json([
    				// 			'status' => 'error',
    				// 			'message' => $Response.'.Please contact admin.',
    				// 		], 401);
    				// 	}
				}
				if($request->patient_reminder == 1){
				    // $key = $_ENV['WHATSAPPKEY'];		
    				$userData = User::where(["user_id" => $request->doctor_id,"role_id"=> 3])->first();
    				$patient = Patient::where(["patient_id" => $request->patient_id])->first();
    				//$msg = "Dear ". $patient->name_prefix ." ". $patient->name .", Your appointment is confirmed  for ".date('D ,M d, Y',strtotime($request->appointment_date))."  ". $request->appointment_time ." at Vraj Group Of Dental clini.... Thanks-Dentee";
				
// 				    $msg ="*Patient Appointment Confirmation - Vraj Group of Dental Clinics* 
				        
// Dear ". $patient->name_prefix ." ". $patient->name .",
				        
// We are pleased to confirm your upcoming appointment at Vraj Group of Dental Clinics. Here are the details:
                        
// Appointment Time: ". $request->appointment_time ." 
// Appointment Date: ". date('D ,M d, Y',strtotime($request->appointment_date)) ."
// Branch name : ". $Branch->branch_name ." 
// Branch address : ". $Branch->address ." 
// Location link : ". $Branch->strAddressLink ."";
				    
// 					$users = new User();
// 					$currentUser = Auth::user();

// 					$mobileNo = $patient->mobile_no;
// 					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,"");
					
// 					$statusofMessage = $status->status;
                    $mobileNo = $patient->mobile_no;
                    $whatsappService = new AuthkeyWhatsAppService();
					//$wid = "28841"; // template id
					$wid = "30605";
					$PatientName = $patient->name_prefix ." ". $patient->name;
					$bodyValues = [
						"1" => $PatientName,
						"2" => $userData->user_name,
						"3" => $request->appointment_time,
						"4" => date('D ,M d, Y',strtotime($request->appointment_date)),
						"5" => $Branch->branch_name,
						"6" => $Branch->address,
						"7" => $Branch->strAddressLink
					];
					$statusofMessage = $whatsappService->sendText($mobileNo, $wid, $bodyValues);
					
					// $Response = $status->response;
				
    				// 	if($statusofMessage == 1){
    					
    				// 		return response()->json([
    				// 			'status' => 'success',
    				// 			'message' => 'DailyCollection Report sent on your registered mobile number.',
    				// 			'dailycollectionFile' => $dailycollectionFile
    				// 		]);
    						
    				// 	}else{
    						
    				// 		return response()->json([
    				// 			'status' => 'error',
    				// 			'message' => $Response.'.Please contact admin.',
    				// 		], 401);
    				// 	}
				}
				
				return response()->json([
					'status' => 'success',
					'message' => 'Appointment created successfully',
					'appointment' => $appointment
			    ]);
			}
	    }else{
		    return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
		    ], 401);
		}
	}
		
	public function updateAppointment(Request $request, $id){
		
		if(Auth::user()){
			
			$existAppointments = Appointments::where('appointment_date', '=', $request->appointment_date)->where('appointment_time', '=', $request->appointment_time)
			                    ->where(["branch_id" => $request->branch_id,"doctor_id" => $request->doctor_id,"clinic_id" => $request->clinic_id])
		                        ->where('appointment_id', '<>', $id)->first();
// 			$existAppointments = Appointments::where('appointment_date', '=', $request->appointment_date)->where('appointment_time', '=', $request->appointment_time)
// 			                    ->where(["branch_id" => $request->branch_id,"doctor_id" => $request->doctor_id,"clinic_id" => $request->clinic_id])->first();
			if(!empty($existAppointments)){
					
					return response()->json([
						'status' => 'fail',
						'message' => 'Appointment already exist.'
					]);
				
			}else{
		
				$appointments= Appointments::find($id);
				$appointments->update($request->all());
				
				return response()->json([
					'status' => 'success',
					'message' => 'Appointment updated successfully',
					'appointment' => $appointments
				]);
			}
		}else{
			return response()->json([
    					'status' => 'error',
    					'message' => 'User is not Authorised.',
    			], 401);
		}
	}
		
		//update branch
		public function destroyAppointment(Request $request,$id)
		{
				 if(Auth::user()){

				 $data = Appointments::where('appointment_id',$id)->count();
					if($data){
						$appointmentsDelete = Appointments::find($id);
						$appointmentsDelete->delete();
						
						return response()->json([
							'status' => 'success',
							'message' => 'Appointment deleted Successfully.',]);
					}else{
						return response()->json([
							'status' => 'error',
							'message' => 'Somethig is wrong.Please try again',], 401);
					}
				
			   }else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function allAppointment(Request $request){
			
			$doctorId = $request->doctor_id;
			$month = $request->month;
			
			 if(Auth::user()){

				/*  $appointments = new Appointments();
				$allappointmentlist = $appointments->allAppointments();
				return $allappointmentlist;  */
				//\DB::connection()->enableQueryLog();

					 $appointmentsList = Appointments::select(
					 
					'appointments.appointment_id',
					'appointments.patient_id',
					'appointments.doctor_id',
					'appointments.clinic_id',
					'appointments.branch_id',
					'appointments.treatment_id',
					'appointments.suggested_treatment_id',
					'appointments.notes',
					'appointments.appointment_date',
					'appointments.appointment_time',
					'appointments.patient_reminder',
					'appointments.doctor_reminder',
					'appointments.duration',
					'appointments.status',
					'appointments.created_at',
					'users.user_name as doctor_name',
					'users.mobile_no as mobile_no',
					'patients.name as patient_name',
					'patients.name_prefix as patient_name_prefix',
					'patients.case_no',
					'patients.email',
					 DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
					'patients.address',
					'patients.mobile_no',
					'patients.gender',
					'patients.occumpation',
					'patients.language',
					'patients.note',
					'patients.created_at',
					'patients.group_id',
					'groups.group_name as group_name'
					)
					->where(['appointments.branch_id' => $request->branch_id])
					->join('users', 'appointments.doctor_id', '=', 'users.user_id')
					->join('patients', 'appointments.patient_id', '=', 'patients.patient_id')
					->join('groups', 'patients.group_id', '=', 'groups.group_id')
					 ->when($request->doctor_id, function ($query) use ($request) {
                        $query->where('appointments.doctor_id' ,'=',$request->doctor_id);
                    })
					->when($request->fromDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
									})
					->when($request->toDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
									})
					->when($request->selected_date, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
									})
					->when($request->month, function ($query) use ($request) {
										$query->where(DB::raw("MONTH(appointments.appointment_date)"),'=',$request->month);
									})
					->when($request->year, function ($query) use ($request) {
										$query->where(DB::raw("YEAR(appointments.appointment_date)"),'=',$request->year);
									})
					//->orderBy('appointments.appointment_id', 'desc')
					->orderBy('appointments.appointment_date', 'asc')
					->orderBy(DB::raw("STR_TO_DATE(`appointments`.`appointment_time`, '%h:%i %p')"), 'asc')
					->get(); 
				// 	->toSql();
				// 	 echo $request->fromDate;
				// 	 echo "<br />";
				// 	 echo $request->toDate;
				// 	 echo "<br />";
				// 	 echo $request->doctor_id;
				// 	 dd($appointmentsList);
					
					//$queries = \DB::getQueryLog();
					//dd($queries);
					
					return response()->json([
							'status' => 'success',
							'appointmentsList' => $appointmentsList]);
				
				}else{
					
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		
		}
		
		public function getappointmentDetailbyPatient(Request $request,$patient_id){

			 if(Auth::user()){
				
				$appointmentdataArr = [];
				$appointmentsData = Appointments::select(
				
					'appointments.doctor_id',
					'appointments.appointment_date',
					'appointments.appointment_time',
					'users.user_name as doctor_name',
					'appointments.duration',
					'appointments.notes',
					'appointments.status',
					'patients.name as patient_name',
					'appointments.appointment_id',
					'appointments.patient_reminder',
					'appointments.doctor_reminder'
				)
				
				->where(['appointments.patient_id'=>$patient_id])
				->join('patients', 'patients.patient_id', '=', 'appointments.patient_id')
				->join('users', 'users.user_id', '=', 'appointments.doctor_id')
				
				->get();
				
				foreach($appointmentsData as $AppointmentsData){
				
					$status = $AppointmentsData->status;
					if($status == 1){
						$statusName = "Scheduled";
						
					}else if($status == 2){
						$statusName = "Waiting";
						
					}else if($status == 3){
						$statusName = "Engaged";
						
					}else if($status == 4){
						$statusName = "Completed";
						
					}else if($status == 5){
						$statusName = "Missed";
					}
					$appointmentdataArr[] = array(
						'doctor_id' => $AppointmentsData->doctor_id,
						'appointment_date' => $AppointmentsData->appointment_date,
						'appointment_time' => $AppointmentsData->appointment_time,
						'doctor_name' => $AppointmentsData->doctor_name,
						'duration' => $AppointmentsData->duration,
						'note' => $AppointmentsData->notes,
						'patient_name' => $AppointmentsData->patient_name,
						'status' => $statusName,
						'appointment_id' => $AppointmentsData->appointment_id,
						'patient_reminder' => $AppointmentsData->patient_reminder,
						'doctor_reminder' => $AppointmentsData->doctor_reminder
					
					);
				}
				
				
				return response()->json([
									'status' => 'success',
									'appointmentData' => $appointmentdataArr
								]);
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
		 }
		
		public function appointmenttime(Request $request){
			
			$timeDetails = DB::table('appointment_times')->where(['deleted_at' => NULL,'clinic_id'=> $request->clinic_id])->first();

			$start = strtotime($timeDetails->start_time);
			$end = strtotime($timeDetails->end_time);
			$start_duration = $timeDetails->start_duration;
			  
					$range = array();

					while ($start <= $end)
					{
						$range[] = date('h:i A',$start );
						$start = strtotime('+15 minutes',$start);
					}
					
					for ($j = $start_duration; $j <= 155; $j+=15){
						
						$duration[] = $j." "."Mins";
					}
					//echo "<pre>";
					//print_r($duration);
					//die;

					return compact('range','duration');
		}

		public function updateAppointmentStatus(Request $request, $id){
			
			$appointment_status = $request->status;
			if(Auth::user()){
				
				$appointment = Appointments::where('appointment_id','=',$id)->update([

							'status' => $request->status,
					]);
					
				return response()->json(['status' => 'success','message' => 'Patient appointment status Updated Successfully.']);
						
				
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		
		public function countappointmentbystatus(Request $request){
			
			if(Auth::user()){
			$count =  Appointments::select('status', DB::raw('count(*) as total'))
			->where(['branch_id' =>$request->branch_id, 'clinic_id' => $request->clinic_id])
            ->groupBy('status')
            ->get();
		
				$result = array();

					foreach ($count as $counts) {
						if ($counts->status == 1) {
							if($counts->total)
							$result['Scheduled'] = $counts->total;
						}
						if ($counts->status == 2) {
							$result['Waiting'] = $counts->total;
						}
						if ($counts->status == 3) {
							$result['Engaged'] = $counts->total;
						}
						if ($counts->status == 4) {
							$result['Completed'] = $counts->total;
						}
						if ($counts->status == 5) {
							$result['Missed'] = $counts->total;
						}
					}
					
					$allCount =  array_sum($result);
					
					if (!array_key_exists("Scheduled",$result))
						{
							  $result['Scheduled'] = 0;
						}
					if (!array_key_exists("Waiting",$result))
						{
							  $result['Waiting'] = 0;
						}
					if (!array_key_exists("Engaged",$result))
						{
							  $result['Engaged'] = 0;
						}
					if (!array_key_exists("Completed",$result))
						{
							  $result['Completed'] = 0;
						}
					if (!array_key_exists("Missed",$result))
						{
							  $result['Missed'] = 0;
						}
					if (!array_key_exists("All",$result))
						{
							  $result['All'] = $allCount;
						}
						
					return response()->json(['status' => 'success','count' => $result]);
					
					//return $result;
				
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
			}
}
