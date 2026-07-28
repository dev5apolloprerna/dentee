<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Treatments;
use Illuminate\Validation\Rule;
use App\Models\SuggestedTreatments;
use App\Models\OrderMaster;
use App\Models\OrderDetail;
use App\Models\patientTreatments;
use App\Models\BranchCaseNumber;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Branch;

class TreatmentsController extends Controller
{
		public function addTreatment(Request $request){
		
			if(Auth::user()){
				/* $request->validate([
					'name' => 'required|string|max:255|unique:treatments,name,NULL,id,deleted_at,NULL',
					'amount' => 'required',
				],
				[
					'name.required' => 'Lab name is required',
					'amount.required' => 'Amount is required',
				]); */

					$treatmentData = Treatments::where(['name' => trim($request->name),'branch_id' => $request->branch_id])->first();
						if(empty($treatmentData)){
								$treatments = Treatments::create([
									'clinic_id' => $request->clinic_id,
									'branch_id' => $request->branch_id,
									'name' => $request->name,
									'amount' => $request->amount,
									'labwork_required' => $request->labwork_required,
								]);
							
								return response()->json([
									'status' => 'success',
									'message' => 'Treatment created successfully',
									'treatments' => $treatments
								]);
						}else {
						    return response()->json([
								'status' => 'error',
								'message' => 'Treatment Name is already exist.',
						    ], 401);
						}
					
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		//update treatment
		public function updateTreatment(Request $request,$id)
		{
				if(Auth::user()){

					/* $request->validate([
						'name' => ['required', Rule::unique('treatments', 'name')->ignore($id,'treatment_id')]
					],
					[
						'name.required' => 'Treatment name is required',
					]); */
					
					$treatmentData = Treatments::where(['name' => trim($request->name),'branch_id' => $request->branch_id])->where('treatment_id', '<>', $id)->first();
					
						if(empty($treatmentData)){
									$treatment= Treatments::find($id);
									$treatment->update($request->all());
							//return $user;
							return response()->json(['status' => 'success','message' => 'Treatment Updated Successfully.','treatment' => $treatment]);
						}else{
							return response()->json([
								'status' => 'error',
								'message' => 'Treatment Name is already exist.',
						    ], 401);
						}
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		//destroy vendor
				public function destroyTreatment($id)
				{
				   if(Auth::user()){

					 $data = Treatments::where('treatment_id',$id)->count();
						if($data){
							$TreatmentsDelete = Treatments::find($id);
							$TreatmentsDelete->delete();
							
							return response()->json([
								'status' => 'success',
								'message' => 'Treatment deleted Successfully.',]);
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
		
		//add treatment screen with multiple treatment select and edit
		public function suggestedTreatment(Request $request)
		{
			if(Auth::user()){
				if(!empty($request->istatus) && isset($request->istatus)){
					$istatus = $request->istatus;
				}else{
					$istatus = 0;
				}
				$patient_treatment_id = $request->patient_treatment_id;
				$suggested_treatment_id = $request->suggested_treatment_id;
				$edit = $request->edit;
				$discount_type = $request->discount_type;
				$treatmentBydoctor_id = $request->treatmentBydoctor_id;

				if(empty($discount_type)){
					$discount_type = 0;
				}
				
				$discountperornum = $request->discount;
				
				$treatment_ids = $request->treatment_id;
				$treatment_ids_array = explode(",", $treatment_ids);

				//already added treatment by edit 
				if(!empty($patient_treatment_id) && empty($edit)){
					
					//one treatment added using edit and another one saved directry without edit
					foreach ($treatment_ids_array as $treatment_id) {
									
						$treatmentDataexist = SuggestedTreatments::where(['treatment_id' => $treatment_id,
						'patient_treatment_id' => $patient_treatment_id])->first();
						//print_r($treatmentDataexist->suggested_treatment_id);
						
						if($treatmentDataexist){
							$suggestedalreadyexist = $treatmentDataexist->suggested_treatment_id;
						
							$SuggestedTreatmentUpdateStatus = SuggestedTreatments::where('suggested_treatment_id','=',$suggestedalreadyexist)->update([
							'istatus' => 1,
							'treatment_date' => $request->treatment_date
							]);
						}
									
						if(!$treatmentDataexist){
							
							$treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
							$treatmentName = $treatmentData->name;
							//$amount = $treatmentData->amount;
							$amount = $request->amount;
							$selectedTeeth = $request->selected_teeth;
							$selectedTeeth_array = explode(",", $selectedTeeth);
							$selectedTeethcount = count($selectedTeeth_array);
							if(!empty($request->rate)){
								$rate = $request->rate;
							}else{
								$rate = $amount*$selectedTeethcount;
							}
							if(!empty($request->note)){
								$note = $request->note;
							}else{
								$note = "";
							}
							if($request->total_amount == 0){
								$totalAmount = $rate;
							}else{
								$totalAmount = $request->total_amount;
							}
							if(!empty($request->discount)){
								if($discount_type == 1){
									$discount = ($rate*$discountperornum)/100;
									$discount = round($discount);
								}else{
									$discount = $request->discount;
								}
								//$totalAmount = $totalAmount-$discount;
							}else{
								$discount = 0;
								$discountperornum = 0;
							}

							$random = Str::random(4);
							$sTreatment = SuggestedTreatments::create([
								'patient_treatment_id' => $patient_treatment_id,
								'treatment_id' => $treatment_id,
								'clinic_id' => $request->clinic_id,
								'patient_id' => $request->patient_id,
								'branch_id' => $request->branch_id,
								'SuggestedBydoctor_id' => $request->SuggestedBydoctor_id,
								'treatmentBydoctor_id' => $request->treatmentBydoctor_id,
								'rate' => $rate,
								'selected_teeth' => $request->selected_teeth,
								'amount' => $amount,
								'discount' => $discountperornum,
								'discount_amount' => $discount,
								'discount_type' => $discount_type,
								'total_amount' => $totalAmount,
								'treatment_name' => $treatmentName,
								'selected_teeth_count' => $selectedTeethcount,
								'treatment_status' => $request->treatment_status,
								'treatment_date' => $request->treatment_date,
								//'is_billing' => $request->is_billing,
								'istatus' => $istatus,
								'ref_id' => $random."-".$request->patient_id,
								'is_completed_by_doctorId' => $request->is_completed_by_doctorId,
								'completed_datetime' => $request->completed_datetime,
								'strnote' =>$note,
							]);
						}
					}
									
					$patientTreatments = patientTreatments::where(['patient_treatment_id'=>$patient_treatment_id])->update([
							'doctor_id' => $request->SuggestedBydoctor_id,
							'treatment_date' => $request->treatment_date
						]);
					return response()->json(['status' => 'success',
						'message' => 'Patient treatment Updated Successfully.'
					]);
				
				} elseif((!empty($patient_treatment_id) && !empty($edit)) || ($suggested_treatment_id != 0)){
					if($suggested_treatment_id != 0){
						$selectedTeeth = $request->selected_teeth;
						$selectedTeeth_array = explode(",", $selectedTeeth);
						$selectedTeethcount = count($selectedTeeth_array);
						$totalAmount = $request->total_amount;
						$SuggestedTreatmentData = SuggestedTreatments::where('suggested_treatment_id','=',$suggested_treatment_id)->first();
						$amount = $SuggestedTreatmentData->amount;
						if(!empty($request->rate)){
							$rate = $request->rate;
						}else{
							$rate = $amount*$selectedTeethcount;
						}
						if(!empty($request->discount)){
							if($discount_type == 1){
								$discount = ($rate*$discountperornum)/100;
								$discount = round($discount);
							}else{
								$discount = $request->discount;
							}
							//$totalAmount = $totalAmount-$discount;
						}else{
							$discount = 0;
							$discountperornum = 0;
						}

						$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$suggested_treatment_id)->update([
							'SuggestedBydoctor_id' => $request->SuggestedBydoctor_id,
							'treatmentBydoctor_id' => $request->treatmentBydoctor_id,
							'selected_teeth' => $request->selected_teeth,
							'selected_teeth_count' => $selectedTeethcount,
							'rate' => $request->rate,
							'discount' => $discountperornum,
							'discount_type' => $discount_type,
							'discount_amount' => $discount,
							'total_amount' => $request->total_amount,
							'istatus' => $istatus,
							'treatment_date' => $request->treatment_date
						]);
						
						$SuggestedTreatmentsUpdated = SuggestedTreatments::select('*')
									->where('suggested_treatment_id', '=', $suggested_treatment_id)
									->first();	
									
					    return response()->json(['status' => 'success',
						    'message' => 'Patient treatment Updated Successfully.','suggested_treatments' => $SuggestedTreatmentsUpdated]);
					}
					
					$patientTreatments = patientTreatments::where(['patient_treatment_id'=>$patient_treatment_id])->update([
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date
					]);
									
				    $lastInsertpatientTreatmentsID = $patient_treatment_id;
				} else{
					$patientTreatments = patientTreatments::create([
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date,
					]);
					$lastInsertpatientTreatmentsID = $patientTreatments->patient_treatment_id;
				}
				
				
				foreach ($treatment_ids_array as $treatment_id) {
				    $treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
					$treatmentName = $treatmentData->name;
					//$amount = $treatmentData->amount;
					$amount = $request->amount;
					$selectedTeeth = $request->selected_teeth;
					$selectedTeeth_array = explode(",", $selectedTeeth);
					$selectedTeethcount = count($selectedTeeth_array);
					if(!empty($request->rate)){
						$rate = $request->rate;
					}else{
						$rate = $amount*$selectedTeethcount;
					}
					if(!empty($request->note)){
						$note = $request->note;
					}else{
						$note = "";
					}
							
					if($request->total_amount == 0){
						$totalAmount = $rate;
					}else{
						$totalAmount = $request->total_amount;
					}
					if(!empty($request->discount)){
						if($discount_type == 1){
							$discount = ($rate*$discountperornum)/100;
							$discount = round($discount);
						}else{
							$discount = $request->discount;
						}
						//$totalAmount = $totalAmount-$discount;
					}else{
							$discount = 0;
							$discountperornum = 0;
					}
				
					$random = Str::random(4);
					$sTreatment = SuggestedTreatments::create([
						'patient_treatment_id' => $lastInsertpatientTreatmentsID,
						'treatment_id' => $treatment_id,
						'patient_id' => $request->patient_id,
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
						'SuggestedBydoctor_id' => $request->SuggestedBydoctor_id,
						'treatmentBydoctor_id' => $request->treatmentBydoctor_id,
						'rate' => $rate,
						'selected_teeth' => $request->selected_teeth,
						'amount' => $amount,
						'discount' => $discountperornum,
						'discount_amount' => $discount,
						'discount_type' => $discount_type,
						'total_amount' => $totalAmount,
						'treatment_name' => $treatmentName,
						'selected_teeth_count' => $selectedTeethcount,
						'treatment_status' => $request->treatment_status,
						'treatment_date' => $request->treatment_date,
						'istatus' => $istatus,
						'ref_id' => $random."-".$request->patient_id,
						'is_completed_by_doctorId' => $request->is_completed_by_doctorId,
						'completed_datetime' => $request->completed_datetime,
						'strnote' =>$note
					]);
				}
				
				return response()->json([
					'status' => 'success',
					'message' => 'Suggested treatment created successfully',
					'suggested_treatments' => $sTreatment
				]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		} 
		
		//add treatment screen with multiple treatment select and edit
		public function addsuggestedTreatment(Request $request)
		{
			if(Auth::user()){
				if(!empty($request->istatus) && isset($request->istatus)){
					$istatus = $request->istatus;
				}else{
					$istatus = 0;
				}
				$edit = $request->edit;
				$lastInsertpatientTreatmentsID = $lastInsertpatientTreatmentsID ?? 0;
				if(isset($edit) && $edit != ""){
				    $discountperornum = $request->discount;
    				
    				$treatment_ids = $request->treatment_id;
    				$treatment_ids_array = explode(",", $treatment_ids);
                    $patient_id = $request->patient_id ?? 0;
                    $discount_type = $request->discount_type;
    				//$treatmentBydoctor_id = $request->treatmentBydoctor_id;
    
    				if(empty($discount_type)){
    					$discount_type = 0;
    				}
    				
				    foreach ($treatment_ids_array as $treatment_id) {
				        $SuggestedTreatment = SuggestedTreatments::where(['patient_id' => $request->patient_id, 'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,"patient_treatment_id" => $lastInsertpatientTreatmentsID])
				            ->where('treatment_id',$treatment_id)->first();
			            $treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
    					$treatmentName = $treatmentData->name;
    					$amount = $treatmentData->amount;
    				    $lastInsertpatientTreatmentsID = $lastInsertpatientTreatmentsID ?? 0;
    					$random = Str::random(4);
    					$selectedTeeth = $request->selected_teeth;
    					$selectedTeeth_array = explode(",", $selectedTeeth);
    					$selectedTeethcount = count($selectedTeeth_array);
    					if(!empty($request->rate)){
    						$rate = $request->rate;
    					}else{
    						$rate = $amount*$selectedTeethcount;
    					}
    					if($request->total_amount == 0){
    						$totalAmount = $rate;
    					}else{
    						$totalAmount = $request->total_amount;
    					}
    					$discount = 0;
						//$discountperornum = 0;
    					if(!empty($request->discount)){
    						if($discount_type == 1){
    							$discount = ($rate*$request->discount)/100;
    							$discount = round($discount);
    						}else{
    							$discount = $request->discount;
    						}
    						//$totalAmount = $totalAmount-$discount;
    					}else{
    						$discount = 0;
    						$discountperornum = 0;
    					}
			            if(empty($SuggestedTreatment)){
			                $sTreatment = SuggestedTreatments::create([
        						'patient_treatment_id' => $lastInsertpatientTreatmentsID ?? 0,
        						'treatment_id' => $treatment_id,
        						'patient_id' => $request->patient_id ?? 0,
        						'clinic_id' => $request->clinic_id,
        						'branch_id' => $request->branch_id,
        				// 		'selected_teeth' => $request->selected_teeth ?? 1,
        				// 		'amount' => $amount,
        				        'rate' => $rate,
        						'selected_teeth' => $request->selected_teeth ?? 1,
        						'amount' => $amount,
        						'discount' => $discountperornum,
        						'discount_amount' => $discount,
        						'discount_type' => $discount_type,
        						'total_amount' => $totalAmount,
        						'treatment_name' => $treatmentName,
        						'selected_teeth_count' => $selectedTeethcount ?? 1,
        						'istatus' => $istatus,
        						'ref_id' => $random."-".$request->patient_id
        					]);
			            }
    				}
    				$SuggestedTreatment = SuggestedTreatments::where(['patient_id' => $request->patient_id, 'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,"patient_treatment_id" => $lastInsertpatientTreatmentsID])
				            ->whereNotIn("treatment_id",$treatment_ids_array)->delete();
				            
		            $Treatment = SuggestedTreatments::select('suggested_treatments.*',
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.SuggestedBydoctor_id=users.user_id) as SuggestedBydoctorName'),
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.treatmentBydoctor_id=users.user_id) as TreatmentBydoctorName'))
				                ->where(['patient_id' => $patient_id, 'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,"patient_treatment_id" => $lastInsertpatientTreatmentsID])
				                ->get();
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Suggested treatment Update successfully',
    					'suggested_treatments' => $Treatment
    				]);
				} else{
    				$SuggestedTreatments = SuggestedTreatments::where(['patient_id' => $request->patient_id, 'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,"patient_treatment_id" => $lastInsertpatientTreatmentsID]);
    				if(!empty($SuggestedTreatments)){
    				    $SuggestedTreatments->delete();
    				}
    				// $patient_treatment_id = $request->patient_treatment_id;
    				// $suggested_treatment_id = $request->suggested_treatment_id;
    				// $edit = $request->edit;
    				// $discount_type = $request->discount_type;
    				// $treatmentBydoctor_id = $request->treatmentBydoctor_id;
    
    				// if(empty($discount_type)){
    				// 	$discount_type = 0;
    				// }
    				
    				$discountperornum = $request->discount;
    				
    				$treatment_ids = $request->treatment_id;
    				$treatment_ids_array = explode(",", $treatment_ids);
                    $patient_id = $request->patient_id ?? 0;
                    $discount_type = $request->discount_type;
    				//$treatmentBydoctor_id = $request->treatmentBydoctor_id;
    
    				if(empty($discount_type)){
    					$discount_type = 0;
    				}
    				foreach ($treatment_ids_array as $treatment_id) {
    				    $treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
    					$treatmentName = $treatmentData->name;
    					$amount = $treatmentData->amount;
    				    $lastInsertpatientTreatmentsID = $lastInsertpatientTreatmentsID ?? 0;
    					$random = Str::random(4);
    					$selectedTeeth = $request->selected_teeth;
    					$selectedTeeth_array = explode(",", $selectedTeeth);
    					$selectedTeethcount = count($selectedTeeth_array);
    					if(!empty($request->rate)){
    						$rate = $request->rate;
    					}else{
    						$rate = $amount*$selectedTeethcount;
    					}
    					if($request->total_amount == 0){
    						$totalAmount = $rate;
    					}else{
    						$totalAmount = $request->total_amount;
    					}
    					if(!empty($request->discount)){
    						if($discount_type == 1){
    							$discount = ($rate * $request->discount)/100;
    							$discount = round($discount);
    						}else{
    							$discount = $request->discount;
    						}
    						//$totalAmount = $totalAmount-$discount;
    					}else{
    						$discount = 0;
    						$discountperornum = 0;
    					}
    					$sTreatment = SuggestedTreatments::create([
    						'patient_treatment_id' => $lastInsertpatientTreatmentsID ?? 0,
    						'treatment_id' => $treatment_id,
    						'patient_id' => $request->patient_id ?? 0,
    						'clinic_id' => $request->clinic_id,
    						'branch_id' => $request->branch_id,
    				// 		'selected_teeth' => $request->selected_teeth ?? 1,
    				// 		'amount' => $amount,
    				        'rate' => $rate,
    						'selected_teeth' => $request->selected_teeth ?? 1,
    						'amount' => $amount,
    						'discount' => $discountperornum,
    						'discount_amount' => $discount,
    						'discount_type' => $discount_type,
    						'total_amount' => $totalAmount,
    						'treatment_name' => $treatmentName,
    						'selected_teeth_count' => $selectedTeethcount ?? 1,
    						'istatus' => $istatus,
    						'ref_id' => $random."-".$request->patient_id
    					]);
    				}
    				$Treatment = SuggestedTreatments::select('suggested_treatments.*',
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.SuggestedBydoctor_id=users.user_id) as SuggestedBydoctorName'),
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.treatmentBydoctor_id=users.user_id) as TreatmentBydoctorName'))
				                ->where(['patient_id' => $patient_id, 'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,"patient_treatment_id" => $lastInsertpatientTreatmentsID])
				                ->get();
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Suggested treatment created successfully',
    					'suggested_treatments' => $Treatment
    				]);
				}
				
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		}
		
		public function submitsuggestedTreatment(Request $request){
		    
		    
		    if(Auth::user()){
				if(!empty($request->istatus) && isset($request->istatus)){
					$istatus = $request->istatus;
				}else{
					$istatus = 0;
				}
				$patientTreatments = patientTreatments::create([
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date,
					]);
				$patient_treatment_id = $patientTreatments->patient_treatment_id;
				
				$suggested_treatment_id = $request->suggested_treatment_id;
				// $discount_type = $request->discount_type;
				$treatmentBydoctor_id = $request->treatmentBydoctor_id;

				// if(empty($discount_type)){
				// 	$discount_type = 0;
				// }
				
				//$discountperornum = $request->discount;
				$patientTreatments = patientTreatments::where(['patient_treatment_id'=>$patient_treatment_id])->update([
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date
					]);
				$suggested_treatments = $request->suggested_treatments;
    		    
				//one treatment added using edit and another one saved directry without edit
				foreach ($suggested_treatments as $suggested_treatment) { // $treatment_id
					$treatmentData = Treatments::where('treatment_id',$suggested_treatment['treatment_id'])->first();
					$treatmentName = $treatmentData->name;
					$amount = $suggested_treatment['amount'];
					$selectedTeeth = $suggested_treatment['selected_teeth'];
					$selectedTeeth_array = explode(",", $selectedTeeth);
					$selectedTeethcount = count($selectedTeeth_array);
					if(!empty($suggested_treatment['rate'])){
						$rate = $suggested_treatment['rate'];
					}else{
						$rate = $amount*$selectedTeethcount;
					}
				// 	if(!empty($suggested_treatment['note'])){
				// 		$note = $suggested_treatment['note'];
				// 	}else{
				// 		$note = "";
				// 	}
					if($suggested_treatment['total_amount'] == 0){
						$totalAmount = $rate;
					}else{
						$totalAmount = $suggested_treatment['total_amount'];
					}
					$discount = 0;
					$discountperornum = 0;
					if(!empty($suggested_treatment['discount'])){
						if($suggested_treatment['discount_type'] == 1){
							$discount = ($rate* $suggested_treatment['discount'])/100;
							$discount = round($discount);
						}else{
							$discount = $suggested_treatment['discount'];
						}
						//$totalAmount = $totalAmount-$discount;
					}else{
						$discount = 0;
						$discountperornum = 0;
					}

					$random = Str::random(4);
					$sTreatment = SuggestedTreatments::where(['suggested_treatment_id'=>$suggested_treatment['suggested_treatment_id']])->update([
						'patient_treatment_id' => $patient_treatment_id,
						'treatment_id' => $suggested_treatment['treatment_id'],
						'clinic_id' => $suggested_treatment['clinic_id'],
						'patient_id' => $suggested_treatment['patient_id'],
						'branch_id' => $suggested_treatment['branch_id'],
						'SuggestedBydoctor_id' => $suggested_treatment['SuggestedBydoctor_id'] ? $suggested_treatment['SuggestedBydoctor_id'] : $request->SuggestedBydoctor_id,
						'treatmentBydoctor_id' => $suggested_treatment['treatmentBydoctor_id'] ? $suggested_treatment['treatmentBydoctor_id'] : $request->SuggestedBydoctor_id,
				// 		'rate' => $rate,
				// 		'selected_teeth' => $suggested_treatment['selected_teeth'],
				// 		'amount' => $amount,
				// 		'discount' => $discountperornum,
				// 		'discount_amount' => $discount,
				// 		'discount_type' => $suggested_treatment['discount_type'],
				// 		'total_amount' => $totalAmount,
				// 		'treatment_name' => $treatmentName,
				// 		'selected_teeth_count' => $selectedTeethcount,
						'treatment_status' => $suggested_treatment['treatment_status'],
						'treatment_date' => $request->treatment_date,
						//'is_billing' => $request->is_billing,
						'istatus' => 1,
						'ref_id' => $random."-".$suggested_treatment['patient_id'],
						'is_completed_by_doctorId' => $suggested_treatment['is_completed_by_doctorId'],
						'completed_datetime' => $suggested_treatment['completed_datetime'],
						//'strnote' =>$note,
					]);
				}
				
				return response()->json(['status' => 'success',
					'message' => 'Patient treatment Updated Successfully.'
				]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		}
		
		
		public function editsuggestedTreatment(Request $request){
		    if(Auth::user()){
		        $discount_type = $request->discount_type;
				//$treatmentBydoctor_id = $request->treatmentBydoctor_id;

				if(empty($discount_type)){
					$discount_type = 0;
				}
				if(!empty($request->istatus) && isset($request->istatus)){
					$istatus = $request->istatus;
				}else{
					$istatus = 0;
				}
		        $suggested_treatment_id = $request->suggested_treatment_id;
				$selectedTeeth = $request->selected_teeth;
				$selectedTeeth_array = explode(",", $selectedTeeth);
				$selectedTeethcount = count($selectedTeeth_array);
				$totalAmount = $request->total_amount;
				// $SuggestedTreatmentData = SuggestedTreatments::where('suggested_treatment_id','=',$suggested_treatment_id)->first();
				$amount = $request->amount;
				if(!empty($request->rate)){
					$rate = $request->rate;
				}else{
					$rate = $amount*$selectedTeethcount;
				}
				$discountperornum = $request->discount;
				$discount = 0;
				// $discountperornum = 0;
				// dd($request->discount);
				if(!empty($request->discount)){
					if($discount_type == 1){
						$discount = ($rate*$request->discount)/100;
						$discount = round($discount);
					}else{
						$discount = $request->discount;
					}
					//$totalAmount = $totalAmount-$discount;
				} else {
					$discount = 0;
					$discountperornum = 0;
				}
                if(!empty($request->note)){
					$note = $request->note;
				}else{
					$note = "";
				}
				$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$suggested_treatment_id)->update([
					'SuggestedBydoctor_id' => $request->SuggestedBydoctor_id,
					'treatmentBydoctor_id' => $request->treatmentBydoctor_id,
					'selected_teeth' => $request->selected_teeth,
					'selected_teeth_count' => $selectedTeethcount,
					'rate' => $request->rate,
					'amount' => $amount,
					'discount' => $discountperornum,
					'discount_type' => $discount_type,
					'discount_amount' => $discount,
					'total_amount' => $request->total_amount,
					'istatus' => $istatus,
					'treatment_date' => $request->treatment_date,
					'strnote' => $note
				]);
				
				$SuggestedTreatmentsUpdated = SuggestedTreatments::select('suggested_treatments.*',
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.SuggestedBydoctor_id=users.user_id) as SuggestedBydoctorName'),
				                DB::raw('(SELECT user_name FROM `users` where suggested_treatments.treatmentBydoctor_id=users.user_id) as TreatmentBydoctorName'))
						->where(['patient_id' => $request->patient_id,'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id])
						->where("patient_treatment_id",'=',$request->patient_treatment_id)
						->get();	
							
			    return response()->json(['status' => 'success',
				    'message' => 'Patient treatment Updated Successfully.','suggested_treatments' => $SuggestedTreatmentsUpdated]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		}
		
		public function deletesuggestedTreatment(Request $request){
		    if(Auth::user()){
		        $suggested_treatment_id = $request->suggested_treatment_id;
				SuggestedTreatments::where(["suggested_treatment_id" =>  $suggested_treatment_id])
						->delete();
				$SuggestedTreatmentsUpdated = SuggestedTreatments::where(['patient_id' => $request->patient_id,'clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id])
						->where("patient_treatment_id",'=',$request->patient_treatment_id)
						->get();	
							
			    return response()->json(['status' => 'success',
				    'message' => 'Patient treatment Deleted Successfully.','suggested_treatments' => $SuggestedTreatmentsUpdated]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}
		}
		
		
		//update patienttreatment
		public function updatepatienttreatment(Request $request,$id)
		{
			if(Auth::user()){
							
				$patientTreatments = patientTreatments::where(['patient_treatment_id'=>$id])->update([
						'doctor_id' => $request->doctor_id,
						'treatment_date' => $request->treatment_date
					]);
				return response()->json([
				    'status' => 'success',
					'message' => 'Patient treatment Updated Successfully.'
				]);

			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
			}

		}
		
		//update patienttreatment
		public function updatesuggestedtreatment(Request $request,$id)
		{

			if(Auth::user()){
					
					$selectedTeeth = $request->selected_teeth;
					$totalAmount =  $request->total_amount;
					$amount =  $request->amount;
					$rate = $request->rate;
					if(!empty($selectedTeeth)){
						
						$selectedTeeth_array = explode(",", $selectedTeeth);
						$selectedTeethcount = count($selectedTeeth_array);
						
					}else{
						$amount = 0;
						$selectedTeethcount = 0;
					}
					$discount_type = $request->discount_type;
					$discountperornum = $request->discount;
					
					if($request->treatment_status == 1){
						$is_completed_by_doctorId = $request->SuggestedBydoctor_id;
						$completed_datetime = date('d-m-y h:i:s');
					}else{
						$is_completed_by_doctorId = NULL;
						$completed_datetime = NULL;
					}
					
					if(!empty($request->discount)){
												
						if($discount_type == 1){
								$discount = ($rate*$discountperornum)/100;
								$discount = round($discount);
						}else{
								$discount = $request->discount;
						}
																
						//$totalAmount = $totalAmount-$discount;
																
					}else{
																
							$discount = 0;
							$discountperornum = 0;
					}
					
					if(empty($request->amount)){
						$request->amount = 0;
					} 

					if(empty($request->rate)){
						$request->rate = 0;
					}
					
					if(empty($request->total_amount)){
						$request->total_amount = 0;
					}					
					$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$id)->update([
							'treatmentBydoctor_id' => $request->treatmentBydoctor_id,
							'selected_teeth' => $request->selected_teeth,
							'selected_teeth_count' => $selectedTeethcount,
							'treatment_status' => $request->treatment_status,
							'rate' => $request->rate,
							'amount' => $request->amount,
							'discount' => $discountperornum,
							'discount_type' => $request->discount_type,
							'discount_amount' => $discount,
							'total_amount' => $request->total_amount,
							'treatment_date' => $request->treatment_date,
							'is_completed_by_doctorId' => $is_completed_by_doctorId,
							'completed_datetime' => $completed_datetime,
							'strnote' =>$request->note,
					]);
					

							return response()->json(['status' => 'success','message' => 'Patient treatment Updated Successfully.']);

				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
			
		}
		
		
		//destroy patient treatment
		public function destroypatienttreatment($id)
				{
				   if(Auth::user()){

					 $data = SuggestedTreatments::where('suggested_treatment_id',$id)->count();
						if($data){
							$TreatmentdesDelete = SuggestedTreatments::find($id);
							$TreatmentdesDelete->delete();
							
							return response()->json([
								'status' => 'success',
								'message' => 'Treatment deleted Successfully.',]);
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
		
		
		public function getallpatienttreatment(Request $request, $id){
			$clinic_id = $request->clinic_id;
			if(Auth::user()){
					
					
					$treatmentData = SuggestedTreatments::select(
					'suggested_treatments.treatment_date',
					'suggested_treatments.treatmentBydoctor_id',
					'suggested_treatments.suggested_treatment_id'
					//'users.user_name as doctor_name'
					)
					->where(['suggested_treatments.patient_id' => $id,'suggested_treatments.clinic_id' => $clinic_id,'suggested_treatments.istatus' => 1])
					//->join('users', 'suggested_treatments.treatmentBydoctor_id', '=', 'users.user_id')
					->groupBy('suggested_treatments.treatment_date')
					->orderBy('suggested_treatments.treatment_date', 'desc')
					->get();
				
					$arr = [];
					
					foreach($treatmentData as $TreatmentData){
						
						$treatment_date = $TreatmentData->treatment_date;
						
						 $treatmentList = SuggestedTreatments::where([
						 'treatment_date' => $TreatmentData->treatment_date,
						 'patient_id' =>$id,
						 'istatus' => 1,
						// 'treatmentBydoctor_id' => $TreatmentData->treatmentBydoctor_id,
						 ])
						 
						->get();
						$arr[] = array(
							"treatment_date" => $TreatmentData->treatment_date,
							"treatmentBydoctor_id" => $TreatmentData->treatmentBydoctor_id,
							//"doctor_name" => $TreatmentData->doctor_name,
							"treatmentList" => $treatmentList 
						); 
							
					}
					
						return response()->json([
							'status' => 'success',
							'treatmentData' => $arr
						]);

					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		//This is for print button click on treatment list
		public function getpatienttreatmentbydate(Request $request){
			
			$patientId = $request->patient_id;
			$clinicId = $request->clinic_id;
			$doctorId = $request->SuggestedBydoctor_id;
			$originalTreatmentDate = $request->treatment_date;
			
			$treatmentDate = date("Y-m-d", strtotime($originalTreatmentDate));
			if(Auth::user()){
					
					
					$treatmentData = SuggestedTreatments::select(
					'suggested_treatments.*',
					//'users.user_name as doctor_name'
					)
					->where(['suggested_treatments.patient_id' => $patientId,'suggested_treatments.clinic_id' => $clinicId,
					'suggested_treatments.istatus' => 1,
					//'suggested_treatments.SuggestedBydoctor_id' => $doctorId,
					'suggested_treatments.treatment_date' => $treatmentDate,
					
					])
					//->join('users', 'suggested_treatments.SuggestedBydoctor_id', '=', 'users.user_id')
					->orderBy('suggested_treatments.treatment_date', 'desc')
					->get();

						return response()->json([
							'status' => 'success',
							'treatmentData' => $treatmentData
						]);

					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function getpatienttreatmentbyId(Request $request, $id){
			
			if(Auth::user()){
				
					$treatmentData = SuggestedTreatments::select(
						'users.user_name as doctor_name',
						'suggested_treatments.treatment_date',
						'suggested_treatments.SuggestedBydoctor_id',
						'suggested_treatments.treatmentBydoctor_id',
						'suggested_treatments.rate',
						'suggested_treatments.discount',
						'suggested_treatments.discount_type',
						'suggested_treatments.total_amount',
						'suggested_treatments.discount_amount',
						'suggested_treatments.amount',
						'suggested_treatments.selected_teeth',
						'suggested_treatments.treatment_status',
						'suggested_treatments.strnote',
						'suggested_treatments.treatment_date',
						'treatments.name as treatment_name'
						
						)
					->where('suggested_treatments.suggested_treatment_id', '=', $id)
					->join('users', 'suggested_treatments.treatmentBydoctor_id', '=', 'users.user_id')
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->first();
					
					
					
					return response()->json([
							'status' => 'success',
							'treatmentData' => $treatmentData
						]);

					
					}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function getalltreatmentnotbilling(Request $request){
			
			$orgDate = $request->selected_date ?? "";
			$patient_id = $request->patient_id;
			$branch_id = $request->branch_id;
			$newDate = date("Y-m-d", strtotime($orgDate));
			$current_date = $request->current_date ?? "";
			
			$treatmentData = SuggestedTreatments::select(
						'treatments.name as treatment_name',
						'suggested_treatments.treatment_date',
						'suggested_treatments.rate',
						'suggested_treatments.discount',
						'suggested_treatments.amount',
						//	DB::raw('sum(total_amount) as totalcost'),
						'suggested_treatments.discount_amount',
						'suggested_treatments.total_amount',
						'suggested_treatments.treatment_status',
						'suggested_treatments.suggested_treatment_id'
						
						)
					->where(['suggested_treatments.is_billing' => 0, 'suggested_treatments.patient_id' => $patient_id ,
					'suggested_treatments.branch_id' => $branch_id,'suggested_treatments.istatus' => 1])
					
				// ->when($request->selected_date, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
				// })
				// ->when($request->current_date, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->current_date."','%Y-%m-%d')"));
				// })
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->get();
					
				
				//calculation and query to show bill total on add billing screen	
				$treatmentDatatoalsum = SuggestedTreatments::select(

						DB::raw('sum(total_amount) as totalcost')
						
						)
					->where(['suggested_treatments.is_billing' => 0, 'suggested_treatments.patient_id' => $patient_id ,
					'suggested_treatments.branch_id' => $branch_id])
					
					->when($request->selected_date, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
				})
				->when($request->current_date, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->current_date."','%Y-%m-%d')"));
				})
				
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->get();
					
					if(!empty($treatmentDatatoalsum[0]['totalcost'])){
						$billTotal = $treatmentDatatoalsum[0]['totalcost'];
					}else{
						$billTotal = 0;
					}
					return response()->json([
							'status' => 'success',
							'treatmentData' => $treatmentData,
							'billTotal' => $billTotal
						]);
			
		}
		
		public function updateStatus(Request $request, $id){
			
			$treatment_status = $request->treatment_status;
			if(Auth::user()){
				
				if($request->treatment_status == 2){
						$is_completed_by_doctorId = $request->SuggestedBydoctor_id;
						$completed_datetime = date('d-m-y h:i:s');
					}else{
						$is_completed_by_doctorId = NULL;
						$completed_datetime = NULL;
					}
				
				$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$id)->update([

							'treatment_status' => $request->treatment_status,
							'is_completed_by_doctorId' => $is_completed_by_doctorId,
							'completed_datetime' => $completed_datetime
					]);
					
				return response()->json(['status' => 'success','message' => 'Patient treatment status Updated Successfully.']);
						
				
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}

		public function updatealltreatmentnotbilling(Request $request){
			
			if(Auth::user()){
				$suggestedTreatmentIds = $request->suggestedIds;
				$clinic_id = $request->clinic_id;
				$branch_id = $request->branch_id;
				$patient_id = $request->patient_id;
				$net_amount = 0;
				
				$lastOrderData = OrderMaster::where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'istatus' => 0])
					->orderBy('created_at', 'desc')
					->first();
				$isPaid = "";
				if(!empty($lastOrderData)){
					$isPaid = $lastOrderData->is_paid;
				}
				
						if(empty($lastOrderData) || $isPaid == 2)
						{
										$existPrefix = BranchCaseNumber::where('branch_id', '=', $branch_id)->first();
										$case_pre = $existPrefix->case_pre;
										$lastBillNo = $existPrefix->bill_no;
										
										 $OrderMaster = OrderMaster::create([
										
															'clinic_id' => $clinic_id,
															'branch_id' => $branch_id,
															'patient_id' => $patient_id,
															'is_paid' => 0,
															//'net_amount' => $net_amount,
										]);
										$lastInsertorderID = $OrderMaster->order_master_id; 
										
										foreach($suggestedTreatmentIds as $suggestedTreatmentId){

											$SuggestedTreatmentData = SuggestedTreatments::where('suggested_treatment_id',$suggestedTreatmentId)->first();
															$clinic_id = $SuggestedTreatmentData->clinic_id;
															$branch_id = $SuggestedTreatmentData->branch_id;
															$patient_id = $SuggestedTreatmentData->patient_id;
															$rate = $SuggestedTreatmentData->rate;
															$selectedTeeth = $SuggestedTreatmentData->selected_teeth;
															$amount = $SuggestedTreatmentData->amount;
															$discount = $SuggestedTreatmentData->discount;
															$net_amount+= $SuggestedTreatmentData->total_amount;
															$total_amount = $SuggestedTreatmentData->total_amount;
											
											 $OrderDetail = OrderDetail::create([
															'order_id' => $lastInsertorderID,
															'patient_id' => $patient_id,
															'suggested_treatment_id' => $suggestedTreatmentId,
															'rate' => $rate,
															'selected_teeth' => $selectedTeeth,
															'amount' => $total_amount,
															//'discount' => $discount,
												]); 
												
										 $updatealltreatmentnotbilling = SuggestedTreatments::where(['suggested_treatment_id' => $suggestedTreatmentId])
											->update([
											'is_billing' => 1
											]);

										}
										$billNo = strtoupper($case_pre).$lastBillNo;
										$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $lastInsertorderID])
											->update([
											'bill_no' => $billNo,
											'net_amount' => $net_amount
											]);
											
										$updatedBillNo = $lastBillNo + 1; 
										$BranchCaseNumber = BranchCaseNumber::where(['branch_id' => $branch_id])->update([
																'bill_no' => $updatedBillNo
																]);
																
								return response()->json([
									'status' => 'success',
									'message' => 'Bill generated successfully.',
									'order_id' => $lastInsertorderID
								]);
						}else{
							
							return response()->json([
									'status' => 'error',
									'message' => 'Last Bill is not Paid yet.Please complete that first.'
									], 401);
						}	
				
			
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function addtoexistorderpayment(Request $request){
			if(Auth::user()){
				
				$suggestedTreatmentIds = $request->suggestedIds;
				$clinic_id = $request->clinic_id;
				$branch_id = $request->branch_id;
				$patient_id = $request->patient_id;
				$net_amount = 0;
				
				//last order of current patient
				$lastOrderData = OrderMaster::where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'istatus' => 0])
					->orderBy('created_at', 'desc')
					->first();
				
				if(!empty($lastOrderData)){
					
					$lastInsertorderID = $lastOrderData->order_master_id;
					$is_paid = $lastOrderData->is_paid;
					$netAmountExistingOrder = $lastOrderData->net_amount;					
					$dueAmountExistingOrder = $lastOrderData->due_amount;										
					
					foreach($suggestedTreatmentIds as $suggestedTreatmentId){

						$SuggestedTreatmentData = SuggestedTreatments::where('suggested_treatment_id',$suggestedTreatmentId)->first();
										$clinic_id = $SuggestedTreatmentData->clinic_id;
										$branch_id = $SuggestedTreatmentData->branch_id;
										$patient_id = $SuggestedTreatmentData->patient_id;
										$rate = $SuggestedTreatmentData->rate;
										$selectedTeeth = $SuggestedTreatmentData->selected_teeth;
										$amount = $SuggestedTreatmentData->amount;
										$discount = $SuggestedTreatmentData->discount;
										$net_amount+= $SuggestedTreatmentData->total_amount;
										$total_amount = $SuggestedTreatmentData->total_amount;
						
						 $OrderDetail = OrderDetail::create([
										'order_id' => $lastInsertorderID,
										'patient_id' => $patient_id,
										'suggested_treatment_id' => $suggestedTreatmentId,
										'rate' => $rate,
										'selected_teeth' => $selectedTeeth,
										'amount' => $total_amount,
										//'discount' => $discount,
							]); 
							
					 $updatealltreatmentnotbilling = SuggestedTreatments::where(['suggested_treatment_id' => $suggestedTreatmentId])
						->update([
						'is_billing' => 1
						]);

					}
					
					if($is_paid == 0){
						
						$newNetAmount = $netAmountExistingOrder + $net_amount;
						
							$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $lastInsertorderID])
								->update([
								'net_amount'
								=> $newNetAmount
							]);
					}else if($is_paid == 1){

						$newDueAmount = $dueAmountExistingOrder + $net_amount;
						$newNetAmount = $netAmountExistingOrder + $net_amount;
						
						$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $lastInsertorderID])
								->update([
								'due_amount' => $newDueAmount,
								'net_amount' => $newNetAmount
							]);
					}else if($is_paid == 2){

						$newDueAmount = $dueAmountExistingOrder + $net_amount;
						$newNetAmount = $netAmountExistingOrder + $net_amount;
						
						$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $lastInsertorderID])
								->update([
								'due_amount' => $newDueAmount,
								'net_amount' => $newNetAmount,
								'is_paid' => 1
							]);
							
					}
						
					return response()->json([
								'status' => 'success',
								'message' => 'Bill added to existig invoice successfully.',
								'order_id' => $lastInsertorderID
							]);
				}else{
					
					return response()->json([
							'status' => 'error',
							'message' => 'invoice does not exist for this patient.',
						], 401);
				}
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function getlastorderDatabypatient(Request $request){
			
			//last order of current patient
			
			if(Auth::user()){
					
					$lastOrderData = OrderMaster::select(
					'order_master_id',
					'bill_no',
					'is_paid',
					'net_amount',
					'paid_amount',
					'deleted_at',
					DB::raw('DATE_FORMAT(created_at, "%d-%M-%Y") as created_date')
					)
					 ->where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'istatus' => 0])
					->orderBy('created_at', 'desc')
					->first();
				

									
				if(!empty($lastOrderData)){
					
					$created_date = $lastOrderData->created_date;
					$now = time(); // or your date as well
					$your_date = strtotime($created_date);
					$datediff = $now - $your_date;
					$numberOfDays =  round($datediff / (60 * 60 * 24));
					
					return response()->json([
								'status' => 'success',
								'lastOrderData' => $lastOrderData,
								'dayslastorder' => $numberOfDays
							], 401);
						
				}else{
					
					return response()->json([
								'status' => 'error',
								'message' => 'No data found.',
							], 401);

				}
					
					
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function treatmentDataForPrintScreen(Request $request){
				$treatments = new Treatments();
				
				  $suggestedTreatmentIds = $request->suggestedIds;
				  $clinic_id = $request->clinic_id;
				  $branch_id = $request->branch_id;
				  $patient_id = $request->patient_id;

				  $patientData = Patient::where(['patient_id'=> $patient_id])->first();
				  $patientMobileNo = $patientData->mobile_no;
				  $netAmount = 0;
				  $discountAmount = 0;
				  $totalAmount = 0;
				  foreach($suggestedTreatmentIds as $suggestedTreatmentId){


						$TreatmentData = SuggestedTreatments::select(
						'users.user_name as doctor_name',
						'users.address as address',
						'patients.name_prefix as name_prefix',
						'patients.name as patient_name',
						'patients.mobile_no as mobile_no',
						'patients.case_no as case_no',
						'patients.address as patient_address',
						 DB::raw('DATE_FORMAT(suggested_treatments.treatment_date, "%d-%M-%Y") as treatment_date'),
						'treatments.name as treatment_name',
						'suggested_treatments.selected_teeth',
						'suggested_treatments.selected_teeth_count',
						'suggested_treatments.rate',
						'suggested_treatments.discount',
						'suggested_treatments.discount_type',
						'suggested_treatments.total_amount',
						'suggested_treatments.discount_amount',
						'suggested_treatments.amount',
						'suggested_treatments.treatment_status',
						'suggested_treatments.strnote'
						
						)
						->where(['suggested_treatments.suggested_treatment_id' => $suggestedTreatmentId])
						->join('users', 'suggested_treatments.SuggestedBydoctor_id', '=', 'users.user_id')
						->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
						->join('patients', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
						->first();
						
						if(!empty($TreatmentData)){	
						
						$netAmount += intval($TreatmentData->rate);
						$discountAmount += intval($TreatmentData->discount_amount);
						$totalAmount += intval($TreatmentData->total_amount);
						$arr[] = array(
							"doctor_name" => $TreatmentData->doctor_name,
							"address" => $TreatmentData->address,
							"patient_name" => $TreatmentData->patient_name,
							"name_prefix" => $TreatmentData->name_prefix,
							"mobile_no" => $TreatmentData->mobile_no,
							"case_no" => $TreatmentData->case_no,
							"patient_address" => $TreatmentData->patient_address,
							"treatment_date" => $TreatmentData->treatment_date,
							"treatment_name" => $TreatmentData->treatment_name,
							"selected_teeth" => $TreatmentData->selected_teeth,
							"selected_teeth_count" => $TreatmentData->selected_teeth_count,
							"amount" => $TreatmentData->amount,
							"net_amount" => $TreatmentData->rate,
							"discount" => $TreatmentData->discount,
							"discount_amount" => $TreatmentData->discount_amount,
							"total_amount" => $TreatmentData->total_amount
						);
						
						
						}else{
							$arr[] = "";
						}

				  }
				  
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "Dear User, Please find attached bill of treatments.";
						$fileName = $patientData->case_no."_".date('d-m-Y');
						 

						$pdf = PDF::loadView('treatmentinvoice',['Treatments' => $arr,'netAmount' => $netAmount,'discountAmount' => $discountAmount,'totalAmount' => $totalAmount]);
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/bills/'.$fileName . '.pdf',$content);
						
						//$pdf->save(public_path('assets/bills/')  . $fileName. '.pdf');
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/bills/')  . $fileName. '.pdf');
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/bills/')  . $fileName. '.pdf');

						}

						$billFile = asset('assets/bills/'. $fileName. '.pdf');
						//return $pdf->download($fileName . '.pdf');
						$users = new User();
						$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$billFile);
						//dd($status->status);
				// 		$statusofMessage = "success" // $status->status;
						//$Response = $status->response;
					
				// 		if($statusofMessage == "success"){
							return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $billFile,
								'message' => 'Quotation sent on your registered mobile number.',
							], 401);
				// 		}else{
							
				// 			return response()->json([
				// 				'status' => 'error',
				// 				'message' => $Response.'.Please contact admin.',
				// 			], 401);
				// 		}
				
						
					return response()->json([
								'status' => 'success',
								'Treatments' => $arr
							], 401);
		
		}
		
				
		public function treatmentpdfFilelink(Request $request){
				$treatments = new Treatments();
				
				  $suggestedTreatmentIds = $request->suggestedIds;
				  $clinic_id = $request->clinic_id;
				  $branch_id = $request->branch_id;
				  $patient_id = $request->patient_id;

				  $patientData = Patient::where(['patient_id'=> $patient_id])->first();
				  $patientMobileNo = $patientData->mobile_no;
				  $netAmount = 0;
				  $discountAmount = 0;
				  $totalAmount = 0;
				  foreach($suggestedTreatmentIds as $suggestedTreatmentId){


						$TreatmentData = SuggestedTreatments::select(
						'users.user_name as doctor_name',
						'users.address as address',
						'patients.name_prefix as name_prefix',
						'patients.name as patient_name',
						'patients.mobile_no as mobile_no',
						'patients.case_no as case_no',
						'patients.address as patient_address',
						 DB::raw('DATE_FORMAT(suggested_treatments.treatment_date, "%d-%M-%Y") as treatment_date'),
						'treatments.name as treatment_name',
						'suggested_treatments.selected_teeth',
						'suggested_treatments.selected_teeth_count',
						'suggested_treatments.rate',
						'suggested_treatments.discount',
						'suggested_treatments.discount_type',
						'suggested_treatments.total_amount',
						'suggested_treatments.discount_amount',
						'suggested_treatments.amount',
						'suggested_treatments.treatment_status',
						'suggested_treatments.strnote'
						
						)
						->where(['suggested_treatments.suggested_treatment_id' => $suggestedTreatmentId])
						->join('users', 'suggested_treatments.SuggestedBydoctor_id', '=', 'users.user_id')
						->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
						->join('patients', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
						->first();
						
						if(!empty($TreatmentData)){	
						
						$netAmount += intval($TreatmentData->rate);
						$discountAmount += intval($TreatmentData->discount_amount);
						$totalAmount += intval($TreatmentData->total_amount);
						$arr[] = array(
							"doctor_name" => $TreatmentData->doctor_name,
							"address" => $TreatmentData->address,
							"patient_name" => $TreatmentData->patient_name,
							"name_prefix" => $TreatmentData->name_prefix,
							"mobile_no" => $TreatmentData->mobile_no,
							"case_no" => $TreatmentData->case_no,
							"patient_address" => $TreatmentData->patient_address,
							"treatment_date" => $TreatmentData->treatment_date,
							"treatment_name" => $TreatmentData->treatment_name,
							"selected_teeth" => $TreatmentData->selected_teeth,
							"selected_teeth_count" => $TreatmentData->selected_teeth_count,
							"amount" => $TreatmentData->amount,
							"net_amount" => $TreatmentData->rate,
							"discount" => $TreatmentData->discount,
							"discount_amount" => $TreatmentData->discount_amount,
							"total_amount" => $TreatmentData->total_amount
						);
						
						
						}else{
							$arr[] = "";
						}

				  }
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "Dear User, Please find attached quotation of treatments.";
						$fileName = $patientData->case_no."_".date('d-m-Y');
						 

						$pdf = PDF::loadView('treatmentinvoice',['Treatments' => $arr,'netAmount' => $netAmount,'discountAmount' => $discountAmount,'totalAmount' => $totalAmount]);
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/bills/'.$fileName . '.pdf',$content);
						
						//$pdf->save(public_path('assets/bills/')  . $fileName. '.pdf');
						
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/bills/')  . $fileName. '.pdf');

						$billFile = asset('assets/bills/'. $fileName. '.pdf');
						//return $pdf->download($fileName . '.pdf');
						//$users = new User();
						//$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$billFile);
						
						//$statusofMessage = $status->status;
					
						/* if($statusofMessage == 1){
							return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $billFile,
								'message' => 'Bill sent on your registered mobile number.',
							], 401);
						}else{
							
							return response()->json([
								'status' => 'error',
								'message' => 'User does not exist.',
							], 401);
						} */
						
						return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $billFile,
								'message' => 'PDF file.',
							], 401);
				
						
					return response()->json([
								'status' => 'success',
								'Treatments' => $arr
							], 401);
		
		}
		
		public function getallpatienttreatmentforlabwork(Request $request){
			
			
			if(Auth::user()){
				
				$clinic_id = $request->clinic_id;
				$patient_id = $request->patient_id;
				$branch_id = $request->branch_id;
				$treatmentDataLabwork = SuggestedTreatments::select(
					'suggested_treatments.treatment_date',
					'suggested_treatments.treatment_name',
					'suggested_treatments.treatment_id',
					'suggested_treatments.suggested_treatment_id',
					'suggested_treatments.selected_teeth',
					'suggested_treatments.selected_teeth_count'
					
					//'users.user_name as doctor_name'
					)
					->where(['suggested_treatments.is_billing' => 1,'suggested_treatments.treatment_status' => 0,
					'suggested_treatments.clinic_id' => $clinic_id,'treatments.labwork_required' => 1,
					'suggested_treatments.patient_id' => $patient_id,'suggested_treatments.branch_id' => $branch_id,
					'order_detail.labwork_master_id'=> 0])
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->join('order_detail', 'suggested_treatments.suggested_treatment_id', '=', 'order_detail.suggested_treatment_id')
					->orderBy('suggested_treatments.treatment_date', 'asc')
					->get();
					
						return response()->json([
							'status' => 'success',
							'treatmentData' => $treatmentDataLabwork
						]);
						
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function allTreatment(Request $request){
				$treatments = new Treatments();
				
				  $allTreatments = Treatments::select(
					 
					'treatment_id',
					'name',
					'amount',
					'labwork_required',
					'deleted_at',
					'created_at',
					'updated_at'
					)
					->where(['clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id])
					 ->when($request->name, function ($query) use ($request) {
                        $query->where('name','LIKE', '%' . $request->name .'%');
                    })
					->orderBy('name', 'asc')
					->get();
				$allTreatmentslist = [];
				$iCounter = 0;
				foreach($allTreatments as $allTreatment){
				    $allTreatmentslist[] = array(
				        'serialNo' => $iCounter, 
				        'treatment_id' => $allTreatment['treatment_id'],
    					'name' => $allTreatment['name'],
    					'amount' => $allTreatment['amount'],
    					'labwork_required' => $allTreatment['labwork_required'],
    					'deleted_at' => $allTreatment['deleted_at'],
    					'created_at' => $allTreatment['created_at'],
    					'updated_at' => $allTreatment['updated_at']
			        );   
			        $iCounter++;
				}

				return response()->json(['Treatments' => $allTreatmentslist]);
		
		}
		
		public function allTreatmentLabWorkWise(Request $request){
				$treatments = new Treatments();
				
				  $allTreatmentslist = Treatments::select(
					 
					'treatment_id',
					'name',
					'amount',
					'labwork_required',
					'deleted_at',
					'created_at',
					'updated_at'
					)
					->where(['clinic_id' => $request->clinic_id,'branch_id' => $request->branch_id,'labwork_required' => 1])
					->get();
					

				return response()->json(['Treatments' => $allTreatmentslist]);
		
		}
		
		public function treatmentreport(Request $request){
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$pdffile = $request->pdffile;
			$whatsappfile = $request->whatsappfile;
			$treatment_id = $request->treatment_id;
			
			if(Auth::user()){
					
					
					$treatmentData = SuggestedTreatments::select(
					'suggested_treatments.treatment_date',
					'suggested_treatments.total_amount',
					//DB::raw('sum(suggested_treatments.total_amount) as total_amount_all'),
					'patients.name_prefix',
					'patients.name',
					'treatments.name as treatment_name',
					'users.user_name as doctor_name'
					)
					->where(['suggested_treatments.clinic_id' => $clinic_id,'suggested_treatments.istatus' => 1])
					->when($request->branch_id, fn ($query, $branch_id) => $query->WhereIn('suggested_treatments.branch_id',$branch_id))
					->join('patients', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->join('users', 'suggested_treatments.treatmentBydoctor_id', '=', 'users.user_id')
					//->groupBy('suggested_treatments.treatment_date')
					->when($request->selected_date, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
					    })
					->when($request->fromDate, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
						})
					->when($request->toDate, function ($query) use ($request) {
							$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
						})
					->when($request->treatment_name, function ($query) use ($request) {
							$query->where('treatments.name','LIKE', '%' . $request->treatment_name .'%');
						})
				// 	->when($request->branch_id, function ($query) use ($request) {
				// 			$query->where('suggested_treatments.branch_id','=',$request->branch_id);
				// 		})
					->when($request->month, function ($query) use ($request) {
						    $query->where(DB::raw("MONTH(suggested_treatments.treatment_date)"),'=',$request->month);
					    })
					->when($request->year, function ($query) use ($request) {
    						$query->where(DB::raw("YEAR(suggested_treatments.treatment_date)"),'=',$request->year);
    					})
					->when($request->doctor_id, function ($query) use ($request) {
							$query->where('suggested_treatments.treatmentBydoctor_id','=',$request->doctor_id);
						})
					->orderBy('suggested_treatments.treatment_date', 'desc')
					->get();
					
				$totaltreatmentDataCount = "";
				
				/* if(!empty($treatment_id)){
					
				 $treatmentDataCount = SuggestedTreatments::select(
					'suggested_treatments.treatment_date ',
					'suggested_treatments.total_amount',
					'patients.name_prefix',
					'patients.name',
					'treatments.name as treatment_name'
					)
					->where(['suggested_treatments.branch_id' => $branch_id,
						'suggested_treatments.clinic_id' => $clinic_id,
						'suggested_treatments.istatus' => 1,
						'suggested_treatments.treatment_id' => $treatment_id
						])
						
					->join('patients', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					
					//->groupBy('suggested_treatments.treatment_date')
					->when($request->treatment_name, function ($query) use ($request) {
										$query->where('treatments.name','LIKE', '%' . $request->treatment_name .'%');
									})
					->orderBy('suggested_treatments.treatment_date', 'desc')
					->get(); 
					
					$totaltreatmentDataCount = count($treatmentDataCount);
				} */
				//echo $totaltreatmentDataCount;
				//echo "sdsad";
				//die;
				// $branchData = Branch::where('branch_id','=',$request->branch_id)->first();
    // 			$branchName = $branchData->branch_name;
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
					$arr = [];
					$grand_amount = 0;
					if(count($treatmentData) != 0){
						
						foreach($treatmentData as $TreatmentData){
							
							$treatment_date = $TreatmentData->treatment_date;
							$grand_amount += $TreatmentData->total_amount;

							$arr[] = array(
								"treatment_date" => $TreatmentData->treatment_date,
								"total_amount" => $TreatmentData->total_amount,
								"patient_name" => $TreatmentData->name_prefix." ".$TreatmentData->name,
								"treatment_name" => $TreatmentData->treatment_name,
								"doctor_name" => $TreatmentData->doctor_name
							); 
								
						}
						
						
							if($pdffile == 1){
									
										$pdf = PDF::loadView('treatment_report',['treatmentData' => $arr,'grand_total' => $grand_amount,"Duration" => $Duration,
										"branchName" => $branchName
										]);
										
										$fileName = date('d-m-Y')."_treatment";
										
										$content = $pdf->download()->getOriginalContent();
										Storage::put('public/assets/treatment_report/'.$fileName . '.pdf',$content);
										
										if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
											$pdf->save(public_path('assets/treatment_report/')  . $fileName. '.pdf');	
										}else {
											$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/treatment_report/')  . $fileName. '.pdf');

										}
								
										$labFile = asset('assets/treatment_report/'. $fileName. '.pdf');
								
										//return $pdf->download($fileName . '.pdf');
										
									
								$key = $_ENV['WHATSAPPKEY'];		
								$treatmentListFile = asset('assets/treatment_report/'. $fileName. '.pdf');
								$msg = "Dear User, Please find attached details of treatments.";
											
								if($whatsappfile == 1){
										$users = new User();
										$currentUser = Auth::user();

										$mobileNo = $currentUser->mobile_no;
										$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$treatmentListFile);
										
								// 		$statusofMessage = $status->status;
										//$Response = $status->response;
									
								// 		if($statusofMessage == "success"){
											return response()->json([
												'status' => 'success',
												'pdfFileUrl' => $treatmentListFile,
												'treatmentData' => $arr,
												'grand_total' => $grand_amount,
												'message' => 'Treatment Report sent on your registered mobile number.',
											], 401);
								// 		}else{
											
								// 			return response()->json([
								// 				'status' => 'error',
								// 				'message' => $Response.'.Please contact admin.',
								// 			], 401);
								// 		}
									}else{
										return response()->json([
											'status' => 'success',
											'treatmentData' => $arr,
											'grand_total' => $grand_amount,
											'treatmentDataFile' => $treatmentListFile
										]);
									}
								}
								
						
							return response()->json([
								'status' => 'success',
								'treatmentData' => $arr,
								'grand_total' => $grand_amount
							]);
						
						
					}else{
							return response()->json([
								'status' => 'error',
								'message' => 'No Record Found.',
								'treatmentData' => $arr
							]);
					}
					
						

					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
	public function NoOfStartedlist(Request $request){
		if(Auth::user()){

			/*$NoOfStarted = Patient::select('patients.patient_id','patients.name_prefix','patients.name',
					DB::raw('(select treatments.name from treatments where treatments.treatment_id=suggested_treatments.treatment_id) as treatmentName'),
					DB::raw('(select doctors.name from doctors where doctors.doctor_id=suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
					'order_master.net_amount', 'order_master.paid_amount','order_master.due_amount')
				->leftjoin('order_master','patients.patient_id','=','order_master.patient_id')
				->leftjoin('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where(['order_master.clinic_id' =>$request->clinic_id, 'order_master.branch_id' =>$request->branch_id])
				->whereIn('order_master.is_paid',[1,2])
				->whereNotIn('suggested_treatments.treatment_id', function($query){
					$query->select('treatments.treatment_id')
					->from(with(new Treatments)->getTable())
					->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
					->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
				})
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				// ->join('groups', 'patients.group_id', '=', 'groups.group_id')
					->orderBy('patients.patient_id', 'desc')
				->distinct()
				//->get();
				->toSql();*/
			$NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    DB::raw('(SELECT treatments.name FROM treatments WHERE treatments.treatment_id = suggested_treatments.treatment_id) as treatmentName'),
                    DB::raw('(SELECT doctors.name FROM doctors WHERE doctors.doctor_id = suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->distinct()
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where(function ($query) use ($request) {
                    $query->where('patients.clinic_id', $request->clinic_id)
                          ->where('patients.branch_id', $request->branch_id);
                })
                ->where(function ($query) use ($request) {
                    $query->where('order_master.clinic_id', $request->clinic_id)
                          ->where('order_master.branch_id', $request->branch_id);
                })
                ->whereIn('order_master.is_paid', [1, 2])
                ->whereNotIn('suggested_treatments.treatment_id', function ($query) {
                    $query->select('treatments.treatment_id')
                          ->from('treatments')
                          ->whereColumn('treatments.treatment_id', 'suggested_treatments.treatment_id')
                          ->whereNotIn('treatments.name', ['X ray', 'consultation', 'Medicines']);
                })
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')
                ->get();
            DB::beginTransaction();
            /*$NoOfStarted = Patient::select('patients.patient_id','patients.name_prefix','patients.name','order_master.net_amount', 'order_master.paid_amount','order_master.due_amount')
				->leftjoin('order_master','patients.patient_id','=','order_master.patient_id')
				->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
				//->join('treatments','treatments.treatment_id','=','suggested_treatments.treatment_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where(['order_master.clinic_id' =>$request->clinic_id, 'order_master.branch_id' =>$request->branch_id])
				->whereIn('order_master.is_paid',[1,2])
				//->whereNotIn('treatments.name', ['X ray','consultation','Medicines'])
				// ->whereNotIn('patients.patient_id', function($query){
				// 	$query->select('suggested_treatments.patient_id')
				// 	->from(with(new SuggestedTreatments)->getTable())
				// 	->whereNotIn('treatment_id',function($query){
				// 	    $query->select('treatments.treatment_id')
				// 	    ->from(with(new Treatments)->getTable())
				// 	    ->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
				// 	    ->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
				// 	})
				// 	->where('suggested_treatments.istatus','=','1');
				// })
				->whereNotIn('suggested_treatments.treatment_id', function($query){
    				$query->select('treatments.treatment_id')
    				->from(with(new Treatments)->getTable())
    				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
    				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
    			})
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				->orderBy('patients.patient_id', 'desc')
				->get();*/
				// ->toSql();
				// echo $request->fromDate;
				// echo "<br />";
				// echo $request->toDate;
				// echo "<br />";
				//dd($NoOfStarted);
			DB::commit(); 
// 			echo "fromDate: " .$request->fromDate."<br />";
// 			echo "toDate : ". $request->toDate."<br />";
// 			echo "branch_id: ". $request->branch_id."<br />";
// 			echo "clinic_id: " .$request->clinic_id."<br />";
// 			dd($NoOfStarted);
			// $totaltreatmentDataCount = "";
			$arr = [];
			// dd($NoOfStarted);
			if(count($NoOfStarted) != 0){
				foreach($NoOfStarted as $NoOfStartData){
					$arr[] = array(
						"patient_id" => $NoOfStartData->patient_id,
						"patient_name" => $NoOfStartData->name_prefix." ".$NoOfStartData->name,
						"treatmentName" => "", //$NoOfStartData->treatmentName,
						"DoctorName" => "",//$NoOfStartData->DoctorName,
						"total_amount" => $NoOfStartData->net_amount,
						"paid_amount" => $NoOfStartData->paid_amount,
						"due_amount" => $NoOfStartData->due_amount
					); 
						
				}
				DB::rollBack();
				return response()->json([
					'status' => 'success',
					'NoOfStartDataList' => $arr
				]);
				
			}else{
			    DB::rollBack();
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'NoOfStartDataLis' => $arr
				]);
			}
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	
	public function NoOfStartedLessThen700list(Request $request){
		if(Auth::user()){
            DB::beginTransaction();
            /*$NoOfStarted = Patient::select('patients.patient_id','patients.name_prefix','patients.name','order_master.net_amount', 'order_master.paid_amount','order_master.due_amount')
				->leftjoin('order_master','patients.patient_id','=','order_master.patient_id')
				->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where(['order_master.clinic_id' =>$request->clinic_id, 'order_master.branch_id' =>$request->branch_id])
				->whereIn('order_master.is_paid',[1,2])
				->where('paid_amount','<',700)
				->whereNotIn('suggested_treatments.treatment_id', function($query){
    				$query->select('treatments.treatment_id')
    				->from(with(new Treatments)->getTable())
    				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
    				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
    			})
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				->orderBy('patients.patient_id', 'desc')
				->get();*/
			/*$NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where(function ($query) use($request) {
                    $query->where('patients.clinic_id', $request->clinic_id)
                        ->where('patients.branch_id', $request->branch_id);
                })
                ->where(function ($query) use($request){
                    $query->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id);
                })
                ->whereIn('order_master.is_paid', [1, 2])
                ->where('order_master.paid_amount', '<', 700)
                ->whereNotIn('suggested_treatments.treatment_id', function ($query) {
                    $query->select('treatments.treatment_id')
                        ->from('treatments')
                        ->whereColumn('treatments.treatment_id', 'suggested_treatments.treatment_id')
                        ->whereNotIn('treatments.name', ['X ray', 'consultation', 'Medicines']);
                })
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')
                ->get();*/
			/*$NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    DB::raw('(SELECT treatments.name FROM treatments WHERE treatments.treatment_id = suggested_treatments.treatment_id) as treatmentName'),
                    DB::raw('(SELECT doctors.name FROM doctors WHERE doctors.doctor_id = suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->distinct()
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where(function ($query) use ($request) {
                    $query->where('patients.clinic_id', $request->clinic_id)
                          ->where('patients.branch_id', $request->branch_id);
                })
                ->where(function ($query) use ($request) {
                    $query->where('order_master.clinic_id', $request->clinic_id)
                          ->where('order_master.branch_id', $request->branch_id);
                })
                ->whereIn('order_master.is_paid', [1, 2])
                ->where('order_master.paid_amount', '<', 700)
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->orderByDesc('patients.patient_id')
                ->get();*/
            $NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    DB::raw('(SELECT treatments.name FROM treatments WHERE treatments.treatment_id = suggested_treatments.treatment_id) as treatmentName'),
                    DB::raw('(SELECT doctors.name FROM doctors WHERE doctors.doctor_id = suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
                    DB::raw('MAX(order_master.net_amount) as net_amount'),
                    DB::raw('MAX(order_master.paid_amount) as paid_amount'),
                    DB::raw('MAX(order_master.due_amount) as due_amount')
                )
                ->leftJoin('order_master', function ($join) use ($request) {
                    $join->on('patients.patient_id', '=', 'order_master.patient_id')
                        ->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id)
                        ->whereIn('order_master.is_paid', [1, 2]);
                })
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->whereBetween(DB::raw('DATE(patients.created_at)'), [$request->fromDate, $request->toDate])
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->havingRaw('MAX(order_master.paid_amount) < 700') // 👈 count query jevu same logic
                ->orderByDesc('patients.patient_id')
                ->get();
			DB::commit(); 
			$arr = [];
			if(count($NoOfStarted) != 0){
				foreach($NoOfStarted as $NoOfStartData){
					$arr[] = array(
						"patient_id" => $NoOfStartData->patient_id,
						"patient_name" => $NoOfStartData->name_prefix." ".$NoOfStartData->name,
						"treatmentName" => "", //$NoOfStartData->treatmentName,
						"DoctorName" => "",//$NoOfStartData->DoctorName,
						"total_amount" => $NoOfStartData->net_amount,
						"paid_amount" => $NoOfStartData->paid_amount,
						"due_amount" => $NoOfStartData->due_amount
					); 
						
				}
				DB::rollBack();
				return response()->json([
					'status' => 'success',
					'NoOfStartDataList' => $arr
				]);
				
			}else{
			    DB::rollBack();
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'NoOfStartDataLis' => $arr
				]);
			}
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	
	public function NoOfStartedGreaterThen700list(Request $request){
		if(Auth::user()){
            DB::beginTransaction();
            /*$NoOfStarted = Patient::select('patients.patient_id','patients.name_prefix','patients.name','order_master.net_amount', 'order_master.paid_amount','order_master.due_amount')
				->leftjoin('order_master','patients.patient_id','=','order_master.patient_id')
				->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where(['order_master.clinic_id' =>$request->clinic_id, 'order_master.branch_id' =>$request->branch_id])
				->whereIn('order_master.is_paid',[1,2])
				->where('paid_amount','>',700)
				->whereNotIn('suggested_treatments.treatment_id', function($query){
    				$query->select('treatments.treatment_id')
    				->from(with(new Treatments)->getTable())
    				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
    				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
    			})
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				->orderBy('patients.patient_id', 'desc')
				->get();*/
			/*$NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where(function ($query) use($request) {
                    $query->where('patients.clinic_id', $request->clinic_id)
                        ->where('patients.branch_id', $request->branch_id);
                })
                ->where(function ($query) use($request){
                    $query->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id);
                })
                ->whereIn('order_master.is_paid', [1, 2])
                ->where('order_master.paid_amount', '>', 700)
                ->whereNotIn('suggested_treatments.treatment_id', function ($query) {
                    $query->select('treatments.treatment_id')
                        ->from('treatments')
                        ->whereColumn('treatments.treatment_id', 'suggested_treatments.treatment_id')
                        ->whereNotIn('treatments.name', ['X ray', 'consultation', 'Medicines']);
                })
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')
                ->get();*/
            /*$NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    DB::raw('(SELECT treatments.name FROM treatments WHERE treatments.treatment_id = suggested_treatments.treatment_id) as treatmentName'),
                    DB::raw('(SELECT doctors.name FROM doctors WHERE doctors.doctor_id = suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->distinct()
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where(function ($query) use ($request) {
                    $query->where('patients.clinic_id', $request->clinic_id)
                          ->where('patients.branch_id', $request->branch_id);
                })
                ->where(function ($query) use ($request) {
                    $query->where('order_master.clinic_id', $request->clinic_id)
                          ->where('order_master.branch_id', $request->branch_id);
                })
                ->whereIn('order_master.is_paid', [1, 2])
                ->where('order_master.paid_amount', '>', 700)
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->orderByDesc('patients.patient_id')
                ->get();*/
            $NoOfStarted = DB::table('patients')
                ->select(
                    'patients.patient_id',
                    'patients.name_prefix',
                    'patients.name',
                    DB::raw('(SELECT treatments.name FROM treatments WHERE treatments.treatment_id = suggested_treatments.treatment_id) as treatmentName'),
                    DB::raw('(SELECT doctors.name FROM doctors WHERE doctors.doctor_id = suggested_treatments.SuggestedBydoctor_id) as DoctorName'),
                    DB::raw('MAX(order_master.net_amount) as net_amount'),
                    DB::raw('MAX(order_master.paid_amount) as paid_amount'),
                    DB::raw('MAX(order_master.due_amount) as due_amount')
                )
                ->leftJoin('order_master', function ($join) use ($request) {
                    $join->on('patients.patient_id', '=', 'order_master.patient_id')
                        ->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id)
                        ->whereIn('order_master.is_paid', [1, 2]);
                })
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->whereBetween(DB::raw('DATE(patients.created_at)'), [$request->fromDate, $request->toDate])
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->havingRaw('MAX(order_master.paid_amount) >= 700') // 👈 important change
                ->orderByDesc('patients.patient_id')
                ->get();
			
			DB::commit(); 
			$arr = [];
			if(count($NoOfStarted) != 0){
				foreach($NoOfStarted as $NoOfStartData){
					$arr[] = array(
						"patient_id" => $NoOfStartData->patient_id,
						"patient_name" => $NoOfStartData->name_prefix." ".$NoOfStartData->name,
						"treatmentName" => "", //$NoOfStartData->treatmentName,
						"DoctorName" => "",//$NoOfStartData->DoctorName,
						"total_amount" => $NoOfStartData->net_amount,
						"paid_amount" => $NoOfStartData->paid_amount,
						"due_amount" => $NoOfStartData->due_amount
					); 
						
				}
				DB::rollBack();
				return response()->json([
					'status' => 'success',
					'NoOfStartDataList' => $arr
				]);
				
			}else{
			    DB::rollBack();
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'NoOfStartDataLis' => $arr
				]);
			}
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function noBillGenratedlist(Request $request){
		if(Auth::user()){
		    DB::beginTransaction();
            $NoOfStarted = DB::table('patients')
                ->select(
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
                    'order_master.net_amount',
                    'order_master.paid_amount',
                    'order_master.due_amount'
                )
                ->distinct()
                ->leftJoin('order_master', function ($join) use ($request) {
                    $join->on('patients.patient_id', '=', 'order_master.patient_id')
                         ->where('order_master.clinic_id', $request->clinic_id)
                         ->where('order_master.branch_id', $request->branch_id)
                         ->whereIn('order_master.is_paid', [1, 2]);
                })
                ->join('groups', 'patients.group_id', '=', 'groups.group_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('order_master.patient_id') // Ensures no bill is generated
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')
                ->get();
			
			DB::commit(); 
			$arr = [];
			if(count($NoOfStarted) != 0){
				foreach($NoOfStarted as $NoOfStartData){
					$arr[] = array(
						"patient_id" => $NoOfStartData->patient_id,
						"patient_name" => $NoOfStartData->name_prefix." ".$NoOfStartData->name,
					    'clinic_id' => $NoOfStartData->clinic_id,
    					'branch_id' => $NoOfStartData->branch_id,
    					'doctor_id' => $NoOfStartData->doctor_id,
    					'group_id'=> $NoOfStartData->group_id,
    					'case_no' => $NoOfStartData->case_no,
    					'email' => $NoOfStartData->email,
    					'date_of_birth' => $NoOfStartData->date_of_birth,
    					'address' => $NoOfStartData->address,
    					'mobile_no' => $NoOfStartData->mobile_no,
    					'gender' => $NoOfStartData->gender,
    					'occumpation'=> $NoOfStartData->occumpation,
    					'language' => $NoOfStartData->language,
    					'note' => $NoOfStartData->note,
    					'created_at' => $NoOfStartData->created_at,
    					'group_name' => $NoOfStartData->group_name, 
						"treatmentName" => "",
						"DoctorName" => "",
						"total_amount" => $NoOfStartData->net_amount,
						"paid_amount" => $NoOfStartData->paid_amount,
						"due_amount" => $NoOfStartData->due_amount
					); 
						
				}
				DB::rollBack();
				return response()->json([
					'status' => 'success',
					'NoOfStartDataList' => $arr
				]);
				
			}else{
			    DB::rollBack();
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'NoOfStartDataLis' => $arr
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
