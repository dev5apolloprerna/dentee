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

class UserController extends Controller
{
		    public function userRegister(Request $request){
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
			public function userRegisterBeforeLogin(Request $request){
				
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
		public function updateUser(Request $request,$id)
		{
			
			$requestdataExBranch = $request->except(['branch_id']);
			$brahnches = $request->branch_id;
			
			if(Auth::user()){
				
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

		}
		
		public function allUser(Request $request){
			
			if(Auth::user()){
				$userList = User::where(['role_id' => 2,'istatus' => 1,'clinic_id' => 1])->get();
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
		
		public function destroyUser($id){
			
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
				
				/**
			 * Change Password
			 * @param Old Password, New Password, Confirm New Password
			 * @return Boolean With Success Message
			 * @author Shani Singh
			 */
			public function myprofile(Request $request)
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
			
			public function forgotpassword(Request $request){
				
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
								 ->update([
										'password' => Hash::make($newPassword)
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

    public function changepassword(Request $request){
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
	
}
