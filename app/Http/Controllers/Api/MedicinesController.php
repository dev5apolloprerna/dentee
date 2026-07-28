<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Medicines;
use Illuminate\Support\Facades\DB;

class MedicinesController extends Controller
{
		public function addMedicine(Request $request){
		
				if(Auth::user()){
					
					$existMedicines = Medicines::where(['name' => $request->name,'branch_id' => $request->branch_id])->first();
					
					if(!empty($existMedicines)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Medicine name already exist.'
							]);
						
					}else{
						
						$medicines = Medicines::create([
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
						'name' => $request->name,
						'molecule' => $request->molecule,
						'dosage' => $request->dosage,
						'frequency' => $request->frequency,
						'duration' => $request->duration,
						'notes' => $request->notes,
					]);
					
						
						return response()->json([
						'status' => 'success',
						'message' => 'Medicine added successfully'
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
		public function updateMedicine(Request $request,$id)
		{
				if(Auth::user()){

					$existMedicines = Medicines::where(['name' => $request->name,'branch_id' => $request->branch_id])->where('medicine_id', '<>', $id)->first();
				
					if(!empty($existMedicines)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Medicine name already exist.'
							]);
						
					}else{
					
					$medicines= Medicines::find($id);
					$medicines->update($request->all());
					//return $user;
					return response()->json(['status' => 'success','message' => 'Medicine added successfully.','medicines' => $medicines]);
					}
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function allMedicine(Request $request){
				//$medicines = new Medicines();
				//$allmedicineslist = $medicines->allMedicines();
				//return $allmedicineslist;
				
				$medicineData = Medicines::select(
				'medicines.medicine_id',
				'medicines.name',
				'medicines.molecule',
				'medicines.dosage',
				'medicines.duration',
				'medicines.notes',
				'frequencies.name as frequencyname',
				'frequencies.frequency_id'
				)
				->where(['medicines.clinic_id' => $request->clinic_id,'medicines.branch_id' => $request->branch_id])
				->join('frequencies', 'medicines.frequency', '=', 'frequencies.frequency_id')
				->get();
				
				if(Auth::user()){
					
					$arr = [];
					
					if(count($medicineData) != 0){
					
						foreach($medicineData as $MedicineData){
								
							$medicine_id = $MedicineData->medicine_id;
							$name = $MedicineData->name;
							$molecule = $MedicineData->molecule;
							$dosage = $MedicineData->dosage;
							$duration = $MedicineData->duration;
							$notes = $MedicineData->notes;
							$frequencyname = $MedicineData->frequencyname;
							$frequency_id = $MedicineData->frequency_id;
							
							$arr[] = array(
								'medicine_id' => $medicine_id,
								'name' => $name,
								'molecule' => $molecule,
								'dosage' => $dosage,
								'duration' => $duration." days",
								'notes' => $notes,
								'frequencyname' => $frequencyname,
								'frequency_id' => $frequency_id
								);
						}
						
						return response()->json([
								'status' => 'success',
								'MedicineData' => $arr
							]);
					}else{
						
						return response()->json([
								'status' => 'success',
								'MedicineData' => $arr
							]);
					}
				
				}
				else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
			}
				
				return $medicineData;
		
		}
		
			//destroy medicine
		public function destroyMedicine($id)
		{
		   if(Auth::user()){

			 $medicinesdata = Medicines::where('medicine_id',$id)->count();
				if($medicinesdata){
					$MedicineDelete = Medicines::find($id);
					$MedicineDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Medicine deleted Successfully.',]);
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
