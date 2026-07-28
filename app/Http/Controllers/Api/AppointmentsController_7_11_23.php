<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointments;
use App\Models\Branch;
use App\Models\BranchCaseNumber;
use Illuminate\Support\Facades\DB;

class AppointmentsController extends Controller
{
    public function addAppointment(Request $request){
		
			if(Auth::user()){
				
				$existAppointments = Appointments::where('appointment_date', '=', $request->appointment_date)->where('appointment_time', '=', $request->appointment_time)->first();
				
					if(!empty($existAppointments)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Appointment already exist.'
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
				
				$existAppointments = Appointments::where('appointment_date', '=', $request->appointment_date)->where('appointment_time', '=', $request->appointment_time)->where('appointment_id', '<>', $id)->first();
				
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
					'patients.name as patient_name'
					)
					->where(['appointments.branch_id' => $request->branch_id])
					->join('users', 'appointments.doctor_id', '=', 'users.user_id')
					->join('patients', 'appointments.patient_id', '=', 'patients.patient_id')
					
					 ->when($request->doctor_id, function ($query) use ($request) {
                        $query->where('doctor_id' ,'=',$request->doctor_id);
                    })
					->when($request->fromDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%d-%m-%Y')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%d-%m-%Y')"));
									})
					->when($request->toDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%d-%m-%Y')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%d-%m-%Y')"));
									})
					->when($request->selected_date, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%d-%m-%Y')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%d-%m-%Y')"));
									})
					->when($request->month, function ($query) use ($request) {
										$query->where(DB::raw("MONTH(appointments.appointment_date)"),'=',$request->month);
									})
					->when($request->year, function ($query) use ($request) {
										$query->where(DB::raw("YEAR(appointments.appointment_date)"),'=',$request->year);
									})
					->orderBy('appointments.appointment_id', 'desc')
					->get(); 
					 
					
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
