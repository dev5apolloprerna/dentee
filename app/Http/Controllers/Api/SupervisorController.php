<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Branch;
use App\Models\UserBranches;
use App\Models\SuggestedTreatments;
use Illuminate\Support\Str;
use App\Models\UserDashboradRights;
use Carbon\Carbon;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionAnswer;

class SupervisorController extends Controller
{
    
    public function supervisorRegister(Request $request){
		// echo "sdfsdf";
		// 		die;
		if(Auth::user()){
			$user_login = Auth::user();
			$userData = User::where("mobile_no",trim($request->mobile_no))->first();
			if(empty($userData)){
			    $user = User::create([
					'user_name' => $request->user_name,
					'email' => $request->email,
					'password' => Hash::make($request->password),
					'mobile_no' => $request->mobile_no,
					'address' => $request->address,
					'last_modify_by' => $user_login->user_id,
					'clinic_id' => $request->clinic_id,
					'role_id' => $request->role_id,
					'date_of_birth' => $request->date_of_birth
				]);
				
				$RightsArr = array(
					"userid" => $user->user_id,
					"isAccessDashboard" => $request->isAccessDashboard ?? 0,
					"isAccessPatients" => $request->isAccessPatients ?? 0,
					"isAccessAppointment" => $request->isAccessAppointment ?? 0,
					"isAccessSetting" => $request->isAccessSetting ?? 0,
					"isAccessReport" => $request->isAccessReport ?? 0,
					"isAccessLabwork" => $request->isAccessLabwork ?? 0,
					"strIP" => $request->ip()
				);
				UserDashboradRights::create($RightsArr);
				
				$brahnches = $request->branch_id;
				
				$brahnchesstr = str_replace(array('[',']') ,'' , $brahnches);
				$brahnchesArr = explode(',' , $brahnchesstr);

				 foreach ($brahnchesArr as $key => $value) {
					 $userBranch = UserBranches::create([
					'user_id' => $user->user_id,
					'branch_id' => $value,
				]);
				}
				return response()->json([
					'status' => 'success',
					'message' => 'User created successfully'
				]);
			} else {
			    return response()->json([
					'status' => 'error',
					'message' => 'User Name is already exist.',
			    ], 401);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	// this function is for admin registration before login
	public function supervisorRegisterBeforeLogin(Request $request){
		
		$user = User::where('is_admin',1)->first();
		if(!$user)
		{
			 $request->validate([
				'clinic_name' => 'required|string|max:250',
				'branch_name' => 'required|string|max:250',
				'user_name' => 'required|string|max:255|unique:users,user_name,NULL,id,deleted_at,NULL',
				'first_name' => 'required|string|max:255',
				'last_name' => 'required|string|max:255',
				'email' => 'required|string|email|max:255',
				'mobile_no.required' => 'Mobile number is required',
				'password' => [
					'required',
					Password::min(8)
						->mixedCase()
						->numbers()
						->symbols()
				],
				
				'mobile_no' => 'required|numeric|digits:10',
				'address' => 'required|string|max:255',
			],
			[
				'clinic_name.required' => 'Clinic name is required',
				'branch_name.required' => 'Branch name is required',
				'user_name.required' => 'User name is required',
				'user_name.unique' => 'User name already taken please try different name.',
				'first_name.required' => 'First name is required',
				'last_name.required' => 'Last name is required',
				'email.required' => 'Email is required',
				'email.email' => 'Email is not in proper format',
				'password.required' => 'Password number is required',
				'mobile_no.required' => 'Mobile number is required',
				'address.required' => 'address is required',
			]);
			
			$clinic = Clinic::create([
				'clinic_name' => $request->clinic_name,
			]);
			
			
			$branch = Branch::create([
				'branch_name' => $request->branch_name,
				'clinic_id' => $clinic->clinic_id,
				]);
				
				$user = User::create([
				'clinic_id' => $clinic->clinic_id,
				'branch_id' => $branch->branch_id,
				'user_name' => $request->user_name,
				'first_name' => $request->last_name,
				'last_name' => $request->last_name,
				'email' => $request->email,
				'password' => Hash::make($request->password),
				'mobile_no' => $request->mobile_no,
				'address' => $request->address,
				'is_admin' => 1,
			]);

			//$token = Auth::login($user);
			return response()->json([
				'status' => 'success',
				'message' => 'User created successfully',
				'user' => $user,
				/* 'authorisation' => [
					//'token' => $token,
					'type' => 'bearer',
				] */
			]);
		}else{
				return response()->json([
				'status' => 'error',
				'message' => 'Super Admin is already created.',
			], 401);
		
		}
	}
			
			//update branch
	public function updateSupervisor(Request $request,$id)
	{
		
		$requestdataExBranch = $request->except(['branch_id']);
		$brahnches = $request->branch_id;
			
		if(Auth::user()){
            $userData = User::where("mobile_no",trim($request->mobile_no))->where('user_id', '<>', $id)->first();
			if(empty($userData)){
		
				$user= User::find($id);
				$user->update($requestdataExBranch);
				
				//to insert new branch ids we will remove old records
				
				UserDashboradRights::where("userid", $user->user_id)->delete();

				$RightsArr = array(
					"userid" => $id,
					"isAccessDashboard" => $request->isAccessDashboard ?? 0,
					"isAccessPatients" => $request->isAccessPatients ?? 0,
					"isAccessAppointment" => $request->isAccessAppointment ?? 0,
					"isAccessSetting" => $request->isAccessSetting ?? 0,
					"isAccessReport" => $request->isAccessReport ?? 0,
					"isAccessLabwork" => $request->isAccessLabwork ?? 0,
					"strIP" => $request->ip()
				);
				UserDashboradRights::create($RightsArr);
				
				$userBranchObj = UserBranches::where('user_id', $id);
					if(!empty($userBranchObj)){
					$userBranchObj->delete();
				}
				
				$brahnchesstr = str_replace(array('[',']') ,'' , $brahnches);
				$brahnchesArr = explode(',' , $brahnchesstr);
			
				foreach ($brahnchesArr as $key => $value) {
						 $userBranch = UserBranches::create([
						'user_id' => $user->user_id,
						'branch_id' => $value,
					]);
				}
		        return response()->json(['status' => 'success','message' => 'User Updated Successfully.','user' => $user]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User Name is already exist.',
			    ], 401);
				
			}
			
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	
	}
	
	public function allSupervisor(Request $request){
		
		if(Auth::user()){
			$userList = User::where(['role_id' => 4,'istatus' => 1,'clinic_id' => 1])->get();
			$arr = [];
			foreach ($userList as $user) {
				$UserDashboradRight = UserDashboradRights::where("userid", $user->user_id)->first();
				$arr[] = array(
					"user_id" => $user->user_id,
					"clinic_id" => $user->clinic_id,
					"user_name" => $user->user_name,
					"first_name" => $user->first_name,
					"last_name" => $user->last_name,
					"email" => $user->email,
					"address" => $user->address,
					"mobile_no" => $user->mobile_no,
					"date_of_birth" => $user->date_of_birth,
					"last_modify_by" => $user->last_modify_by,
					"isadmin" => $user->isadmin,
					"role_id" => $user->role_id,
					"istatus" => $user->istatus,
					"deleted_at" => $user->deleted_at,
					"created_at" => $user->created_at,
					"updated_at" => $user->updated_at,
					"isAccessDashboard" => $UserDashboradRight->isAccessDashboard ?? 0,
					"isAccessPatients" => $UserDashboradRight->isAccessPatients ?? 0,
					"isAccessAppointment" =>  $UserDashboradRight->isAccessAppointment ?? 0,
					"isAccessSetting" =>  $UserDashboradRight->isAccessSetting ?? 0,
					"isAccessReport" => $UserDashboradRight->isAccessReport ?? 0,
					"isAccessLabwork" => $UserDashboradRight->isAccessLabwork ?? 0,
				);
			}
			//return response()->json(['users' => $userList]);
			return response()->json(['users' => $arr]);
		}else{
			return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
		}
	}
	
	public function destroySupervisor($id){
		if(Auth::user()){
		    $data = User::where('user_id',$id)->count();
			if($data){
				$treatmentDataexist = SuggestedTreatments::where(['treatmentBydoctor_id' => $id])->first();
				if(!empty($treatmentDataexist)){	
					return response()->json([
						'status' => 'fail',
						'message' => 'This User is already in use.'
					]);
				}else{
    				$userDelete = User::find($id);
    				$userDelete->delete();
    	
    				return response()->json([
    					'status' => 'success',
    					'message' => 'User deleted Successfully.'],200
					);
				}
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'Somethig is wrong.Please try again'], 
			    401);
			}
			
	    }else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
				
	/**
	 * Change Password
	 * @param Old Password, New Password, Confirm New Password
	 * @return Boolean With Success Message
	 * @author Shani Singh
	 */
	public function mysupervisorprofile(Request $request)
	{
		if(Auth::user()){
			$user = Auth::user();
			$userId = $user->user_id;
			//$user = User::where('user_id', '=', $session)->where(['istatus' => 1])->first();
			$newpassword = $request->password;
			$user_name = $request->user_name;
				
			$users = User::where(['user_id' => $userId])
				 ->update([
						'password' => Hash::make($newpassword),
					'user_name' => $user_name
			]);
						
			return response()->json([
				'status' => 'success',
				'message' => 'Profile Updated Successfully.',
			], 401);
				
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}	
	}
	
	public function supervisorforgotpassword(Request $request){
		
		$key = $_ENV['WHATSAPPKEY'];
		$mobile = $request->mobile;
		//echo $randomString = Str::random(8);
		
		
		$userData = User::where("mobile_no",trim($request->mobile))->first();
		
		if(!empty($userData)){
			
			$seed = str_split('abcdefghijklmnopqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'0123456789[!@#\$&*~]'); 
		
			shuffle($seed);
			$newPassword = '';
			foreach (array_rand($seed, 8) as $k) {
				$newPassword .= $seed[$k];
			}
		
			$userId = $userData->user_id;
			$users = User::where(['user_id' => $userId])
						 ->update(['password' => Hash::make($newPassword)
			]);
			
			$forgotpasswordMsg = "Dear User, Please find your loginid & Password as mention below. \n\nLogin id : ".$mobile." and password : ".$newPassword;
			$users = new User();
			$status = $users->sendWhatsappMessage($mobile,$key,$forgotpasswordMsg,'');
			/* echo "sdfsd";
			echo "<pre>";
			print_r($status);
			die; */
			$statusofMessage = $status->status;
			// $Response = $status->response;
			
			if($statusofMessage == "success"){
				return response()->json([
					'status' => 'success',
					'message' => 'New Password sent on your mobile successfully.',
				], 401);
			}else{
				
				return response()->json([
					'status' => 'error',
					'message' => $Response.'.Please contact admin.',
				], 401);
			}
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Registered with this mobile number.',], 401);
		}
	}

    public function supervisorchangepassword(Request $request){
		if(Auth::user()){
            $newpassword = $request->new_password;
            $confirmpassword = $request->new_confirm_password;

            if ($newpassword == $confirmpassword) {
			    $seed = str_split('abcdefghijklmnopqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'0123456789[!@#\$&*~]'); 
		
    			shuffle($seed);
    			$newPassword = '';
    			foreach (array_rand($seed, 8) as $k) {
    				$newPassword .= $seed[$k];
    			}
    		
    			//$userId = $userData->user_id;
    			$users = User::where(['user_id' => $request->user_id,"clinic_id" => $request->clinic_id])
    				 ->update([
    					'password' => Hash::make($newpassword)
			        ]);
    			return response()->json(['status' => 'success','message' => 'New Password Updated Successfully.',], 401);
			} else {
			    return response()->json(['status' => 'error','message' => 'password and confirm password does not match.',], 401);
			}
		}else{
			return response()->json(['status' => 'error','message' => 'User is not Authorised.',], 401);
		}
	}
	
    public function supervisorDashboardCount(Request $request){
		if(Auth::user()){
		    $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');

			$newPatientCount = Patient::join('groups', 'patients.group_id', '=', 'groups.group_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				// ->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				// ->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				// ->when($request->fromDate, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				// })
				// ->when($request->toDate, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				// })
				->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd])
				->whereNotIn('patients.patient_id', function ($query) {
                    $query->select('order_master.patient_id')
                          ->from('order_master');
                })
				 ->orderBy('patients.patient_id', 'desc')
				->count();
								
			$onGoingPatientCount = Patient::join('groups', 'patients.group_id', '=', 'groups.group_id')
				->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
				->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where('is_paid',"!=",2)->whereNull('order_master.deleted_at')
				// ->where(function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				// 		->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				// })->orWhere(function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				// 		->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				// })
				->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd])
				->groupBy('patients.patient_id')
				->get();
				
			$importantPatientCount = Patient::join('groups', 'patients.group_id', '=', 'groups.group_id')
			    ->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
				->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where('isImportant',"=",1)
				->where('is_paid',"!=",2)->whereNull('order_master.deleted_at')
				->groupBy('patients.patient_id')
				->get();
				//->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd])
				
				
			$query = QuestionAnswer::where('iSupervisorId', Auth::user()->user_id)
                       ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id])
                       ->whereNull('strReplyDatetime')
                       ->orderBy('created_at', 'asc');
                       
            $QuestionAnswer = $query->count();
				
			return response()->json([
				'status' => 'success',
				'newPatientCount' => $newPatientCount,
				'onGoingPatientCount' => count($onGoingPatientCount),
				'pendingAnswer' => $QuestionAnswer,
				'importantPatientCount' => count($importantPatientCount),
				'message' => 'Dashboard Count',
		    ],200);	
		}else{
		    return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function supervisorNewPatient(Request $request){
		if(Auth::user()){
		    $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');

			/*$newPatient = Patient::select('patients.patient_id', 
			            "patients.clinic_id",
			            "patients.branch_id",
			            "patients.doctor_id",
			            "patients.group_id",
			            "patients.case_no",
			            "patients.email",
			            "patients.date_of_birth",
			            "patients.mobile_no",
			            "patients.gender",
                        // "patients.name_prefix",
                        // "patients.name",
                        DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
                        "patients.created_at",
                        "patients.updated_at",
                        "group_name"
                        )
			    ->join('groups', 'patients.group_id', '=', 'groups.group_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id]);
				if($request->fromDate == "" && $request->toDate == ""){
				    $newPatient->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd]);
				}
				$newPatient->whereNotIn('patients.patient_id', function ($query) {
                    $query->select('order_master.patient_id')
                          ->from('order_master');
                })
                ->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
				->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
			    ->orderBy('patients.patient_id', 'desc');
			$newPatients = $newPatient->get();*/
			
			$newPatient = Patient::select(
                    'patients.patient_id', 
                    'patients.clinic_id',
                    'patients.branch_id',
                    //'patients.doctor_id',
                    'patients.group_id',
                    'patients.case_no',
                    DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
                    'patients.created_at',
                    'patients.updated_at',
                    'groups.group_name',
                    'patients.date_of_birth',
                    'patients.mobile_no',
                    'patients.gender',
                    DB::raw('(select suggested_treatments.SuggestedBydoctor_id from suggested_treatments where suggested_treatments.patient_id=patients.patient_id) as doctor_id'),
                    //DB::raw('sum(order_master.net_amount) as net_amount'),
                    DB::raw('ifnull((select sum(order_master.net_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0),0) as net_amount'),
                    DB::raw('ifnull((select sum(order_master.discount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0),0) as discount'),
                    DB::raw('ifnull((select sum(order_master.paid_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0),0) as paid_amount'),
                    DB::raw('ifnull((select sum(order_master.due_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0),0) as due_amount'),
                    DB::raw('(select count(*) from questionanswer where questionanswer.iPatientId=patients.patient_id) as QuestionCount')
                )
                ->join('groups', 'patients.group_id', '=', 'groups.group_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                // ->where('order_master.is_paid', '!=', 2)
                // ->whereNull('order_master.deleted_at')
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id');
                if($request->fromDate == "" && $request->toDate == ""){
				    $newPatient->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd]);
				}
				$newPatient->whereNotIn('patients.patient_id', function ($query) {
                    $query->select('order_master.patient_id')
                          ->from('order_master');
                })
                ->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
				->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
			    ->orderBy('patients.patient_id', 'desc');
            $newPatients = $newPatient->get();
            
			if(!$newPatients->isEmpty()){
			    
    			return response()->json([
    				'status' => 'success',
    				'newPatientList' => $newPatients,
    				'message' => 'Patient List Found',
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'Patient List Not Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
    	public function supervisorOnGoingPatient(Request $request){
		if(Auth::user()){
		  //  $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            //         $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');

            // 			$OnGoingPatient = Patient::select('patients.patient_id', 
            // 			            "patients.clinic_id",
            // 			            "patients.branch_id",
            // 			            "patients.doctor_id",
            // 			            "patients.group_id",
            // 			            "patients.case_no",
            // 			            // "patients.name_prefix",
            //                         // "patients.name",
            //                         DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
            //                         "patients.created_at",
            //                         "patients.updated_at",
            //                         "group_name",
            //     				    DB::raw('order_master.net_amount as net_amount'),
            //     					DB::raw('order_master.discount as discount'),
            //     					DB::raw('order_master.paid_amount as paid_amount'),
            //     					DB::raw('order_master.due_amount as due_amount')
            //                     )
            // 			    ->join('groups', 'patients.group_id', '=', 'groups.group_id')
            // 				->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
            // 				->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
            // 				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id]);
            // 				if($request->fromDate == "" && $request->toDate == ""){
            // 				    $OnGoingPatient->whereBetween('patients.created_at', [$currentMonthStart, $currentMonthEnd]);
            // 				}
            // 				$OnGoingPatient->when($request->fromDate, function ($query) use ($request) {
            // 					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
            // 				})
            // 				->when($request->toDate, function ($query) use ($request) {
            // 					$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
            // 				})
            // 				->when($request->fromAmount, function ($query) use ($request) {
            // 					$query->where("order_master.net_amount",'>=',".$request->fromAmount.");
            // 				})
            // 				->when($request->toAmount, function ($query) use ($request) {
            // 					$query->where("order_master.net_amount",'<=',".$request->toAmount.");
            // 				})
            // 		        ->where('is_paid',"!=",2)->whereNull('order_master.deleted_at')
            // 			    ->orderBy('net_amount', 'desc');
            // 			$OnGoingPatients = $OnGoingPatient->get();


            // Create a base query for ongoing patients
            $OnGoingPatient = Patient::select(
                    'patients.patient_id', 
                    'patients.clinic_id',
                    'patients.branch_id',
                    'suggested_treatments.treatmentBydoctor_id as doctor_id',
                    //'patients.doctor_id',
                    'patients.group_id',
                    'patients.case_no',
                    DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
                    'patients.created_at',
                    'patients.updated_at',
                    'groups.group_name',
                    //DB::raw('sum(order_master.net_amount) as net_amount'),
                    DB::raw('(select sum(order_master.net_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as net_amount'),
                    DB::raw('(select sum(order_master.discount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as discount'),
                    DB::raw('(select sum(order_master.paid_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as paid_amount'),
                    DB::raw('(select sum(order_master.due_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as due_amount')
                    // DB::raw('sum(order_master.discount) as discount'),
                    // DB::raw('sum(order_master.paid_amount) as paid_amount'),
                    // DB::raw('sum(order_master.due_amount) as due_amount')
                )
                ->join('groups', 'patients.group_id', '=', 'groups.group_id')
                ->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->join('order_master', 'order_master.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id);
            
            // Apply date filters if provided
            // $OnGoingPatient->when(
            //     $request->fromDate,
            //     function ($query) use ($request) {
            //         $query->whereDate('patients.created_at', '>=', $request->fromDate);
            //     }
            // )->when(
            //     $request->toDate,
            //     function ($query) use ($request) {
            //         $query->whereDate('patients.created_at', '<=', $request->toDate);
            //     }
            // );
            // Apply amount filters if provided
            
            /*if($request->id == 1){
                $OnGoingPatient->having(DB::raw('SUM(order_master.net_amount)'), '>=', 0)
                        ->having(DB::raw('SUM(order_master.net_amount)'), '<=', 5000);
            } else if($request->id == 2){
                $OnGoingPatient->having(DB::raw('SUM(order_master.net_amount)'), '>=', 5001)
                        ->having(DB::raw('SUM(order_master.net_amount)'), '<=', 10000);
            } else  if($request->id == 3){
                $OnGoingPatient->having(DB::raw('SUM(order_master.net_amount)'), '>=', 10001)
                        ->having(DB::raw('SUM(order_master.net_amount)'), '<=', 50000);
            } else  if($request->id == 4){
                $OnGoingPatient->having(DB::raw('SUM(order_master.net_amount)'), '>=', 50001)
                        ->having(DB::raw('SUM(order_master.net_amount)'), '<=', 100000);
            } else  if($request->id == 5){
                $OnGoingPatient
                        ->having(DB::raw('SUM(order_master.net_amount)'), '<=', 100001);
                        //->having(DB::raw('SUM(order_master.net_amount)'), '>=', 5001)
            }*/ 
            /*if($request->id == 1){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 0)
                        ->having(DB::raw('net_amount'), '<=', 5000);
            } else if($request->id == 2){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 5001)
                        ->having(DB::raw('net_amount'), '<=', 10000);
            } else  if($request->id == 3){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 10001)
                        ->having(DB::raw('net_amount'), '<=', 50000);
            } else  if($request->id == 4){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 50001)
                        ->having(DB::raw('net_amount'), '<=', 100000);
            } else  if($request->id == 5){
                $OnGoingPatient
                        ->having(DB::raw('net_amount'), '>=', 100001);
            }*/
            if($request->id == 1){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 0)
                        ->having(DB::raw('net_amount'), '<=', 10000);
            } else if($request->id == 2){
                $OnGoingPatient->having(DB::raw('net_amount'), '>=', 10001)
                        ->having(DB::raw('net_amount'), '<=', 50000);
            } else  if($request->id == 3){
                $OnGoingPatient
                        ->having(DB::raw('net_amount'), '>=', 50001);
            }
            // $OnGoingPatient->when(
            //     $request->fromAmount,
            //     function ($query) use ($request) {
            //         //$query->where(DB::raw('sum(order_master.net_amount)'), '>=', $request->fromAmount);
            //         $query->having(DB::raw('SUM(order_master.net_amount)'), '>=', $request->fromAmount);
            //     }
            // )->when(
            //     $request->toAmount,
            //     function ($query) use ($request) {
            //         //$query->where(DB::raw('sum(order_master.net_amount)'), '<=', $request->toAmount);
            //         $query->having(DB::raw('SUM(order_master.net_amount)'), '<=', $request->toAmount);
            //     }
            // );
            
            // Additional filters and ordering
            $OnGoingPatient->where('order_master.is_paid', '!=', 2)
                ->whereNull('order_master.deleted_at')
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->orderBy('net_amount', 'desc');
            
            // Get the results
            $OnGoingPatients = $OnGoingPatient->get();
            
			if(!$OnGoingPatients->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'OnGoingPatientList' => $OnGoingPatients,
    				'Count' => count($OnGoingPatients),
    				'message' => 'Patient List Found',
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'Patient List Not Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function AddSupervisorQuestionAnswer(Request $request){
		if(Auth::user()){
		    $data = array(
	            'iDoctorId' => $request->doctor_id,
        		'iSupervisorId' => $request->iSupervisorId,
        		'iPatientId' => $request->patient_id,
        		'iClinicId' => $request->clinic_id,
        		'iBranchId' => $request->branch_id,
        		'strQuestion' => $request->strQuestion,
        		'strAnswer' => $request->strAnswer,
        		'iEntryBy' => $request->iSupervisorId,
        		'strEntryDate'  => date('Y-m-d H:i:s'),
        		'strIP'  => $request->ip()
	        );
		    $QuestionAnswer = QuestionAnswer::create($data);
			if($QuestionAnswer){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Question added successfully.',
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'Something went wrong.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function SupervisorQuestionAnswerList(Request $request){
		if(Auth::user()){
		    if($request->clinic_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Clinic Required.',
				], 401);    
		    }
		    if($request->branch_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Branch Required.',
				], 401);    
		    }
		  //  $QuestionAnswer = QuestionAnswer::where('iSupervisorId',Auth::user()->user_id)->where(["iClinicId" => $request->clinic_id,"iBranchId" => $request->branch_id])->whereNull('strReplyDatetime')->orderBy('created_at','asc')->get();
		  $query = QuestionAnswer::where('iSupervisorId', Auth::user()->user_id)
                       ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id,"iPatientId" => $request->patient_id])
                       //->whereNull('strReplyDatetime')
                       ->orderBy('created_at', 'asc');
                       
            $QuestionAnswer = $query->get();
            
            //$bindings = $query->getBindings();
            
			if(!$QuestionAnswer->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Question List Found.',
    				'List' => $QuestionAnswer
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function DoctorQuestionAnswerList(Request $request){
		if(Auth::user()){
		    if($request->clinic_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Clinic Required.',
				], 401);    
		    }
		    if($request->branch_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Branch Required.',
				], 401);    
		    }
		  //  $QuestionAnswer = QuestionAnswer::where('iSupervisorId',Auth::user()->user_id)->where(["iClinicId" => $request->clinic_id,"iBranchId" => $request->branch_id])->whereNull('strReplyDatetime')->orderBy('created_at','asc')->get();
		  $query = QuestionAnswer::select('questionanswer.*',DB::raw('(select CONCAT(patients.name_prefix," ", patients.name) from patients where patients.patient_id=questionanswer.iPatientId) as patientName'))
		        ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id])
                ->whereNull('strReplyDatetime')
                ->where('iDoctorId', Auth::user()->user_id)
                ->orderBy('created_at', 'asc');
                       
            $QuestionAnswer = $query->get();
            
            //$bindings = $query->getBindings();
            
			if(!$QuestionAnswer->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Question List Found.',
    				'List' => $QuestionAnswer
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function DoctorQuestionAnswerReplay(Request $request){
		if(Auth::user()){
		    $data = array(
        		'strAnswer' => $request->strAnswer,
        		'strReplyDatetime'  => date('Y-m-d H:i:s')
	        );
		    $QuestionAnswer = QuestionAnswer::where("id",$request->iQuestionId)->update($data);
			if($QuestionAnswer){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Question updated successfully.',
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'Something went wrong.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function SupervisorPendingQuestionAnswerListAllPatient(Request $request){
		if(Auth::user()){
		    if($request->clinic_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Clinic Required.',
				], 401);    
		    }
		    if($request->branch_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Branch Required.',
				], 401);    
		    }
		    //  $QuestionAnswer = QuestionAnswer::where('iSupervisorId',Auth::user()->user_id)->where(["iClinicId" => $request->clinic_id,"iBranchId" => $request->branch_id])->whereNull('strReplyDatetime')->orderBy('created_at','asc')->get();
		    //echo Auth::user()->user_id;
		    $OnGoingPatients = Patient::select(
                    'patients.patient_id', 
                    'patients.clinic_id',
                    'patients.branch_id',
                    'suggested_treatments.treatmentBydoctor_id as doctor_id',
                    //'patients.doctor_id',
                    'patients.group_id',
                    'patients.case_no',
                    DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
                    'patients.created_at',
                    'patients.updated_at',
                    'groups.group_name',
                    'patients.date_of_birth',
                    'patients.mobile_no',
                    'patients.gender',
                    //DB::raw('sum(order_master.net_amount) as net_amount'),
                    DB::raw('(select sum(order_master.net_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as net_amount'),
                    DB::raw('(select sum(order_master.discount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as discount'),
                    DB::raw('(select sum(order_master.paid_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as paid_amount'),
                    DB::raw('(select sum(order_master.due_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as due_amount'),
                    DB::raw('(select count(*) from questionanswer where questionanswer.iPatientId=patients.patient_id) as QuestionCount')
                )
                ->join('groups', 'patients.group_id', '=', 'groups.group_id')
                ->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->join('order_master', 'order_master.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->where('order_master.is_paid', '!=', 2)
                ->whereIn('patients.patient_id', function ($query) use ($request) {
                        $query->select('iPatientId')
                            ->from('questionanswer')
                            ->where('iClinicId', $request->clinic_id)
                            ->where('iBranchId', $request->branch_id)
                            ->where('iSupervisorId', Auth::user()->user_id)
                            ->whereNull('strReplyDatetime');
                    })
                ->whereNull('order_master.deleted_at')
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->orderBy('order_master.net_amount', 'desc')->get();
                // dd($OnGoingPatients);
		    
		  //  $query = QuestionAnswer::select('questionanswer.*',DB::raw('(select CONCAT(patients.name_prefix," ", patients.name) from patients where patients.patient_id=questionanswer.iPatientId) as patientName'))
		  //              ->where('iSupervisorId', Auth::user()->user_id)
    //                     ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id])
    //                     ->whereNull('strReplyDatetime')
    //                     ->orderBy('created_at', 'asc');
    //         $QuestionAnswer = $query->get();
            
            //$bindings = $query->getBindings();
            
			if(!$OnGoingPatients->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'List Found.',
    				'List' => $OnGoingPatients
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function SuperAdminQuestionAnswerListAllPatient(Request $request){
	    if(Auth::user()){
		    if($request->clinic_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Clinic Required.',
				], 401);    
		    }
		    if($request->branch_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Branch Required.',
				], 401);    
		    }
		    //  $QuestionAnswer = QuestionAnswer::where('iSupervisorId',Auth::user()->user_id)->where(["iClinicId" => $request->clinic_id,"iBranchId" => $request->branch_id])->whereNull('strReplyDatetime')->orderBy('created_at','asc')->get();
		    $QuestionAnswer = QuestionAnswer::select('questionanswer.*',DB::raw('(select CONCAT(patients.name_prefix," ", patients.name) from patients where patients.patient_id=questionanswer.iPatientId) as patientName'))
		                ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id])
                        ->whereNull('strReplyDatetime')
                        ->orderBy('created_at', 'asc')
                        ->get();
            $QuestionAnsweredList = QuestionAnswer::select('questionanswer.*',DB::raw('(select CONCAT(patients.name_prefix," ", patients.name) from patients where patients.patient_id=questionanswer.iPatientId) as patientName'))
                        ->where(["iClinicId" => $request->clinic_id, "iBranchId" => $request->branch_id])
                        ->whereNotNull('strReplyDatetime')
                        ->orderBy('created_at', 'desc')
                        ->get();
            //$bindings = $query->getBindings();
            
			if(!$QuestionAnswer->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Question Answer List Found.',
    				'PendingList' => $QuestionAnswer,
    				'AnsweredList' => $QuestionAnsweredList
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function addtoImportantList(Request $request){
	    if(Auth::user()){
		    if($request->patient_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Patient Not Found.',
				], 401);    
		    }
		    $data = array(
		            "isImportant" => 1
		        );
            $Patient = Patient::where("patient_id", $request->patient_id)->update($data);
			if($Patient){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'Move to important Successfully',
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'invalid Request.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
    
    public function importantPatientList(Request $request)
    {
		if(Auth::user()){
		    if($request->clinic_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Clinic Required.',
				], 401);    
		    }
		    if($request->branch_id == ""){
		        return response()->json([
					'status' => 'error',
					'message' => 'Branch Required.',
				], 401);    
		    }
		    //  $QuestionAnswer = QuestionAnswer::where('iSupervisorId',Auth::user()->user_id)->where(["iClinicId" => $request->clinic_id,"iBranchId" => $request->branch_id])->whereNull('strReplyDatetime')->orderBy('created_at','asc')->get();
		    
		    $OnGoingPatients = Patient::select(
                    'patients.patient_id', 
                    'patients.clinic_id',
                    'patients.branch_id',
                    'suggested_treatments.treatmentBydoctor_id as doctor_id',
                    //'patients.doctor_id',
                    'patients.group_id',
                    'patients.case_no',
                    DB::raw("CONCAT(patients.name_prefix, ' ', patients.name) AS patientName"),
                    'patients.created_at',
                    'patients.updated_at',
                    'groups.group_name',
                    'patients.date_of_birth',
                    'patients.mobile_no',
                    'patients.gender',
                    //DB::raw('sum(order_master.net_amount) as net_amount'),
                    DB::raw('(select sum(order_master.net_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as net_amount'),
                    DB::raw('(select sum(order_master.discount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as discount'),
                    DB::raw('(select sum(order_master.paid_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as paid_amount'),
                    DB::raw('(select sum(order_master.due_amount) from order_master where order_master.clinic_id='.$request->clinic_id.' and order_master.branch_id='.$request->branch_id.' and order_master.patient_id=patients.patient_id and order_master.istatus=0) as due_amount'),
                    DB::raw('(select count(*) from questionanswer where questionanswer.iPatientId=patients.patient_id) as QuestionCount')
                )
                ->join('groups', 'patients.group_id', '=', 'groups.group_id')
                ->leftjoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->leftjoin('order_master', 'order_master.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->where('order_master.is_paid', '!=', 2)
                ->where('patients.isImportant', '=', 1)
                ->whereNull('order_master.deleted_at')
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->orderBy('order_master.net_amount', 'desc')->get();
            
			if(!$OnGoingPatients->isEmpty()){
    			return response()->json([
    				'status' => 'success',
    				'message' => 'List Found.',
    				'List' => $OnGoingPatients
    		    ],200);	
			} else{
			    return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);    
			}
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
	}
	
	public function pricelist(Request $request){
	    set_time_limit(300);
	    if(Auth::user()){
    		
    		$data = array(
    		        array(
    		            "key" => "",
    		            "value" => "Select"
		            ),
		          //  array(
    		      //      "key" => "1",
    		      //      "value" => "0 to 5000"
		          //  ),
		          //  array(
    		      //      "key" => "2",
    		      //      "value" => "5001 to 10000"
		          //  ),
		          //  array(
    		      //      "key" => "3",
    		      //      "value" => "10001 to 50000"
		          //  ),
		          //  array(
    		      //      "key" => "4",
    		      //      "value" => "50001 to 100000"
		          //  ),
		          //  array(
    		      //      "key" => "5",
    		      //      "value" => "100001 and more"
		          //  )
		          array(
    		            "key" => "1",
    		            "value" => "0 to 10000"
		            ),
		            array(
    		            "key" => "2",
    		            "value" => "10001 to 50000"
		            ),
		            array(
    		            "key" => "3",
    		            "value" => "50001 and more"
		            )
    		    );
    		
				return response()->json([
					'status' => 'success',
					'message' => 'Price List Found.',
					'pricelist' => $data,
				]);
			
	    } else {
	        return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
    		  
	    }
	}
    	
}
