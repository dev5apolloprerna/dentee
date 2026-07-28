<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserBranches;
use App\Models\BranchCaseNumber;
use App\Models\Patient;
use App\Models\Appointments;
use App\Models\OrderMaster;
use App\Models\OrderPaymentDetail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Treatments;
use App\Models\CashLedger;

class BranchController extends Controller
{
        public function addBranch(Request $request){
		
			if(Auth::user()){
			/* $request->validate([
				'branch_name' => 'required|string|max:255|unique:branches,branch_name,NULL,id,deleted_at,NULL',
			],
			[
				'branch_name.required' => 'Branch name is required',
			]); */
			
				$existBranch = Branch::where(['branch_name'=>$request->branch_name,'deleted_at' => NULL])->first();
				$existMobile = Branch::where(['mobile_no'=>$request->mobile_no,'deleted_at' => NULL])->first();
				
				if(!empty($existBranch)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Branch already exist.'
						]);
					
				}else if(!empty($existMobile)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Branch with this mobile number already exist.'
						]);
					
				}else{
					
					$branch = Branch::create([
						'branch_name' => $request->branch_name,
						'state' => $request->state,
						'city' => $request->city,
						'zipcode' => $request->zipcode,
						'address' => $request->address,
						'mobile_no' => $request->mobile_no,
						'clinic_id' => $request->clinic_id,
						'istatus' => $request->istatus,
					
					]);
				
					
					return response()->json([
					'status' => 'success',
					'message' => 'Branch created successfully',
					'branches' => $branch
					
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
		public function updateBranch(Request $request,$id)
		{
			
				if(Auth::user()){
					
				$existBranch = Branch::where('branch_name', '=', $request->branch_name)->where('deleted_at', '=', NULL)->where('branch_id', '<>', $id)->first();
				$existMobile = Branch::where('mobile_no', '=', $request->mobile_no)->where('deleted_at', '=', NULL)->where('branch_id', '<>', $id)->first();
				
					if(!empty($existBranch)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Branch already exist.'
							]);
						
					}else if(!empty($existMobile)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Branch with this mobile number already exist.'
							]);
						
					}else{
					
					$branch = Branch::find($id);
					$branch->update($request->all());
					
					return response()->json(['status' => 'success','message' => 'Branch Updated Successfully.','branch' => $branch,]);
					}
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
	
		public function allBranch(Request $request){
			$clinic_id = $request->clinic_id;
			$user_id = $request->user_id;
			$role_id = $request->role_id;
			if(Auth::user()){
				if($role_id == 3 || $role_id == 2 || $role_id == 4){
					
					$allbranchlist = Branch::select(
						'branches.*',
						DB::raw("(select cl_amt from `cash_ledgers` where clinic_id=".$clinic_id." and cash_ledgers.branch_id=branches.branch_id order by id desc limit 1) as cash_on_hand")
					)
					->where(['branches.clinic_id' => $clinic_id, 'user_branches.user_id' => $user_id,'user_branches.deleted_at' => NULL])
					->join('user_branches', 'branches.branch_id', '=', 'user_branches.branch_id')
					->get()->toArray();
				}else{
					
					$allbranchlist = Branch::select(
						'branches.*',
						DB::raw("(select cl_amt from `cash_ledgers` where clinic_id=".$clinic_id." and cash_ledgers.branch_id=branches.branch_id order by id desc limit 1) as cash_on_hand")
					)->where(['clinic_id' => $clinic_id])->get()->toArray();
				}
				
				return $allbranchlist;
				
			}else{
				return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
			}
		
		}
		
		public function addCasePrefix(Request $request){
			
			if(Auth::user()){

				$existPrefix = BranchCaseNumber::where('branch_id', '=', $request->branch_id)->first();
				
				if(!empty($existPrefix)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'branch case prefix already exist.'
							]);
						
					}else{
				
							$branch = BranchCaseNumber::create([
								'clinic_id' => $request->clinic_id,
								'branch_id' => $request->branch_id,
								'case_pre' => $request->case_pre,
								'case_no' => $request->case_no,
								'case_suf' => $request->case_suf,
							]);
							
							
							if($request->branch_id){
							 Branch::where(['branch_id'=>$request->branch_id])->update([
										'last_case_no' => $request->case_no
									]);
							}
							
							return response()->json([
								'status' => 'success',
								'message' => 'Branch case prefixs added successfully',
								'branchdetail' => $branch
							]);
					}
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
				], 401);
			}
		
		}
		
		public function getCasePrefix(Request $request,$id){
			
					// get case prifix by branch id
					if(Auth::user()){
					$clinic_id = $request->clinic_id;
					$branchCaseNumberPrefix = BranchCaseNumber::where(['branch_id'=> $id,'clinic_id' => $clinic_id])->first();
					
					//get last patient case number of perticular branch
					
					$lastPatientCaseNumber = Branch::where(['branch_id'=> $id,'clinic_id' => $clinic_id])->first();
					
					$totalPatientBranchWise = Patient::where(['branch_id'=> $id,'clinic_id' => $clinic_id])->get();
					
					$casenoLastPatient = "";

						if(!empty($lastPatientCaseNumber))
						{
								$casenoLastPatient = $lastPatientCaseNumber->last_case_no;
								
								if(empty($casenoLastPatient) || ($casenoLastPatient == 0)){
									
										$casenoLastPatient = $branchCaseNumberPrefix->case_no;

								}
						}
					
					return response()->json([
						'status' => 'success',
						'branchCaseNumberPrefix' => $branchCaseNumberPrefix,
						'casenoLastPatient' => $casenoLastPatient
					]);
			
					}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
							], 401);
					}
		}
		
		public function updateCasePrefix(Request $request,$id){
			
			if(Auth::user()){

				$existPrefix = BranchCaseNumber::where('branch_id', '=', $request->branch_id)
				->where('branch_case_number_id', '<>', $id)->first();
				
				if(!empty($existPrefix)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'branch case prefix already exist.'
							]);
						
					}else{

							 $BranchCaseNumber = BranchCaseNumber::where(['branch_case_number_id'=>$id])->update([
										'branch_id' => $request->branch_id,
										'case_pre' => $request->case_pre,
										'case_no' => $request->case_no,
										'case_suf' => $request->case_suf,
									]);
						
							if($request->branch_id){
							 Branch::where(['branch_id'=>$request->branch_id])->update([
										'last_case_no' => $request->case_no
									]);
							}
							
							return response()->json([
								'status' => 'success',
								'message' => 'Branch case prefixs updated successfully'
							]);
					}
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
				], 401);
			}
		
		}
		//branch case number id
		public function  destroyCasePrefix($id){
			
			if(Auth::user()){
				

			 $data = BranchCaseNumber::where('branch_case_number_id',$id)->first();
			 $branchId = $data->branch_id;
			 $totalPatientInBranch = Patient::where(['branch_id'=> $branchId])->count();
			 
			 if($totalPatientInBranch == 0){
				 
				if($data){
					$BranchCaseNumberDelete = BranchCaseNumber::find($id);
					$BranchCaseNumberDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Branch Case Number deleted Successfully.',]);
				}else{
					return response()->json([
						'status' => 'error',
						'message' => 'Somethig is wrong.Please try again',], 401);
				}
			}else{
				
				return response()->json([
						'status' => 'error',
						'message' => 'Can not remove this branch prefix because the branch has patients.',]);
			}
			
		   }else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
			
		}
		
		public function allCaseprefix(Request $request){
				/* $branchCaseNumber = new BranchCaseNumber();
				$allbranchlist = $branchCaseNumber->allBranchCaseNumber();
				return $allbranchlist; */
				//\DB::connection()->enableQueryLog();
				$branchCaseNumberList = BranchCaseNumber::select(
				'branch_case_number.branch_case_number_id',
				'branch_case_number.branch_id',
				'branch_case_number.case_pre',
				'branch_case_number.case_no',
				'branch_case_number.case_suf',
				'branches.branch_name as branch_name'
				)
				->where(['branch_case_number.clinic_id' =>$request->clinic_id])
				->join('branches', 'branch_case_number.branch_id', '=', 'branches.branch_id')
				->get();
				
				//$queries = \DB::getQueryLog();
					//dd($queries);
				
				return $branchCaseNumberList;
		
		}
		
		//destroy vendor
		public function destroyBranch($id)
		{
		   if(Auth::user()){

			$totalPatientInBranch = Patient::where(['branch_id'=> $id])->count();
			
			if($totalPatientInBranch == 0){
			 $data = Branch::where('branch_id',$id)->count();
				if($data){
					$BranchDelete = Branch::find($id);
					$BranchDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Branch deleted Successfully.',]);
				}else{
					return response()->json([
						'status' => 'error',
						'message' => 'Somethig is wrong.Please try again',], 401);
				}
			}else{
				
				return response()->json([
						'status' => 'error',
						'message' => 'Can not remove this branch because the branch has patients.',]);
			}
			
		   }else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		}
	
	public function branchByUser(Request $request, $id)
    {
		$clinic_id = $request->clinic_id;
		if(Auth::user()){
			//DB::connection()->enableQueryLog();
			//$branchList = UserBranches::select('branch_name')->where('user_id',$id)->get();
			
			$branchList = UserBranches::select(
					'user_branches.branch_id',
					'branches.branch_name'
				)
            ->where(['user_branches.user_id' => $id, 'branches.clinic_id' => $clinic_id])
            ->join('branches', 'user_branches.branch_id', '=', 'branches.branch_id')
            ->whereNull('branches.deleted_at')
            ->get();
            //->toSql();
			//dd($branchList);
			//$queries = \DB::getQueryLog();
				//dd($queries);
			//echo "<pre>";
			//print_r($branchList);
			//die;
			return response()->json([
					'status' => 'success',
					'branch' => $branchList]);
		
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
				], 401);
		}
		
	}
	
	//branchdeshboardcount patient
// 	public function branchdeshboardcount(Request $request)
// 		{					
// 			if(Auth::user()){

// 				//\DB::connection()->enableQueryLog();

// 				 $appointmentsList = Appointments::select(
//     					'appointments.appointment_id'
//     				)
// 				    ->where(['appointments.branch_id' => $request->branch_id, 'appointments.clinic_id' => $request->clinic_id])
// 				    ->when($request->fromDate, function ($query) use ($request) {
// 					    //$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
// 					    $query->whereDate('appointments.appointment_date', '>=', $request->fromDate);
// 				    })
// 				    ->when($request->toDate, function ($query) use ($request) {
// 					    //$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
// 					    $query->whereDate('appointments.appointment_date', '<=', $request->toDate);
// 				    })
// 				    ->when($request->doctor_id, function ($query) use ($request) {
// 						$query->where('appointments.doctor_id','=',$request->doctor_id);
// 				    })
// 				    //->orderBy('appointments.appointment_id', 'desc')
// 				    ->count();
							
							
// 				$PatientrecentlyregisteredList = Patient::select(
// 						    'patients.patient_id'
// 						)
// 						->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
// 						//->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
// 				// 		->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
// 				// 		->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
//     				    ->whereDate('patients.created_at', '>=', $request->fromDate)
//     				    ->whereDate('patients.created_at', '<=', $request->toDate)
// 						//->join('groups', 'patients.group_id', '=', 'groups.group_id')
// 						 //->orderBy('patients.patient_id', 'desc')
// 			            ->count();
									
// 			    $PatientList = Patient::select(
// 							'patients.patient_id',
// 							'suggested_treatments.treatment_date' 
// 						)
// 						->join('groups', 'patients.group_id', '=', 'groups.group_id')
// 						->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
// 						->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
// 												//->where(['suggested_treatments.treatment_date' =>$currentDate])
// 												/* ->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
// 												->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
// 												->orWhere(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
// 												->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')")) */
// 						->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
// 						->where(function ($query) use ($request) {
// 				// 			$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
// 				// 				->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
// 								$query->whereDate('suggested_treatments.treatment_date', '>=', $request->fromDate)
//                                 ->whereDate('suggested_treatments.treatment_date', '<=', $request->toDate);
// 						})->orWhere(function ($query) use ($request) {
// 							$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
// 								->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
// 				            // $query->whereDate('order_master.created_at', '>=', $request->fromDate)
//                 //                 ->whereDate('SUGGESTED_TREATMENTS.TREATMENT_DATEorder_master.created_atte);
// 						})
						
// 						->groupBy('patients.patient_id')
// 						->count();
// 											 //   ->toSql();
// 											 //   echo $request->clinic_id . 'patients.branch_id : ' . $request->branch_id;
// 											 //   echo $request->fromDate;
// 											 //   echo $request->toDate;
// 											 //   dd($PatientList);
												
// 				$PatientrecentlyvisitedList = $PatientList;
				
// 				$totalpaidAmountforCount = OrderMaster::select(
// 					    DB::raw('sum(paid_amount) as paid_amount')
// 					)
//     				->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id, 'is_paid'=> 2])
//     				->get()->toArray();
					
					
// 				if(!empty($totalpaidAmountforCount[0]['paid_amount'])){
// 					$paid_amount = $totalpaidAmountforCount[0]['paid_amount'];
// 				}else{
// 					$paid_amount = 0;
// 				}
								
// 				// 			$monthlyCollectionAmount = OrderMaster::select(
//                 //                 				DB::raw('sum(paid_amount) as monthlyCollectionAmount')
//                 //                 			)
//                 //                 			->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id, 'is_paid'=> 2])
//                 //                 			->when($request->fromDate, function ($query) use ($request) {
//                 //                 				$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
//                 //                 			})
//                 //                 			->when($request->toDate, function ($query) use ($request) {
//                 //                 				$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
//                 //                 			})
//                 //                 			->first();
//                 $date = date('Y-m-d');
//                 // 			$monthlyCollection = OrderMaster::select(
//                 // 					DB::raw('ifnull(sum(paid_amount),0) as monthlyCollectionAmount')
//                 // 				)
//                 // 				->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id, 'is_paid'=> 2]);
//                 // 				// if($request->fromDate == $date && $request->toDate == $date){
//                 // 				//     $MONTH = date('m',strtotime($request->fromDate));
//                 // 				//     $YEAR = date('Y',strtotime($request->fromDate));
//                 // 				// 	$monthlyCollection->where(DB::raw("MONTH(order_master.created_at)"),'=',$MONTH)
//                 // 				// 	->where(DB::raw("YEAR(order_master.created_at)"),'=',$YEAR);
//                 // 				// } else {
//                 // 					$monthlyCollection->when($request->fromDate, function ($query) use ($request) {
//                 // 						$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate." 00:00:00','%Y-%m-%d')"));
//                 // 					})
//                 // 					->when($request->toDate, function ($query) use ($request) {
//                 // 						$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate." 23:59:59','%Y-%m-%d')"));
//                 // 					});
//                 // 				// }
// 	             //   $monthlyCollectionAmount = $monthlyCollection->first();
	                
//                 /*$dueCollection = OrderMaster::where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
//     				->where('is_paid','!=',2);*/
//     				/*$dueCollection->when($request->fromDate, function ($query) use ($request) {
//     					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
//     				})
//     				->when($request->toDate, function ($query) use ($request) {
//     					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
//     				});*/
//     			/*$dueCollectionAmounts = $dueCollection->get();*/
    			
//     			///////////////Old Query//////////////
//     			/*$dueCollection = DB::table('order_detail')
//                     ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
//                     ->select(
//                         'order_master.patient_id','order_detail.order_detail_id',
//                         DB::raw('(SELECT CONCAT(patients.name_prefix," ",patients.name) FROM patients WHERE patients.patient_id=order_master.patient_id) AS patientsName'),
//                         'order_master_id',
//                         DB::raw('(SELECT SUM(amount) FROM suggested_treatment_payment WHERE suggested_treatment_payment.order_detail_id=order_detail.order_detail_id) AS PaidAmount'),
//                         DB::raw('(SELECT SUM(suggested_treatments.total_amount) FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS TotalAmount')
//                     )
//                     ->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
//                     ->where('is_paid','!=',2)
//                     ->where('istatus','=',0);
//                     $dueCollection->when($request->fromDate, function ($query) use ($request) {
//     					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
//     				})
//     				->when($request->toDate, function ($query) use ($request) {
//     					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
//     				});
//     				if(isset($request->doctor_id)){
//                         $dueCollection->whereIn('order_detail.suggested_treatment_id', function ($query)  use ($request) {
//                             $query->select('suggested_treatments.suggested_treatment_id')
//                                 ->from('suggested_treatments')
//                                 ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
//                         });
//     				}
//                     $dueCollectionAmounts = $dueCollection->get();
//         			$totalDueCollection = 0;
//         			foreach($dueCollectionAmounts as $dueCollectionAmount){
        				
//         				$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
//     					if($due_amount > 0){
//     					    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
//     					}
//         			}*/
                		
//                 	///////////////New Query//////////////	
//                 		$dueCollection = DB::table('order_detail')
//     ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
//     ->leftJoin('patients', 'patients.patient_id', '=', 'order_master.patient_id') // Join with patients to get name directly
//     ->leftJoin('suggested_treatment_payment', 'suggested_treatment_payment.order_detail_id', '=', 'order_detail.order_detail_id') // Join with payments to get paid amount directly
//     ->leftJoin('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'order_detail.suggested_treatment_id') // Join with suggested treatments to get total amount directly
//     ->select(
//         'order_master.patient_id',
//         'order_detail.order_detail_id',
//         DB::raw('CONCAT(patients.name_prefix, " ", patients.name) AS patientsName'),
//         'order_master_id',
//         DB::raw('COALESCE(SUM(suggested_treatment_payment.amount), 0) AS PaidAmount'), // Use COALESCE to handle nulls
//         DB::raw('COALESCE(SUM(suggested_treatments.total_amount), 0) AS TotalAmount'), // Use COALESCE to handle nulls
//         DB::raw('(SUM(suggested_treatments.total_amount) - COALESCE(SUM(suggested_treatment_payment.amount), 0)) AS DueAmount') // Calculate DueAmount directly
//     )
//     ->where([
//         'order_master.branch_id' => $request->branch_id,
//         'order_master.clinic_id' => $request->clinic_id,
//     ])
//     ->where('order_master.is_paid', '!=', 2)
//     ->where('order_master.istatus', '=', 0)
//     ->when($request->fromDate, function ($query) use ($request) {
//         $query->whereDate('order_master.created_at', '>=', $request->fromDate);
//     })
//     ->when($request->toDate, function ($query) use ($request) {
//         $query->whereDate('order_master.created_at', '<=', $request->toDate);
//     })
//     ->when($request->doctor_id, function ($query) use ($request) {
//         $query->whereIn('order_detail.suggested_treatment_id', function ($query) use ($request) {
//             $query->select('suggested_treatments.suggested_treatment_id')
//                   ->from('suggested_treatments')
//                   ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
//         });
//     })
//     ->groupBy('order_detail.order_detail_id') // Group by order_detail_id
//     ->get();

// // Calculate total due collection from the result set
// $totalDueCollection = $dueCollection->sum('DueAmount');

// ///////////////New Query//////////////
                			
//                 // 			$NoOfStarted = Patient::select('patients.patient_id')
//                 //     			->join('order_master','patients.patient_id','=','order_master.patient_id')
//                 //     			->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
//                 //     			->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
//                 //     			->whereIn('order_master.is_paid',[1,2])
//                 //     			->whereNotIn('suggested_treatments.treatment_id', function($query){
//                 //     				$query->select('treatments.treatment_id')
//                 //     				->from(with(new Treatments)->getTable())
//                 //     				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
//                 //     				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
//                 //     			})
//                 //     			//->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
//                 //     			->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
//                 //     			->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
//                 //     			->join('groups', 'patients.group_id', '=', 'groups.group_id')
//                 //     				->orderBy('patients.patient_id', 'desc')
//                 //     			->count();
//                 $NoOfStart = Patient::select('patients.patient_id')
//             			->join('order_master','patients.patient_id','=','order_master.patient_id')
//             			->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
//             			//->join('treatments','treatments.treatment_id','=','suggested_treatments.treatment_id')
//             			->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
//             			->whereIn('order_master.is_paid',[1,2])
//             			//->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
//             // 			->whereNotIn('suggested_treatments.treatment_id', function($query){
//             // 				$query->select('treatments.treatment_id')
//             // 				->from(with(new Treatments)->getTable())
//             // 				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
//             // 				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
//             // 			});
//                         ->whereNotIn('suggested_treatments.treatment_id', function($query) {
//                             $query->select('treatments.treatment_id')
//                                   ->from('treatments')
//                                   ->where('treatments.name', 'not in', ['X ray', 'consultation', 'Medicines']);
//                         });
//             			//->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
//             // 			if($request->fromDate == $date && $request->toDate == $date){
//         				//     $MONTH = date('m',strtotime($request->fromDate));
//         				//     $YEAR = date('Y',strtotime($request->fromDate));
//         				// 	$NoOfStart->where(DB::raw("MONTH(patients.created_at)"),'=',$MONTH)
//         				// 	->where(DB::raw("YEAR(patients.created_at)"),'=',$YEAR);
//         				// } else {
//     					$NoOfStart->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
//         			        ->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
//         				// }
            			
//             			$NoOfStart->join('groups', 'patients.group_id', '=', 'groups.group_id')
//             				->orderBy('patients.patient_id', 'desc');
//         				// echo $request->fromDate;
//         				// echo "<br />";
//         				// echo $request->toDate;
//         				// echo "<br />";
//         				//dd($NoOfStart->toSql());
//             			$No_Of_Started = $NoOfStart->count();
//                 		//dd($No_Of_Started);
//                 		$monthlyCollection = OrderPaymentDetail::select(
//                 					DB::raw('ifnull(sum(amount),0) as monthlyCollectionAmount')
//                 				)
//                 				->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
//                 				->where('istatus','=','0');
//                 				// if($request->fromDate == $date && $request->toDate == $date){
//                 				//     $MONTH = date('m',strtotime($request->fromDate));
//                 				//     $YEAR = date('Y',strtotime($request->fromDate));
//                 				// 	$monthlyCollection->where(DB::raw("MONTH(order_master.created_at)"),'=',$MONTH)
//                 				// 	->where(DB::raw("YEAR(order_master.created_at)"),'=',$YEAR);
//                 				// } else {
//                 					$monthlyCollection->when($request->fromDate, function ($query) use ($request) {
//                 						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
//                 					})
//                 					->when($request->toDate, function ($query) use ($request) {
//                 						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
//                 					});
//                 					if(isset($request->doctor_id)){
//                                         $monthlyCollection->whereIn('order_payment_detail.order_id', function ($query)  use ($request) {
//                                             $query->select('order_master.order_master_id')
//                                                 ->from('order_master')
//                                                 ->join('order_detail','order_master.order_master_id','=','order_detail.order_id')
//                                                 ->join('suggested_treatments','suggested_treatments.suggested_treatment_id','=','order_detail.suggested_treatment_id')
//                                                 ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
//                                         });
//                     				}
//                 				// }
// 			                $monthlyCollectionAmount = $monthlyCollection->first();
// 							return response()->json([
//     							'status' => 'success',
//     							'apppointmentCount' => $appointmentsList,
//     							'newPatientCount' => $PatientrecentlyregisteredList,
//     							'patientVisitedCount' => $PatientrecentlyvisitedList,
//     							'totalCollection' => $paid_amount,
//     	                        'monthlyCollectionAmount' => $monthlyCollectionAmount->monthlyCollectionAmount,
//     	                        'DueAmountCollection' => $totalDueCollection,
// 				                'NoOfStarted' => $No_Of_Started	
// 						    ]);
						
// 			}else{
							
// 				return response()->json([
// 						'status' => 'error',
// 						'message' => 'User is not Authorised.',
// 					], 401);
// 				}
							
// 		}
    
    public function branchdeshboardcount(Request $request)
    {					
		if(Auth::user()){
			$appointmentsList = Appointments::select(
					'appointments.appointment_id'
				)
			    ->where(['appointments.branch_id' => $request->branch_id, 'appointments.clinic_id' => $request->clinic_id])
			    ->when($request->fromDate, function ($query) use ($request) {
				    //$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				    $query->whereDate('appointments.appointment_date', '>=', $request->fromDate);
			    })
			    ->when($request->toDate, function ($query) use ($request) {
				    //$query->where(DB::raw("DATE_FORMAT(appointments.appointment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				    $query->whereDate('appointments.appointment_date', '<=', $request->toDate);
			    })
			    /*->when($request->doctor_id, function ($query) use ($request) {
					$query->where('appointments.doctor_id','=',$request->doctor_id);
			    })*/
			    //->orderBy('appointments.appointment_id', 'desc')
			    ->count();
						
						
			$PatientrecentlyregisteredList = Patient::select(
					    'patients.patient_id'
					)
					->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
			        //->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
			        //->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
				    ->whereDate('patients.created_at', '>=', $request->fromDate)
				    ->whereDate('patients.created_at', '<=', $request->toDate)
					//->join('groups', 'patients.group_id', '=', 'groups.group_id')
				    
		            ->count();
					
		    /*$PatientList = Patient::select(
					'patients.patient_id',
					'suggested_treatments.treatment_date' 
				)
				->join('groups', 'patients.group_id', '=', 'groups.group_id')
				->leftJoin('suggested_treatments','suggested_treatments.patient_id', '=', 'patients.patient_id')
				->leftJoin('order_master','order_master.patient_id', '=', 'patients.patient_id')
				->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
				->where(function ($query) use ($request) {
    		        //$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
    			    //->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
					$query->whereDate('suggested_treatments.treatment_date', '>=', $request->fromDate)
                    ->whereDate('suggested_treatments.treatment_date', '<=', $request->toDate);
				})->orWhere(function ($query) use ($request) {
				// 	$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
				// 	->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
			            $query->whereDate('order_master.created_at', '>=', $request->fromDate)
                        ->whereDate('order_master.created_at', '<=', $request->toDate);
				})
				->groupBy('patients.patient_id')
				->get();
			
			$PatientrecentlyvisitedList = count($PatientList);*/
			
			$totalpaidAmountforCount = OrderMaster::select(
				    DB::raw('sum(paid_amount) as paid_amount')
				)
				->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id, 'is_paid'=> 2])
				->get()->toArray();
				
				
			if(!empty($totalpaidAmountforCount[0]['paid_amount'])){
				$paid_amount = $totalpaidAmountforCount[0]['paid_amount'];
			}else{
				$paid_amount = 0;
			}
            $date = date('Y-m-d');
			
			///////////////Old Query//////////////
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
                $dueCollectionAmounts = $dueCollection->get();
    			$totalDueCollection = 0;
    			foreach($dueCollectionAmounts as $dueCollectionAmount){
    				
    				$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					if($due_amount > 0){
					    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					}
    			}*/
            		
            	///////////////New Query//////////////	
        		$dueCollection = DB::table('order_detail')
                        ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
                        ->leftJoin('patients', 'patients.patient_id', '=', 'order_master.patient_id') // Join with patients to get name directly
                        ->leftJoin('suggested_treatment_payment', 'suggested_treatment_payment.order_detail_id', '=', 'order_detail.order_detail_id') // Join with payments to get paid amount directly
                        ->leftJoin('suggested_treatments', 'suggested_treatments.suggested_treatment_id', '=', 'order_detail.suggested_treatment_id') // Join with suggested treatments to get total amount directly
                        ->select(
                            'order_master.patient_id',
                            'order_detail.order_detail_id',
                            DB::raw('CONCAT(patients.name_prefix, " ", patients.name) AS patientsName'),
                            'order_master_id',
                            DB::raw('COALESCE(SUM(suggested_treatment_payment.amount), 0) AS PaidAmount'), // Use COALESCE to handle nulls
                            DB::raw('COALESCE(SUM(suggested_treatments.total_amount), 0) AS TotalAmount'), // Use COALESCE to handle nulls
                            DB::raw('(SUM(suggested_treatments.total_amount) - COALESCE(SUM(suggested_treatment_payment.amount), 0)) AS DueAmount') // Calculate DueAmount directly
                        )
                        ->where([
                            'order_master.branch_id' => $request->branch_id,
                            'order_master.clinic_id' => $request->clinic_id,
                        ])
                        ->where('order_master.is_paid', '!=', 2)
                        ->where('order_master.istatus', '=', 0)
                        ->when($request->fromDate, function ($query) use ($request) {
                            $query->whereDate('order_master.created_at', '>=', $request->fromDate);
                        })
                        ->when($request->toDate, function ($query) use ($request) {
                            $query->whereDate('order_master.created_at', '<=', $request->toDate);
                        })
                        /*->when($request->doctor_id, function ($query) use ($request) {
                            $query->whereIn('order_detail.suggested_treatment_id', function ($query) use ($request) {
                                $query->select('suggested_treatments.suggested_treatment_id')
                                      ->from('suggested_treatments')
                                      ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
                            });
                        })*/
                        ->groupBy('order_detail.order_detail_id') // Group by order_detail_id
                        ->get();

                // Calculate total due collection from the result set
                $totalDueCollection = $dueCollection->sum('DueAmount');

            ///////////////New Query//////////////
            			
            // 			$NoOfStarted = Patient::select('patients.patient_id')
            //     			->join('order_master','patients.patient_id','=','order_master.patient_id')
            //     			->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
            //     			->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
            //     			->whereIn('order_master.is_paid',[1,2])
            //     			->whereNotIn('suggested_treatments.treatment_id', function($query){
            //     				$query->select('treatments.treatment_id')
            //     				->from(with(new Treatments)->getTable())
            //     				->where('treatments.treatment_id','=','suggested_treatments.treatment_id')
            //     				->whereNotIn('treatments.name', ['X ray','consultation','Medicines']);
            //     			})
            //     			//->where(DB::raw("(DATE_FORMAT(patients.created_at,'%d-%m-%Y'))"),$currentDate)
            //     			->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
            //     			->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"))
            //     			->join('groups', 'patients.group_id', '=', 'groups.group_id')
            //     				->orderBy('patients.patient_id', 'desc')
            //     			->count();
            /*$NoOfStart = Patient::select('patients.patient_id')
        			->join('order_master','patients.patient_id','=','order_master.patient_id')
        			->join('suggested_treatments','suggested_treatments.patient_id','=','patients.patient_id')
        			//->join('treatments','treatments.treatment_id','=','suggested_treatments.treatment_id')
        			->where(['patients.clinic_id' =>$request->clinic_id, 'patients.branch_id' =>$request->branch_id])
        			->whereIn('order_master.is_paid',[1,2])
                    ->whereNotIn('suggested_treatments.treatment_id', function($query) {
                        $query->select('treatments.treatment_id')
                              ->from('treatments')
                              ->where('treatments.name', 'not in', ['X ray', 'consultation', 'Medicines']);
                    })
                    ->whereDate('patients.created_at', '>=', $request->fromDate)
                    ->whereDate('patients.created_at', '<=', $request->toDate);
// 			$NoOfStart->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"))
			        
// 		        ->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
			
			$NoOfStart->join('groups', 'patients.group_id', '=', 'groups.group_id')
				->orderBy('patients.patient_id', 'desc');*/
			/*$NoOfStart = DB::table('patients')
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
                ->whereNotIn('suggested_treatments.treatment_id', function ($query) {
                    $query->select('treatments.treatment_id')
                        ->from('treatments')
                        ->whereColumn('treatments.treatment_id', 'suggested_treatments.treatment_id')
                        ->whereNotIn('treatments.name', ['X ray', 'consultation', 'Medicines']);
                })
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id');*/
                $No_Of_Started = DB::table('patients')
                    ->selectRaw('COUNT(DISTINCT patients.patient_id) as Cnt')
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
                    ->orderByDesc('patients.patient_id')->value('Cnt');
			//$No_Of_Started = $NoOfStart->count();
			
			/*$No_Of_StartedLess700 = DB::table('patients')
                ->selectRaw('COUNT(DISTINCT patients.patient_id) as Cnt')
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
                ->orderByDesc('patients.patient_id')
                //->count();
                ->value('Cnt');*/
            $subQuery = DB::table('patients')
                ->select('patients.patient_id')
                ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->where('order_master.clinic_id', $request->clinic_id)
                ->where('order_master.branch_id', $request->branch_id)
                ->whereIn('order_master.is_paid', [1, 2])
                ->whereBetween(DB::raw('DATE(patients.created_at)'), [$request->fromDate, $request->toDate])
                ->whereNull('patients.deleted_at')
                ->groupBy('patients.patient_id')
                ->havingRaw('MAX(order_master.paid_amount) < 700');
            
            $No_Of_StartedLess700 = DB::query()
                ->fromSub($subQuery, 't')
                ->count();
                //dd($No_Of_StartedLess700);
			//$No_Of_StartedLess700 = $NoOfStartLess700->count();
			
			/*$No_Of_StartedGrater700 = DB::table('patients')
                ->selectRaw('COUNT(DISTINCT patients.patient_id) as Cnt')
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
                ->where('order_master.paid_amount', '>=', 700)
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')->value('Cnt');*/
            $No_Of_StartedGrater700 = DB::query()
                ->fromSub(function ($query) use ($request) {
                    $query->from('patients')
                        ->select('patients.patient_id')
                        ->leftJoin('order_master', 'patients.patient_id', '=', 'order_master.patient_id')
                        ->leftJoin('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
                        ->where('patients.clinic_id', $request->clinic_id)
                        ->where('patients.branch_id', $request->branch_id)
                        ->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id)
                        ->whereIn('order_master.is_paid', [1, 2])
                        ->whereBetween(DB::raw('DATE(patients.created_at)'), [$request->fromDate, $request->toDate])
                        ->whereNull('patients.deleted_at')
                        ->groupBy('patients.patient_id')
                        ->havingRaw('MAX(order_master.paid_amount) >= 700');
                }, 't')
                ->count();
                
			//$No_Of_StartedGrater700 = $NoOfStartGrater700->count();
			
			/*$noBillGenrated = DB::table('patients')
                ->selectRaw('COUNT(patients.patient_id) as Cnt')
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->whereNotIn('patients.patient_id', function ($query) use ($request) {
                    $query->select('order_master.patient_id')
                        ->from('order_master')
                        ->where('order_master.clinic_id', $request->clinic_id)
                        ->where('order_master.branch_id', $request->branch_id)
                        ->whereIn('order_master.is_paid', [1, 2])
                        ->whereDate('order_master.created_at', '>=', $request->fromDate)
                        ->whereDate('order_master.created_at', '<=', $request->toDate);
                })
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('patients.deleted_at')
                ->orderByDesc('patients.patient_id')
                ->value('Cnt');*/
                
            $noBillGenrated = DB::table('patients')
                ->selectRaw('COUNT(DISTINCT patients.patient_id) as Cnt')
                ->leftJoin('order_master', function ($join) use ($request) {
                    $join->on('patients.patient_id', '=', 'order_master.patient_id')
                         ->where('order_master.clinic_id', $request->clinic_id)
                         ->where('order_master.branch_id', $request->branch_id)
                         ->whereIn('order_master.is_paid', [1, 2]);
                })
                ->where('patients.clinic_id', $request->clinic_id)
                ->where('patients.branch_id', $request->branch_id)
                ->whereDate('patients.created_at', '>=', $request->fromDate)
                ->whereDate('patients.created_at', '<=', $request->toDate)
                ->whereNull('order_master.patient_id') // Ensures no bill is generated
                ->whereNull('patients.deleted_at')
                ->value('Cnt');

			
    		$monthlyCollection = OrderPaymentDetail::select(
					DB::raw('ifnull(sum(amount),0) as monthlyCollectionAmount')
				)
				->where(['branch_id' => $request->branch_id,'clinic_id' => $request->clinic_id])
				->where('istatus','=','0');
					$monthlyCollection->when($request->fromDate, function ($query) use ($request) {
					    $query->whereDate('payment_date', '>=', $request->fromDate);
						//$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
					})
					->whereIn('order_payment_detail.patient_id', function($query) {
                        $query->select('patient_id')
                              ->from('patients')
                              ->whereNull('deleted_at');
                    })
					->when($request->toDate, function ($query) use ($request) {
					    $query->whereDate('payment_date', '<=', $request->toDate);
						//$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
					});
					/*if(isset($request->doctor_id)){
                        $monthlyCollection->whereIn('order_payment_detail.order_id', function ($query)  use ($request) {
                            $query->select('order_master.order_master_id')
                                ->from('order_master')
                                ->join('order_detail','order_master.order_master_id','=','order_detail.order_id')
                                ->join('suggested_treatments','suggested_treatments.suggested_treatment_id','=','order_detail.suggested_treatment_id')
                                ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
                        });
    				}*/
            $monthlyCollectionAmount = $monthlyCollection->first();
            $NewBillGeneratedAmount = DB::table('order_master')
                ->where('branch_id', $request->branch_id)
                ->where('clinic_id', $request->clinic_id)
                ->where('order_master.istatus',0)
                //->where('created_at', 'like', $date . '%')
                ->whereDate('created_at', '>=', $request->fromDate)
                ->whereDate('created_at', '<=', $request->toDate)
                ->sum('net_amount');
			return response()->json([
				'status' => 'success',
				'apppointmentCount' => $appointmentsList,
				'newPatientCount' => $PatientrecentlyregisteredList,
				'patientVisitedCount' => $PatientrecentlyvisitedList ?? 0,
				'totalCollection' => $paid_amount,
                'monthlyCollectionAmount' => $monthlyCollectionAmount->monthlyCollectionAmount,
                'DueAmountCollection' => $totalDueCollection,
                'NoOfStarted' => $No_Of_Started,
                'No_Of_StartedLess700' => $No_Of_StartedLess700,
                'No_Of_StartedGrater700' => $No_Of_StartedGrater700,
                "noBillGenrated" => $noBillGenrated,
                "NewBillGeneratedAmount" => $NewBillGeneratedAmount
		    ]);
		}else{		
		    return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
    						
	}
	
	public function deshboardcashonhand(Request $request){
			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			if(Auth::user()){
				$allbranchlist = CashLedger::select("cl_amt","branch_id","clinic_id")
					->where(['clinic_id' => $clinic_id, 'branch_id' => $branch_id])
					->orderBy('id',"desc")
					->first()->toArray();
				//return $allbranchlist;
				return response()->json([
							'status' => 'success',
							'message' => 'success.',
							'cl_amt' => $allbranchlist['cl_amt'],
							'branch_id' => $allbranchlist['branch_id'],
							'clinic_id' => $allbranchlist['clinic_id']
						], 200);
				
			}else{
				return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
			}
		
		}
	
	
}
