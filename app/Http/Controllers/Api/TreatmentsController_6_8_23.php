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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

					$treatmentData = Treatments::where("name",trim($request->name))->first();
						if(empty($treatmentData)){
								$treatments = Treatments::create([
									'clinic_id' => $request->clinic_id,
									'name' => $request->name,
									'amount' => $request->amount,
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
					$treatmentData = Treatments::where("name",trim($request->name))->where('treatment_id', '<>', $id)->first();
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
					
				
				$patient_treatment_id = $request->patient_treatment_id;
				$suggested_treatment_id = $request->suggested_treatment_id;
				$edit = $request->edit;
				$discount_type = $request->discount_type;
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
									
									if(!$treatmentDataexist){
										
											$treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
											$treatmentName = $treatmentData->name;
											$amount = $treatmentData->amount;
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
				
				}elseif((!empty($patient_treatment_id) && !empty($edit)) || ($suggested_treatment_id != 0)){
					
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
							'selected_teeth' => $request->selected_teeth,
							'selected_teeth_count' => $selectedTeethcount,
							'rate' => $request->rate,
							'discount' => $discountperornum,
							'discount_type' => $discount_type,
							'discount_amount' => $discount,
							'total_amount' => $request->total_amount,
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
				}
				else{
					
					$patientTreatments = patientTreatments::create([
					
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date,
						
					]);
					$lastInsertpatientTreatmentsID = $patientTreatments->patient_treatment_id;
					
				}
				
				
						foreach ($treatment_ids_array as $treatment_id) {
					
									$treatmentData = Treatments::where('treatment_id',$treatment_id)->first();
									$treatmentName = $treatmentData->name;
									$amount = $treatmentData->amount;
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
										'ref_id' => $random."-".$request->patient_id,
										'is_completed_by_doctorId' => $request->is_completed_by_doctorId,
										'completed_datetime' => $request->completed_datetime,
										'strnote' =>$note,
										
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
		
		//update patienttreatment
		public function updatepatienttreatment(Request $request,$id)
		{
				if(Auth::user()){
								
								$patientTreatments = patientTreatments::where(['patient_treatment_id'=>$id])->update([
										'doctor_id' => $request->doctor_id,
										'treatment_date' => $request->treatment_date
									]);
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
					
					/* if(empty($request->amount)){
						$request->amount = 0;
					} */

					if(empty($request->rate)){
						$request->rate = 0;
					}	
					$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$id)->update([
							'SuggestedBydoctor_id' => $request->SuggestedBydoctor_id,
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
					'suggested_treatments.SuggestedBydoctor_id',
					'suggested_treatments.suggested_treatment_id',
					'users.user_name as doctor_name'
					)
					->where(['suggested_treatments.patient_id' => $id,'suggested_treatments.clinic_id' => $clinic_id])
					->join('users', 'suggested_treatments.SuggestedBydoctor_id', '=', 'users.user_id')
					->groupBy('suggested_treatments.treatment_date')
					->orderBy('suggested_treatments.treatment_date', 'desc')
					->get();
				
					$arr = [];
					
					foreach($treatmentData as $TreatmentData){
						
						$treatment_date = $TreatmentData->treatment_date;
						
						 $treatmentList = SuggestedTreatments::where(['treatment_date' => $TreatmentData->treatment_date,'patient_id' =>$id])
						->get();
						$arr[] = array(
							"treatment_date" => $TreatmentData->treatment_date,
							"SuggestedBydoctor_id" => $TreatmentData->SuggestedBydoctor_id,
							"doctor_name" => $TreatmentData->doctor_name,
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
		
		public function getpatienttreatmentbyId(Request $request, $id){
			
			if(Auth::user()){
				
					$treatmentData = SuggestedTreatments::select(
						'users.user_name as doctor_name',
						'suggested_treatments.treatment_date',
						'suggested_treatments.SuggestedBydoctor_id',
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
					->join('users', 'suggested_treatments.SuggestedBydoctor_id', '=', 'users.user_id')
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
			
			$orgDate = $request->selected_date;
			$patient_id = $request->patient_id;
			$branch_id = $request->branch_id;
			$newDate = date("Y-m-d", strtotime($orgDate));
			$current_date = $request->current_date;
			
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
					'suggested_treatments.branch_id' => $branch_id])
					
				->when($request->selected_date, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
				})
				->when($request->current_date, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->current_date."','%Y-%m-%d')"));
				})
					->join('treatments', 'suggested_treatments.treatment_id', '=', 'treatments.treatment_id')
					->get();
					
				$treatmentDatatoalsum = SuggestedTreatments::select(

						DB::raw('sum(total_amount) as totalcost')
						
						)
					->where(['suggested_treatments.is_billing' => 0, 'suggested_treatments.patient_id' => $patient_id ,
					'suggested_treatments.branch_id' => $branch_id])
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
									'discount' => $discount,
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
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function allTreatment(Request $request){
				$treatments = new Treatments();
				
				  $allTreatmentslist = Treatments::select(
					 
					'treatment_id',
					'name',
					'amount',
					'deleted_at',
					'created_at',
					'updated_at'
					)
					->where(['clinic_id' => $request->clinic_id])
					 ->when($request->name, function ($query) use ($request) {
                        $query->where('name','LIKE', '%' . $request->name .'%');
                    })
					->get();
					

				return response()->json(['Treatments' => $allTreatmentslist]);
		
		}
		
		
}
