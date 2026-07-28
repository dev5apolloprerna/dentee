<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Patient;
use App\Models\Group;
use App\Models\Branch;
use App\Models\BranchCaseNumber;
use App\Models\SuggestedTreatments;
use App\Models\Appointments;
use App\Models\Document;
use App\Models\OrderMaster;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\User;

class PatientController extends Controller
{
     public function patientRegister(Request $request){
				
		if(Auth::user()){
				
			$existPatient = Patient::where('case_no', '=', $request->case_no)->first();
			//$existMobileNumber = Patient::where('mobile_no', '=', $request->mobile_no)->first();
				
			 $orgDate = $request->date_of_birth;  
			 $newBirthDate = date("Y-m-d", strtotime($orgDate));  
				
				if(!empty($existPatient)){
						
					return response()->json([
						'status' => 'fail',
						'message' => 'Patient already exist.'
					]);
					
				}else{
						
					$patient = Patient::create([
						'case_no' => $request->case_no,
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
						'doctor_id' => $request->doctor_id,
						'group_id' => $request->group_id,
						'name_prefix' => $request->name_prefix,
						'name' => $request->name,
						'email' => $request->email,
						'mobile_no' => $request->mobile_no,
						'date_of_birth' => $newBirthDate,
						'address' => $request->address,
						'gender' => $request->gender,
						'occumpation' => $request->occumpation,
						'language' => $request->language,
						'note' => $request->note,
						
						
					]);
					
                    /* if($request->branch_id){
						 Branch::where(['branch_id'=>$request->branch_id])->update([
									'last_case_no' => $request->case_no
								]);
					} */
					
					if($request->branch_id){
						Branch::where(['branch_id'=>$request->branch_id])->update([
							'last_case_no' => $request->case_no_int
						]);
					}
                    
                    $Branchs = Branch::whereNotIn('branch_id', [12,13])->get();
                        //where(['branch_id'=>$request->branch_id])->first();
                    
                    $key = $_ENV['WHATSAPPKEY'];
                    $PatientName = $request->name_prefix . " " . $request->name;
					$msg = "Welcome to Vraj Group of Dental Clinics, ".$PatientName."! We're delighted to have you on board, and we look forward to providing you with exceptional dental care and a smile that radiates confidence.
Here are the Vraj Group of Dental Clinics details:
";
$iCounter = 1;
foreach($Branchs as $Branch){
$msg .= "*(".$iCounter.") Branch name:* ". $Branch->branch_name ."
*Branch address:* ". $Branch->address ."
*Location link:* ". $Branch->strAddressLink ."

";
$iCounter++;
}
    			$users = new User();
    	 		$status = $users->sendWhatsappMessage($request->mobile_no,$key,$msg,$billFile="");
					   // $data1 = [
        //                     'msg' => $msg
        //                 ];
                        
        //                 $curl = curl_init();
                        
        //                 curl_setopt_array($curl, array(
        //                     CURLOPT_URL => "http://api.bulkcampaigns.com/api/wapi?json=true&apikey=".$key."&mobile=".$request->mobile_no,// your preferred url
        //                     CURLOPT_RETURNTRANSFER => true,
        //                     CURLOPT_ENCODING => "",
        //                     CURLOPT_MAXREDIRS => 10,
        //                     CURLOPT_TIMEOUT => 30000,
        //                     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //                     CURLOPT_CUSTOMREQUEST => "POST",
        //                     CURLOPT_POSTFIELDS => json_encode($data1),
        //                     CURLOPT_HTTPHEADER => array(
        //                         // Set here requred headers
        //                         "accept: */*",
        //                         "accept-language: en-US,en;q=0.8",
        //                         "content-type: application/json",
        //                     ),
        //                 ));
                        
        //                 $response = curl_exec($curl);
        //                 $err = curl_error($curl);
                        
        //                 curl_close($curl);
                        
            // if ($err) {
            //     echo "cURL Error #:" . $err;
            // } else {
            //   print_r(json_decode($response));
            // }

			//$token = Auth::login($user);
			return response()->json([
				'status' => 'success',
				'message' => 'Patient created successfully',
				'patient' => $patient
			]);
		}
			
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
				
				
	public function updatePatient(Request $request, $id){
		
		if(Auth::user()){
			$existPatient = Patient::where('case_no', '=', $request->case_no)->where('patient_id', '<>', $id)->first();
			//$existMobile = Patient::where('mobile_no', '=', $request->mobile_no)->where('patient_id', '<>', $id)->first();
		
			if(!empty($existPatient)){
				return response()->json([
					'status' => 'fail',
					'message' => 'Patient already exist.'
				]);
			}else{
				$patient= Patient::find($id);
				$patient->update($request->all());
				//return $user;
				return response()->json(['status' => 'success','message' => 'Patient Updated Successfully.','patient' => $patient,]);
			}
		}else{
    		return response()->json([
    			'status' => 'error',
    			'message' => 'User is not Authorised.',
    		], 401);
		}
	}
				
	public function allPatient(Request $request){
		$flag = $request->flag;
		if($flag == 1){
	
			//patient with reecntly registered.
			$currentDate =date('d-m-Y');
			$fromDate = $request->fromDate;
			$toDate =$request->toDate;
			if(Auth::user()){
						
				$clinic_id = $request->clinic_id;
				$branch_id = $request->branch_id;
				//$patient = new Patient();
				//$allpatientlist = $patient->allPatient();
				//return $allpatientlist;
						
				if(!empty($fromDate) && !empty($toDate))
				{									
					$PatientList = Patient::select(
    					'patients.patient_id',
    					'patients.clinic_id',
    					'patients.branch_id',
    					'patients.doctor_id',
    					'patients.group_id',
    					'patients.case_no',
    					'patients.name_prefix',
    					'patients.name',
    					'patients.email',
    					DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
    					'patients.address',
    					'patients.mobile_no',
    					'patients.gender',
    					'patients.occumpation',
    					'patients.language',
    					'patients.note',
    					'patients.created_at',
    					'groups.group_name as group_name'
					)
					->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
					//->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
					->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
					->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
					->join('groups', 'patients.group_id', '=', 'groups.group_id')
					 ->when($request->name, function ($query) use ($request) {
						$query->where('patients.name','LIKE', '%' . $request->name .'%')
						->orWhere('patients.mobile_no','like','%'. $request->name.'%');
					})
					 ->orderBy('patients.patient_id', 'desc')
					->get();
				/*->toSql();
				echo $request->name;
				dd($PatientList);*/
				// 	echo $request->fromDate;
				// 	echo "<br />";
				// 	echo $request->toDate;
				// 	echo "<br />";
				// 	echo $request->name;
				// 	echo "<br />";
				// 	dd($PatientList);
				}else{
                     $PatientList = Patient::select(
    					'patients.patient_id',
    					'patients.clinic_id',
    					'patients.branch_id',
    					'patients.doctor_id',
    					'patients.group_id',
    					'patients.case_no',
    					'patients.name_prefix',
    					'patients.name',
    					'patients.email',
    					DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
    					'patients.address',
    					'patients.mobile_no',
    					'patients.gender',
    					'patients.occumpation',
    					'patients.language',
    					'patients.note',
    					'patients.created_at',
    					'groups.group_name as group_name'
					)
					->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
					->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
					->join('groups', 'patients.group_id', '=', 'groups.group_id')
					 ->when($request->name, function ($query) use ($request) {
						$query->where('patients.name','LIKE', '%' . $request->name .'%')
						->orWhere('patients.mobile_no','like','%'. $request->name.'%');
					})
					 ->orderBy('patients.patient_id', 'desc')
					->get();
					/*->toSql();
					echo $request->name;
					dd($PatientList);*/
				}
					
			    return response()->json([	
					'status' => 'success',
					'patient' => $PatientList
				]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		
		}else if($flag == 2){
		
			//patient with today appointment
			$currentDate =date('Y-m-d');
			if(Auth::user()){
				
				$clinic_id = $request->clinic_id;
				$branch_id = $request->branch_id;
				//$patient = new Patient();
				//$allpatientlist = $patient->allPatient();
				//return $allpatientlist;
				
				 $PatientList = Patient::select(
    				'patients.patient_id',
    				'patients.clinic_id',
    				'patients.branch_id',
    				'patients.doctor_id',
    				'patients.group_id',
    				'patients.case_no',
    				'patients.name_prefix',
    				'patients.name',
    				'patients.email',
    				DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
    				'patients.address',
    				'patients.mobile_no',
    				'patients.gender',
    				'patients.occumpation',
    				'patients.language',
    				'patients.note',
    				'patients.created_at',
    				'groups.group_name as group_name'
				)
				->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id,'appointments.appointment_date' =>$currentDate])
				//->where(DB::raw("(DATE_FORMAT(appointments.created_at,'%d-%m-%Y'))"),$currentDate)
				->join('groups', 'patients.group_id', '=', 'groups.group_id')
				->join('appointments', 'appointments.patient_id', '=', 'patients.patient_id')
				 ->when($request->name, function ($query) use ($request) {
					$query->where('patients.name','LIKE', '%' . $request->name .'%')
					->orWhere('patients.mobile_no','like','%'. $request->name.'%');
				})
				->orderBy('patients.patient_id', 'desc')
				->get();
			
				return response()->json([	
					'status' => 'success',
					'patient' => $PatientList
				]);
			
			}else{
			    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		}else if($flag == 3){
		    
			//recently visited
			$currentDate =date('Y-m-d');
			
			$fromDate = $request->fromDate;
			$toDate =$request->toDate;
			
			if(Auth::user()){
				if(!empty($fromDate) && !empty($toDate))
				{
						
					$clinic_id = $request->clinic_id;
					$branch_id = $request->branch_id;
					$PatientList = Patient::select(
						 
						'patients.patient_id',
						'patients.clinic_id',
						'patients.branch_id',
						'patients.doctor_id',
						'patients.group_id',
						'patients.case_no',
						'patients.name_prefix',
						'patients.name',
						'patients.email',
						DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
						'patients.address',
						'patients.mobile_no',
						'patients.gender',
						'patients.occumpation',
						'patients.language',
						'patients.note',
						'patients.created_at',
						'groups.group_name as group_name',
						'suggested_treatments.treatment_date' 
					)
					->join('groups', 'patients.group_id', '=', 'groups.group_id')
					->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
					->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
					->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
					//->where(['suggested_treatments.treatment_date' =>$currentDate])
					->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
					->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
					->orWhere(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
					->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
					->groupBy('patients.patient_id')
					->get();
					
				// 	->toSql();
    //     		    echo $branch_id;
    //     		    echo "<br />";
    //     		    echo $request->fromDate;
    //     		    echo "<br />";
    //     		    $request->toDate;
    //     		    echo "<br />";
    //     		    dd($PatientList);
				}else{
					
					$clinic_id = $request->clinic_id;
					$branch_id = $request->branch_id;
						//$patient = new Patient();
						//$allpatientlist = $patient->allPatient();
						//return $allpatientlist;
						
					    /* 	 $PatientList = Patient::select(
						 
						'patients.patient_id',
						'patients.clinic_id',
						'patients.branch_id',
						'patients.doctor_id',
						'patients.group_id',
						'patients.case_no',
						'patients.name_prefix',
						'patients.name',
						'patients.email',
						DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
						'patients.address',
						'patients.mobile_no',
						'patients.gender',
						'patients.occumpation',
						'patients.language',
						'patients.note',
						'patients.created_at',
						'groups.group_name as group_name',
						'suggested_treatments.treatment_date' 
						)
						->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id,'suggested_treatments.treatment_date' =>$currentDate])
						//->orWhere([DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')") => $currentDate])
						
						//->orWhere(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"), "=", $currentDate)
						->join('groups', 'patients.group_id', '=', 'groups.group_id')
						->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
						->join('order_master', 'order_master.patient_id', '=', 'patients.patient_id')
						 ->when($request->name, function ($query) use ($request) {
							$query->where('patients.name','LIKE', '%' . $request->name .'%');
						})
						 ->orderBy('patients.patient_id', 'desc')
						->get(); */
						
					$PatientList = Patient::select(
						 
						'patients.patient_id',
						'patients.clinic_id',
						'patients.branch_id',
						'patients.doctor_id',
						'patients.group_id',
						'patients.case_no',
						'patients.name_prefix',
						'patients.name',
						'patients.email',
						DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
						'patients.address',
						'patients.mobile_no',
						'patients.gender',
						'patients.occumpation',
						'patients.language',
						'patients.note',
						'patients.created_at',
						'groups.group_name as group_name',
						'suggested_treatments.treatment_date' 
					)
					->join('groups', 'patients.group_id', '=', 'groups.group_id')
					->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
					->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
					->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
					->where(['suggested_treatments.treatment_date' =>$currentDate])
					->orWhere(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),$currentDate)
					->groupBy('patients.patient_id')
					->get();
				}
			    return response()->json([	
					'status' => 'success',
					'patient' => $PatientList
				]);
			}else{
			    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
			
		}else{
			//all patient
			if(Auth::user()){
			
    			$clinic_id = $request->clinic_id;
    			$branch_id = $request->branch_id;
    			//$patient = new Patient();
    			//$allpatientlist = $patient->allPatient();
    			//return $allpatientlist;
    			
    			 $PatientList = Patient::select(
        			'patients.patient_id',
        			'patients.clinic_id',
        			'patients.branch_id',
        			'patients.doctor_id',
        			'patients.group_id',
        			'patients.case_no',
        			'patients.name_prefix',
        			'patients.name',
        			'patients.email',
        			DB::raw('DATE_FORMAT(patients.date_of_birth, "%d-%M-%Y") as date_of_birth'),
        			'patients.address',
        			'patients.mobile_no',
        			'patients.gender',
        			'patients.occumpation',
        			'patients.language',
        			'patients.note',
        			'patients.created_at',
        			'groups.group_name as group_name'
    			)
    			->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
    			->join('groups', 'patients.group_id', '=', 'groups.group_id')
    			 ->when($request->name, function ($query) use ($request) {
    				$query->where('patients.name','LIKE', '%' . $request->name .'%')
    				->orWhere('patients.mobile_no','like','%'. $request->name.'%');
    			})
    			->orderBy('patients.patient_id', 'desc')
    			->get();
    		
    		    return $PatientList;
		
    		}else{
        		return response()->json([
    				'status' => 'error',
    				'message' => 'User is not Authorised.',
    			], 401);
    	    }
		}
	}
				
	//destroy patient
	public function destroyPatient($id)
	{
	   if(Auth::user()){
            $data = Patient::where('patient_id',$id)->count();
        	if($data){
        		$PatientDelete = Patient::find($id);
        		$PatientDelete->delete();
        		
        		return response()->json([
        			'status' => 'success',
        			'message' => 'Patient deleted Successfully.',]);
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
					
	//destroy patient
	public function patientdeshboardcount($id)
	{

		if(Auth::user()){

			 $examinationCount = 0;
			 $treatmentCount = SuggestedTreatments::where(['patient_id'=>$id,'istatus'=>1])->count();
			 $prescriptionCount = 0;
			// $DocumentCount = 0;
			 $appointmentCount = Appointments::where('patient_id',$id)->count();
			 $prescriptionCount = Prescription::where(['patient_id' => $id,'istatus' => 1])->count();
			 
			 $DocumentCount = Document::where(['patient_id' => $id])->count();
			 
			/*  $billingAmount = OrderMaster::select(
				DB::raw('sum(due_amount) as due_amount'),
				DB::raw('sum(paid_amount) as paid_amount')
				)
				->where(['is_paid' => 1,'patient_id' => $id])
				->get(); */
				
			$netAmountofallunpaidOrders = OrderMaster::select(
				DB::raw('sum(net_amount) as net_amount')
				)
				->where(['is_paid' => 0,'patient_id' => $id,'istatus' => 0])
				->first();
				
				
			$dueAmountlastAlreadyPaidOrder = OrderMaster::select(
				DB::raw('sum(due_amount) as due_amount'),
				)
				->where(['is_paid' => 1,'patient_id' => $id,'istatus' => 0])
				->get();
				
				if(!empty($netAmountofallunpaidOrders['net_amount'])){
					$net_amount = $netAmountofallunpaidOrders['net_amount'];
				}else{
					$net_amount = 0;
				}
				
				if(!empty($dueAmountlastAlreadyPaidOrder[0]['due_amount'])){
						$due_amount = $dueAmountlastAlreadyPaidOrder[0]['due_amount'];
					}else{
						$due_amount = 0;
					}
				
			 $billingDueAmount = $net_amount+$due_amount;
					
					return response()->json([
						'status' => 'success',
						'examinationCount' => $examinationCount,
						'treatmentCount' => $treatmentCount,
						'prescriptionCount' => $prescriptionCount,
						'DocumentCount' => $DocumentCount,
						'appointmentCount' => $appointmentCount,
						'billingDueAmount' => $billingDueAmount,
						'prescriptionCount' => $prescriptionCount
						
						
					]);
			
		   }else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
			
	}
	
	
	//patient with today appointments 
	public function patientwithappointment(Request $request)
	{
		
		
	}
	
	//patient with today appointments 
	public function patientwithbirthday(Request $request)
	{
		$orgDate = $request->selected_date;  
		$newSelectedate = date("Y-m-d", strtotime($orgDate));  
		if(Auth::user()){
			
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$branch_id = $request->branch_id;
			//$patient = new Patient();
			//$allpatientlist = $patient->allPatient();
			//return $allpatientlist;
			
			 $PatientList = Patient::select(
			  
			'patients.patient_id',
			'patients.clinic_id',
			'patients.branch_id',
			'patients.doctor_id',
			'patients.date_of_birth',
			 'patients.group_id',
			'patients.case_no',
			'patients.name_prefix',
			'patients.name',
			'patients.email',
			
			'patients.address',
			'patients.mobile_no',
			'patients.gender',
			'patients.occumpation',
			'patients.language',
			'patients.note', 
			'patients.created_at',
			'groups.group_name as group_name'
			)
			->where(['patients.clinic_id' =>$clinic_id, 'patients.branch_id' =>$branch_id])
			->join('groups', 'patients.group_id', '=', 'groups.group_id')
		
		->when($request->fromDate, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(patients.date_of_birth,'%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%m-%d')"));
						}) 
		 ->when($request->toDate, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(patients.date_of_birth,'%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%m-%d')"));
						}) 
		->when($request->selected_date, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(patients.date_of_birth,'%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%m-%d')"));
						})
		->when($request->month, function ($query) use ($request) {
							$query->where(DB::raw("MONTH(patients.date_of_birth)"),'=',$request->month);
						})
		->when($request->year, function ($query) use ($request) {
							$query->where(DB::raw("YEAR(patients.date_of_birth)"),'=',$request->year);
						})
		->orderBy('patients.patient_id', 'desc')
		->get(); 
		
			return response()->json([	
				'status' => 'success',
				'patient' => $PatientList
				]);
		
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
		
	}
	//patient with today appointments 
	public function patientdetailbyId(Request $request)
	{
		$patientId = $request->patient_id;
		$clinic_id = $request->clinic_id;
		$branch_id = $request->branch_id;
		
		if(Auth::user()){
	
			$patientDetails = Patient::select(
					'patients.patient_id',
					'patients.clinic_id',
					'patients.branch_id',
					'patients.doctor_id',
					//'users.user_name as doctor_name',
					'patients.date_of_birth',
					 'patients.group_id',
					'patients.case_no',
					'patients.name_prefix',
					'patients.name',
					'patients.email',
					'patients.address',
					'patients.mobile_no',
					'patients.gender',
					'groups.group_name as group_name'
			)
			->where(['patients.patient_id' => $patientId,'patients.clinic_id' => $clinic_id,'patients.branch_id' => $branch_id])
			->join('groups', 'patients.group_id', '=', 'groups.group_id')
			//->join('users', 'patients.doctor_id', '=', 'users.user_id')
			->first();
			
			
			return response()->json([	
				'status' => 'success',
				'message' => 'Patient Details.',
				'patient' => $patientDetails
				
				]);
			
		
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}	
				
				
	public function patientreport(Request $request){
		$clinic_id = $request->clinic_id;
		$branch_id = $request->branch_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		$treatment_id = $request->treatment_id;
		
		if(Auth::user()){
// 			$treatmentData = SuggestedTreatments::
// 			// select(
// 			// 	'suggested_treatments.treatment_date',
// 			// 	'suggested_treatments.total_amount',
// 			// 	//DB::raw('sum(suggested_treatments.total_amount) as total_amount_all'),
// 			// 	'patients.name_prefix',
// 			// 	'patients.name',
// 			// 	'treatments.name as treatment_name',
// 			// 	'users.user_name as doctor_name'
// 			// )
// 			where(['suggested_treatments.branch_id' => $branch_id,'suggested_treatments.clinic_id' => $clinic_id,'suggested_treatments.istatus' => 1])
// 			->join('patients', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
// 			->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
// 			->join('users', 'suggested_treatments.treatmentBydoctor_id', '=', 'users.user_id')
// 			//->groupBy('suggested_treatments.treatment_date')
// 			->when($request->selected_date, function ($query) use ($request) {
// 				$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
// 			})
// 			->when($request->fromDate, function ($query) use ($request) {
// 				$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
// 			})
// 			->when($request->toDate, function ($query) use ($request) {
// 				$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
// 			})
// 			->when($request->treatment_name, function ($query) use ($request) {
// 				$query->where('treatments.name','LIKE', '%' . $request->treatment_name .'%');
// 			})
// 			->when($request->branch_id, function ($query) use ($request) {
// 				$query->where('suggested_treatments.branch_id','=',$request->branch_id);
// 			})
// 			->when($request->month, function ($query) use ($request) {
// 				$query->where(DB::raw("MONTH(suggested_treatments.treatment_date)"),'=',$request->month);
// 			})
// 			->when($request->year, function ($query) use ($request) {
// 				$query->where(DB::raw("YEAR(suggested_treatments.treatment_date)"),'=',$request->year);
// 			})
// 			->orderBy('suggested_treatments.treatment_date', 'desc')
// 			//->get();
// 			->toSql();
// 			dd($treatmentData);
            $where = " and 1=1 ";
            $wherePatients = " 1=1 ";
            $SuggestedTreatmentsWhere = " 1=1";
            $OrderPaymentDetailWhere = " 1=1";
            $checkDate = "";
			if(isset($request->fromDate) && $request->fromDate != ""){
				$where .= " and DATE_FORMAT(tbl1.created_at,'%Y-%m-%d') >= DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')";
				$wherePatients .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') >= DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')";
				$checkDate = "DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') <= DATE_FORMAT('".$request->fromDate."','%d-%m-%Y')";
			} 
// 			else {
// 			    //$where .= " and DATE_FORMAT(tbl1.created_at,'%Y-%m-%d') = date_format(now(),'%d-%m-%Y')";
// 			    $wherePatients .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') = date_format(now(),'%d-%m-%Y')";
// 			    $SuggestedTreatmentsWhere .=" and date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y')";
// 			    $OrderPaymentDetailWhere .=" and date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format(now(),'%d-%m-%Y')";
// 			    $checkDate = "DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') = DATE_FORMAT('".$request->fromDate."','%d-%m-%Y')";
// 			}
			if(isset($request->toDate) && $request->toDate != ""){
				$where .= " and DATE_FORMAT(tbl1.created_at,'%Y-%m-%d') <=  DATE_FORMAT('".$request->toDate."','%Y-%m-%d')";
				$wherePatients .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') <= DATE_FORMAT('".$request->toDate."','%Y-%m-%d')";
			} else {
			    //$where .= " and DATE_FORMAT(tbl1.created_at,'%Y-%m-%d') = date_format(now(),'%d-%m-%Y')";
			 //   $wherePatients .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') = date_format(now(),'%d-%m-%Y')";
			 //   $SuggestedTreatmentsWhere .=" and date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y')";
			 //   $OrderPaymentDetailWhere .=" and date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format(now(),'%d-%m-%Y')";
			}
// 			if(isset($request->treatment_name) && $request->treatment_name != ""){
// 				$where .= ' and treatments.name LIKE %'.$request->treatment_name.'%';
// 			}
// 			if(isset($request->branch_id) && $request->branch_id != ""){
// 				$where .= ' and suggested_treatments.branch_id='.$request->branch_id;
// 			}
            if(isset($request->selected_date) && $request->selected_date != ""){
				$where .= " and DATE_FORMAT(tbl1.created_at,'%Y-%m-%d') = DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')";
				$wherePatients .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') = DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')";
				$checkDate = "DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') = DATE_FORMAT('".$request->selected_date."','%d-%m-%Y')";
				$SuggestedTreatmentsWhere .=" and date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format('".$request->selected_date."','%d-%m-%Y')";
			    $OrderPaymentDetailWhere .=" and date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format('".$request->selected_date."','%d-%m-%Y')";
			} 
			if(isset($request->month) && $request->month != ""){
				$where .= " and MONTH(tbl1.created_at) = ".$request->month;
				$SuggestedTreatmentsWhere .=" and MONTH(suggested_treatments.updated_at)=".$request->month;
			    $OrderPaymentDetailWhere .=" and MONTH(order_payment_detail.created_at)=".$request->month;
			    $wherePatients .= " and MONTH(patients.created_at)='".$request->month."'";
				$checkDate = " MONTH(tbl1.created_at,'%d-%m-%Y')='".$request->month."'";
			}
			if(isset($request->year) && $request->year != "") {
				$where .= " and YEAR(tbl1.created_at)=".$request->year;
				$SuggestedTreatmentsWhere .=" and YEAR(suggested_treatments.updated_at)=".$request->year;
			    $OrderPaymentDetailWhere .=" and YEAR(order_payment_detail.created_at)=".$request->year;
			    $wherePatients .= " and YEAR(patients.created_at)= '".$request->year."'";
				$checkDate = " YEAR(tbl1.created_at)='".$request->year."'";
			}
			
			if(!empty($request->branch_id)){
			    $where .=" and tbl1.branch_id in (".implode(", ", $request->branch_id).")";
			}
			
			/* echo "select DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') as 'Date'
			    ,CONCAT(tbl1.name_prefix,' ',tbl1.name) as patientsName
    			,CASE 
    			  WHEN 
    			    ".$checkDate." THEN 'New' 
    			  ELSE 'Old' 
    			  END as 'OldOrNow'  
    			,(select 
    			  CASE 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=0 THEN 'Pending' 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=1 THEN 'Billing' 
    			  ELSE 'Completed' END  
    			  from suggested_treatments where 
			    ".$SuggestedTreatmentsWhere." and suggested_treatments.patient_id = tbl1.patient_id limit 1) as workdone
    			,(select sum(order_payment_detail.amount) from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
    			".$OrderPaymentDetailWhere." limit 1) as amount
    			,(select CASE 
    			WHEN payment_mode = 1 THEN 'Cash'
    			WHEN payment_mode = 2 THEN 'Cheque'
    			WHEN payment_mode = 3 THEN 'Card'
    			WHEN payment_mode = 4 THEN 'RTGS'
    			WHEN payment_mode = 5 THEN 'NEFT'
    			WHEN payment_mode = 6 THEN 'Paytm'
    			WHEN payment_mode = 7 THEN 'Coupons'
    			WHEN payment_mode = 8 THEN 'Online'
    			WHEN payment_mode = 9 THEN 'WriteOff'
    			ELSE 'GooglePay' END 
			  from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
			  ".$OrderPaymentDetailWhere." limit 1) as 'payment_mode'
			
			
			from  (
			select * from patients
			where patients.patient_id in 
			(select patients.patient_id from patients where ".$wherePatients.")
			UNION 
			select * from patients
			where patients.patient_id not in 
			(select patients.patient_id from patients where ".$wherePatients.")
			and patients.patient_id in (select order_payment_detail.patient_id from order_payment_detail WHERE ".$OrderPaymentDetailWhere.")
			UNION
			select * from patients
			where patients.patient_id in (select suggested_treatments.patient_id from suggested_treatments where 
			date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y'))
				)tbl1 where tbl1.clinic_id='".$request->clinic_id."' and tbl1.branch_id='".$request->branch_id."' ".$where."";exit; */
				
				
			/*$treatmentData = DB::select("select DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') as 'Date'
			    ,CONCAT(tbl1.name_prefix,' ',tbl1.name) as patientsName
    			,CASE 
    			  WHEN DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') = DATE_FORMAT(now(),'%d-%m-%Y') THEN 'New' 
    			  ELSE 'Old' 
    			  END as 'OldOrNow'  
    			,(select 
    			  CASE 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=0 THEN 'Pending' 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=1 THEN 'Billing' 
    			  ELSE 'Completed' END  
    			  from suggested_treatments where 
			    date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y') and suggested_treatments.patient_id = tbl1.patient_id limit 1) as workdone
    			,(select sum(order_payment_detail.amount) from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
    			date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format(now(),'%d-%m-%Y')) as amount
    			,(select CASE 
    			WHEN payment_mode = 1 THEN 'Cash'
    			WHEN payment_mode = 2 THEN 'Cheque'
    			WHEN payment_mode = 3 THEN 'Card'
    			WHEN payment_mode = 4 THEN 'RTGS'
    			WHEN payment_mode = 5 THEN 'NEFT'
    			WHEN payment_mode = 6 THEN 'Paytm'
    			WHEN payment_mode = 7 THEN 'Coupons'
    			WHEN payment_mode = 8 THEN 'Online'
    			WHEN payment_mode = 9 THEN 'WriteOff'
    			ELSE 'GooglePay' END 
			  from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
			  date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format(now(),'%d-%m-%Y')) as 'payment_mode'
			
			
			from  (
			select * from patients
			where patients.patient_id in 
			(select patients.patient_id from patients where ".$wherePatients.")
			UNION 
			select * from patients
			where patients.patient_id not in 
			(select patients.patient_id from patients where ".$wherePatients.")
			and patients.patient_id in (select order_payment_detail.patient_id from order_payment_detail WHERE date_format(order_payment_detail.created_at,'%d-%m-%Y') = date_format(now(),'%d-%m-%Y'))
			UNION
			select * from patients
			where patients.patient_id in (select suggested_treatments.patient_id from suggested_treatments where 
			date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y'))
				)tbl1 where tbl1.clinic_id='".$request->clinic_id."' and tbl1.branch_id='".$request->branch_id."' ".$where.""); */
			
			$treatmentData = DB::select("select DATE_FORMAT(tbl1.created_at,'%d-%m-%Y') as 'Date'
			    ,CONCAT(tbl1.name_prefix,' ',tbl1.name) as patientsName
    			,CASE 
    			  WHEN 
    			    ".$checkDate." THEN 'New' 
    			  ELSE 'Old' 
    			  END as 'OldOrNow'  
    			,(select 
    			  CASE 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=0 THEN 'Pending' 
    			  WHEN suggested_treatments.treatment_status = 0 AND suggested_treatments.is_billing=1 THEN 'Billing' 
    			  ELSE 'Completed' END  
    			  from suggested_treatments where 
			    ".$SuggestedTreatmentsWhere." and suggested_treatments.patient_id = tbl1.patient_id limit 1) as workdone
    			,(select sum(order_payment_detail.amount) from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
    			".$OrderPaymentDetailWhere." limit 1) as amount
    			,(select CASE 
    			WHEN payment_mode = 1 THEN 'Cash'
    			WHEN payment_mode = 2 THEN 'Cheque'
    			WHEN payment_mode = 3 THEN 'Card'
    			WHEN payment_mode = 4 THEN 'RTGS'
    			WHEN payment_mode = 5 THEN 'NEFT'
    			WHEN payment_mode = 6 THEN 'Paytm'
    			WHEN payment_mode = 7 THEN 'Coupons'
    			WHEN payment_mode = 8 THEN 'Online'
    			WHEN payment_mode = 9 THEN 'WriteOff'
    			ELSE 'GooglePay' END 
			  from order_payment_detail where order_payment_detail.patient_id = tbl1.patient_id and 
			  ".$OrderPaymentDetailWhere." limit 1) as 'payment_mode'
			from  (
			select * from patients
			where patients.patient_id in 
			(select patients.patient_id from patients where ".$wherePatients.")
			UNION 
			select * from patients
			where patients.patient_id not in 
			(select patients.patient_id from patients where ".$wherePatients.")
			and patients.patient_id in (select order_payment_detail.patient_id from order_payment_detail WHERE ".$OrderPaymentDetailWhere.")
			UNION
			select * from patients
			where patients.patient_id in (select suggested_treatments.patient_id from suggested_treatments where 
			date_format(suggested_treatments.updated_at,'%d-%m-%Y') =date_format(now(),'%d-%m-%Y'))
				)tbl1 where tbl1.clinic_id='".$request->clinic_id."' ".$where."");
			
            //$branchData = Branch::where('branch_id','=',$request->branch_id)->first();
			//$branchName = $branchData->branch_name;
			
			$branchData = Branch::whereIn('branch_id',$request->branch_id)->get();
			$branchName = "";
			foreach($branchData as $branch){
		        $branchName .= $branch->branch_name . ",";
			}
			$branchName = rtrim($branchName,",");
			
			$Duration = "";
			if(isset($request->fromDate) && $request->toDate != ""){
			    $Duration .= date('d-m-Y',strtotime($request->fromDate)) ." To ". date('d-m-Y',strtotime($request->toDate));
			}
			if(isset($request->selected_date) && $request->selected_date != ""){
			    $Duration .= $request->selected_date;
			}
			if((isset($request->month) && $request->month != "") && isset($request->year) && $request->year != ""){
			    $Duration .= $request->month ."-".$request->year;
			}
			/*if(isset($request->year) && $request->year != "") {
			    $Duration .= $request->year ." ";
			}*/
			$totaltreatmentDataCount = "";
			
			$arr = [];
			$grand_amount = 0;
			$Cash = 0;
			$Cheque = 0;
			$Card = 0;
			$Online = 0;
			$other = 0;
			if(count($treatmentData) != 0){
				$icounter = 0;
				foreach($treatmentData as $TreatmentData){
				// 	$treatment_date = $TreatmentData->treatment_date;
				    $grand_amount += $TreatmentData->amount;
				    
				    $payment_mode = $TreatmentData->payment_mode;
				    if($payment_mode == 'Cash'){
						$Cash += $TreatmentData->amount;
					}else if($payment_mode == 'Cheque'){
						$Cheque += $TreatmentData->amount;
					}else if($payment_mode == 'Card'){
						$Card += $TreatmentData->amount;
					}else if($payment_mode == 'Online'){
						$Online += $TreatmentData->amount;
					} else {
					    $other += $TreatmentData->amount;
					}
					$arr[] = array(
						"Date" => $TreatmentData->Date,
						"patientsName" => $TreatmentData->patientsName,
						"OldOrNow" => $TreatmentData->OldOrNow,
						"workdone" => $TreatmentData->workdone ?? '-',
						"amount" => $TreatmentData->amount ?? 0,
						"payment_mode" => $TreatmentData->payment_mode ?? '-',
						"icounter" => $icounter
					); 
					$icounter++;
				}
					
				if($pdffile == 1){
				// 	$options = [
    //                   'orientation'   => 'portrait',
    //                   'encoding'      => 'UTF-8',
    //                   'header-html'   => 'https://getdemo.in/dentee/assets/images/New-Header.png',
    //                   'footer-html'   => 'https://getdemo.in/dentee/assets/images/new-footer.png'
    //                 ];
					$pdf = PDF::loadView('patient_report',['treatmentData' => $arr,'grand_total' => $grand_amount,"branchName" => $branchName,"Duration" => $Duration
					])->setPaper('a4','portrait');
				// 	$pdf->setOptions($options);
					$fileName = date('d-m-Y')."_patient";
				// 	dd($pdf);
					$content = $pdf->download()->getOriginalContent();
					Storage::put('public/assets/patient_report/'.$fileName . '.pdf',$content);
					
					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
						$pdf->save(public_path('assets/patient_report/')  . $fileName. '.pdf');	
					}else {
						$pdf->save(public_path('../../public_html/dentee/assets/patient_report/')  . $fileName. '.pdf');
					}
					$labFile = asset('assets/patient_report/'. $fileName. '.pdf');
									
					$key = $_ENV['WHATSAPPKEY'];		
					$treatmentListFile = asset('assets/patient_report/'. $fileName. '.pdf');
					$msg = "Dear User, Please find attached details of treatments.";
										
					if($whatsappfile == 1){
						$users = new User();
						$currentUser = Auth::user();

						$mobileNo = $currentUser->mobile_no;
						$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$treatmentListFile);
						
						$statusofMessage = $status->status;
						// $Response = $status->response;
					
						if($statusofMessage == "success"){
							return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $treatmentListFile,
								'treatmentData' => $arr,
								'grand_total' => $grand_amount,
								"Cash" => $Cash,
                				"Cheque" => $Cheque,
                				"Card" => $Card,
                				"Online" => $Online,
                				"other" => $other,
								'message' => 'Treatment Report sent on your registered mobile number.',
							], 401);
						}else{
							
							return response()->json([
								'status' => 'error',
								'message' => $Response.'.Please contact admin.',
							], 401);
						}
					}else{
						return response()->json([
							'status' => 'success',
							'treatmentData' => $arr,
							'grand_total' => $grand_amount,
							"Cash" => $Cash,
            				"Cheque" => $Cheque,
            				"Card" => $Card,
            				"Online" => $Online,
            				"other" => $other,
							'treatmentDataFile' => $treatmentListFile
						]);
					}
				}
				return response()->json([
					'status' => 'success',
					'treatmentData' => $arr,
					'grand_total' => $grand_amount,
					"Cash" => $Cash,
    				"Cheque" => $Cheque,
    				"Card" => $Card,
    				"Online" => $Online,
    				"other" => $other
				]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'treatmentData' => $arr,
					"Cash" => $Cash,
    				"Cheque" => $Cheque,
    				"Card" => $Card,
    				"Online" => $Online,
    				"other" => $other
				]);
			}	
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
}
