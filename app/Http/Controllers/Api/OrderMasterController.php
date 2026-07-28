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
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Branch;
use App\Models\Patient;
use App\Models\CashLedger;
use App\Services\AuthkeyWhatsAppService;

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

						
				/*$netAmountofallunpaidOrders = OrderMaster::select(
							DB::raw('sum(net_amount) as net_amount')
							)
							->where(['is_paid' => 0,'branch_id' => $branch_id,'patient_id' => $patient_id,'istatus' =>0])
							//->where(['branch_id' => $branch_id,'patient_id' => $patient_id,'istatus' =>0])
							//->whereIn('is_paid',[0,1,2])
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
							//->first();
							->get()->toArray();
				//dd($totalAmountforCount);
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
				
				$net_amount = OrderMaster::select(
					DB::raw('sum(net_amount) as net_amount')
					)
					->where('is_paid', 2)
					->where(['branch_id' => $branch_id,'patient_id' => $patient_id, 'istatus' =>0])
					->first();
				//dd($net_amount);
				//die;
				if($totalAmountforCount != 0){
				    if($net_amount->net_amount != 0){
				        $totalAmountforCount +=$net_amount->net_amount;    
				    } else {
				        $totalAmountforCount = $totalAmountforCount;
				    }
				} else {
				    if($net_amount->net_amount != 0){
				        $totalAmountforCount =$net_amount->net_amount;    
				    } else {
				        $totalAmountforCount = $totalAmountforCount;
				    }
				}
				if($paid_amount != 0){
				    if($net_amount->net_amount != 0){
				        $paid_amount +=$net_amount->net_amount;    
				    } else {
				        $paid_amount = $paid_amount;
				    }
				} else {
				    if($net_amount->net_amount != 0){
				        $paid_amount =$net_amount->net_amount;    
				    } else {
				        $paid_amount = $paid_amount;
				    }
				}*/
				$OrderMaster = OrderMaster::select(
					DB::raw('sum(net_amount) as net_amount'),
					DB::raw('sum(discount) as discount'),
					DB::raw('sum(paid_amount) as paid_amount'),
					DB::raw('sum(due_amount) as due_amount')
					)
					->where(['branch_id' => $branch_id,'patient_id' => $patient_id,'istatus' =>0])
					->first();
				
				$totalPayableAmount = $OrderMaster->net_amount - $OrderMaster->paid_amount - $OrderMaster->discount;
				//$order_id;
				$totalAmountforCount = $OrderMaster->net_amount;
				$paid_amount = $OrderMaster->paid_amount;
				$due_amount = $OrderMaster->due_amount;
				$discount = $OrderMaster->discount;
				return response()->json([
					'status' => 'success',
					'TotalPayableAmount' => $totalPayableAmount ?? 0,
					'order_id' => $order_id ?? 0,
					'Totalamount' => $totalAmountforCount ?? 0,
					'Paidamount'=> $paid_amount ?? 0,
					'Dueamount'=> $due_amount ?? 0,
					"discount" => $discount ?? 0
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
								
								//sencond round payment need to subtract paid amount
								$orderdetailAmountAfterDiscount = $orderdetailAmountAfterDiscount - $amountTotal;
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
    			$paidAmount = 0;
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
						
						
				} else if($isPaid == 1){
					
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
    					'payment_date' => $request->payment_date ? date('Y-m-d',strtotime($request->payment_date)) : date('Y-m-d'),
    				]);
    				
				$patient = Patient::where(["patient_id" => $request->patient_id])->first();
				$payment_mode = "";
				if($request->payment_mode == 1){
				    $payment_mode = "Cash";
				} else if($request->payment_mode == 2){
				    $payment_mode = "Cheque";
				}  else if($request->payment_mode == 3){
				    $payment_mode = "Card";
				} else if($request->payment_mode == 4){
				    $payment_mode = "RTGS";
				} else if($request->payment_mode == 5){
				    $payment_mode = "NEFT";
				}  else if($request->payment_mode == 6){
				    $payment_mode = "Paytm";
				}  else if($request->payment_mode == 7){
				    $payment_mode = "Coupons";
				} else if($request->payment_mode == 8){
				    $payment_mode = "Online";
				} else if($request->payment_mode == 9){
				    $payment_mode = "WriteOff";
				} else {
			        $payment_mode = "GooglePay";
				}
				
				
				if($request->payment_mode == 1){
				    $cashLedger = CashLedger::where(["clinic_id" => $request->clinic_id,"branch_id" => $request->branch_id])->orderBy("id","desc")->first();
                    $op_amt = $cashLedger->cl_amt ?? 0;
                    $cr_amt = $actualpaidAmount;
                    $dr_amt = 0;
                    $cl_amt = $op_amt + $actualpaidAmount;
                    
                    $ledger = array(
                        "clinic_id" => $request->clinic_id, 
                        "branch_id" => $request->branch_id,
            	        "op_amt" => $op_amt,
                    	"cr_amt" => $cr_amt,
                    	"dr_amt" => $dr_amt,
                    	"cl_amt" => $cl_amt,
                    	"order_id" => $request->order_id,
                    	"order_payment_detail_id" => $paymentOrderDetails->order_payment_detail_id,
                    	"strIP" => $request->ip(),
                    	"created_at" =>date('Y-m-d H:i:s')
                    );
                    CashLedger::create($ledger);   
				}
    			
    			//add record in new table suggested treatment payment
    			$lastpaymentOrderDetailId = $paymentOrderDetails->order_payment_detail_id;
    			
    			$suggestedTreatmentIds = $request->treatmentIds;
    			$treatmentamounts = $request->treatmentamounts;
    			$discount = $request->discount;
    					  
    			$array = array_combine($suggestedTreatmentIds, $treatmentamounts);
    				
    				
    			foreach($array as $suggestedTreatmentId => $treatmentamounts){
    					
    					$treatmentOrderDetail = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
    					->first();
    					
    					$SuggestedTreatmentPaymentOrder = SuggestedTreatmentPayment::select(
    					DB::raw('sum(amount) as amount')
    					)
    					->where(['suggested_treatments_id' => $suggestedTreatmentId])
    					->first();
    					
    					$order_detail_id = $treatmentOrderDetail->order_detail_id;
    					$amount = $treatmentOrderDetail->amount;
    					$discountoforder = $treatmentOrderDetail->discount;
    					
    					//if half payment done than calculate amount.
    					if(!empty($SuggestedTreatmentPaymentOrder)){
    							
    							$paidAmount = $SuggestedTreatmentPaymentOrder->amount;
    							$totalamountoriginal = $amount - round($discountoforder);
    							$totalamount = $totalamountoriginal - round($paidAmount);
    					}else{
    						$totalamount = $amount - round($discountoforder);
    					}
    					
    					if(!empty($discount)){
    
    				// 			$percentageAmount = round((100*$totalamount)/$netAmount);
    				            $percentageAmount = (100*$totalamount)/$netAmount;
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
							'amount' => $treatmentamounts,
							'discount' => $discountAmttreatmentwise
						]);
    						
    						
    					$isPaidoflastOrder = $lastOrderList->is_paid;
    					if($isPaidoflastOrder == 0){
    					
    					$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
							->update([
								'discount' => $discountAmttreatmentwise
							]);
    								
    					}else if($isPaidoflastOrder == 1){
    						
    						$totalDiscount = $discountoforder + $discountAmttreatmentwise;
    						
    						$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
    								->update([
    									'discount' => $totalDiscount
    								]);
    					}
    					
    			}
    			//add record in new table suggested treatment payment  , and a PDF receipt has attached 
    				
				if($request->payment_sms == 1){
					//send pdf and whatsapp
				}
				
				
				
// 				$key = $_ENV['WHATSAPPKEY'];
// 				$PaymentDate = $request->payment_date ? date('d-m-Y',strtotime($request->payment_date)) : date('d-m-Y');
// 				$msg_text = "*Payment Confirmation and Receipt - Vraj Dental Clinics Pvt Ltd* 
				      
// Dear ". $patient->name_prefix ." ". $patient->name .",
                    
// Thank you for your payment of ".$request->paid_amount." to Vraj Dental Clinics Pvt Ltd. 
// Your transaction has been successfully processed. 

// Transaction Details:

                    
// *Amount:* ".$request->paid_amount ."
// *Payment Date:* ".$PaymentDate."
// *Mode of payment:* (".$payment_mode.")
// *Remaining Balance:* ".$dueAmount . "
                    
// Please feel free to reach out at reception or call *9427784433* if you have any questions or concerns regarding your payment.
                    
// Best regards,
// Vraj Dental Clinics Pvt Ltd.";
			    
				// $users = new User();
				// // $currentUser = Auth::user();

				// // $mobileNo = $currentUser->mobile_no;
				// // dd($msg_text);
				$mobileNo = $patient->mobile_no;
				// $status = $users->sendWhatsappMessage($mobileNo,$key,$msg_text,"");
				
				$whatsappService = new AuthkeyWhatsAppService();
				$wid = "29126"; // template id
				$PatientName = ($patient->name_prefix ?? "") ." ". ($patient->name ?? "");
				$bodyValues = [
					"1" => trim($PatientName),
					"2" => trim($request->paid_amount),
				];
				$statusofMessage = $whatsappService->sendText($mobileNo, $wid, $bodyValues);
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
		
		public function addPayment_new(Request $request){
    		if(Auth::user()){
    			$actualpaidAmount = round($request->paid_amount);
    			$netAmount = $request->net_amount;
    			$paidAmount = 0;
    			if(!empty($request->discount)){
    				$paidAmount = round($request->paid_amount);
    				$paidAmountwithDiscount = round(($request->paid_amount))+ round(($request->discount));
    			}else{
    				$paidAmount = round($request->paid_amount);
    				$paidAmountwithDiscount = $paidAmount; 
    			}
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
						$allsuggestedIdList = OrderDetail::where(['patient_id' => $request->patient_id, 'order_id' => $lastOrderList->order_master_id])->get();
						foreach($allsuggestedIdList as $allsuggestedIdlist){
								$OrderMasterUpdate = SuggestedTreatments::where(['is_billing' => 0, 'patient_id' => $request->patient_id,
								'branch_id' => $request->branch_id,'suggested_treatment_id' => $allsuggestedIdlist->suggested_treatment_id])
								->update([
								'is_billing' => 1
								]);
						}
					}else{
						$newPaidAmountOfpatient = 0;
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
				} else if($isPaid == 1){
					$net_amount = $lastOrderList->net_amount;
					$paidAmountOfCurrentOrder = $lastOrderList->paid_amount;
					$paidAmount = $paidAmount + $paidAmountOfCurrentOrder;
					$discountOfCurrentOrder = $lastOrderList->discount;
					$discount = $discount + $discountOfCurrentOrder;
					if(!empty($dueAmount) && $dueAmount != "0.00"){
						$dueAmountOfCurrentOrder = $lastOrderList->due_amount;
						$isPaid = 1;
					}else{
						$dueAmount = 0;
						$isPaid = 2;
					}
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
    					'payment_date' => $request->payment_date ? date('Y-m-d',strtotime($request->payment_date)) : date('Y-m-d'),
    				]);
    				
				$patient = Patient::where(["patient_id" => $request->patient_id])->first();
				$payment_mode = "";
				if($request->payment_mode == 1){
				    $payment_mode = "Cash";
				} else if($request->payment_mode == 2){
				    $payment_mode = "Cheque";
				}  else if($request->payment_mode == 3){
				    $payment_mode = "Card";
				} else if($request->payment_mode == 4){
				    $payment_mode = "RTGS";
				} else if($request->payment_mode == 5){
				    $payment_mode = "NEFT";
				}  else if($request->payment_mode == 6){
				    $payment_mode = "Paytm";
				}  else if($request->payment_mode == 7){
				    $payment_mode = "Coupons";
				} else if($request->payment_mode == 8){
				    $payment_mode = "Online";
				} else if($request->payment_mode == 9){
				    $payment_mode = "WriteOff";
				} else {
			        $payment_mode = "GooglePay";
				}

				if($request->payment_mode == 1){
				    $cashLedger = CashLedger::where(["clinic_id" => $request->clinic_id,"branch_id" => $request->branch_id])->orderBy("id","desc")->first();
                    $op_amt = $cashLedger->cl_amt ?? 0;
                    $cr_amt = $actualpaidAmount;
                    $dr_amt = 0;
                    $cl_amt = $op_amt + $actualpaidAmount;
                    
                    $ledger = array(
                        "clinic_id" => $request->clinic_id, 
                        "branch_id" => $request->branch_id,
            	        "op_amt" => $op_amt,
                    	"cr_amt" => $cr_amt,
                    	"dr_amt" => $dr_amt,
                    	"cl_amt" => $cl_amt,
                    	"order_id" => $request->order_id,
                    	"order_payment_detail_id" => $paymentOrderDetails->order_payment_detail_id,
                    	"strIP" => $request->ip(),
                    	"created_at" =>date('Y-m-d H:i:s')
                    );
                    CashLedger::create($ledger);   
				}
    			
    			$lastpaymentOrderDetailId = $paymentOrderDetails->order_payment_detail_id;
    			
    			$suggestedTreatmentIds = $request->treatmentIds;
    			$treatmentamounts = $request->treatmentamounts;
    			$discount = $request->discount;
    					  
    			$array = array_combine($suggestedTreatmentIds, $treatmentamounts);
    			foreach($array as $suggestedTreatmentId => $treatmentamounts){
    					$treatmentOrderDetail = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
    					->first();
    					$SuggestedTreatmentPaymentOrder = SuggestedTreatmentPayment::select(DB::raw('sum(amount) as amount'))
        					->where(['suggested_treatments_id' => $suggestedTreatmentId])
        					->first();
    					
    					$order_detail_id = $treatmentOrderDetail->order_detail_id;
    					$amount = $treatmentOrderDetail->amount;
    					$discountoforder = $treatmentOrderDetail->discount;
    					
    					if(!empty($SuggestedTreatmentPaymentOrder)){
							$paidAmount = $SuggestedTreatmentPaymentOrder->amount;
							$totalamountoriginal = $amount - round($discountoforder);
							$totalamount = $totalamountoriginal - round($paidAmount);
    					}else{
    						$totalamount = $amount - round($discountoforder);
    					}
    					
    					if(!empty($discount)){
				            $percentageAmount = (100*$totalamount)/$netAmount;
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
							'amount' => $treatmentamounts,
							'discount' => $discountAmttreatmentwise
						]);
						
						
						
    					$isPaidoflastOrder = $lastOrderList->is_paid;
    					if($isPaidoflastOrder == 0){
        					$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
    							->update([
    								'discount' => $discountAmttreatmentwise
    							]);
    					}else if($isPaidoflastOrder == 1){
    						$totalDiscount = $discountoforder + $discountAmttreatmentwise;
    						$OrderDetailUpdate = OrderDetail::where(['suggested_treatment_id' => $suggestedTreatmentId])
								->update([
									'discount' => $totalDiscount
								]);
    					}
    			}
    			
    			
    			$billList = OrderDetail::select(
					'order_detail.order_id',
					'order_detail.suggested_treatment_id',
					'order_detail.selected_teeth',
					'suggested_treatments.discount_amount',
					'suggested_treatments.selected_teeth_count',
					'suggested_treatments.amount',
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
					 ->where(['order_detail.order_id' => $lastOrderList->order_master_id])
					->orderBy('order_master.created_at', 'desc')
					->get();
					
					
					$arr = [];
					$grand_amount = 0;
					
					foreach($billList as $BillList){
						
						$treatmentBydoctor_id = $BillList->treatmentBydoctor_id;
						
						 $userList = User::where([
						 'user_id' => $treatmentBydoctor_id
						 ])->first();
						 
						$grand_amount += $BillList->total_amount;
						 
						$arr[] = array(
							"treatment_date" => $BillList->treatment_date,
							"discount_amount" => $BillList->discount_amount,
							"total_amount" => $BillList->total_amount,
							"treatmentcost" => $BillList->treatmentcost,
							"treatment_name" => $BillList->treatment_name,
							"doctor_name" => $userList->user_name,
							"bill_no" => $BillList->bill_no,
							"selected_teeth_count" => $BillList->selected_teeth_count,
							"amount" => $BillList->amount,
							"doctor_address" => $userList->address,
							"bill_date" => $BillList->created_date,
							"selected_teeth" => $BillList->selected_teeth
						); 
							
					}
					
					$masterOrderDetails = OrderMaster::select(
							'net_amount',
							'discount',
							'paid_amount',
							'due_amount'
							)
							->where(['order_master_id' => $lastOrderList->order_master_id])
							->first();
						
					$masterOrderPaymentDetail = OrderPaymentDetail::select(
							DB::raw('DATE_FORMAT(created_at, "%d-%M-%Y") as created_date'),
							'order_payment_detail_id',
							'payment_mode',
							'amount',
							DB::raw('DATE_FORMAT(payment_date, "%d-%M-%Y") as payment_date')
							)
							->where(['order_id' => $lastOrderList->order_master_id,'istatus' => 0])
							->get();
					
						//send bill detail pdf 
				    $billData = OrderMaster::where(['order_master_id'=> $lastOrderList->order_master_id])->first();
				    $billNo = $billData->bill_no;
				    $branch = Branch::where("branch_id",'=',$billData->branch_id)->first();
				// 		$key = $_ENV['WHATSAPPKEY'];
				// 		$msg = "Dear User, Please find attached bill details of treatments.";
						$fileName = $billNo."_".date('d-m-Y');
						 

						$pdf = PDF::loadView('billdetail',['BillorderdetailList' => $arr,
						'masterOrderDetails' => $masterOrderDetails,
						'masterOrderPaymentDetail' => $masterOrderPaymentDetail,
						'name_prefix' => $patient->name_prefix ?? "",
						'patient_name' => $patient->name ?? "",
						'case_no' =>  $patient->case_no ?? '',
						//'address' => $address,
						'address' => $branch->branch_name,
						'grand_amount'=>$grand_amount
						
						]);
						
						
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/treatment_report/'.$fileName . '.pdf',$content);
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/treatment_report/')  . $fileName. '.pdf');	
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/treatment_report/')  . $fileName. '.pdf');
						}
						
						$billdetailFile = asset('assets/treatment_report/'. $fileName. '.pdf');
						

				$mobileNo = $patient->mobile_no;
				$whatsappService = new AuthkeyWhatsAppService();
				$wid = "35680"; // "29126"; // template id
				$PatientName = ($patient->name_prefix ?? "") ." ". ($patient->name ?? "");
				$bodyValues = [
					"1" => trim($PatientName),
					"2" => trim($request->paid_amount),
					"3" => $fileName
				];
				$statusofMessage = $whatsappService->sendText($mobileNo, $wid, $bodyValues);
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
				// 	->toSql();
				// 	dd($billList);
				// if ($billList->isEmpty()) {
    //                 // No data found
    //                 return response()->json(['status' => "error", 'message' => 'No bills found.']);
    //             } else {
    //                 // Data exists
    //                 return response()->json(['status' => "success", 'BillList' => $billList]);
    //             }
			
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
						DB::raw('DATE_FORMAT(order_payment_detail.payment_date, "%d-%M-%Y") as payment_date'),
						'order_master.bill_no'
						)
						 ->where(['order_payment_detail.patient_id' => $request->patient_id,
							'order_payment_detail.branch_id' => $request->branch_id,'order_payment_detail.clinic_id' => $request->clinic_id])
						->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
						->orderBy('order_payment_detail.created_at', 'desc')
					->get();
				// 		->toSql();
				// 		dd($paymentlist);
						
						

			
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
					DB::raw('DATE_FORMAT(payment_date, "%d-%M-%Y") as payment_date')
					
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

				if($OrderPaymentDetailList->payment_mode == 1){
				    $cashLedger = CashLedger::where(["clinic_id" => $request->clinic_id,"branch_id" => $request->branch_id])->orderBy("id","desc")->first();
				    $actualpaidAmount = $OrderPaymentDetailList->amount;
                    $op_amt = $cashLedger->cl_amt ?? 0;
                    $cr_amt = 0;
                    $dr_amt = $actualpaidAmount;
                    $cl_amt = $op_amt - $actualpaidAmount;
                    
                    $ledger = array(
                        "clinic_id" => $request->clinic_id, 
                        "branch_id" => $request->branch_id,
            	        "op_amt" => $op_amt,
                    	"cr_amt" => $cr_amt,
                    	"dr_amt" => $dr_amt,
                    	"cl_amt" => $cl_amt,
                    	"order_id" => $OrderPaymentDetailList->order_id,
                    	"order_payment_detail_id" => $OrderPaymentDetailList->order_payment_detail_id,
                    	"strIP" => $request->ip(),
                    	"created_at" =>date('Y-m-d H:i:s')
                    );
                    CashLedger::create($ledger);   
				}
				
				
				$orderAmount = $ordermasterList->paid_amount;
				$dueAmount = $ordermasterList->due_amount;
				$discountAmount = $ordermasterList->discount;
					
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
										
					$OrderDetailUpdate = OrderDetail::where(['order_id' => $orderId])
										->update([
											'discount' => 0
										]);
										
					
										
					}else if($totalAmountofOrderDetal  < $orderAmount){
						
						// cancel payment discount
						$totalDiscount = 0;
						$allsuggestedPaymentList = SuggestedTreatmentPayment::where(['order_id' => $orderId,'order_payment_detail_id' => $orderPaymentDetailId])
						->get();

						foreach($allsuggestedPaymentList as $allSuggestedPaymentList){
							
							$orderDetailId = $allSuggestedPaymentList->order_detail_id;
							$discount = $allSuggestedPaymentList->discount;
							$totalDiscount += $discount;
							
							$orderDetailObj = OrderDetail::where(['order_detail_id' => $orderDetailId])
							->first();
							
							$orderDetailDiscount = $orderDetailObj->discount;
							
							$remainingDiscount = $orderDetailDiscount - round($discount);
							
							$OrderDetailUpdate = OrderDetail::where(['order_detail_id' => $orderDetailId])
										->update([
											'discount' => $remainingDiscount
										]);
						}
						
						$remainingPaidAmount = $orderAmount - round($totalAmountofOrderDetal);
						$remainingDueAmount = $dueAmount + round($totalAmountofOrderDetal);
						$remainingdiscountAmount = $discountAmount - round($totalDiscount);
						
						$OrderMasterUpdate = OrderMaster::where(['order_master_id' => $orderId])
										->update([
											'is_paid' => 1,
											'due_amount' => $remainingDueAmount + $totalDiscount,
											'paid_amount' => $remainingPaidAmount,
											'discount' => $remainingdiscountAmount
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
			$pdffile = $request->pdffile;
			$whatsappfile = $request->whatsappfile;
			
			if(Auth::user()){
				
				// $reportCollection = SuggestedTreatmentPayment::select(
				// 			DB::raw('sum(order_payment_detail.amount) as amount'),
				// 			DB::raw('DATE_FORMAT(order_master.created_at, "%d-%M-%Y") as order_date'),
				// 			'patients.patient_id',
				// 			'patients.name_prefix as name_prefix',
				// 			'patients.name as patient_name',
				// 			'order_payment_detail.payment_mode',
				// 			'order_payment_detail.order_payment_detail_id  as receipt',
				// 			'branches.branch_name',
				// 			'suggested_treatments.treatmentBydoctor_id',
				// 			'suggested_treatment_payment.patient_id',
				// 			"groups.group_name"
				// 			)
				// ->whereNot('order_master.is_paid', 0)
				// ->where(['suggested_treatment_payment.branch_id' => $branch_id,'suggested_treatment_payment.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				// ->join('patients', 'patients.patient_id', '=', 'suggested_treatment_payment.patient_id')
				// ->join('order_payment_detail', 'order_payment_detail.order_payment_detail_id', '=', 'suggested_treatment_payment.order_payment_detail_id')
				// ->join('order_detail', 'order_detail.order_detail_id', '=', 'suggested_treatment_payment.order_detail_id')
				// ->join('order_master', 'order_master.order_master_id', '=', 'suggested_treatment_payment.order_id')
				// ->join('branches', 'branches.branch_id', '=', 'suggested_treatment_payment.branch_id')
				// ->join('groups','groups.group_id','=','patients.group_id')
				// ->join('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'suggested_treatment_payment.suggested_treatments_id')
				// ->where("suggested_treatment_payment.istatus","=",0)
				// ->when($request->doctor_id, function ($query) use ($request) {
				// 	$query->where('suggested_treatments.treatmentBydoctor_id' ,'=',$request->doctor_id);
    //             })
			 //   ->when($request->fromDate, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				// })
    // 			->when($request->toDate, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				// })
    // 			->when($request->selected_date, function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
				// })
    // 			->when($request->search_branch_id, function ($query) use ($request) {
				// 	$query->where('suggested_treatment_payment.branch_id' ,'=',$request->search_branch_id);
				// })	
    // 			->when($request->payment_mode, function ($query) use ($request) {
				//     $query->where('order_payment_detail.payment_mode' ,'=',$request->payment_mode);
    //             })
    //             ->when($request->group_id, function ($query) use ($request) {
				// 	$query->where('patients.group_id' ,'=',$request->group_id);
    //             })
    // 			->when($request->month, function ($query) use ($request) {
				// 	$query->where(DB::raw("MONTH(order_master.created_at)"),'=',$request->month);
				// })
    // 			->when($request->year, function ($query) use ($request) {
				// 	$query->where(DB::raw("YEAR(order_master.created_at)"),'=',$request->year);
				// })
				// ->groupBy('patients.patient_id')
				// ->get(); 
				
				$reportCollection = OrderPaymentDetail::select(
							DB::raw('sum(order_payment_detail.amount) as amount'),
							DB::raw('DATE_FORMAT(order_payment_detail.payment_date, "%d-%M-%Y") as order_date'),
							'patients.patient_id',
							'patients.name_prefix as name_prefix',
							'patients.name as patient_name',
							'order_payment_detail.payment_mode',
							'order_payment_detail.order_payment_detail_id  as receipt',
							'branches.branch_name',
							//'suggested_treatments.treatmentBydoctor_id',
							'order_payment_detail.patient_id',
							"groups.group_name",
							DB::raw('(select `suggested_treatments`.`treatmentBydoctor_id` from suggested_treatments inner join  suggested_treatment_payment on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id where suggested_treatment_payment.order_id=order_master.order_master_id limit 1) as treatmentBydoctor_id')
							)
				//->whereNot('order_master.is_paid', 0)
				->where(['order_payment_detail.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				->where('order_payment_detail.amount','>',0)
				->whereNull('patients.deleted_at')
				->when($request->branch_id, fn ($query, $branch_id) => $query->WhereIn('order_payment_detail.branch_id',$branch_id))
				->join('patients', 'patients.patient_id', '=', 'order_payment_detail.patient_id')
				//->join('order_payment_detail', 'order_payment_detail.order_payment_detail_id', '=', 'suggested_treatment_payment.order_payment_detail_id')
				->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
				// ->join('order_detail', 'order_detail.order_id', '=', 'order_master.order_master_id')
				->join('branches', 'branches.branch_id', '=', 'order_payment_detail.branch_id')
				->join('groups','groups.group_id','=','patients.group_id')
				//->leftjoin('suggested_treatment_payment', 'suggested_treatments.suggested_treatment_id', '=', 'suggested_treatment_payment.suggested_treatments_id')
				//->join('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'order_detail.suggested_treatment_id')
				->where("order_payment_detail.istatus","=",0)
				// ->when($request->doctor_id, function ($query) use ($request) {
				// 	$query->where('suggested_treatments.treatmentBydoctor_id' ,'=',$request->doctor_id);
                // })
                ->when($request->doctor_id, fn ($query, $doctor_id) => $query->WhereIn(
                    'order_master.order_master_id',
                    function ($query) use ($doctor_id) {
                        $query->select('suggested_treatment_payment.order_id')
                            ->from(with(new SuggestedTreatments)->getTable())
                            ->join('suggested_treatment_payment','suggested_treatments.suggested_treatment_id','=','suggested_treatment_payment.suggested_treatments_id')
                            //->where('suggested_treatment_payment.order_id','=','order_master.order_master_id')
                            ->where('suggested_treatments.treatmentBydoctor_id', $doctor_id);
                    }
                ))
			    ->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
    			->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
    			->when($request->selected_date, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
				})
    // 			->when($request->search_branch_id, function ($query) use ($request) {
				// 	$query->where('suggested_treatment_payment.branch_id' ,'=',$request->search_branch_id);
				// })	
    			->when($request->payment_mode, function ($query) use ($request) {
				    $query->where('order_payment_detail.payment_mode' ,'=',$request->payment_mode);
                })
                ->when($request->group_id, function ($query) use ($request) {
					$query->where('patients.group_id' ,'=',$request->group_id);
                })
    			->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_payment_detail.payment_date)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_payment_detail.payment_date)"),'=',$request->year);
				})
				->orderBy('order_payment_detail.payment_date','asc')
				->groupBy('patients.patient_id','order_payment_detail.payment_date')
				->get(); 
				// ->toSql();
			 //   dd($reportCollection);
				
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
    			
				$arr = [];
				$mode = "";
				$grand_amount = 0;
				
				/* if ($reportCollection->isNotEmpty($reportCollection)) {
					 echo "vvvvvvvvvvv";
				}else{
					echo "xxxxxxxxx";
				}
				die; */ 
				$Cash = 0;
				$Cheque = 0;
				$Card = 0;
				$Online = 0;
				$other = 0;
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
						    
						    if($payment_mode == 1){
								$Cash += $ReportCollection->amount;
							}else if($payment_mode == 2){
								$Cheque += $ReportCollection->amount;
							}else if($payment_mode == 3){
								$Card += $ReportCollection->amount;
							}else if($payment_mode == 8){
								$Online += $ReportCollection->amount;
							} else {
							    $other += $ReportCollection->amount;
							}
						    
    						$grand_amount += $ReportCollection->amount;
    						$arr[] = array(
    						
    						'amount' =>$ReportCollection->amount,
    						'order_date' =>$ReportCollection->order_date,
    						'patient_name' =>$ReportCollection->name_prefix." ".$ReportCollection->patient_name,
    						'payment_mode' =>$mode,
    						'receipt' =>"RCPT".$ReportCollection->receipt,
    						'branch_name' =>$ReportCollection->branch_name,
    						'treatmentBydoctor_id' =>$ReportCollection->treatmentBydoctor_id,
    						'patient_id' =>$ReportCollection->patient_id,
    						"group_name" => $ReportCollection->group_name,
    						
    						);
						
							/* return response()->json([
									'status' => 'success',
									'message' => 'reportData',
									'dailyCollection' => $arr
							], 401); */
							
						}else{
							
							/* return response()->json([
									'status' => 'success',
									'message' => 'reportData',
									'dailyCollection' => $arr
							], 401); */
						}
					}
						
						
					if($pdffile == 1 && !empty($arr)){
									
						$pdf = PDF::loadView('dailycollection_report',['dailyCollection' => $arr,'grand_total' => $grand_amount,"branchName" => $branchName,"Duration" => $Duration]);
								
						$fileName = date('d-m-Y')."_dailyCollection";
						
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/assets/dailycollection_report/'.$fileName . '.pdf',$content);
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/dailycollection_report/')  . $fileName. '.pdf');	
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/dailycollection_report/')  . $fileName. '.pdf');

						}
				
						$dailycollectionFile = asset('assets/dailycollection_report/'. $fileName. '.pdf');
				
						//return $pdf->download($fileName . '.pdf');
						
						$key = $_ENV['WHATSAPPKEY'];		
						$dailycollectionListFile = asset('assets/dailycollection_report/'. $fileName. '.pdf');
						$msg = "Dear User, Please find attached details of treatments.";
									
						if($whatsappfile == 1){
							$users = new User();
							$currentUser = Auth::user();

							$mobileNo = $currentUser->mobile_no;
							$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
							
				// 			$statusofMessage = $status->status;
							// $Response = $status->response;
						
				// 			if($statusofMessage == "success"){
							
								return response()->json([
									'status' => 'success',
									'message' => 'DailyCollection Report sent on your registered mobile number.',
									'dailycollectionFile' => $dailycollectionFile
								]);
								
				// 			}else{
								
				// 				return response()->json([
				// 					'status' => 'error',
				// 					'message' => $Response.'.Please contact admin.',
				// 				], 401);
				// 			}
						}else{
							return response()->json([
								'status' => 'success',
								'message' => 'reportData',
								'grand_total' => $grand_amount,
								'dailycollection' => $arr,
								'dailycollectionFile' => $dailycollectionFile
							]);
						}
					}else{
						return response()->json([
							'status' => 'success',
							'message' => 'reportData.',
							'grand_total' => $grand_amount,
							'dailycollection' => $arr,
							"Cash" => $Cash,
            				"Cheque" => $Cheque,
            				"Card" => $Card,
            				"Online" => $Online,
            				"other" => $other
						]);
					}
				}else{
					return response()->json([
						'status' => 'error',
						'message' => 'No Record Found.',
						'dailycollection' => $arr,
						"Cash" => $Cash,
        				"Cheque" => $Cheque,
        				"Card" => $Card,
        				"Online" => $Online,
        				"other" => $other
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
		
    	public function tobecollectedlist(Request $request){
    			
    		if(Auth::user()){
    			$date = date('Y-m-d');
    			$dueCollection = OrderMaster::select('order_master.*',
    			    DB::raw('(select CONCAT(patients.name_prefix," ",patients.name) from patients where patients.patient_id=order_master.patient_id) as patientsName'))
    			    ->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
    				->where('is_paid','!=',2)
    				->where('istatus','=',0);
    				$dueCollection->when($request->fromDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
    				})
    				->when($request->toDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
    				});
    			    if(isset($request->doctor_id)){
    				    $dueCollection->whereIn('order_master.order_master_id', function ($query) use ($request) {
                            $query->select('order_detail.order_id')
                                ->from('order_detail')
                                ->join('suggested_treatments', 'order_detail.suggested_treatment_id', '=', 'suggested_treatments.suggested_treatment_id')
                                ->whereRaw('order_detail.order_id = order_master.order_master_id')
                                ->where('suggested_treatments.treatmentBydoctor_id', $request->doctor_id);
                        });
    	            }
    			$dueCollectionAmounts = $dueCollection->get();
                /*$dueCollection = DB::table('order_detail')
                    ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
                    ->select(
                        'order_master.patient_id','order_detail.order_detail_id',
                        DB::raw('(SELECT CONCAT(patients.name_prefix," ",patients.name) FROM patients WHERE patients.patient_id=order_master.patient_id) AS patientsName'),
                        'order_master_id',
                        DB::raw('(SELECT SUM(amount) FROM suggested_treatment_payment WHERE suggested_treatment_payment.order_detail_id=order_detail.order_detail_id) AS PaidAmount'),
                        DB::raw('(SELECT SUM(suggested_treatments.total_amount) FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS TotalAmount')
                    )
                    ->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
                    ->where('is_paid','!=',2)
                    ->where('istatus','=',0);
                    $dueCollection->when($request->fromDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
    				})
    				->when($request->toDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
    				});
    				if(isset($request->doctor_id)){
                        $dueCollection->whereIn('order_detail.suggested_treatment_id', function ($query)  use ($request) {
                            $query->select('suggested_treatments.suggested_treatment_id')
                                ->from('suggested_treatments')
                                ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
                        });
    				}
                    $dueCollectionAmounts = $dueCollection->get();*/
    				
    				
    				//$dueCollectionAmounts = $dueCollection->toSql();
    				//dd($dueCollectionAmounts);
    			
    				$totalDueCollection = 0;
    				$due_amount = 0;
    				foreach($dueCollectionAmounts as $dueCollectionAmount){
    					if($dueCollectionAmount->is_paid == 1){
    						$totalDueCollection += $dueCollectionAmount->due_amount;
    						$due_amount = $totalDueCollection;
    					} else {
    						$totalDueCollection += $dueCollectionAmount->net_amount;
    						$due_amount = $totalDueCollection;
    					}
    				// 	$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
    				// 	if($due_amount > 0){
    				// 	    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
    				// 	}
    					$treatmentviewlistList = OrderDetail::select(
    					    DB::raw('DISTINCT order_detail.suggested_treatment_id'),
        					//'',
        					'suggested_treatments.treatment_name',
        					'suggested_treatments.treatment_id',
        					'suggested_treatments.treatment_date',
        					'suggested_treatments.treatmentBydoctor_id',
        					'suggested_treatments.is_billing'
    					)
    					->join('suggested_treatments', 'order_detail.suggested_treatment_id', '=', 'suggested_treatments.suggested_treatment_id')
    					//->where(['suggested_treatments.patient_id' => $dueCollectionAmount->patient_id,"is_billing" => 1, 'order_detail.order_id'=>$dueCollectionAmount->order_master_id,"order_detail_id" => $dueCollectionAmount->order_detail_id])
    					->where(['suggested_treatments.patient_id' => $dueCollectionAmount->patient_id,"is_billing" => 1, 'order_detail.order_id'=>$dueCollectionAmount->order_master_id])
    					->when($request->doctor_id, function ($query) use ($request) {
    						$query->where("treatmentBydoctor_id",$request->doctor_id);
    					})
    					
    					->when($request->fromDate, function ($query) use ($request) {
    						$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
    					})
    					->when($request->toDate, function ($query) use ($request) {
    						$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
    					})
    					->get();
    				// ->toSql();
    				// echo $dueCollectionAmount->patient_id;
    				// echo "<br />";
    				// echo $dueCollectionAmount->order_master_id;
    				// echo "<br />";
    				// echo $dueCollectionAmount->order_detail_id;
    				// dd($treatmentviewlistList);
    					    $arr = [];
        					foreach($treatmentviewlistList as $TreatmentviewlistList){
        						$treatmentBydoctor_id = $TreatmentviewlistList->treatmentBydoctor_id;
        						 $userList = User::where([
        						 'user_id' => $treatmentBydoctor_id
        						 ])->first();
        						$arr[] = array(
        							"treatment_name" => $TreatmentviewlistList->treatment_name,
        							"treatment_id" => $TreatmentviewlistList->treatment_id,
        							"treatment_date" => $TreatmentviewlistList->treatment_date,
        							"treatmentBydoctor_id" => $TreatmentviewlistList->treatmentBydoctor_id,
        							"doctor_name" => $userList->user_name,
        							"suggested_treatment_id" => $TreatmentviewlistList->suggested_treatment_id,
        							"is_billing" => $TreatmentviewlistList->is_billing,
        						); 		
        					}
    					if($due_amount > 0){
        					$lastOrderData[] = array(
        						"patient_id" => $dueCollectionAmount->patient_id,
        						"patientsName" => $dueCollectionAmount->patientsName,
        						"TotalAmount" => $dueCollectionAmount->net_amount,
        						//"TotalAmount" => $dueCollectionAmount->TotalAmount ?? 0,
        						"PaidAmount" => $dueCollectionAmount->paid_amount,
        						//"PaidAmount" => $dueCollectionAmount->PaidAmount ?? 0,
        						"DueAmount" => ($dueCollectionAmount->is_paid == 1) ? $dueCollectionAmount->due_amount : $dueCollectionAmount->net_amount,
        						//"DueAmount" => $due_amount,
        						"SuggestedTreatmentList" => $arr
        					);
    					}
    				}
    				
    			
    			if(!empty($lastOrderData)){
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Order Details.',
    					'totalDueCollection' => $totalDueCollection,
    					'tobecollectedlist' => $lastOrderData
    				]);
    			} else {
    				return response()->json([
    					'status' => 'error',
    					'message' => 'No Data Found!.',
    				], 401);	
    			}
    		}else{
    			return response()->json([
    				'status' => 'error',
    				'message' => 'User is not Authorised.',
    			], 401);
    		}
    	}	
    		
    	public function paymentcollctionlist(Request $request){
    	    if(Auth::user()){
    
        		$monthlyCollection = OrderPaymentDetail::select(
    					DB::raw("(case when payment_mode=1 then 'Cash'
                            when payment_mode=2 then 'Cheque'
                            when payment_mode=3 then 'Card'
                            when payment_mode=4 then 'RTGS'
                            when payment_mode=5 then 'NEFT'
                            when payment_mode=6 then 'Paytm'
                            when payment_mode=7 then 'Coupons'
                            when payment_mode=8 then 'Online'
                            when payment_mode=9 then 'WriteOff'
                            else 'GooglePay' end ) as paymentMode"),
                        "patients.patient_id",
                        "order_payment_detail_id",
                        "order_id",
                        DB::raw("CONCAT(patients.name_prefix,' ',patients.name) as  Name"),
                        "order_payment_detail.amount",
                        "groups.group_name",
                        DB::raw('DATE_FORMAT(order_payment_detail.payment_date, "%d-%M-%Y") as payment_date'),
                        
    				)->join('patients','order_payment_detail.patient_id','=','patients.patient_id')
    				->join('groups','groups.group_id','=','patients.group_id')
    				->whereNull('patients.deleted_at')
    				->where(['order_payment_detail.branch_id' => $request->branch_id,'order_payment_detail.clinic_id' => $request->clinic_id])
    				->where('order_payment_detail.istatus','=','0');
    					$monthlyCollection->when($request->fromDate, function ($query) use ($request) {
    						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
    				})
    				->when($request->toDate, function ($query) use ($request) {
    						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
    			    });	
    			    if(isset($request->doctor_id)){
                        $monthlyCollection->whereIn('order_payment_detail.order_id', function ($query)  use ($request) {
                            $query->select('order_master.order_master_id')
                                ->from('order_master')
                                ->join('order_detail','order_master.order_master_id','=','order_detail.order_id')
                                ->join('suggested_treatments','suggested_treatments.suggested_treatment_id','=','order_detail.suggested_treatment_id')
                                ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
                        });
    				}
                    $monthlyCollectionAmounts = $monthlyCollection->get();
                    // $monthlyCollectionAmounts = $monthlyCollection->toSql();
                    // dd($monthlyCollectionAmounts);
                    
                    $monthlyCollectionAmount = [];
                    $totalCollection = 0;
                    foreach($monthlyCollectionAmounts as $monthlyCollectionedAmount){
                        $monthlyCollectionAmount[] = array(
                            "paymentMode" => $monthlyCollectionedAmount->paymentMode,
                            "patient_id" => $monthlyCollectionedAmount->patient_id,
                            "order_payment_detail_id" => $monthlyCollectionedAmount->order_payment_detail_id,
                            "order_id" => $monthlyCollectionedAmount->order_id,
                            "Name" => $monthlyCollectionedAmount->Name,
                            "amount"=> $monthlyCollectionedAmount->amount,
                            "group_name" => $monthlyCollectionedAmount->group_name,
                            "payment_date" => $monthlyCollectionedAmount->payment_date
                        );
                        $totalCollection+= $monthlyCollectionedAmount->amount;
                    }
                    
    				return response()->json([
    					'status' => 'success',
    					"totalCollection" => $totalCollection,
                        'CollectionList' => $monthlyCollectionAmount,
                        'message' => 'List Found'
    			    ]);
    						
    		}else{
    						
    		    return response()->json([
    				'status' => 'error',
    				'message' => 'User is not Authorised.',
    			    ], 401);
    	    }
    	}	
    	
    	public function reportpatientcollection(Request $request){
    			
    		$clinic_id = $request->clinic_id;
    		$branch_id = $request->branch_id;
    		$pdffile = $request->pdffile;
    		$whatsappfile = $request->whatsappfile;
    		
    		if(Auth::user()){
    			
    			$reportCollection = OrderPaymentDetail::select(
    						DB::raw('sum(order_payment_detail.amount) as amount'),
    						//DB::raw('DATE_FORMAT(order_payment_detail.payment_date, "%d-%M-%Y") as order_date'),
    						'patients.patient_id',
    						'patients.name_prefix as name_prefix',
    						'patients.name as patient_name',
    						'order_payment_detail.payment_mode',
    						'order_payment_detail.order_payment_detail_id  as receipt',
    						'branches.branch_name',
    						//'suggested_treatments.treatmentBydoctor_id',
    						'order_payment_detail.patient_id',
    						"groups.group_name",
    						DB::raw('(select `suggested_treatments`.`treatmentBydoctor_id` from suggested_treatments inner join  suggested_treatment_payment on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id where suggested_treatment_payment.order_id=order_master.order_master_id limit 1) as treatmentBydoctor_id')
    						)
    			//->whereNot('order_master.is_paid', 0)
    			->where(['order_payment_detail.clinic_id' => $clinic_id,'order_master.istatus' =>0])
    			->where('order_payment_detail.amount','>',0)
    			->whereNull('patients.deleted_at')
    			->when($request->branch_id, fn ($query, $branch_id) => $query->WhereIn('order_payment_detail.branch_id',$branch_id))
    			->join('patients', 'patients.patient_id', '=', 'order_payment_detail.patient_id')
    			//->join('order_payment_detail', 'order_payment_detail.order_payment_detail_id', '=', 'suggested_treatment_payment.order_payment_detail_id')
    			->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
    			// ->join('order_detail', 'order_detail.order_id', '=', 'order_master.order_master_id')
    			->join('branches', 'branches.branch_id', '=', 'order_payment_detail.branch_id')
    			->join('groups','groups.group_id','=','patients.group_id')
    			//->leftjoin('suggested_treatment_payment', 'suggested_treatments.suggested_treatment_id', '=', 'suggested_treatment_payment.suggested_treatments_id')
    			//->join('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'order_detail.suggested_treatment_id')
    			->where("order_payment_detail.istatus","=",0)
    			// ->when($request->doctor_id, function ($query) use ($request) {
    			// 	$query->where('suggested_treatments.treatmentBydoctor_id' ,'=',$request->doctor_id);
    			// })
    			->when($request->doctor_id, fn ($query, $doctor_id) => $query->WhereIn(
    				'order_master.order_master_id',
    				function ($query) use ($doctor_id) {
    					$query->select('suggested_treatment_payment.order_id')
    						->from(with(new SuggestedTreatments)->getTable())
    						->join('suggested_treatment_payment','suggested_treatments.suggested_treatment_id','=','suggested_treatment_payment.suggested_treatments_id')
    						//->where('suggested_treatment_payment.order_id','=','order_master.order_master_id')
    						->where('suggested_treatments.treatmentBydoctor_id', $doctor_id);
    				}
    			))
    			->when($request->fromDate, function ($query) use ($request) {
    				$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
    			})
    			->when($request->toDate, function ($query) use ($request) {
    				$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
    			})
    			->when($request->selected_date, function ($query) use ($request) {
    				$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
    			})
    			// ->when($request->search_branch_id, function ($query) use ($request) {
    			// 	$query->where('suggested_treatment_payment.branch_id' ,'=',$request->search_branch_id);
    			// })	
    			->when($request->payment_mode, function ($query) use ($request) {
    				$query->where('order_payment_detail.payment_mode' ,'=',$request->payment_mode);
    			})
    			->when($request->group_id, function ($query) use ($request) {
    				$query->where('patients.group_id' ,'=',$request->group_id);
    			})
    			->when($request->month, function ($query) use ($request) {
    				$query->where(DB::raw("MONTH(order_payment_detail.payment_date)"),'=',$request->month);
    			})
    			->when($request->year, function ($query) use ($request) {
    				$query->where(DB::raw("YEAR(order_payment_detail.payment_date)"),'=',$request->year);
    			})
    			->orderBy('order_payment_detail.payment_date','asc')
    			->groupBy('patients.patient_id','order_payment_detail.payment_mode')
    			->get(); 
    			// ->toSql();
    			// dd($reportCollection);
    			
    			// $branchData = Branch::where('branch_id','=',$request->branch_id)->first();
    			// $branchName = $branchData->branch_name;
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
    			
    			$arr = [];
    			$mode = "";
    			$grand_amount = 0;
    			
    			/* if ($reportCollection->isNotEmpty($reportCollection)) {
    					echo "vvvvvvvvvvv";
    			}else{
    				echo "xxxxxxxxx";
    			}
    			die; */ 
    			$Cash = 0;
    			$Cheque = 0;
    			$Card = 0;
    			$Online = 0;
    			$other = 0;
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
    						
    						if($payment_mode == 1){
    							$Cash += $ReportCollection->amount;
    						}else if($payment_mode == 2){
    							$Cheque += $ReportCollection->amount;
    						}else if($payment_mode == 3){
    							$Card += $ReportCollection->amount;
    						}else if($payment_mode == 8){
    							$Online += $ReportCollection->amount;
    						} else {
    							$other += $ReportCollection->amount;
    						}
    						
    						$grand_amount += $ReportCollection->amount;
    						$arr[] = array(
    						
    						'amount' =>$ReportCollection->amount,
    						'order_date' =>$ReportCollection->order_date,
    						'patient_name' =>$ReportCollection->name_prefix." ".$ReportCollection->patient_name,
    						'payment_mode' =>$mode,
    						'receipt' =>"RCPT".$ReportCollection->receipt,
    						'branch_name' =>$ReportCollection->branch_name,
    						'treatmentBydoctor_id' =>$ReportCollection->treatmentBydoctor_id,
    						'patient_id' =>$ReportCollection->patient_id,
    						"group_name" => $ReportCollection->group_name,
    						
    						);						
    						/* return response()->json([
    								'status' => 'success',
    								'message' => 'reportData',
    								'dailyCollection' => $arr
    						], 401); */							
    					}else{							
    						/* return response()->json([
    								'status' => 'success',
    								'message' => 'reportData',
    								'dailyCollection' => $arr
    						], 401); */
    					}
    				}
    					
    					
    				if($pdffile == 1 && !empty($arr)){
    								
    					$pdf = PDF::loadView('patientcollection_report',['dailyCollection' => $arr,'grand_total' => $grand_amount,"branchName" => $branchName,"Duration" => $Duration]);
    							
    					$fileName = date('d-m-Y')."_patientCollection";
    					
    					$content = $pdf->download()->getOriginalContent();
    					Storage::put('public/assets/patientcollection_report/'.$fileName . '.pdf',$content);
    					
    					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
    						$pdf->save(public_path('assets/patientcollection_report/')  . $fileName. '.pdf');	
    					}else {
    						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/patientcollection_report/')  . $fileName. '.pdf');
    
    					}
    			
    					$dailycollectionFile = asset('assets/patientcollection_report/'. $fileName. '.pdf');
    			
    					//return $pdf->download($fileName . '.pdf');
    					
    					$key = $_ENV['WHATSAPPKEY'];		
    					$dailycollectionListFile = asset('assets/patientcollection_report/'. $fileName. '.pdf');
    					$msg = "Dear User, Please find attached details of treatments.";
    								
    					if($whatsappfile == 1){
    						$users = new User();
    						$currentUser = Auth::user();
    
    						$mobileNo = $currentUser->mobile_no;
    						$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
    						
    				// 		$statusofMessage = $status->status;
    						// $Response = $status->response;
    					
    				// 		if($statusofMessage == "success"){
    						
    							return response()->json([
    								'status' => 'success',
    								'message' => 'DailyCollection Report sent on your registered mobile number.',
    								'dailycollectionFile' => $dailycollectionFile
    							]);
    							
    				// 		}else{
    							
    				// 			return response()->json([
    				// 				'status' => 'error',
    				// 				'message' => $Response.'.Please contact admin.',
    				// 			], 401);
    				// 		}
    					}else{
    						return response()->json([
    							'status' => 'success',
    							'message' => 'reportData',
    							'grand_total' => $grand_amount,
    							'dailycollection' => $arr,
    							'dailycollectionFile' => $dailycollectionFile
    						]);
    					}
    				}else{
    					return response()->json([
    						'status' => 'success',
    						'message' => 'reportData.',
    						'grand_total' => $grand_amount,
    						'dailycollection' => $arr,
    						"Cash" => $Cash,
    						"Cheque" => $Cheque,
    						"Card" => $Card,
    						"Online" => $Online,
    						"other" => $other
    					]);
    				}
    			}else{
    				return response()->json([
    					'status' => 'error',
    					'message' => 'No Record Found.',
    					'dailycollection' => $arr,
    					"Cash" => $Cash,
    					"Cheque" => $Cheque,
    					"Card" => $Card,
    					"Online" => $Online,
    					"other" => $other
    				]);
    			}
    		}else{
    			return response()->json([
    					'status' => 'error',
    					'message' => 'User is not Authorised.',
    			], 401);
    		}
    	}
    	
    	
    	public function newBillGeneratedAmount(Request $request){
			
			if(Auth::user()){
				$OrderLists = OrderMaster::join('patients','order_master.patient_id','=','patients.patient_id')
				    ->select(
				        'order_master.order_master_id',
				        'order_master.bill_no',
				        'order_master.net_amount',
				        'order_master.discount',
				        'order_master.paid_amount',
				        'order_master.due_amount',
				        'order_master.adjusted_amount',
				        'order_master.created_at',
				        'patients.patient_id',
				        'patients.case_no',
				        'patients.name_prefix',
				        'patients.name',
				        'patients.date_of_birth',
				        'patients.address',
				        'patients.mobile_no',
				        'patients.gender'
			        )
			        ->where('order_master.branch_id', $request->branch_id)
                    ->where('order_master.clinic_id', $request->clinic_id)
                    ->where('order_master.istatus',0)
                    ->whereDate('order_master.created_at', '>=', $request->fromDate)
                    ->whereDate('order_master.created_at', '<=', $request->toDate)
        			->get();
					
					$arr = [];
					$net_amount = 0;
					$discount = 0;
					$paid_amount = 0;
					$due_amount = 0;
					
					foreach($OrderLists as $OrderList){
						$net_amount += $OrderList->net_amount;
    					$discount += $OrderList->discount;
    					$paid_amount += $OrderList->paid_amount;
    					$due_amount += $OrderList->due_amount;
						$arr[] = array(
							'order_master_id' => $OrderList->order_master_id,
    				        'bill_no' => $OrderList->bill_no,
    				        'net_amount' => $OrderList->net_amount,
    				        'discount' => $OrderList->discount,
    				        'paid_amount' => $OrderList->paid_amount,
    				        'due_amount' => $OrderList->due_amount,
    				        'adjusted_amount' => $OrderList->adjusted_amount,
    				        'patient_id' => $OrderList->patient_id,
    				        'case_no' => $OrderList->case_no,
    				        'name_prefix' => $OrderList->name_prefix,
    				        'name' => $OrderList->name,
    				        'date_of_birth' => $OrderList->date_of_birth,
    				        'address' => $OrderList->address,
    				        'mobile_no' => $OrderList->mobile_no,
    				        'gender' => $OrderList->gender,
    				        'created_at' => date('d-m-Y',strtotime($OrderList->created_at))
						); 
					}
					
    				return response()->json([
    				    'status' => 'success',
						'message' => 'success.',
        				'newBillGeneratedAmount' => $arr,
        				'net_amount' => $net_amount,
    					'discount' => $discount,
    					'paid_amount' => $paid_amount,
    					'due_amount' => $due_amount,
    				]);
			}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
				], 401);
			}
		}
		
}
