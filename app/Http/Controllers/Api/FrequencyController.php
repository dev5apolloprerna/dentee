<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Frequency;
use App\Models\Medicines;
use Illuminate\Support\Facades\DB;

class FrequencyController extends Controller
{
   public function addFrequency(Request $request){
		
				if(Auth::user()){
					
					$existFrequency = Frequency::where(['name' => $request->name,'branch_id' => $request->branch_id])->first();
					
					if(!empty($existFrequency)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Frequency name already exist.'
							]);
						
					}else{
						
						$frequency = Frequency::create([
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
						'name' => $request->name
					]);
					
						
						return response()->json([
						'status' => 'success',
						'message' => 'Frequency added successfully'
					]);
					}
					
				}else{
					return response()->json([
										'status' => 'error',
										'message' => 'User is not Authorised.',
								], 401);
					}
			}
			
			
	//update Medicine
		public function updateFrequency(Request $request,$id)
		{
				if(Auth::user()){

					$existFrequency = Frequency::where(['name' => $request->name,'branch_id' => $request->branch_id])->where('frequency_id', '<>', $id)->first();
				
					if(!empty($existFrequency)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Frequency name already exist.'
							]);
						
					}else{
					
					$frequency= Frequency::find($id);
					$frequency->update($request->all());
					//return $user;
					return response()->json(['status' => 'success','message' => 'Frequency added successfully.','frequency' => $frequency]);
					}
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		//destroy frequncy
		public function destroyFrequency($id)
		{
		   if(Auth::user()){

			 $frequencydata = Frequency::where('frequency_id',$id)->count();
				if($frequencydata){
					
						$existMedicines = Medicines::where('frequency', '=', $id)->first();
					
						if(!empty($existMedicines)){
								
								return response()->json([
									'status' => 'fail',
									'message' => 'This frequncy already in use.'
								]);
							
						}else{
								$frequencyDelete = Frequency::find($id);
								$frequencyDelete->delete();
					
								return response()->json([
									'status' => 'success',
									'message' => 'Frequency deleted Successfully.',]);
						}
				}else{
					return response()->json([
						'status' => 'error',
						'message' => 'Frequency does not exist.',], 401);
				}
			
		   }else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		}
		
		public function allFrequency(Request $request){
			
			if(Auth::user()){
			
			 $allFrequencylist = Frequency::select(
					 
					'frequency_id',
					'clinic_id',
					'branch_id',
					'name',
					'created_at',
					'updated_at'
					)
					->where(['clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id])
					->get();
					
					return response()->json([
								'status' => 'success',
								'FrequencyData' => $allFrequencylist
					]);
							
			}else{
				return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
				], 401);
			}
		}
}
