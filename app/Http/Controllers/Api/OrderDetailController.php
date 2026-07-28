<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderMaster;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\SuggestedTreatments;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Branch;
use App\Services\AuthkeyWhatsAppService;

class OrderDetailController extends Controller
{
    public function orderdetailwhatsappdata(Request $request){
		
		if(Auth::user()){
			
				$billData = OrderMaster::where(['order_master_id'=> $request->order_id])->first();
				$billNo = $billData->bill_no;
			    $branch = Branch::where("branch_id",'=',$billData->branch_id)->first();
				$patient_id = $request->patient_id;
				$printfile = $request->printfile;

				$patientData = Patient::where(['patient_id'=> $patient_id])->first();
				$patientMobileNo = $patientData->mobile_no;
				$name_prefix = $patientData->name_prefix;
				$patient_name = $patientData->name;
				$address = $patientData->address;
				$case_no = $patientData->case_no;
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
					 ->where(['order_detail.order_id' => $request->order_id])
					->orderBy('order_master.created_at', 'desc')
					->get();
					
					
					$arr = [];
					$grand_amount = 0;
					
					foreach($billList as $BillList){
						
						$treatmentBydoctor_id = $BillList->treatmentBydoctor_id;
						
						 $userList = User::where([
						 'user_id' => $treatmentBydoctor_id
						 ])->first();
						 
						// echo "<pre>";
						// print
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
							->where(['order_master_id' => $request->order_id])
							->first();
						
					$masterOrderPaymentDetail = OrderPaymentDetail::select(
							DB::raw('DATE_FORMAT(created_at, "%d-%M-%Y") as created_date'),
							'order_payment_detail_id',
							'payment_mode',
							'amount',
							DB::raw('DATE_FORMAT(payment_date, "%d-%M-%Y") as payment_date')
							)
							->where(['order_id' => $request->order_id,'istatus' => 0])
							->get();
					
						//send bill detail pdf 
				
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "Dear User, Please find attached bill details of treatments.";
						$fileName = $billNo."_".date('d-m-Y');
						 

						$pdf = PDF::loadView('billdetail',['BillorderdetailList' => $arr,
						'masterOrderDetails' => $masterOrderDetails,
						'masterOrderPaymentDetail' => $masterOrderPaymentDetail,
						'name_prefix' => $name_prefix,
						'patient_name' => $patient_name,
						'case_no' => $case_no,
						//'address' => $address,
						'address' => $branch->branch_name,
						'grand_amount'=>$grand_amount
						
						]);
						
						
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/orderdetails/'.$fileName . '.pdf',$content);
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/orderdetails/')  . $fileName. '.pdf');	
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/orderdetails/')  . $fileName. '.pdf');

						}
						
						
						
						$billdetailFile = asset('assets/orderdetails/'. $fileName. '.pdf');
						
						//return $pdf->download($fileName . '.pdf');
						
						if($printfile == 1){
							$users = new User();
							//$patientMobileNo 9904500629
							// $status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$billdetailFile);
							$whatsappService = new AuthkeyWhatsAppService();
            				//$wid = "30216"; // template id
            				$wid = "31157";
            				$bodyValues = [
            					"1" => $fileName
            				];
            				
            				$statusofMessage = $whatsappService->sendText($patientMobileNo, $wid, $bodyValues);
            				// $wid = "31158";
            				// $bodyValues = [];
            				// $fileUrl = $billdetailFile;
            				// $statusofMessage = $whatsappService->sendMedia($patientMobileNo, $wid, $fileUrl,$bodyValues);
            				
							// $statusofMessage = $status->status;
							// $Response = $status->response;
											
						
							//if($statusofMessage == "success"){
								return response()->json([
									'status' => 'success',
									'pdfFileUrl' => $billdetailFile,
									'message' => 'Bill Details sent on your registered mobile number.',
								], 200);
				// 			}else{
								
				// 				return response()->json([
				// 					'status' => 'error',
				// 					//'message' => $Response.'.Please contact admin.',
				// 					'message' => ' Please contact admin.',
				// 				], 401);
				// 			}
						}else{
							return response()->json([
									'status' => 'success',
									'pdfFileUrl' => $billdetailFile
								], 200);
						}
						
					return response()->json([
					
					'BillorderdetailList' => $arr,
					'masterOrderPaymentDetail' => $masterOrderPaymentDetail
					
					]);
				
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
					], 401);
			}
	}
}
