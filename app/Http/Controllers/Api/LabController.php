<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Lab;
use App\Models\LabTreatmentCharges;
use Illuminate\Validation\Rule;

class LabController extends Controller
{
        public function addLab(Request $request){
		
			if(Auth::user()){
				$existLab = Lab::where(['lab_name'=>$request->lab_name,'deleted_at' => NULL])->first();
				
				if(!empty($existLab)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Lab already exist.'
						]);
					
				}else{
					
					$lab = Lab::create([
					'clinic_id' => $request->clinic_id,
					'lab_name' => $request->lab_name,
					'contact_person' => $request->contact_person,
					'address' => $request->address,
					'email' => $request->email,
					'mobile_no' => $request->mobile_no,
				]);
				
					
					return response()->json([
					'status' => 'success',
					'message' => 'Lab created successfully',
					'lab' => $lab
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
		public function updateLab(Request $request,$id)
		{
				if(Auth::user()){

					$existLab = Lab::where('lab_name', '=', $request->lab_name)->where('deleted_at', '=', NULL)->where('lab_id', '<>', $id)->first();
				
					if(!empty($existLab)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Lab already exist.'
							]);
						
					}else{
					
					$lab= Lab::find($id);
					$lab->update($request->all());
					//return $user;
					return response()->json(['status' => 'success','message' => 'Lab Updated Successfully.','lab' => $lab]);
					}
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function labTreatmentCharges(Request $request){
			
			if(Auth::user()){
				$lab_id = $request->lab_id;
				$lab_idobj = LabTreatmentCharges::where('lab_id', $lab_id);
				if(!empty($lab_idobj)){
					$lab_idobj->delete();
				}
				$labcharges = $request->labcharges;
				
				foreach ($labcharges as $key => $value) {
							
							$lab = LabTreatmentCharges::create([
							'lab_id' => $lab_id,
							'treatment_id' => $key,
							'amount' => $value,
							]);
				}
						
				return response()->json([
					'status' => 'success',
					'message' => 'Lab Treatment charge is created successfully'
				]);
				
			}else{
					
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
		
		public function allLab(Request $request){

			$clinic_id = $request->clinic_id;
			if(Auth::user()){
				$alllablist = Lab::where(['clinic_id' =>$clinic_id])->get()->toArray();
				
				return $alllablist;
			}else{
				return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
			}
		
		}
		
			//destroy lab
		public function destroyLab($id)
		{
		   if(Auth::user()){

			 $data = Lab::where('lab_id',$id)->count();
				if($data){
					$LabDelete = Lab::find($id);
					$LabDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Lab deleted Successfully.',]);
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
}
