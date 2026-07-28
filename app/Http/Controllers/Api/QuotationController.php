<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Treatments;
use Illuminate\Validation\Rule;
use App\Models\Quotation;
use App\Models\OrderMaster;
use App\Models\OrderDetail;
use App\Models\quotationTreatments;
use App\Models\patientTreatments;
use App\Models\SuggestedTreatments;
use App\Models\BranchCaseNumber;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Services\AuthkeyWhatsAppService;

class QuotationController extends Controller
{
		
			//add treatment screen with multiple treatment select and edit
		public function addQuotation(Request $request)
		{
				if(Auth::user()){
					
				if(!empty($request->istatus) && isset($request->istatus)){
					$istatus = $request->istatus;
				}else{
					$istatus = 0;
				}
				$quotation_treatment_id = $request->quotation_treatment_id;
				$quotation_id = $request->quotation_id;
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
				if(!empty($quotation_treatment_id) && empty($edit)){
					
					//one treatment added using edit and another one saved directry without edit
					foreach ($treatment_ids_array as $treatment_id) {
									
									$treatmentDataexist = Quotation::where(['treatment_id' => $treatment_id,
									'quotation_treatment_id' => $quotation_treatment_id])->first();
									//print_r($treatmentDataexist->suggested_treatment_id);
									
									if($treatmentDataexist){
										$quotationalreadyexist = $treatmentDataexist->quotation_id;
									
										$SuggestedTreatmentUpdateStatus = Quotation::where('quotation_id','=',$quotationalreadyexist)->update([
										'istatus' => 1,
										'treatment_date' => $request->treatment_date,
										'quotation_name' => $request->quotation_name
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
											$sTreatment = Quotation::create([
											
												'quotation_treatment_id' => $quotation_treatment_id,
												'quotation_name' => $request->quotation_name,
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
						
									
					$quotationTreatments = quotationTreatments::where(['quotation_treatment_id'=>$quotation_treatment_id])->update([
										'doctor_id' => $request->SuggestedBydoctor_id,
										'treatment_date' => $request->treatment_date
									]);
					return response()->json(['status' => 'success',
											'message' => 'Quotation treatment Updated Successfully.'
					]);
				
				}elseif((!empty($quotation_treatment_id) && !empty($edit)) || ($quotation_id != 0)){
					
					if($quotation_id != 0){
						
						$selectedTeeth = $request->selected_teeth;
						$selectedTeeth_array = explode(",", $selectedTeeth);
						$selectedTeethcount = count($selectedTeeth_array);
						$totalAmount = $request->total_amount;
						
						$quotationTreatmentData = Quotation::where('quotation_id','=',$quotation_id)->first();
						$amount = $quotationTreatmentData->amount;
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
						
						
						$QuotationTreatments = Quotation::where('quotation_id','=',$quotation_id)->update([
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
						
						$QuotationTreatmentsUpdated = Quotation::select('*')
									->where('quotation_id', '=', $quotation_id)
									->first();	
									
						return response()->json(['status' => 'success',
							'message' => 'Patient quotation Updated Successfully.','suggested_treatments' => $QuotationTreatmentsUpdated]);
					}
					
						$patientTreatments = quotationTreatments::where(['quotation_treatment_id'=>$quotation_treatment_id])->update([
										'doctor_id' => $request->SuggestedBydoctor_id,
										'treatment_date' => $request->treatment_date
									]);
									
						$lastInsertpatientTreatmentsID = $quotation_treatment_id;
				}
				else{
					
					$patientTreatments = quotationTreatments::create([
					
						'doctor_id' => $request->SuggestedBydoctor_id,
						'treatment_date' => $request->treatment_date,
						
					]);
					$lastInsertpatientTreatmentsID = $patientTreatments->quotation_treatment_id;
					
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
									$sTreatment = Quotation::create([
									
										'quotation_treatment_id' => $lastInsertpatientTreatmentsID,
										'quotation_name' => $request->quotation_name,
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
										'strnote' =>$note,
										
									]);
						}
				
				return response()->json([
					'status' => 'success',
					'message' => 'Quotation created successfully',
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
								
								$patientTreatments = quotationTreatments::where(['quotation_treatment_id'=>$id])->update([
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
		public function updatequotation(Request $request,$id)
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
					$SuggestedTreatments = Quotation::where('quotation_id','=',$id)->update([
							'quotation_name' => $request->quotation_name,
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
					

							return response()->json(['status' => 'success','message' => 'Quotation Updated Successfully.']);

				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
			
		}
		
		
		//destroy patient treatment
		public function destroyquotation($id)
				{
				   if(Auth::user()){

					 $data = Quotation::where('quotation_id',$id)->count();
						if($data){
							$TreatmentdesDelete = Quotation::find($id);
							$TreatmentdesDelete->delete();
							
							return response()->json([
								'status' => 'success',
								'message' => 'Quotation deleted Successfully.',]);
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
		
		
		public function getallquotation(Request $request, $id){
			$clinic_id = $request->clinic_id;
			if(Auth::user()){
					
					
					$treatmentData = Quotation::select(
					'quotation.treatment_date',
					'quotation.quotation_name',
					'quotation.treatmentBydoctor_id',
					'quotation.quotation_id',
					'quotation.created_at'
					//'users.user_name as doctor_name'
					)
					->where(['quotation.patient_id' => $id,'quotation.clinic_id' => $clinic_id,'quotation.istatus' => 1])
					//->join('users', 'quotation.treatmentBydoctor_id', '=', 'users.user_id')
					//->groupBy(['quotation.treatment_date',DB::raw('hour(quotation.created_at)')])
					->groupBy(['quotation.quotation_name'])
					->orderBy('quotation.treatment_date', 'desc')
					->get();
			
					$arr = [];
					
					foreach($treatmentData as $TreatmentData){
						
						$treatment_date = $TreatmentData->treatment_date;
						
						 $treatmentList = Quotation::where([
						 'treatment_date' => $TreatmentData->treatment_date,
						 //'created_at' => $TreatmentData->created_at,
						 'patient_id' =>$id,
						 'istatus' => 1,
						// 'treatmentBydoctor_id' => $TreatmentData->treatmentBydoctor_id,
						 ])
						// ->where(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H')"), DB::raw("DATE_FORMAT('".$TreatmentData->created_at."','%Y-%m-%d %H')"))
						 ->where(['quotation_name' =>$TreatmentData->quotation_name])
						->get();
						
						$quotationIdArray = [];
						foreach($treatmentList as $TreatmentList){
							
							$quotationIdtemp = $TreatmentList->quotation_id;
							array_push($quotationIdArray, $quotationIdtemp);
						}
						
						
						 $arr[] = array(
							"treatment_date" => $TreatmentData->treatment_date,
							"treatmentBydoctor_id" => $TreatmentData->treatmentBydoctor_id,
							"quotation_name" => $TreatmentData->quotation_name,
							"treatmentList" => $treatmentList,
							"quatationIdarray" => $quotationIdArray
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
		public function getquotationbydate(Request $request){
			
			$patientId = $request->patient_id;
			$clinicId = $request->clinic_id;
			$doctorId = $request->SuggestedBydoctor_id;
			$originalTreatmentDate = $request->treatment_date;
			$quotation_name = $request->quotation_name;
			
			$treatmentDate = date("Y-m-d", strtotime($originalTreatmentDate));
			if(Auth::user()){
					
					
					$treatmentData = Quotation::select(
					'quotation.*',
					//'users.user_name as doctor_name'
					)
					->where(['quotation.patient_id' => $patientId,'quotation.clinic_id' => $clinicId,
					'quotation.istatus' => 1,
					//'quotation.SuggestedBydoctor_id' => $doctorId,
					'quotation.treatment_date' => $treatmentDate,
					'quotation_name' => $quotation_name
					
					])
					//->join('users', 'quotation.SuggestedBydoctor_id', '=', 'users.user_id')
					->orderBy('quotation.treatment_date', 'desc')
					->get();

						return response()->json([
							'status' => 'success',
							'quotationtdata' => $treatmentData
						]);

					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		public function getquotationbyId(Request $request, $id){
			
			if(Auth::user()){
				
					$treatmentData = Quotation::select(
						'users.user_name as doctor_name',
						'quotation.treatment_date',
						'quotation.SuggestedBydoctor_id',
						'quotation.treatmentBydoctor_id',
						'quotation.rate',
						'quotation.discount',
						'quotation.discount_type',
						'quotation.total_amount',
						'quotation.discount_amount',
						'quotation.amount',
						'quotation.selected_teeth',
						'quotation.treatment_status',
						'quotation.strnote',
						'quotation.treatment_date',
						'treatments.name as treatment_name'
						
						)
					->where('quotation.quotation_id', '=', $id)
					->join('users', 'quotation.treatmentBydoctor_id', '=', 'users.user_id')
					->join('treatments', 'quotation.treatment_id', '=', 'treatments.treatment_id')
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

		
		/* public function updateStatus(Request $request, $id){
			
			$treatment_status = $request->treatment_status;
			if(Auth::user()){
				
				if($request->treatment_status == 2){
						$is_completed_by_doctorId = $request->SuggestedBydoctor_id;
						$completed_datetime = date('d-m-y h:i:s');
					}else{
						$is_completed_by_doctorId = NULL;
						$completed_datetime = NULL;
					}
				
				$SuggestedTreatments = Quotation::where('suggested_treatment_id','=',$id)->update([

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
		} */
		
		public function quotationDataForPrintScreen(Request $request){
				$treatments = new Treatments();
				
				  $quotationTreatmentIds = $request->quotationIds;
				  $clinic_id = $request->clinic_id;
				  $branch_id = $request->branch_id;
				  $patient_id = $request->patient_id;
				  $whatsapp = $request->whatsapp;

				  $patientData = Patient::where(['patient_id'=> $patient_id])->first();
				  $patientMobileNo = $patientData->mobile_no;
				  $netAmount = 0;
				  $discountAmount = 0;
				  $totalAmount = 0;
				  foreach($quotationTreatmentIds as $quotationTreatmentId){


						$TreatmentData = Quotation::select(
						'users.user_name as doctor_name',
						'users.address as address',
						'patients.name_prefix as name_prefix',
						'patients.name as patient_name',
						'patients.mobile_no as mobile_no',
						'patients.case_no as case_no',
						'patients.address as patient_address',
						 DB::raw('DATE_FORMAT(quotation.treatment_date, "%d-%M-%Y") as treatment_date'),
						'treatments.name as treatment_name',
						'quotation.quotation_name',
						'quotation.selected_teeth',
						'quotation.selected_teeth_count',
						'quotation.rate',
						'quotation.discount',
						'quotation.discount_type',
						'quotation.total_amount',
						'quotation.discount_amount',
						'quotation.amount',
						'quotation.treatment_status',
						'quotation.strnote'
						
						)
						->where(['quotation.quotation_id' => $quotationTreatmentId])
						->join('users', 'quotation.SuggestedBydoctor_id', '=', 'users.user_id')
						->join('treatments', 'quotation.treatment_id', '=', 'treatments.treatment_id')
						->join('patients', 'quotation.patient_id', '=', 'patients.patient_id')
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
				  
				// 		$key = $_ENV['WHATSAPPKEY'];
				// 		$msg = "Dear User, Please find attached quotation.";
						
						$pdf = PDF::loadView('quotationinvoice',['Treatments' => $arr,'netAmount' => $netAmount,'discountAmount' => $discountAmount,'totalAmount' => $totalAmount]);
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/quotation/'.$fileName . '.pdf',$content);
						
						//$pdf->save(public_path('assets/quotation/')  . $fileName. '.pdf');
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/quotation/')  . $fileName. '.pdf');
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/quotation/')  . $fileName. '.pdf');

						}

						$billFile = asset('assets/quotation/'. $fileName. '.pdf');
						//return $pdf->download($fileName . '.pdf');
						$billFile1 = $fileName. '.pdf';
						if($whatsapp == 1){
							
							$users = new User();
				// 			$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$billFile);
				
    						$whatsappService = new AuthkeyWhatsAppService();
            				$wid = "28846"; // template id
            				// $PatientName = $request->name_prefix . " " . $request->name;
            				// $bodyValues = [
            				// 	"1" => $PatientName
            				// ];
            				
            				$statusofMessage = $whatsappService->sendMedia($patientMobileNo, $wid, $billFile1, $bodyValues=[]);
							
							
							// $Response = $status->response;
						
							if($statusofMessage == "Success"){
								return response()->json([
									'status' => 'success',
									'pdfFileUrl' => $billFile,
									'message' => 'Quotation sent on your registered mobile number.',
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
								'pdfFileUrl' => $billFile,
								'Treatments' => $arr
							], 401);
							
						}
		
		}
		
				
		public function treatmentpdfFilelink(Request $request){
				$treatments = new Treatments();
				
				  $quotationTreatmentIds = $request->quotationIds;
				  $clinic_id = $request->clinic_id;
				  $branch_id = $request->branch_id;
				  $patient_id = $request->patient_id;

				  $patientData = Patient::where(['patient_id'=> $patient_id])->first();
				  $patientMobileNo = $patientData->mobile_no;
				  $netAmount = 0;
				  $discountAmount = 0;
				  $totalAmount = 0;
				  foreach($quotationTreatmentIds as $quotationTreatmentId){


						$TreatmentData = Quotation::select(
						'users.user_name as doctor_name',
						'users.address as address',
						'patients.name_prefix as name_prefix',
						'patients.name as patient_name',
						'patients.mobile_no as mobile_no',
						'patients.case_no as case_no',
						'patients.address as patient_address',
						 DB::raw('DATE_FORMAT(quotation.treatment_date, "%d-%M-%Y") as treatment_date'),
						'treatments.name as treatment_name',
						'quotation.selected_teeth',
						'quotation.selected_teeth_count',
						'quotation.rate',
						'quotation.discount',
						'quotation.discount_type',
						'quotation.total_amount',
						'quotation.discount_amount',
						'quotation.amount',
						'quotation.treatment_status',
						'quotation.strnote'
						
						)
						->where(['quotation.suggested_treatment_id' => $suggestedTreatmentId])
						->join('users', 'quotation.SuggestedBydoctor_id', '=', 'users.user_id')
						->join('treatments', 'quotation.treatment_id', '=', 'treatments.treatment_id')
						->join('patients', 'quotation.patient_id', '=', 'patients.patient_id')
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
						 

						$pdf = PDF::loadView('quotationinvoice',['Treatments' => $arr,'netAmount' => $netAmount,'discountAmount' => $discountAmount,'totalAmount' => $totalAmount]);
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/quotation/'.$fileName . '.pdf',$content);
						
						//$pdf->save(public_path('assets/quotation/')  . $fileName. '.pdf');
						
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/quotation/')  . $fileName. '.pdf');

						$billFile = asset('assets/quotation/'. $fileName. '.pdf');
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
		
		
		public function movetosuggestedtreatments(Request $request){
			
			if(Auth::user()){
				
				  $quotationIds = $request->quotationIds;
				  $clinic_id = $request->clinic_id;
				  $branch_id = $request->branch_id;
				  $patient_id = $request->patient_id;

				  $patientData = Patient::where(['patient_id'=> $patient_id])->first();
				  $patientMobileNo = $patientData->mobile_no;
				  
				  
				  
				  foreach($quotationIds as $quotationId){

						$QuotationData = Quotation::where(['quotation_id' => $quotationId])->first();
						
										$quotation_treatment_id = $QuotationData->quotation_treatment_id;
										$treatment_name = $QuotationData->treatment_name;
										$treatment_id = $QuotationData->treatment_id;
										$clinic_id = $QuotationData->clinic_id;
										$branch_id = $QuotationData->branch_id;
										$patient_id = $QuotationData->patient_id;
										$SuggestedBydoctor_id = $QuotationData->SuggestedBydoctor_id;
										$treatmentBydoctor_id = $QuotationData->treatmentBydoctor_id;
										$rate = $QuotationData->rate;
										$selected_teeth = $QuotationData->selected_teeth;
										$amount = $QuotationData->amount;
										$discount = $QuotationData->discount;
										$discount_type = $QuotationData->discount_type;
										$discount_amount = $QuotationData->discount_amount;
										$total_amount = $QuotationData->total_amount;
										$selected_teeth_count = $QuotationData->selected_teeth_count;
										$treatment_status = $QuotationData->treatment_status;
										$treatment_date = $QuotationData->treatment_date;
										$is_billing = $QuotationData->is_billing;
										$ref_id = $QuotationData->ref_id;
										$is_completed_by_doctorId = $QuotationData->is_completed_by_doctorId;
										$completed_datetime = $QuotationData->completed_datetime;
										$strnote = $QuotationData->strnote;
										$istatus = $QuotationData->istatus;
						
						
						$quotationTreatmentsData = quotationTreatments::where('quotation_treatment_id',$quotation_treatment_id)->first();
						
										$doctor_id = $quotationTreatmentsData->doctor_id;
										$treatment_date = $quotationTreatmentsData->treatment_date;
						


						$patientTreatments = patientTreatments::create([
					
									'doctor_id' => $doctor_id,
									'treatment_date' => $treatment_date,
						
							]);
							
						$lastInsertpatientTreatmentsID = $patientTreatments->patient_treatment_id;
										
						$sTreatment = SuggestedTreatments::create([
											
												'patient_treatment_id' => $lastInsertpatientTreatmentsID,
												'treatment_id' => $treatment_id,
												'clinic_id' => $clinic_id,
												'patient_id' => $patient_id,
												'branch_id' => $branch_id,
												'SuggestedBydoctor_id' => $SuggestedBydoctor_id,
												'treatmentBydoctor_id' => $treatmentBydoctor_id,
												'rate' => $rate,
												'selected_teeth' => $selected_teeth,
												'amount' => $amount,
												'discount' => $discount,
												'discount_amount' => $discount_amount,
												'discount_type' => $discount_type,
												'total_amount' => $total_amount,
												'treatment_name' => $treatment_name,
												'selected_teeth_count' => $selected_teeth_count,
												'treatment_status' => $treatment_status,
												'treatment_date' => $treatment_date,
												'is_billing' => $is_billing,
												'istatus' => $istatus,
												'ref_id' => $ref_id,
												'is_completed_by_doctorId' => $is_completed_by_doctorId,
												'completed_datetime' => $completed_datetime,
												'strnote' =>$strnote,
												
											]);

					}
					
					return response()->json([
							'status' => 'success',
							'message' => 'Moved to treatment successfully.',
						], 401);
					
					
			
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		/* public function getallpatienttreatmentforlabwork(Request $request){
			
			
			if(Auth::user()){
				
				$clinic_id = $request->clinic_id;
				$patient_id = $request->patient_id;
				$branch_id = $request->branch_id;
				$treatmentDataLabwork = Quotation::select(
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

		} */
		

		
		/* public function allTreatmentLabWorkWise(Request $request){
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
		
		} */
	
	public function deleteQuatation(Request $request){
		if(Auth::user()){
			$quotationIds = $request->quotationIds;
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$patient_id = $request->patient_id;
			$quata = Quotation::where(["clinic_id" => $clinic_id,"patient_id" => $patient_id,"branch_id" => $branch_id])
				->whereIn("quotation_id",$quotationIds)->get();
			if(!empty($quata)){
				if(!empty($quotationIds)){
					foreach($quotationIds as $qid){
						$quatation = Quotation::where(["quotation_id" => $qid,"clinic_id" => $clinic_id,"patient_id" => $patient_id,"branch_id" => $branch_id])->first();
						if(!empty($quatation)){
							quotationTreatments::where("quotation_treatment_id",$quatation->quotation_treatment_id)->delete();
							Quotation::where(["quotation_id" => $qid,"clinic_id" => $clinic_id,"patient_id" => $patient_id,"branch_id" => $branch_id])->delete();
						}
					}
					return response()->json([
						'status' => 'success',
						'message' => 'Delete Successfully.',
					]);
				} else {
					return response()->json([
						'status' => 'error',
						'message' => 'No Quatation Found.',
					], 401);
				}	
			} else {
				return response()->json([
					'status' => 'error',
					'message' => 'No Quatation Found.',
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
