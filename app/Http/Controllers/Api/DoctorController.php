<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Branch;
use App\Models\UserBranches;
use App\Models\SuggestedTreatments;
use Illuminate\Validation\Rule;
use App\Models\UserDashboradRights;

class DoctorController extends Controller
{
    public function addDoctor(Request $request){

		if(Auth::user()){
			$user_login = Auth::user();
			$userData = User::where(["mobile_no" => trim($request->mobile_no),"role_id"=> 3])->first();
			if(empty($userData)){
				
				//\DB::connection()->enableQueryLog();
			    $user = User::create([
					'user_name' => $request->user_name,
					'email' => $request->email,
					'password' => Hash::make($request->password),
					'mobile_no' => $request->mobile_no,
					'address' => $request->address,
					'last_modify_by' => $user_login->user_id,
					'clinic_id' => $request->clinic_id,
					'role_id' => 3,
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
				//$queries = \DB::getQueryLog();
			//dd($queries);
				return response()->json([
					'status' => 'success',
					'message' => 'Doctor created successfully'
				]);
			} else {
			    return response()->json([
					'status' => 'error',
					'message' => 'Mobile number is already exist.',
			    ], 401);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'Doctor is not Authorised.',
			], 401);
		}
	}
	
	//update doctor
	public function updateDoctor(Request $request,$id)
	{
		$requestdataExBranch = $request->except(['branch_id']);
		$brahnches = $request->branch_id;
			if(Auth::user()){

				/* $request->validate([
					'user_name' => ['required', Rule::unique('users', 'user_name')->ignore($id,'user_id')],
					'first_name' => 'required|string|max:255',
						'last_name' => 'required|string|max:255',
						//'email' => 'required|string|email|max:255',
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
					'user_name.required' => 'User name is required',
					'user_name.unique' => 'User name already taken please try different name.',
					'first_name.required' => 'First name is required.',
					'last_name.required' => 'Last name is required.',
					'password.required' => 'Password number is required.',
					'mobile_no.required' => 'Mobile number is required.',
					'address.required' => 'address is required.',
				]); */
				
				$userData = User::where(["mobile_no" => trim($request->mobile_no),"role_id"=> 3])->where('user_id', '<>', $id)->first();
				//echo "<pre>";
				//print_r($userData);
				//die;
					if(empty($userData)){
				
							$user= User::find($id);
							$user->update($requestdataExBranch);
							
							//to insert new branch ids we will remove old records
							
							$userBranchObj = UserBranches::where('user_id', $id);
								if(!empty($userBranchObj)){
								$userBranchObj->delete();
							}
							
							UserDashboradRights::where("userid",$user->user_id)->delete();
							
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
							
							$brahnchesstr = str_replace(array('[',']') ,'' , $brahnches);
							$brahnchesArr = explode(',' , $brahnchesstr);
						
							foreach ($brahnchesArr as $key => $value) {
									 $userBranch = UserBranches::create([
									'user_id' => $user->user_id,
									'branch_id' => $value,
								]);
								}
				
						return response()->json(['status' => 'success','message' => 'Doctor Updated Successfully.','user' => $user]);
					}else{
						return response()->json([
							'status' => 'error',
							'message' => 'DOctor is already exist.',
					    ], 401);
						
					}
				
			}else{
				return response()->json([
						'status' => 'error',
						'message' => 'Doctor is not Authorised.',
					], 401);
			}
	}
		
	public function allDoctor(Request $request){
		$clinic_id = $request->clinic_id;
		if(Auth::user()){
			$userList = User::where(['role_id' => 3,'istatus' => 1,'clinic_id' =>$clinic_id])->get();
			$Arr = [];
			foreach ($userList as $user) {
				$UserDashboradRight = UserDashboradRights::where("userid", $user->user_id)->first();
				$Arr[] = array(
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
			return response()->json(['users' => $Arr]);
		}else{
			return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
		}
	}
	
	public function destroyDoctor($id){
		
		if(Auth::user()){

			 $data = User::where('user_id',$id)->count();
				if($data){	
				
				$treatmentDataexist = SuggestedTreatments::where(['treatmentBydoctor_id' => $id])->first();
					if(!empty($treatmentDataexist)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'This Doctor is already in use.'
							]);
						
					}else{
							$userDelete = User::find($id);
							$userDelete->delete();
				
							return response()->json([
								'status' => 'success',
								'message' => 'User deleted Successfully.',]);
					}
					
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
		
	public function doctorbybranch(Request $request,$id){
		
		if(Auth::user()){
			
			$doctorList = User::select(
			'users.user_id',
			'users.clinic_id',
			'users.user_name',
			'users.email'
			)
			->where(['users.istatus' => 1,'users.clinic_id' => 1,'users.role_id' => 3,'user_branches.branch_id' => $id, 'user_branches.deleted_at' => NULL])
			->join('user_branches', 'users.user_id', '=', 'user_branches.user_id')
			->get();
			
			return response()->json([
			'status' => 'success',
			'doctorList' => $doctorList
			]);
			
		}else{
			return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
		}
	}
		
	public function activeinactivedoctor(Request $request){
	    if(Auth::user()){
			 $data = User::where('user_id',$request->user_id)->count();
			if($data){	
				//$userlist = User::find($request->user_id);
				$arr = array(
			        "istatus" => $request->istatus
			    );
				User::where("user_id","=",$request->user_id)->update($arr);
				if($request->istatus == 0){
				    return response()->json([
    					'status' => 'success',
    					'message' => 'User Inactive Successfully.',]
    				);
				} else {
				    return response()->json([
    					'status' => 'success',
    					'message' => 'User Active Successfully.',]
    				);
				}
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'Somethig is wrong.Please try again',], 401
				);
			}
	    }else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
    }
    
    public function inactivedoctorlist(Request $request){
        $clinic_id = $request->clinic_id;
       // dd(Auth::user());
		if(Auth::user()){
			$userList = User::where(['role_id' => 3,'istatus' => 0,'clinic_id' =>$clinic_id])->get();
			
			return response()->json(['users' => $userList]);
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
    }
    
}
