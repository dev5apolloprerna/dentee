<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Labwork;
use App\Models\SuggestedTreatments;
use App\Models\OrderMaster;
use App\Models\OrderDetail;
use App\Models\MaterialMaster;
use App\Models\Branch;
use App\Models\User;
use App\Models\Lab;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Lab_Work;
use App\Models\Lab_Work_Detail;
use App\Models\Clinic;


class LabworkController extends Controller
{
    public function addLabwork(Request $request){
					
			if(Auth::user()){
				
					$labWork = [];
					$suggested_treatment_id = $request->suggested_treatment_id;
					$lab_id = $request->lab_id;
					$material_id = $request->material_id;
					$printfile = $request->printfile;
					$teethChange = $request->teeth_change;
					$teeth_array = explode(",", $teethChange);
					$teethCount = count($teeth_array);
					
					
					if(!empty($suggested_treatment_id)){
					$suggestedTreatmentData = SuggestedTreatments::where(['suggested_treatment_id'=>$suggested_treatment_id,'deleted_at' => NULL])->first();
					
					$masterOrderDetails = OrderDetail::select(
							'order_detail_id',
							'order_id'
							)
							->where(['suggested_treatment_id' => $suggested_treatment_id])
							->first();
					$orderId = $masterOrderDetails->order_id;
					$orderDetailId = $masterOrderDetails->order_detail_id;
					}
					if(!empty($lab_id)){
						$labData = Lab::where(['lab_id'=>$lab_id,'deleted_at' => NULL])->first();
						$mobileNo = $labData->mobile_no;
					}
					
					if(!empty($material_id)){
						$MaterialData = MaterialMaster::where(['material_id'=>$material_id])->first();
						$materialprice = $MaterialData->price;
						$labPrice = $teethCount*$materialprice;
					}
					
					$addLabwork = Labwork::create([
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
						'patient_id' => $request->patient_id,
						'lab_id' => $request->lab_id,
						'treatment_id' => $request->treatment_id,
						'doctor_id' => $request->doctor_id,
						'material_id' => $request->material_id,
						'teeth_change' => $request->teeth_change,
						'material_price' => $materialprice,
						'lab_price' => $labPrice,
						'shade' => $request->shade,
						'notes' => $request->notes,
						'order_detail_id' => $orderDetailId,
						'order_id' => $orderId
						]);
								
					$lastInsertID = DB::getPdo()->lastInsertId();
					
					$labworkData = Labwork::where(['labwork_master_id'=>$lastInsertID])->first();
					$patientId = $labworkData['patient_id'];
					$branchId = $labworkData['branch_id'];
					$doctorId = $labworkData['doctor_id'];
					$teethChange = $labworkData['teeth_change'];
					$materialId = $labworkData['material_id'];
					$labId = $labworkData['lab_id'];
					$shade = $labworkData['shade'];
					$notes = $labworkData['notes'];
					$created_at = $labworkData['created_at'];
					
					$teeth_array = explode(",", $teethChange);
					$teethCount = count($teeth_array);
					
					$patientData = Patient::where(['patient_id'=> $patientId])->first();
					$patientName = $patientData->name;
					$case_no = $patientData->case_no;
					
					$branchData = Branch::where(['branch_id'=>$branchId])->first();
					$branchName = $branchData->branch_name;
					$mobileNo = $branchData->mobile_no;
					
					$userData = User::where(['user_id'=>$doctorId])->first();
					$user_name = $userData->user_name;
					$lastName = $userData->last_name;
					
					$MaterialData = MaterialMaster::where(['material_id'=>$materialId])->first();
					$productName = $MaterialData->product_name;
					
					$LabData = Lab::where(['lab_id'=>$labId])->first();
					$labMobileNo = $LabData->mobile_no;
					//die;
					
					$labworkDate = date("d/m/Y", strtotime($created_at));
					
					$labWork = array(
						'date' => $labworkDate,
						'patient_name' => $patientName,
						'branch_name' => $branchName,
						'dentist_name' => $user_name,
						'branch_phone' => $mobileNo,
						'teeth_count' => $teethCount,
						'teeth' => $teethChange,
						'product_name' => $productName,
						'shade' => $shade,
						'notes' => $notes
					);
					
					
					$OrderDetail = OrderDetail::where('order_detail_id','=',$orderDetailId)->update([

							'labwork_master_id' => $lastInsertID,
							'lab_id' => $request->lab_id
					]);
					
					$pdf = PDF::loadView('labwork',['labWorkData' => $labWork,
						
						]);
						$key = $_ENV['WHATSAPPKEY'];
						$labMsg = "Dear User, Please find attached details of product.";
						$fileName = trim($case_no)."_".time()."_labwork";
						
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/prescription/'.$fileName . '.pdf',$content);
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/labwork/')  . $fileName. '.pdf');	
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/labwork/')  . $fileName. '.pdf');

						}
						
						$labworkDataUpdate = Labwork::where(['labwork_master_id'=>$lastInsertID])->update([

							'file_name' => $fileName,
						]);
						
						
						$labFile = asset('assets/labwork/'. $fileName. '.pdf');
						
						//return $pdf->download($fileName . '.pdf');
						
										if($printfile == 1){
											$users = new User();
											$status = $users->sendWhatsappMessage($labMobileNo,$key,$labMsg,$labFile);
								// 			$statusofMessage = $status->status;
											// $Response = $status->response;
										
								// 			if($statusofMessage == "success"){
												return response()->json([
													'status' => 'success',
													'pdfFileUrl' => $labFile,
													'message' => 'Lab details sent on lab`s registered mobile number.labwork added successfully.',
												], 401);
								// 			}else{
												
								// 				return response()->json([
								// 					'status' => 'error',
								// 					'message' => $Response.'.Please contact admin.',
								// 				], 401);
								// 			}
										 }else{
											return response()->json([
													'status' => 'success',
													'pdfFileUrl' => $labFile
												], 401);
										} 
							
							
									
									/* return response()->json([
										'status' => 'success',
										'message' => 'labwork added successfully',
										'addLabwork' => $addLabwork
										
										]); */
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		
		
		public function updatelabwork(Request $request,$id){
			
				if(Auth::user()){

					$labworkUpdate = Labwork::where('labwork_master_id','=',$id)->update([

							'doctor_id' => $request->doctor_id,
							'shade' => $request->shade,
							'notes' => $request->notes
							
					]);
					return response()->json(['status' => 'success','message' => 'Labwork Details Updated Successfully.']);
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		
		}
		
		public function listlabWork(Request $request){
			 
			 if(Auth::user()){
				
				$clinic_id = $request->clinic_id;
				$patient_id = $request->patient_id;
				$branch_id = $request->branch_id;
				
				$treatmentDataLabwork = Labwork::select(
					'labs.lab_name',
					'labs.lab_id',
					'treatments.name as treatment_name',
					'material_master.product_name',
					'labwork.material_price',
					'labwork.teeth_change as teeth',
					'labwork.shade',
					'labwork.notes',
					'labwork.labwork_master_id',
					'labwork.created_at',
					'patients.name_prefix as name_prefix',
					'patients.name as name',
					'users.user_name as doctor_name',
					'users.user_id as doctor_id',
					
					)
					->where(['labwork.istatus' => 0,
					'labwork.clinic_id' => $clinic_id,'labwork.patient_id' => $patient_id,'labwork.branch_id' => $branch_id])
					
					->join('patients', 'patients.patient_id', '=', 'labwork.patient_id')
					->join('labs','labs.lab_id', '=', 'labwork.lab_id')
					->join('treatments','treatments.treatment_id', '=', 'labwork.treatment_id')
					->join('material_master','material_master.material_id', '=', 'labwork.material_id')
					->join('users', 'users.user_id', '=', 'labwork.doctor_id')
					->get();
					
					if(count($treatmentDataLabwork) != 0){
						
					foreach($treatmentDataLabwork as $TreatmentDataLabwork){
						
						$teeth = $TreatmentDataLabwork->teeth;
						
						$teeth_array = explode(",", $teeth);
						$resultCount = count($teeth_array);
						
						$orgDate = $TreatmentDataLabwork->created_at;  
						$newCreatedDate = date("d-M-Y", strtotime($orgDate));
						
						$arr[] = array(
							"lab_name" => $TreatmentDataLabwork->lab_name,
							"lab_id" => $TreatmentDataLabwork->lab_id,
							"treatment_name" => $TreatmentDataLabwork->treatment_name,
							"product_name" => $TreatmentDataLabwork->product_name,
							"material_price" => $TreatmentDataLabwork->material_price,
							"teeth" => $TreatmentDataLabwork->teeth,
							"teeth_count" => $resultCount,
							"shade" => $TreatmentDataLabwork->shade,
							"notes" => $TreatmentDataLabwork->notes,
							"name_prefix" => $TreatmentDataLabwork->name_prefix,
							"name" => $TreatmentDataLabwork->name,
							"doctor_name" => $TreatmentDataLabwork->doctor_name,
							"doctor_id" => $TreatmentDataLabwork->doctor_id,
							"labwork_master_id" => $TreatmentDataLabwork->labwork_master_id,
							"created_at" => $newCreatedDate,
						); 
					}
					
						return response()->json([
							'status' => 'success',
							'treatmentData' => $arr
						]);
						
					}else{
						return response()->json([
							'status' => 'error',
							'message' => 'No Record Found.'
						]);
					}
						
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}
		 }
		 
		 
		public function labworkreport(Request $request){
			
			  set_time_limit(300);
			  if(Auth::user()){
				  
				$clinic_id = $request->clinic_id;
				//$patient_id = $request->patient_id;
				$branch_id = $request->branch_id;
				$pdffile = $request->pdffile;
				$whatsappfile = $request->whatsappfile;
				$arr = [];
				$treatmentDataLabwork = Labwork::select(
				
					 DB::raw('DATE_FORMAT(labwork.created_at, "%d-%M-%Y") as order_date'),
					'patients.name_prefix as name_prefix',
					'patients.name as name',
					'material_master.product_name',
					'labwork.teeth_change as teeth',
					'labwork.material_price',
					'labwork.lab_price',
					'order_detail.rate',
					'branches.branch_name',
					'labs.lab_name'
					
					)
					->where(['labwork.istatus' => 0,'labwork.clinic_id' => $clinic_id])
					->when($request->branch_id, fn ($query, $branch_id) => $query->WhereIn('labwork.branch_id',$branch_id))
					->join('branches', 'branches.branch_id', '=', 'labwork.branch_id')
					->join('patients', 'patients.patient_id', '=', 'labwork.patient_id')
					->join('labs','labs.lab_id', '=', 'labwork.lab_id')
					->join('treatments','treatments.treatment_id', '=', 'labwork.treatment_id')
					->join('material_master','material_master.material_id', '=', 'labwork.material_id')
					->join('users', 'users.user_id', '=', 'labwork.doctor_id')
					->join('order_detail', 'order_detail.labwork_master_id', '=', 'labwork.labwork_master_id')
					
					->when($request->fromDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(labwork.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
									})
					->when($request->toDate, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(labwork.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
									})
					->when($request->labname, function ($query) use ($request) {
										$query->where('labs.lab_name','LIKE', '%' . $request->labname .'%');
									})
					->when($request->branchname, function ($query) use ($request) {
										$query->where('branches.branch_name','LIKE', '%' . $request->branchname .'%');
									})
					->when($request->selected_date, function ($query) use ($request) {
										$query->where(DB::raw("DATE_FORMAT(labwork.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
									})
					->when($request->month, function ($query) use ($request) {
										$query->where(DB::raw("MONTH(labwork.created_at)"),'=',$request->month);
									})
					->when($request->year, function ($query) use ($request) {
										$query->where(DB::raw("YEAR(labwork.created_at)"),'=',$request->year);
									})
					->get();
					// 'istatus' => 0,
					$LabworkData = DB::table('labs')->select('labs.lab_name')
					->where(['clinic_id' => $clinic_id])
				    ->where('labs.lab_name','LIKE', $request->labname)
					->first();
					//dd($LabworkData);
				// 	$branchData = Branch::where('branch_id','=',$request->branch_id)->first();
				// 	$branchName = $branchData->branch_name;
				    
				    $branchData = Branch::whereIn('branch_id',$request->branch_id)->get();
    				$branchName = "";
    				foreach($branchData as $branch){
    			        $branchName .= $branch->branch_name . ",";
    				}
    				$branchName = rtrim($branchName,",");
    				
					$Duration = "";
					if(isset($request->toDate) && $request->toDate != ""){
        			    $Duration .= date('d-m-Y',strtotime($request->fromDate)) ." To ". date('d-m-Y',strtotime($request->toDate));
        			}
					if($request->selected_date != ""){
					    $Duration .= $request->selected_date ." ";
					}
					if($request->month != "" && $request->year != ""){
					    $Duration .= $request->month ."-".$request->year;
					}
    				// 	if($request->year != ""){
    				// 	    $Duration .= $request->year ." ";
    				// 	}
					$grand_amount = 0;
					if(count($treatmentDataLabwork) != 0){
						
							foreach($treatmentDataLabwork as $TreatmentDataLabwork){
								
								$teeth = $TreatmentDataLabwork->teeth;
								
								$teeth_array = explode(",", $teeth);
								$teethCount = count($teeth_array);
								
								if(empty($TreatmentDataLabwork->lab_price)){
									
									$labPrice = 0;
								}else{
									$labPrice = $TreatmentDataLabwork->lab_price;
								}
								$grand_amount += $labPrice;
								$arr[] = array(
								
									"order_date" => $TreatmentDataLabwork->order_date,
									"patient_name" => $TreatmentDataLabwork->name_prefix." ".$TreatmentDataLabwork->name,
									"product_name" => $TreatmentDataLabwork->product_name,
									"teeth" => $TreatmentDataLabwork->teeth,
									"unit" => $teethCount,
									"material_price" => $TreatmentDataLabwork->material_price,
									"lab_price" => $labPrice,
									"rate" => $TreatmentDataLabwork->rate,
									"branch_name" => $TreatmentDataLabwork->branch_name,
									"lab_name" => $TreatmentDataLabwork->lab_name
								); 
	
							
							}
							
							if($pdffile == 1){
									
										$pdf = PDF::loadView('labwork_report',['labWorkData' => $arr,'grand_total' => $grand_amount,
										"branchName" => $branchName,"Duration" => $Duration,"LabworkData" => $LabworkData
										]);
										
										$fileName = date('d-m-Y')."_labwork";
										
										$content = $pdf->download()->getOriginalContent();
										Storage::put('public/assets/labwork_report/'.$fileName . '.pdf',$content);
										
										if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
											$pdf->save(public_path('assets/labwork_report/')  . $fileName. '.pdf');	
										}else {
											$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/labwork_report/')  . $fileName. '.pdf');

										}
								
										$labFile = asset('assets/labwork_report/'. $fileName. '.pdf');
										$key = $_ENV['WHATSAPPKEY'];
										$msg = "Dear User, Please find attached details of Labwork.";
										//return $pdf->download($fileName . '.pdf');
										
										if($whatsappfile == 1){
										$users = new User();
										$currentUser = Auth::user();

										$mobileNo = $currentUser->mobile_no;
										$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$labFile);
										
								// 		$statusofMessage = $status->status;
										// $Response = $status->response;
									
								// 		if($statusofMessage == "success"){
											return response()->json([
												'status' => 'success',
												'pdfFileUrl' => $labFile,
												'message' => 'Labwork Report sent on your registered mobile number.',
												'grand_amount' => $grand_amount
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
											'labworkData' => $arr,
											'labworkDataFile' => $labFile,
											'grand_amount' => $grand_amount
										]);
									}
										
										
								}
								
								return response()->json([
											'status' => 'success',
											'grand_amount' => $grand_amount,
											'labworkData' => $arr
										]);
					}else{
								return response()->json([
									'status' => 'error',
									'message' => 'No Record Found.',
									'labworkData' => $arr,
									'grand_amount' => $grand_amount
								]);
					}
		 
			  }else{
				  
				  return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				  
			  }
		}
		 
		public function deletelabWork(Request $request,$id){
			 
			 if(Auth::user()){
				 
				 
				$labworkDataObj = Labwork::where(['labwork_master_id'=>$id])->first();
				 
				$orderId = $labworkDataObj->order_id;
				$orderDetailId = $labworkDataObj->order_detail_id;
				 
					if(!empty($labworkDataObj)){
						
					$OrderDetail = OrderDetail::where('order_detail_id','=',$orderDetailId)->update([

							'labwork_master_id' => 0,
							'lab_id' => 0
					]);


						$labworkDelete = Labwork::find($id);
						$labworkDelete->delete();
						
						
						
						return response()->json([
							'status' => 'success',
							'message' => 'Labwork data deleted Successfully.',]);
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
		
		public function whatsappLabWork(Request $request,$id){
			 
			 if(Auth::user()){
				 
				 $whatsappfile = $request->whatsappfile;
				 $key = $_ENV['WHATSAPPKEY'];
				 $labMsg = "Dear User, Please find attached details of product.";
				 $labworkDataObj = Labwork::where(['labwork_master_id'=>$id])->first();
				 
				 $labFile = "";
				 if(!empty($labworkDataObj)){
					 
					$file_name = $labworkDataObj->file_name;
					$labFile = asset('assets/labwork/'. $file_name. '.pdf');
				 }
							
							
				 if($whatsappfile == 1){
						$users = new User();
						$currentUser = Auth::user();

						$mobileNo = $currentUser->mobile_no;
						$status = $users->sendWhatsappMessage($mobileNo,$key,$labMsg,$labFile);
										
				// 		$statusofMessage = $status->status;
						//$Response = $status->response;
									
				// 			if($statusofMessage == "success"){
								return response()->json([
									'status' => 'success',
									'pdfFileUrl' => $labFile,
									'message' => 'Labwork Report sent on your registered mobile number.',
										], 401);
								// }else{
											
								// return response()->json([
								// 	'status' => 'error',
								// 	'message' => $Response.'.Please contact admin.',
								// 		], 401);
								// }
							}else{
								return response()->json([
									'status' => 'success',
									'labworkDataFile' => $labFile
								]);
							}
				 
				 
			}else{
				  
				  return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				  
			  }
		}
		
		
    public function addNewLabwork(Request $request){
					
        if(Auth::user()){
        	
            $labWork = [];
            $lab_id = $request->lab_id;
            $material_id = $request->material_id;
            $printfile = $request->printfile;
            $teethChange = $request->teeth_change;
            $teeth_array = explode(",", $teethChange);
            $teethCount = count($teeth_array);
            
            if(!empty($lab_id)){
                $labData = Lab::where(['lab_id'=>$lab_id,'deleted_at' => NULL])->first();
                $mobileNo = $labData->mobile_no;
            }
            
            if(!empty($material_id)){
                $MaterialData = MaterialMaster::where(['material_id'=>$material_id])->first();
                $materialprice = $MaterialData->price;
                $labPrice = $teethCount*$materialprice;
            }
            
            $addLabwork = Lab_Work::create([
                'clinic_id' => $request->clinic_id,
                'branch_id' => $request->branch_id,
                'patient_id' => $request->patient_id,
                'lab_id' => $request->lab_id,
                'doctor_id' => $request->doctor_id,
                'material_id' => $request->material_id,
                'teeth_change' => $request->teeth_change,
                'material_price' => $materialprice,
                'lab_price' => $labPrice,
                'shade' => $request->shade,
                'notes' => $request->notes,
                'iLabWorkStatus' => 1
            ]);
            
            $lastInsertID = DB::getPdo()->lastInsertId();
            
            Lab_Work_Detail::create([
                'labwork_master_id' => $lastInsertID,
        		'clinic_id' => $request->clinic_id,
        		'branch_id' => $request->branch_id,
        		'lab_id' => $request->lab_id,
        		'doctor_id' => $request->doctor_id,
        		'lab_work_status' => 1,
        		'remarks' => "Ordered"
            ]);
            
            $labworkData = Lab_Work::where(['labwork_master_id'=>$lastInsertID])->first();
            $patientId = $labworkData['patient_id'];
            $branchId = $labworkData['branch_id'];
            $doctorId = $labworkData['doctor_id'];
            $teethChange = $labworkData['teeth_change'];
            $materialId = $labworkData['material_id'];
            $labId = $labworkData['lab_id'];
            $shade = $labworkData['shade'];
            $notes = $labworkData['notes'];
            $created_at = $labworkData['created_at'];
            
            $teeth_array = explode(",", $teethChange);
            $teethCount = count($teeth_array);
            
            $patientData = Patient::where(['patient_id'=> $patientId])->first();
            $patientName = $patientData->name;
            $case_no = $patientData->case_no;
            
            $branchData = Branch::where(['branch_id'=>$branchId])->first();
            $branchName = $branchData->branch_name;
            $mobileNo = $branchData->mobile_no;
            
            $userData = User::where(['user_id'=>$doctorId])->first();
            $user_name = $userData->user_name;
            $lastName = $userData->last_name;
            
            $MaterialData = MaterialMaster::where(['material_id'=>$materialId])->first();
            $productName = $MaterialData->product_name;
            
            $LabData = Lab::where(['lab_id'=>$labId])->first();
            $labMobileNo = $LabData->mobile_no;
            
            $Clinic = Clinic::where(['clinic_id'=>$request->clinic_id])->first();
            //die;
            $key = $_ENV['WHATSAPPKEY'];
            $lab_Msg = "
lab order generated for ".$LabData->lab_name." from ". $Clinic->clinic_name ." ". $branchName ." 

Patient name : ". $patientName ."
Product name : ". $productName ."
Teeth nubner : ". $teethCount ."
Shade : ". $shade ."
Notes :". $notes ."
            ";
            $users = new User();
            $users->sendWhatsappMessage($labMobileNo,$key,$lab_Msg,"");
            
            $labworkDate = date("d/m/Y", strtotime($created_at));
            
            $labWork = array(
                'date' => $labworkDate,
                'patient_name' => $patientName,
                'branch_name' => $branchName,
                'dentist_name' => $user_name,
                'branch_phone' => $mobileNo,
                'teeth_count' => $teethCount,
                'teeth' => $teethChange,
                'product_name' => $productName,
                'shade' => $shade,
                'notes' => $notes
            );
            
            
            $pdf = PDF::loadView('lab_work',['labWorkData' => $labWork,
            
            ]);
            
            $labMsg = "Dear User, Please find attached details of product.";
            $fileName = trim($case_no)."_".time()."_labwork";
            
            $content = $pdf->download()->getOriginalContent();
            Storage::put('public/prescription/'.$fileName . '.pdf',$content);
            
            if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
                $pdf->save(public_path('assets/labwork/')  . $fileName. '.pdf');	
            }else {
                $pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/labwork/')  . $fileName. '.pdf');
            }
            
            $labworkDataUpdate = Lab_Work::where(['labwork_master_id'=>$lastInsertID])->update([
                'file_name' => $fileName,
            ]);
            
            $labFile = asset('assets/labwork/'. $fileName. '.pdf');
            
            //return $pdf->download($fileName . '.pdf');
    
        	if($printfile == 1){
        		$users = new User();
        		$status = $users->sendWhatsappMessage($labMobileNo,$key,$labMsg,$labFile);
        // 		$statusofMessage = $status->status;
        		// $Response = $status->response;
        	
        // 		if($statusofMessage == "success"){
        			return response()->json([
            				'status' => 'success',
            				'pdfFileUrl' => $labFile,
            				'message' => 'Lab details sent on lab`s registered mobile number.labwork added successfully.',
            			], 401);
        // 		}else{
        // 			return response()->json([
        //     				'status' => 'error',
        //     				'message' => $Response.'.Please contact admin.',
        //     			], 401);
        // 		}
        	 }else{
        		return response()->json([
        				'status' => 'success',
        				'pdfFileUrl' => $labFile
        			], 401);
        	} 
        	
        }else{
        	return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
    	}
    }
    
    public function newlabworkreport(Request $request){
			
        set_time_limit(300);
        if(Auth::user()){
          
        $clinic_id = $request->clinic_id;
        //$patient_id = $request->patient_id;
        $branch_id = $request->branch_id;
        $pdffile = $request->pdffile;
        $whatsappfile = $request->whatsappfile;
        $arr = [];
        $treatmentDataLabworks = Lab_Work::select(
            	 DB::raw('DATE_FORMAT(lab_work.created_at, "%d-%M-%Y") as order_date'),
            	'patients.name_prefix as name_prefix',
            	'patients.name as name',
            	'material_master.product_name',
            	'lab_work.teeth_change as teeth',
            	'lab_work.material_price',
            	'lab_work.lab_price',
            // 	'order_detail.rate',
            	'branches.branch_name',
            	'labs.lab_name',
            	'iLabWorkStatus',
            	DB::raw("
                    CASE
                        WHEN lab_work.iLabWorkStatus = 1 THEN 'Ordered'
                        WHEN lab_work.iLabWorkStatus = 2 THEN 'In'
                        WHEN lab_work.iLabWorkStatus = 3 THEN 'Out'
                        WHEN lab_work.iLabWorkStatus = 4 THEN 'Completed'
                        WHEN lab_work.iLabWorkStatus = 5 THEN 'Cancel'
                        ELSE 'Pending'
                    END as strLabWorkStatus
                "),
                'lab_work.labwork_master_id',
                'lab_work.clinic_id',
                'lab_work.branch_id',
                'lab_work.patient_id',
                'lab_work.lab_id',
                'lab_work.doctor_id'
        	)
        	->where(['lab_work.istatus' => 0,
        	'lab_work.clinic_id' => $clinic_id])
        	
        	->join('branches', 'branches.branch_id', '=', 'lab_work.branch_id')
        	->join('patients', 'patients.patient_id', '=', 'lab_work.patient_id')
        	->join('labs','labs.lab_id', '=', 'lab_work.lab_id')
        	->join('material_master','material_master.material_id', '=', 'lab_work.material_id')
        	->join('users', 'users.user_id', '=', 'lab_work.doctor_id')
        	->when($request->fromDate, function ($query) use ($request) {
    			$query->where(DB::raw("DATE_FORMAT(lab_work.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
			})
        	->when($request->toDate, function ($query) use ($request) {
				$query->where(DB::raw("DATE_FORMAT(lab_work.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
			})
        	->when($request->labname, function ($query) use ($request) {
				$query->where('labs.lab_name','LIKE', '%' . $request->labname .'%');
			})
			->when($request->branch_id, function ($query) use ($request) {
				$query->where('lab_work.branch_id','=',$request->branch_id);
			})
//         	->when($request->branchname, function ($query) use ($request) {
// 				$query->where('branches.branch_name','LIKE', '%' . $request->branchname .'%');
// 			})
        	->when($request->selected_date, function ($query) use ($request) {
				$query->where(DB::raw("DATE_FORMAT(lab_work.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".$request->selected_date."','%Y-%m-%d')"));
			})
        	->when($request->month, function ($query) use ($request) {
				$query->where(DB::raw("MONTH(lab_work.created_at)"),'=',$request->month);
			})
        	->when($request->year, function ($query) use ($request) {
				$query->where(DB::raw("YEAR(lab_work.created_at)"),'=',$request->year);
			});
			if (isset($request->lab_order_status_id) && $request->lab_order_status_id == 6) {
                $treatmentDataLabworks->whereIn('iLabWorkStatus', [2,3]);
            } else if (isset($request->lab_order_status_id) && $request->lab_order_status_id > 0) {
                $treatmentDataLabworks->where('iLabWorkStatus', '=', $request->lab_order_status_id);
            } else {
                $treatmentDataLabworks->whereNotIn('iLabWorkStatus', [4]);
            }
            
            $treatmentDataLabwork = $treatmentDataLabworks->get();
			//dd($treatmentDataLabwork);
        // 	$treatmentDataLabwork = $treatmentDataLabworks->get();
        	// 'istatus' => 0,
        	$LabworkData = DB::table('labs')->select('labs.lab_name')
            	->where(['clinic_id' => $clinic_id])
                ->where('labs.lab_name','LIKE', $request->labname)
            	->first();
        	//dd($LabworkData);
        	$branchData = Branch::where('branch_id','=',$request->branch_id)->first();
        	$branchName = $branchData->branch_name ?? "";
        	$Duration = "";
        	if(isset($request->toDate) && $request->toDate != ""){
        	    $Duration .= date('d-m-Y',strtotime($request->fromDate)) ." To ". date('d-m-Y',strtotime($request->toDate));
        	}
        	if($request->selected_date != ""){
        	    $Duration .= $request->selected_date ." ";
        	}
        	if($request->month != "" && $request->year != ""){
        	    $Duration .= $request->month ."-".$request->year;
        	}
        	// 	if($request->year != ""){
        	// 	    $Duration .= $request->year ." ";
        	// 	}
        	$grand_amount = 0;
        	if(count($treatmentDataLabwork) != 0){
        		foreach($treatmentDataLabwork as $TreatmentDataLabwork){
    				$teeth = $TreatmentDataLabwork->teeth;
    				$teeth_array = explode(",", $teeth);
    				$teethCount = count($teeth_array);
    				if(empty($TreatmentDataLabwork->lab_price)){
    					$labPrice = 0;
    				}else{
    					$labPrice = $TreatmentDataLabwork->lab_price;
    				}
    				$grand_amount += $labPrice;
    				$arr[] = array(
    					"order_date" => $TreatmentDataLabwork->order_date,
    					"patient_name" => $TreatmentDataLabwork->name_prefix." ".$TreatmentDataLabwork->name,
    					"product_name" => $TreatmentDataLabwork->product_name,
    					"teeth" => $TreatmentDataLabwork->teeth,
    					"unit" => $teethCount,
    					"material_price" => $TreatmentDataLabwork->material_price,
    					"lab_price" => $labPrice,
    				//	"rate" => $TreatmentDataLabwork->rate,
    					"branch_name" => $TreatmentDataLabwork->branch_name,
    					"lab_name" => $TreatmentDataLabwork->lab_name,
    					"iLabWorkStatus" => $TreatmentDataLabwork->iLabWorkStatus,
    					"strLabWorkStatus" => $TreatmentDataLabwork->strLabWorkStatus,
    					'labwork_master_id' => $TreatmentDataLabwork->labwork_master_id,
                        'clinic_id' => $TreatmentDataLabwork->clinic_id,
                        'branch_id' => $TreatmentDataLabwork->branch_id,
                        'patient_id' => $TreatmentDataLabwork->patient_id,
                        'lab_id' => $TreatmentDataLabwork->lab_id,
                        'doctor_id' => $TreatmentDataLabwork->doctor_id,
    				); 
    			}
        			
    			if($pdffile == 1){
    					
					$pdf = PDF::loadView('lab_work_report',['labWorkData' => $arr,'grand_total' => $grand_amount,"branchName" => $branchName,"Duration" => $Duration,"LabworkData" => $LabworkData]);
					
					$fileName = date('d-m-Y')."_labwork";
					
					$content = $pdf->download()->getOriginalContent();
					Storage::put('public/assets/labwork_report/'.$fileName . '.pdf',$content);
					
					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
						$pdf->save(public_path('assets/labwork_report/')  . $fileName. '.pdf');	
					}else {
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/labwork_report/')  . $fileName. '.pdf');
					}
			
					$labFile = asset('assets/labwork_report/'. $fileName. '.pdf');
					$key = $_ENV['WHATSAPPKEY'];
					$msg = "Dear User, Please find attached details of Labwork.";
					//return $pdf->download($fileName . '.pdf');
					
					if($whatsappfile == 1){
    					$users = new User();
    					$currentUser = Auth::user();
    
    					$mobileNo = $currentUser->mobile_no;
    					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$labFile);
    					
    				// 	$statusofMessage = $status->status;
					    // $Response = $status->response;
				
    				// 	if($statusofMessage == "success"){
    						return response()->json([
    							'status' => 'success',
    							'pdfFileUrl' => $labFile,
    							'message' => 'Labwork Report sent on your registered mobile number.',
    							'grand_amount' => $grand_amount
    						]);
    				// 	}else{
    						
    				// 		return response()->json([
    				// 			'status' => 'error',
    				// 			'message' => $Response.'.Please contact admin.',
    				// 		], 401);
    				// 	}
    				}else{
    					return response()->json([
    						'status' => 'success',
    						'labworkData' => $arr,
    						'labworkDataFile' => $labFile,
    						'grand_amount' => $grand_amount
    					]);
    				}
    						
    						
			    }
        				
    			return response()->json([
						'status' => 'success',
						'grand_amount' => $grand_amount,
						'labworkData' => $arr
					]);
        	}else{
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.',
					'labworkData' => $arr,
					'grand_amount' => $grand_amount
				]);
        	}
        }else{
          return response()->json([
        			'status' => 'error',
        			'message' => 'User is not Authorised.',
        		], 401);
          
        }
    }
    
    public function listnewlabWork(Request $request){
		 if(Auth::user()){
			$clinic_id = $request->clinic_id;
			$patient_id = $request->patient_id;
			$branch_id = $request->branch_id;
			
			$treatmentDataLabwork = Lab_Work::select(
				'labs.lab_name',
				'labs.lab_id',
				'material_master.product_name',
				'lab_work.material_price',
				'lab_work.teeth_change as teeth',
				'lab_work.shade',
				'lab_work.notes',
				'lab_work.labwork_master_id',
				'lab_work.created_at',
				'patients.name_prefix as name_prefix',
				'patients.name as name',
				'users.user_name as doctor_name',
				'users.user_id as doctor_id',
				
			)
			->where(['lab_work.istatus' => 0,'lab_work.clinic_id' => $clinic_id,'lab_work.patient_id' => $patient_id,'lab_work.branch_id' => $branch_id])
			->join('patients', 'patients.patient_id', '=', 'lab_work.patient_id')
			->join('labs','labs.lab_id', '=', 'lab_work.lab_id')
			->join('material_master','material_master.material_id', '=', 'lab_work.material_id')
			->join('users', 'users.user_id', '=', 'lab_work.doctor_id')
			->get();
				
			if(count($treatmentDataLabwork) != 0){
					
				foreach($treatmentDataLabwork as $TreatmentDataLabwork){
					$teeth = $TreatmentDataLabwork->teeth;
					$teeth_array = explode(",", $teeth);
					$resultCount = count($teeth_array);
					$orgDate = $TreatmentDataLabwork->created_at;  
					$newCreatedDate = date("d-M-Y", strtotime($orgDate));
					$arr[] = array(
						"lab_name" => $TreatmentDataLabwork->lab_name,
						"lab_id" => $TreatmentDataLabwork->lab_id,
						"product_name" => $TreatmentDataLabwork->product_name,
						"material_price" => $TreatmentDataLabwork->material_price,
						"teeth" => $TreatmentDataLabwork->teeth,
						"teeth_count" => $resultCount,
						"shade" => $TreatmentDataLabwork->shade,
						"notes" => $TreatmentDataLabwork->notes,
						"name_prefix" => $TreatmentDataLabwork->name_prefix,
						"name" => $TreatmentDataLabwork->name,
						"doctor_name" => $TreatmentDataLabwork->doctor_name,
						"doctor_id" => $TreatmentDataLabwork->doctor_id,
						"labwork_master_id" => $TreatmentDataLabwork->labwork_master_id,
						"created_at" => $newCreatedDate,
					); 
				}
				
				return response()->json([
					'status' => 'success',
					'treatmentData' => $arr
				]);
					
			}else{
				return response()->json([
					'status' => 'error',
					'message' => 'No Record Found.'
				]);
			}
					
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
    }
    
    public function updateNewLabwork(Request $request,$id){
		if(Auth::user()){
			$labworkUpdate = Lab_Work::where('labwork_master_id','=',$id)->update([
				'doctor_id' => $request->doctor_id,
				'shade' => $request->shade,
				'notes' => $request->notes
			]);
			return response()->json(['status' => 'success','message' => 'Labwork Details Updated Successfully.']);
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function deleteNewlabWork(Request $request,$id){
        if(Auth::user()){
            $labworkDataObj = Lab_Work::where(['labwork_master_id'=>$id])->first();
         	if(!empty($labworkDataObj)){
        		$labworkDelete = Lab_Work::find($id);
        		$labworkDelete->delete();
        		return response()->json([
        			'status' => 'success',
        			'message' => 'Labwork data deleted Successfully.',
    			]);
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
		
	public function whatsappNewLabWork(Request $request,$id){
		 if(Auth::user()){
			 $whatsappfile = $request->whatsappfile;
			 $key = $_ENV['WHATSAPPKEY'];
			 $labMsg = "Dear User, Please find attached details of product.";
			 $labworkDataObj = Lab_Work::where(['labwork_master_id'=>$id])->first();
			 
			 $labFile = "";
			 if(!empty($labworkDataObj)){
				$file_name = $labworkDataObj->file_name;
				$labFile = asset('assets/labwork/'. $file_name. '.pdf');
	        }
	        
	        if($whatsappfile == 1){
    			$users = new User();
    			$currentUser = Auth::user();
    
    			$mobileNo = $currentUser->mobile_no;
    			$status = $users->sendWhatsappMessage($mobileNo,$key,$labMsg,$labFile);
    							
    // 			$statusofMessage = $status->status;
    			//$Response = $status->response;
    								
    // 			if($statusofMessage == "success"){
				    return response()->json([
					'status' => 'success',
					'pdfFileUrl' => $labFile,
					'message' => 'Labwork Report sent on your registered mobile number.',
						], 401);
				// }else{
							
				//     return response()->json([
    // 					'status' => 'error',
    // 					'message' => $Response.'.Please contact admin.',
				// 		], 401);
				// }
			}else{
				return response()->json([
					'status' => 'success',
					'labworkDataFile' => $labFile
				]);
			}
		}else{
		  return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);  
	    }
	}	
		
	public function lab_order_status(Request $request){
        if(Auth::user()){
            
            $order = array(
                array(
                    "lab_order_status_id" => 0,
                    "lab_order_status_value" => "Select Status",
                ),
                array(
                    "lab_order_status_id" => 1,
                    "lab_order_status_value" => "Ordered",
                ),
                array(
                    "lab_order_status_id" => 2,
                    "lab_order_status_value" => "In",
                ),
                array(
                    "lab_order_status_id" => 3,
                    "lab_order_status_value" => "Out",
                ),
                array(
                    "lab_order_status_id" => 4,
                    "lab_order_status_value" => "Completed",
                ),
                array(
                    "lab_order_status_id" => 5,
                    "lab_order_status_value" => "Cancel",
                ),
                array(
                    "lab_order_status_id" => 6,
                    "lab_order_status_value" => "On Going",
                )
            );
            
            return response()->json([
    				'status' => 'success',
    				'orderStatusList' => $order,
    				'message' => 'Lab Order Status List.',
    			], 200);
        }else{
        	return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
    	}
    }
    
    public function changeLabWork(Request $request){
		if(Auth::user()){
		    
		    if($request->lab_order_status_id == 2){
		        $Doctor = User::where("user_id","=",$request->doctor_id)->first();
		        $DocMobileNo = $Doctor->mobile_no;
		        $LabData = Lab::where(['lab_id'=>$request->lab_id])->first();
	            
	            $Lab_Work = Lab_Work::where('labwork_master_id','=',$request->labwork_master_id)->first();
	            
		        $patientData = Patient::where(['patient_id'=> $Lab_Work->patient_id])->first();
                $patientName = $patientData->name;
                $case_no = $patientData->case_no;
                
                $labMsg = "You patient ". $patientName ."'s  lab work recived from  ".$LabData->lab_name .". Kindly arrenge an appointment for the same.";
		        
		        $key = $_ENV['WHATSAPPKEY'];
		        $users = new User();
        		$status = $users->sendWhatsappMessage($DocMobileNo,$key,$labMsg,"");
		    }
		    
			$labworkUpdate = Lab_Work::where('labwork_master_id','=',$request->labwork_master_id)->update([
				'iLabWorkStatus' => $request->lab_order_status_id
			]);
			
			Lab_Work_Detail::create([
                'labwork_master_id' => $request->labwork_master_id,
        		'clinic_id' => $request->clinic_id,
        		'branch_id' => $request->branch_id,
        		'lab_id' => $request->lab_id,
        		'doctor_id' => $request->doctor_id,
        		'lab_work_status' => $request->lab_order_status_id,
        		'remarks' => $request->remarks,
            ]);
			
			return response()->json(['status' => 'success','message' => 'Labwork Status Updated Successfully.']);
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function LabWorkHistory(Request $request){
		if(Auth::user()){
		    
			$labworkHistory = Lab_Work_Detail::select(
			    DB::raw('DATE_FORMAT(lab_work_detail.created_at, "%d-%M-%Y") as labwork_date'),
            	'patients.name_prefix as name_prefix',
            	'patients.name as name',
            	'material_master.product_name',
            	'lab_work.teeth_change as teeth',
            	'lab_work.material_price',
            	'lab_work.lab_price',
            	'branches.branch_name',
            	'labs.lab_name',
            	'lab_work_status as iLabWorkStatus',
            	DB::raw("
                    CASE
                        WHEN lab_work_detail.lab_work_status = 1 THEN 'Ordered'
                        WHEN lab_work_detail.lab_work_status = 2 THEN 'In'
                        WHEN lab_work_detail.lab_work_status = 3 THEN 'Out'
                        WHEN lab_work_detail.lab_work_status = 4 THEN 'Completed'
                        WHEN lab_work_detail.lab_work_status = 5 THEN 'Cancel'
                        ELSE 'Pending'
                    END as strLabWorkStatus"),
                'remarks',
                'users.user_name as DoctorName'
			    )->join('lab_work', 'lab_work.labwork_master_id', '=', 'lab_work_detail.labwork_master_id')
			    ->join('branches', 'branches.branch_id', '=', 'lab_work.branch_id')
            	->join('patients', 'patients.patient_id', '=', 'lab_work.patient_id')
            	->join('labs','labs.lab_id', '=', 'lab_work.lab_id')
            	->join('material_master','material_master.material_id', '=', 'lab_work.material_id')
            	->join('users', 'users.user_id', '=', 'lab_work.doctor_id')
			    ->where('lab_work_detail.labwork_master_id','=',$request->labwork_master_id)->get();
			
            if ($labworkHistory->isEmpty()) {
                // The query result is empty
                // Handle the case where no records were found
                return response()->json([
    				'status' => 'error',
    				'message' => 'No Record Found.',
    				"labworkHistory" => []
    			]);
            } else {
                // The query result is not empty
                // Handle the case where records were found
                return response()->json([
    				'status' => 'success',
    				'message' => 'Labwork History.',
    				"labworkHistory" => $labworkHistory
    			]);
            }
			
			//return response()->json(['status' => 'success','message' => 'Labwork Status Updated Successfully.']);
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
    
}