<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderMaster;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\SuggestedTreatments;
use App\Models\SuggestedTreatmentPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class OrderMasterController extends Controller
{
	
			//information to show on add payment screen
		public function getpayment(Request $request){
			
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$patient_id = $request->patient_id;
			$order_id = $request->order_id;
			$payment_sms = $request->payment_sms;
									
			if(Auth::user()){

						
				$netAmountofallunpaidOrders = OrderMaster::select(
							DB::raw('sum(net_amount) as net_amount')
							)
							->where(['is_paid' => 0,'branch_id' => $branch_id,'patient_id' => $patient_id,'istatus' =>0])
							->first();
							
							
				$dueAmountlastAlreadyPaidOrder = OrderMaster::select(
							DB::raw('sum(due_amount) as due_amount'),
							DB::raw('sum(paid_amount) as paid_amount')
							)
							->where(['is_paid' => 1,'branch_id' => $branch_id,'patient_id' => $patient_id, 'istatus' =>0])
							->get();
				
				$totalAmountforCount = OrderMaster::select(
							DB::raw('sum(net_amount) as availableAdvance')
							)
							->whereNot('is_paid', 2)
							//->whereNot('order_master_id', $order_id)
							->where(['branch_id' => $branch_id,'patient_id' => $patient_id, 'istatus' =>0])
							->get()->toArray();
							
				$total_amount = 0;
				$net_amount = 0;
				$due_amount = 0;
				

				if(!empty($netAmountofallunpaidOrders['net_amount'])){
					$net_amount = $netAmountofallunpaidOrders['net_amount'];
				}
				if(!empty($dueAmountlastAlreadyPaidOrder[0]['due_amount'])){
					$due_amount = $dueAmountlastAlreadyPaidOrder[0]['due_amount'];
				}else{
					$due_amount = 0;
				}
				
				if(!empty($dueAmountlastAlreadyPaidOrder[0]['paid_amount'])){
					$paid_amount = $dueAmountlastAlreadyPaidOrder[0]['paid_amount'];
				}else{
					$paid_amount = 0;
				}
				
				//echo $total_amount;
				//die;
			
				$totalPayableAmount = $net_amount+$due_amount;
				
				if(!empty($totalAmountforCount[0]['availableAdvance'])){
					$totalAmountforCount = $totalAmountforCount[0]['availableAdvance'];
				}else{
					$totalAmountforCount = 0;
				}
				
				
				//die;
				return response()->json([
					'status' => 'success',
					'TotalPayableAmount' => $totalPayableAmount,
					'order_id' => $order_id,
					'Totalamount' => $totalAmountforCount,
					'Paidamount'=> $paid_amount,
					'Dueamount'=> $due_amount
				]);
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		//get treatment data order wise
		public function gettreatmentdatabyOrderId(Request $request){
			
			
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$patient_id = $request->patient_id;
			$order_id = $request->order_id;

			if(Auth::user()){
				
				
				//all suggested treatment set is billing 0 
						$treatmentviewlistList = OrderDetail::where(['order_id' => $request->order_id])
						->get();
						
						
						
						$arr = [];
						
						foreach($treatmentviewlistList as $TreatmentviewlistList){
							
							$suggestedTreatmentId = $TreatmentviewlistList->suggested_treatment_id;
							$orderdetailAmount = $TreatmentviewlistList->amount;
							$orderdetailDiscount = $TreatmentviewlistList->discount;
							if($orderdetailDiscount == "0.00"){
								$orderdetailDiscount = 0;
							}
							$orderdetailAmountAfterDiscount = $orderdetailAmount - $orderdetailDiscount;
							
							$SuggestedTreatmentPayment = SuggestedTreatmentPayment::select(
								DB::raw('sum(amount) as amount'),
								'suggested_treatments_id'
							)
							->where(['suggested_treatments_id' => $suggestedTreatmentId])
							->first();
							
							$totalAmountByTreatment = $SuggestedTreatmentPayment->amount;
						
							$SuggestedTreatmentData = SuggestedTreatments::select(
								'suggested_treatments.suggested_treatment_id',
								'suggested_treatments.treatment_id',
								'treatments.name',
								'suggested_treatments.treatmentBydoctor_id',
								'suggested_treatments.total_amount',
								'users.user_name',
								'order_detail.rate',
								'order_detail.amount',
								'order_detail.discount'
							)
							
							->where(['suggested_treatments.suggested_treatment_id' => $suggestedTreatmentId])
							->join('treatments', 'treatments.treatment_id', '=', 'suggested_treatments.treatment_id')
							->join('users', 'users.user_id', '=', 'suggested_treatments.treatmentBydoctor_id')
							->join('order_detail', 'order_detail.suggested_treatment_id', '=', 'suggested_treatments.suggested_treatment_id')
							//->join('suggested_treatment_payment', 'suggested_treatments.suggested_treatment_id', '=', 'suggested_treatment_payment.suggested_treatments_id')
							->orderBy('suggested_treatments.created_at', 'desc')
							->first();
							
							$SuggestedTreatmentPaymentData = SuggestedTreatmentPayment::select(
								DB::raw('sum(suggested_treatment_payment.amount) as amountTotal')
							)
							->where(['suggested_treatments_id' => $suggestedTreatmentId])
							->first();
							
							if(!empty($SuggestedTreatmentPaymentData['amountTotal'])){
								
								$amountTotal = $SuggestedTreatmentPaymentData->amountTotal;
							}else{
								
								$amountTotal = 0;
							}
							
							
							/* echo "-------------";
							echo $totalAmountByTreatment."totalAmountByTreatment";
							echo "-------------";
							echo $orderdetailAmountAfterDiscount."orderdetailAmountAfterDiscount";
							echo "-------------"; */
							if($totalAmountByTreatment >= $orderdetailAmountAfterDiscount){
								
								continue;
								
							}else{
								
								$arr[] = array(
										'suggested_treatment_id' => $SuggestedTreatmentData->suggested_treatment_id,
										'treatment_id' => $SuggestedTreatmentData->treatment_id,
										'treatment_name' => $SuggestedTreatmentData->name,
										'treatmentBydoctor_id' => $SuggestedTreatmentData->treatmentBydoctor_id,
										//'total_amount' => $SuggestedTreatmentData->total_amount,
										'paidamount' => $amountTotal,
										'rate' => $SuggestedTreatmentData->rate,
										'discount' => $SuggestedTreatmentData->discount,
										'doctor_name' => $SuggestedTreatmentData->user_name,
										'treatment_amount_after_discount' => $orderdetailAmountAfterDiscount
								);
							}
							

						}
						///die;
						
						return response()->json([
									'status' => 'success',
									'treatmentdataofOrder' => $arr
								], 401);
				
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
							], 401);
				}
			
			
		}
		
		public function addPayment(Request $request){
			
				if(Auth::user()){
					$actualpaidAmount = round($request->paid_amount);
					$netAmount = $request->net_amount;
					
					if(!empty($request->discount)){
						$paidAmount = round($request->paid_amount);
						$paidAmountwithDiscount = round(($request->paid_amount))+ round(($request->discount));
						
					}else{
						
						$paidAmount = round($request->paid_amount);
						$paidAmountwithDiscount = $paidAmount; 

					}
					//$paidAmountforpaymentdetailentry = $paidAmount;
					$lastOrderList = OrderMaster::where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id, 'istatus' =>0])
					->whereNot('is_paid', 2)
					->first();
					
					$dueAmount = $request->due_amount;
					$newPaidAmountOfpatient = 0;
					$paidAmountafterduepaid = 0;
					$discount = $request->discount;
					
					if(empty($discount))
					{
						$discount = 0;
					}

								$isPaid = $lastOrderList->is_paid;
									
								if($isPaid == 0){
										
										$net_amount = $lastOrderList->net_amount;
										if(!empty($dueAmount) && $dueAmount != "0.00"){
												$due_amount = $dueAmount;
												$isPaid = 1;
											}else{
												$due_amount = 0;
												$isPaid = 2;
											}
							
										if($paidAmountwithDiscount == $net_amount){
											
											//$newPaidAmountOfpatient = $paidAmount-$net_amount;
											
											//if amount paid for perticular order
											$OrderMasterUpdate = OrderMaster::where(['patient_id' => $request->patient_id,
														'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,
														'order_master_id' => $lastOrderList->order_master_id])
														->update([
														'is_paid' => $isPaid,
														'paid_amount' => $paidAmount,
														'discount' => $discount,
														'due_amount' => $due_amount,
														'payment_sms' => $request->payment_sms,
														]);
														
											//$paidAmount = $newPaidAmountOfpatient;
											
											//get all suggested treatment id to make them billing 1
											
											$allsuggestedIdList = OrderDetail::where(['patient_id' => $request->patient_id,
																	'order_id' => $lastOrderList->order_master_id])
																->get();
																
											foreach($allsuggestedIdList as $allsuggestedIdlist){
													
													$OrderMasterUpdate = SuggestedTreatments::where(['is_billing' => 0, 'patient_id' => $request->patient_id,
													'branch_id' => $request->branch_id,'suggested_treatment_id' => $allsuggestedIdlist->suggested_treatment_id])
													->update([
													'is_billing' => 1
													]);
											}
											
										}else{
											
											$newPaidAmountOfpatient = 0;
											
											//if amount paid for perticular order
											$OrderMasterUpdate = OrderMaster::where(['patient_id' => $request->patient_id,
														'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,
														'order_master_id' => $lastOrderList->order_master_id])
														->update([
														'paid_amount' => $paidAmount,
														'due_amount' => $due_amount,
														'discount' => $discount,
														'is_paid' => $isPaid,
														'payment_sms' => $request->payment_sms,
														]);
														
										}
										
										
								}else if($isPaid == 1){
									
									$net_amount = $lastOrderList->net_amount;
									$paidAmountOfCurrentOrder = $lastOrderList->paid_amount;
									
									$paidAmount = $paidAmount + $paidAmountOfCurrentOrder;

									
									$discountOfCurrentOrder = $lastOrderList->discount;
									$discount = $discount + $discountOfCurrentOrder;
									
									
								//	$total_amount = $paidAmountwithDiscount + $due_amount + $discount;
										
										if(!empty($dueAmount) && $dueAmount != "0.00"){
											
												$dueAmountOfCurrentOrder = $lastOrderList->due_amount;
												//$dueAmount = $dueAmount + $dueAmountOfCurrentOrder;
												$isPaid = 1;
											}else{
												
												$dueAmount = 0;
												$isPaid = 2;
											}
										
											
									//if amount paid for perticular order
											$OrderMasterUpdate = OrderMaster::where(['patient_id' => $request->patient_id,
														'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,
														'order_master_id' => $lastOrderList->order_master_id])
														->update([
														'paid_amount' => $paidAmount,
														'due_amount' => $dueAmount,
														'discount' => $discount,
														'is_paid' => $isPaid,
														'payment_sms' => $request->payment_sms,
														]);
								}
								
							
					$paymentOrderDetails = OrderPaymentDetail::create([
							'order_id' => $request->order_id,
							'patient_id' => $request->patient_id,
							'clinic_id' => $request->clinic_id,
							'branch_id' => $request->branch_id,
							'amount' => $actualpaidAmount,
							'payment_mode' => $request->payment_mode,
							'cheque_no' => $request->cheque_no,
							'cheque_date' => $request->cheque_date,
							'bank_name' => $request->bank_name,
							//'comment' => $request->comment,
						]);
						
					
					//add record in new table suggested treatment payment
					$lastpaymentOrderDetailId = $paymentOrderDetails->order_payment_detail_id;
					
					$suggestedTreatmentIds = $request->treatmentIds;
					$treatmentamounts = $request->treatmentamounts;
					$discount = $request->discount;
							  
					$array = array_combine($suggestedTreatmentIds, $treatmentamounts);
						
						
					foreach($array as $suggestedTreatmentId => $treatmentamounts){
							
							$treatmentOrderDetail = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
							->first();
							
							$order_detail_id = $treatmentOrderDetail->order_detail_id;
							$rate = $treatmentOrderDetail->rate;
							$discountoforder = $treatmentOrderDetail->discount;
							$amount = $rate - round($discountoforder);
							if(!empty($discount)){

									$percentageAmount = round((100*$amount)/$netAmount);
									$discountAmttreatmentwise = round(($percentageAmount*$discount)/100);
							}else{
								$discountAmttreatmentwise = 0;
							}
							
							
							$SuggestedTreatmentPaymentDetails = SuggestedTreatmentPayment::create([
										'patient_id' => $request->patient_id,
										'clinic_id' => $request->clinic_id,
										'branch_id' => $request->branch_id,
										'order_id' => $request->order_id,
										'order_detail_id' => $order_detail_id,
										'order_payment_detail_id' => $lastpaymentOrderDetailId,
										'suggested_treatments_id' => $suggestedTreatmentId,
										'amount' => $treatmentamounts
								]);
							
							if($isPaid == 0){
							
							
							$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
										->update([
											'discount' => $discountAmttreatmentwise
										]);
										
							}else if($isPaid == 1){
								
								$totalDiscount = $discountoforder + $discountAmttreatmentwise;
								
								$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
										->update([
											'discount' => $totalDiscount
										]);
							}
							
					}
					
					//add record in new table suggested treatment payment
						
						if($request->payment_sms == 1){
							//send pdf and whatsapp
						}
					
					
					return response()->json([
						'status' => 'success',
						'message' => 'Payment paid Successfully.'
					]);
				}else{
					return response()->json([
										'status' => 'error',
										'message' => 'User is not Authorised.',
								], 401);
					}
		}
		//this is for 3 (bill list ) tab screen aftter payment
		public function billlist(Request $request){
			
			if(Auth::user()){
				
				$billList = OrderMaster::select(
					'order_master_id',
					'bill_no',
					'is_paid',
					'net_amount',
					'paid_amount',
					'discount',
					'deleted_at',
					'istatus',
					DB::raw('DATE_FORMAT(created_at, "%d-%M-%Y") as created_date'),
					'updated_at'
					)
					 ->where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'istatus'=> 0])
					->orderBy('created_at', 'desc')
					->get();
					
			
				return response()->json(['BillList' => $billList]);
			
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
					], 401);
			}
		}
		
		public function cancelbill(Request $request){
			
			if(Auth::user()){
					
					$billDatabyorderId = OrderMaster::where(['patient_id' => $request->patient_id,
							'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'order_master_id' => $request->order_id])
						->orderBy('created_at', 'desc')
						->first();
						
						$isPaid = $billDatabyorderId->is_paid;
					
					if($isPaid == 0){
						
						$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $request->order_id])
											->update([
												'istatus' => 1
											]);
						
						//all suggested treatment set is billing 0 
						$treatmentviewlistList = OrderDetail::where(['order_detail.order_id' => $request->order_id])
						->get();
						
						foreach($treatmentviewlistList as $TreatmentviewlistList){
							
							$suggestedTreatmentId = $TreatmentviewlistList->suggested_treatment_id;
							
							$OrderMasterUpdate = SuggestedTreatments::where(['patient_id' => $request->patient_id,
														'branch_id' => $request->branch_id,'suggested_treatment_id' => $suggestedTreatmentId])
														->update([
														'is_billing' => 0
														]);

						}
					}
					
					if($isPaid == 1 || $isPaid == 2){
						
						$getOrderPaymentDetails = OrderPaymentDetail::where(['order_id' => $request->order_id,'istatus' => 0])->count();
						if($getOrderPaymentDetails > 0){
							
							return response()->json([
							'status' => 'error',
							'message' => 'Please cancel all the payments of this invoice.'
							]);
							
						}else{
							
							$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $request->order_id])
											->update([
												'istatus' => 1
											]);
						
							//all suggested treatment set is billing 0 
							$treatmentviewlistList = OrderDetail::where(['order_detail.order_id' => $request->order_id])
							->get();
							
							foreach($treatmentviewlistList as $TreatmentviewlistList){
								
								$suggestedTreatmentId = $TreatmentviewlistList->suggested_treatment_id;
								
								$OrderMasterUpdate = SuggestedTreatments::where(['is_billing' => 0, 'patient_id' => $request->patient_id,
															'branch_id' => $request->branch_id,'suggested_treatment_id' => $suggestedTreatmentIdd])
															->update([
															'is_billing' => 0
															]);

							}
							
						}
						
					}
						
				
						return response()->json([
								'status' => 'success',
								'message' => 'Bill cancel successfully.'
						]);
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
				}
		}
		
		//this is for 3 (bill list ) tab screen aftter payment
		public function billorderdetaillist(Request $request){
			
			if(Auth::user()){
				
				$billList = OrderDetail::select(
					'order_detail.order_id',
					'order_detail.suggested_treatment_id',
					'order_detail.selected_teeth',
					'suggested_treatments.discount_amount',
					'suggested_treatments.total_amount',
					DB::raw('DATE_FORMAT(suggested_treatments.treatment_date, "%d-%M-%Y") as treatment_date'),
					'suggested_treatments.treatmentBydoctor_id',
					'suggested_treatments.amount as treatmentcost',
					'suggested_treatments.treatment_name',
					DB::raw('DATE_FORMAT(order_master.created_at, "%d-%M-%Y") as created_date'),
					'order_master.bill_no'
					)
					->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
					->join('suggested_treatments', 'order_detail.suggested_treatment_id', '=', 'suggested_treatments.suggested_treatment_id')
					 ->where(['order_detail.order_id' => $request->order_id])
					->orderBy('order_master.created_at', 'desc')
					->get();
					
					
					$arr = [];
					
					foreach($billList as $BillList){
						
						$treatmentBydoctor_id = $BillList->treatmentBydoctor_id;
						
						 $userList = User::where([
						 'user_id' => $treatmentBydoctor_id
						 ])->first();
						 
						// echo "<pre>";
						// print
						 
						$arr[] = array(
							"treatment_date" => $BillList->treatment_date,
							"discount_amount" => $BillList->discount_amount,
							"total_amount" => $BillList->total_amount,
							"discount_amount" => $BillList->discount_amount,
							"treatmentcost" => $BillList->treatmentcost,
							"treatment_name" => $BillList->treatment_name,
							"doctor_name" => $userList->user_name,
							"address" => $userList->address,
							"bill_no" => $BillList->user_name
							
						); 
							
					}
					
							
					$masterOrderDetails = OrderMaster::select(
							'net_amount',
							'discount',
							'paid_amount'
							)
							->where(['order_master_id' => $request->order_id])
							->first();
					
						
						
				return response()->json([
				
				'BillorderdetailList' => $arr,
				'masterOrderDetails' => $masterOrderDetails
				
				]);
				
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
					], 401);
			}
		}
		
		
		
		//this is bill list treatment view screen
		public function treatmentviewlistbybillId(Request $request){
			
			if(Auth::user()){
				
				$treatmentviewlistList = OrderDetail::select(
					'order_detail.suggested_treatment_id',
					'suggested_treatments.treatment_name',
					'suggested_treatments.treatment_id',
					'suggested_treatments.treatment_date',
					'suggested_treatments.treatmentBydoctor_id',
					//'users.user_name as doctor_name'
					)
					
					//->join('users', 'suggested_treatments.treatmentBydoctor_id', '=', 'users.user_id')
					->join('suggested_treatments', 'order_detail.suggested_treatment_id', '=', 'suggested_treatments.suggested_treatment_id')
					 ->where(['order_detail.order_id' => $request->order_id])
					->get();
					
					foreach($treatmentviewlistList as $TreatmentviewlistList){
						
						$treatmentBydoctor_id = $TreatmentviewlistList->treatmentBydoctor_id;
						
						 $userList = User::where([
						 'user_id' => $treatmentBydoctor_id
						 ])->first();
						 
						// echo "<pre>";
						// print
						 
						$arr[] = array(
							"treatment_name" => $TreatmentviewlistList->treatment_name,
							"treatment_id" => $TreatmentviewlistList->treatment_id,
							"treatment_date" => $TreatmentviewlistList->treatment_date,
							"treatmentBydoctor_id" => $TreatmentviewlistList->treatmentBydoctor_id,
							"doctor_name" => $userList->user_name,
							"suggested_treatment_id" => $TreatmentviewlistList->suggested_treatment_id,
						); 
							
					}
					
					
					
						
				return response()->json([
				
								'treatmentviewlistList' => $arr
				
				]);
				
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
					], 401);
			}
		}
		
		
		//change doctor by treatment on orderdetail list page
		public function billingorderdetailchangedocotor(Request $request){
			
			if(Auth::user()){
				
				  $treatmentIds = $request->treatmentIds;
				  $doctorIds = $request->treatmentbydoctorIds;
				  
				 $array = array_combine($treatmentIds, $doctorIds);
				 foreach($array as $suggestedtreatmentid => $doctorId){
				
					$SuggestedTreatments = SuggestedTreatments::where('suggested_treatment_id','=',$suggestedtreatmentid)->update([

							'treatmentBydoctor_id' => $doctorId
					]);
				 }

				return response()->json([
								'status' => 'success',
								'message' => 'doctor updated successfully.',
						], 401);
			
			}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
			}
		}

		//this is for 3 (bill list ) tab screen aftter payment
		public function paymentlist(Request $request){
			
				$paymentlist = OrderPaymentDetail::select(
						'order_payment_detail.amount',
						'order_payment_detail.payment_mode',
						'order_payment_detail.istatus',
						'order_payment_detail.order_payment_detail_id',
						DB::raw('DATE_FORMAT(order_payment_detail.created_at, "%d-%M-%Y") as created_date'),
						'order_master.bill_no'
						)
						 ->where(['order_payment_detail.patient_id' => $request->patient_id,
							'order_payment_detail.branch_id' => $request->branch_id,'order_payment_detail.clinic_id' => $request->clinic_id])
						->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
						->orderBy('order_payment_detail.created_at', 'desc')
						->get();
						
						

			
			return response()->json(['paymentlist' => $paymentlist]);
		}
		
		
		public function cancelpayment(Request $request){
			
			if(Auth::user()){
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			$order_payment_detail_id = $request->order_payment_detail_id;
			
			$OrderPaymentDetailList = OrderPaymentDetail::select(
					'order_id',
					'clinic_id',
					'amount',
					'payment_mode',
					'order_payment_detail_id',
					DB::raw('DATE_FORMAT(created_at, "%d-%M-%Y") as created_date'),
					)
					
					->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'order_payment_detail_id' => $order_payment_detail_id])
					->orderBy('created_at', 'desc')
					
					->first();
					$orderPaymentDetailId = $OrderPaymentDetailList->order_payment_detail_id;
					$totalAmountofOrderDetal = $OrderPaymentDetailList->amount;
					$orderId = $OrderPaymentDetailList->order_id;
				
					$ordermasterList = OrderMaster::where(['order_master_id' => $orderId])
					->orderBy('order_master_id', 'desc')
					->first();

				
				
				
				$orderAmount = $ordermasterList->paid_amount;
				$dueAmount = $ordermasterList->due_amount;
					
					if($totalAmountofOrderDetal == $orderAmount){
						
					//if amount paid for perticular order
					$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $orderId])
										->update([
											'is_paid' => 0,
											'discount' => 0,
											'paid_amount' => 0,
											'due_amount' => 0
											
										]);
										
					$OrderPaymentDetailUpdate = OrderPaymentDetail::where(['order_payment_detail_id' => $orderPaymentDetailId])
										->update([
											'istatus' => 1
										]);
										
					}else if($totalAmountofOrderDetal  < $orderAmount){
						
						$remainingPaidAmount = $orderAmount - round($totalAmountofOrderDetal);
						$remainingDueAmount = $dueAmount + round($totalAmountofOrderDetal);
						
						$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $orderId])
										->update([
											'is_paid' => 1,
											'due_amount' => $remainingDueAmount,
											'paid_amount' => $remainingPaidAmount
										]);				
						$OrderPaymentDetailUpdate = OrderPaymentDetail::where(['order_payment_detail_id' => $orderPaymentDetailId])
										->update([
											'istatus' => 1
										]);
					}
					
					
					// remove data from suggested treatment payment
					$suggestedTreatmentPaymentobj = SuggestedTreatmentPayment::where('order_payment_detail_id', $order_payment_detail_id);
					if(!empty($suggestedTreatmentPaymentobj)){
						$suggestedTreatmentPaymentobj->delete();
					}
					// remove data from suggested treatment payment
				
				return response()->json([
								'status' => 'success',
								'message' => 'Payment cancel successfully'
							]);
							
			}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
			}
		}
		
		public function reportdailycollection(Request $request){
			
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			
			if(Auth::user()){
				
				$reportCollection = SuggestedTreatmentPayment::select(
							DB::raw('sum(suggested_treatment_payment.amount) as amount'),
							DB::raw('DATE_FORMAT(order_master.created_at, "%d-%M-%Y") as order_date'),
							'patients.name_prefix as name_prefix',
							'patients.name as patient_name',
							'order_payment_detail.payment_mode',
							'order_payment_detail.order_payment_detail_id  as receipt',
							'branches.branch_name',
							'suggested_treatments.treatmentBydoctor_id'
							)
				->whereNot('order_master.is_paid', 0)
				->where(['suggested_treatment_payment.branch_id' => $branch_id,'suggested_treatment_payment.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				->join('patients', 'patients.patient_id', '=', 'suggested_treatment_payment.patient_id')
				->join('order_payment_detail', 'order_payment_detail.order_payment_detail_id', '=', 'suggested_treatment_payment.order_payment_detail_id')
				->join('order_detail', 'order_detail.order_detail_id', '=', 'suggested_treatment_payment.order_detail_id')
				->join('order_master', 'order_master.order_master_id', '=', 'suggested_treatment_payment.order_id')
				->join('branches', 'branches.branch_id', '=', 'suggested_treatment_payment.branch_id')
				->join('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'suggested_treatment_payment.suggested_treatments_id')
				
					->when($request->doctor_id, function ($query) use ($request) {
										$query->where('suggested_treatments.treatmentBydoctor_id' ,'=',$request->doctor_id);
                    })
					 ->when($request->fromDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
									})
					->when($request->toDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
									})
					->when($request->selected_date, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
									})
					->when($request->search_branch_id, function ($query) use ($request) {
										$query->where('suggested_treatment_payment.branch_id' ,'=',$request->search_branch_id);
									})	
					->when($request->payment_mode, function ($query) use ($request) {
										$query->where('order_payment_detail.payment_mode' ,'=',$request->payment_mode);
                    }) 
				->get(); 
				
					
				$arr = [];
				$mode = "";
				if(count($reportCollection) != 0){
					
						foreach($reportCollection as $ReportCollection){
							
							if($ReportCollection->amount != NULL)
							{
								$payment_mode = $ReportCollection->payment_mode;
							
								if($payment_mode == 1){
									
									$mode = "Cash";
									
								}else if($payment_mode == 2){
									
									$mode = "Cheque";
									
								}else if($payment_mode == 3){
									
									$mode = "Card";
									
									
								}else if($payment_mode == 4){
									
									$mode = "RTGS";
									
								}else if($payment_mode == 5){
									
									$mode = "NEFT";
									
								}else if($payment_mode == 6){
									
									$mode = "Paytm";
									
								}else if($payment_mode == 7){
									
									$mode = "Coupons";
									
								}else if($payment_mode == 8){
									
									$mode = "Online";
									
								}else if($payment_mode == 9){
									
									$mode = "WriteOff";
									
								}else if($payment_mode == 10){
									
									$mode = "GooglePay";
									
								}
							
							
							$arr[] = array(
							
							'amount' =>$ReportCollection->amount,
							'order_date' =>$ReportCollection->order_date,
							'patient_name' =>$ReportCollection->name_prefix." ".$ReportCollection->patient_name,
							'payment_mode' =>$mode,
							'receipt' =>"RCPT".$ReportCollection->receipt,
							'branch_name' =>$ReportCollection->branch_name,
							'treatmentBydoctor_id' =>$ReportCollection->treatmentBydoctor_id,
							
							);
							
								return response()->json([
										'status' => 'success',
										'message' => 'reportData',
										'dailyCollection' => $arr
								], 401);
								
							}else{
								
								return response()->json([
										'status' => 'success',
										'message' => 'reportData',
										'dailyCollection' => $arr
								], 401);
							}
						}
				
				
				}else{
								return response()->json([
									'status' => 'error',
									'message' => 'No Record Found.',
									'labworkData' => $arr
								]);
					}
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
			}
				
				
			
		}
		
		public function lastorderIdbyPatient(Request $request){
			
			if(Auth::user()){
			
			$lastOrderData = OrderMaster::where(['patient_id' => $request->patient_id,
						'branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id,'istatus' => 0])
					->orderBy('created_at', 'desc')
					->whereNot('is_paid', 2)
					->first();
				
				$isPaid = "";
				$orderMasterId = "";
				
				if(!empty($lastOrderData)){
					
					$orderMasterId = $lastOrderData->order_master_id;
					$isPaid = $lastOrderData->is_paid;
					
					return response()->json([
									'status' => 'success',
									'message' => 'Order Details.',
									'order_id' => $orderMasterId
								]);
				}
				
				return response()->json([
									'status' => 'success',
									'message' => 'No bill generated for this patient Yet.',
									'order_id' => $orderMasterId
								]);
								
								
								
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		}
}
