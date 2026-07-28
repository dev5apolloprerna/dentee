<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranches;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ClinicController extends Controller
{
    public function clinicRegister(Request $request){
        
		 $request->validate([
			'clinic_name' => 'required|string|max:255|unique:clinics,clinic_name,NULL,id,deleted_at,NULL',
			'user_name' => 'required|string|max:255|unique:users,user_name,NULL,id,deleted_at,NULL',
            'first_name' => 'required|string|max:255',
			'last_name' => 'required|string|max:255',
            //'email' => 'string|email|max:255',
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
			'user_name.required' => 'User name is required',
			'user_name.unique' => 'User name must be unique',
			'first_name.required' => 'First name is required',
			'last_name.required' => 'Last name is required',
           // 'email.required' => 'Email is required',
			//'email.email' => 'Email is not in proper format',
			'mobile_no.required' => 'Mobile number is required',
			'address.required' => 'address is required',
        ]);

		$clinic = Clinic::create([
			'clinic_name' => $request->clinic_name,
			'email' => $request->email,
			'mobile' => $request->mobile_no,
			'address' => $request->address,
			'address2' => $request->address2,
			'state' => $request->state,
			'city' => $request->city
        ]);
		
		$user = User::create([
			'clinic_id' => $clinic->clinic_id,
            'user_name' => $request->user_name,
			'first_name' => $request->last_name,
			'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
			'mobile_no' => $request->mobile_no,
			'address' => $request->address,
			'istatus' => $request->istatus
        ]);
		
		$branch = Branch::create([
			'branch_name' => $request->clinic_name,
			'clinic_id' => $clinic->clinic_id,
        ]);
		
		$userBranch = UserBranches::create([
							'user_id' => $user->user_id,
							'branch_id' => $request->branch_id,
						]);
		

        return response()->json([
            'status' => 'success',
            'message' => 'Clinic created successfully',
            'user' => $user
        ]);
    }
	
	
	
	//get branch
		public function getclinic(Request $request,$id)
		{
			
				if(Auth::user()){
					
					
					$clinicData = Clinic::where('clinic_id', '=', $id)->get();
					
					return response()->json([
						'status' => 'success',
						'clinicDetails' => $clinicData
					]);

					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
	
	 //update clinic
		public function updateclinic(Request $request,$id)
		{
			
				if(Auth::user()){
					
				$existClinic = Clinic::where('clinic_name', '=', $request->clinic_name)->where('deleted_at', '=', NULL)->where('clinic_id', '<>', $id)->first();
				
					if(!empty($existClinic)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Clinic already exist.'
							]);
						
					}else{
					
					$clinic = Clinic::find($id);
					$clinic->update($request->all());
					
					return response()->json(['status' => 'success','message' => 'Clinic Updated Successfully.','clinic' => $clinic]);
					}
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
}
