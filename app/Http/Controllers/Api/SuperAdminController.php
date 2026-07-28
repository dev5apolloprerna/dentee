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
use App\Models\Labwork;
use App\Models\Lab_Work;

class SuperAdminController extends Controller
{
	
	public function branchwisesuperadmincollection(Request $request){
			
		$clinic_id = $request->clinic_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		
		if(Auth::user()){
			//$branchs = Branch::where('clinic_id','=',1)->get();
			$branchs = Branch::where('clinic_id','=',1)->whereNotIn("branch_id",[18,12,22])->get();
			$dataArr=[]; 
			$TotalCash = 0;
    			$TotalCheque = 0;
    			$TotalCard = 0;
    			$Totalonline = 0;
			foreach($branchs as $branch){
			    
    			$Total = 0;
			    $where=" ";
			    if(isset($request->fromDate) && $request->fromDate != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') >= DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')";
					    //and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') <= DATE_FORMAT('2024-03-31','%Y-%m-%d')";
			    }
			    if(isset($request->toDate) && $request->toDate != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') <= DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')";
			    }
			    if(isset($request->selected_date) && $request->selected_date != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') = DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')";
			    }
			    
			    if(isset($request->month) && $request->month != ""){
			        $where .= " and MONTH(order_payment_detail.payment_date)=".$request->month."";
			    }
			    if(isset($request->year) && $request->year != ""){
			        $where .= " and YEAR(order_payment_detail.payment_date)=".$request->year."";
			    }
			    
			    $reportCollection = OrderPaymentDetail::select(
					DB::raw('(select sum(order_payment_detail.amount) as amount from order_payment_detail where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=1  '.$where.' and `order_payment_detail`.`deleted_at` is null and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as CashAmount'),
                    DB::raw('(select sum(order_payment_detail.amount) as amount from order_payment_detail where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=2 '.$where.' and `order_payment_detail`.`deleted_at` is null and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as ChequeAmount'),
                    DB::raw('(select sum(order_payment_detail.amount) as amount from order_payment_detail where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=3 '.$where.' and `order_payment_detail`.`deleted_at` is null and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as CardAmount'),
                    DB::raw('(select sum(order_payment_detail.amount) as amount from order_payment_detail where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=8 '.$where.' and `order_payment_detail`.`deleted_at` is null and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as OnlineAmount')
				)
				->where('order_master.is_paid',"!=", 0)
				->where(['order_payment_detail.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
				->where("order_payment_detail.istatus","=",0)->where("order_payment_detail.branch_id","=",$branch->branch_id)
				->whereIn('order_payment_detail.patient_id', function($query) {
                        $query->select('patient_id')
                              ->from('patients')
                              ->whereNull('deleted_at');
                    })
				->groupBy('payment_mode')
				->first(); 
				
				$Total= $Total + ($reportCollection->CashAmount ?? 0) + ($reportCollection->ChequeAmount ?? 0) + ($reportCollection->CardAmount ?? 0) + ($reportCollection->OnlineAmount ?? 0);
				$dataArr[] = array(
			        "branch_id" => $branch->branch_id,
			        "branch_name" => $branch->branch_name,
			        "Cash" => $reportCollection->CashAmount ?? 0,
			        "Cheque" => $reportCollection->ChequeAmount ?? 0,
			        "Card" => $reportCollection->CardAmount ?? 0,
			        "Online" => $reportCollection->OnlineAmount ?? 0,
			        "Total" => $Total ?? 0
			    ); 
			    
			    $TotalCash += $reportCollection->CashAmount ?? 0;
				$TotalCheque += $reportCollection->ChequeAmount ?? 0;
				$TotalCard += $reportCollection->CardAmount ?? 0;
				$Totalonline += $reportCollection->OnlineAmount ?? 0;
			}
			
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
	
			if($pdffile == 1 && !empty($dataArr)){
							
				$pdf = PDF::loadView('branch_collection_report',['Collection' => $dataArr,"Duration" => $Duration]);
						
				$fileName = date('d-m-Y')."_branch_wise_collection";
				
				$content = $pdf->download()->getOriginalContent();
				Storage::put('public/assets/branch_wise_collection_report/'.$fileName . '.pdf',$content);
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
					$pdf->save(public_path('assets/branch_wise_collection_report/')  . $fileName. '.pdf');	
				}else {
					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/branch_wise_collection_report/')  . $fileName. '.pdf');

				}
		
				$dailycollectionFile = asset('assets/branch_wise_collection_report/'. $fileName. '.pdf');
		
				//return $pdf->download($fileName . '.pdf');
				
				$key = $_ENV['WHATSAPPKEY'];		
				$dailycollectionListFile = asset('assets/branch_wise_collection_report/'. $fileName. '.pdf');
				$msg = "Dear User, Please find attached details of collection.";
							
				if($whatsappfile == 1){
					$users = new User();
					$currentUser = Auth::user();

					$mobileNo = $currentUser->mobile_no;
					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
					
				// 	$statusofMessage = $status->status;
					// $Response = $status->response;
				
				// 	if($statusofMessage == "success"){
					
						return response()->json([
							'status' => 'success',
							'message' => 'Collection Report sent on your registered mobile number.',
							'dailycollectionFile' => $dailycollectionFile,
							'dailycollection' => $dataArr,
							"TotalCash" => $TotalCash,
        					"TotalCheque" => $TotalCheque,
        					"TotalCard" => $TotalCard,
        					"Totalonline" => $Totalonline
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
						'message' => 'reportData',
						'dailycollection' => $dataArr,
						'dailycollectionFile' => $dailycollectionFile,
						"TotalCash" => $TotalCash,
					"TotalCheque" => $TotalCheque,
					"TotalCard" => $TotalCard,
					"Totalonline" => $Totalonline
					]);
				}
			}else{
				return response()->json([
					'status' => 'success',
					'message' => 'reportData.',
					'dailycollection' => $dataArr,
					"TotalCash" => $TotalCash,
					"TotalCheque" => $TotalCheque,
					"TotalCard" => $TotalCard,
					"Totalonline" => $Totalonline
				]);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
		
	public function dailybranchwisesuperadmincollection(Request $request){
	    $clinic_id = $request->clinic_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		
		if(Auth::user()){
			//$branchs = Branch::where('clinic_id','=',1)->get();
			$branchs = Branch::where('clinic_id','=',1)->whereNotIn("branch_id",[18])->get();
			$dataArr=[]; 
			//foreach($branchs as $branch){
			    $reportCollections = OrderPaymentDetail::select(
                    'order_payment_detail.payment_date',
                    /*DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=2 THEN order_payment_detail.amount ELSE 0 END) AS RAOPURACashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=4 THEN order_payment_detail.amount ELSE 0 END) AS SamaSavliCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=7 THEN order_payment_detail.amount ELSE 0 END) AS HARNICashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=12 THEN order_payment_detail.amount ELSE 0 END) AS ApolloTestCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=13 THEN order_payment_detail.amount ELSE 0 END) AS vrajchilddevlopmentcenterCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=15 THEN order_payment_detail.amount ELSE 0 END) AS GOTRICashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=16 THEN order_payment_detail.amount ELSE 0 END) AS VASNACashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.payment_mode = 1 AND order_payment_detail.branch_id=17 THEN order_payment_detail.amount ELSE 0 END) AS AJWACashAmount")*/
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=2 THEN order_payment_detail.amount ELSE 0 END) AS RAOPURACashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=4 THEN order_payment_detail.amount ELSE 0 END) AS SamaSavliCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=7 THEN order_payment_detail.amount ELSE 0 END) AS HARNICashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=12 THEN order_payment_detail.amount ELSE 0 END) AS ApolloTestCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=13 THEN order_payment_detail.amount ELSE 0 END) AS vrajchilddevlopmentcenterCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=15 THEN order_payment_detail.amount ELSE 0 END) AS GOTRICashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=16 THEN order_payment_detail.amount ELSE 0 END) AS VASNACashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=17 THEN order_payment_detail.amount ELSE 0 END) AS AJWACashAmount"),
                    //DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=18 THEN order_payment_detail.amount ELSE 0 END) AS SuratCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=19 THEN order_payment_detail.amount ELSE 0 END) AS ManjalpurACashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=20 THEN order_payment_detail.amount ELSE 0 END) AS SunPharmaCashAmount"),
                    DB::raw("SUM(CASE WHEN order_payment_detail.branch_id=21 THEN order_payment_detail.amount ELSE 0 END) AS HARNILinkCashAmount")
                )
				->where('order_master.is_paid',"!=", 0)
				->where(['order_payment_detail.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
				->where("order_payment_detail.istatus","=",0)
				//->whereNull('patients.deleted_at')
				//->where("order_payment_detail.branch_id","=",$branch->branch_id)
				->when($request->fromDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->toDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(payment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_payment_detail.payment_date)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_payment_detail.payment_date)"),'=',$request->year);
				})
				->groupBy('order_payment_detail.payment_date')
				->whereIn('order_payment_detail.patient_id', function($query) {
                    $query->select('patient_id')
                          ->from('patients')
                          ->whereNull('deleted_at');
                })
				->get();
				// ->toSql();
				// dd($reportCollections);
				foreach($reportCollections as $reportCollection){
    				$dataArr[] = array(
    			        "payment_date" => date('d-m-Y', strtotime($reportCollection->payment_date)),
    			        "RAOPURACashAmount" => $reportCollection->RAOPURACashAmount ?? 0,
    			        "SamaSavliCashAmount" => $reportCollection->SamaSavliCashAmount ?? 0,
    			        "HARNICashAmount" => $reportCollection->HARNICashAmount ?? 0,
    			        "ApolloTestCashAmount" => $reportCollection->ApolloTestCashAmount ?? 0,
    			        
    			        "vrajchilddevlopmentcenterCashAmount" => $reportCollection->vrajchilddevlopmentcenterCashAmount ?? 0,
    			        "GOTRICashAmount" => $reportCollection->GOTRICashAmount ?? 0,
    			        "VASNACashAmount" => $reportCollection->VASNACashAmount ?? 0,
    			        "AJWACashAmount" => $reportCollection->AJWACashAmount ?? 0,
    			        //"SuratCashAmount" => $reportCollection->SuratCashAmount ?? 0,
    			        "ManjalpurACashAmount" => $reportCollection->ManjalpurACashAmount ?? 0,
    			        "SunPharmaCashAmount" => $reportCollection->SunPharmaCashAmount ?? 0,
    			        "HARNILinkCashAmount" => $reportCollection->HARNILinkCashAmount ?? 0
    			    ); 
				}
			//}
			//dd($dataArr);
			
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
	
			if($pdffile == 1 && !empty($dataArr)){
							
				$pdf = PDF::loadView('daily_branch_collection_report',['Collection' => $dataArr,"Duration" => $Duration]);
						
				$fileName = date('d-m-Y')."_daily_branch_wise_collection";
				
				$content = $pdf->download()->getOriginalContent();
				Storage::put('public/assets/daily_branch_collection_report/'.$fileName . '.pdf',$content);
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
					$pdf->save(public_path('assets/daily_branch_collection_report/')  . $fileName. '.pdf');	
				}else {
				$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/daily_branch_collection_report/')  . $fileName. '.pdf');

				}
		
				$dailycollectionFile = asset('assets/daily_branch_collection_report/'. $fileName. '.pdf');
		
				//return $pdf->download($fileName . '.pdf');
				
				$key = $_ENV['WHATSAPPKEY'];		
				$dailycollectionListFile = asset('assets/daily_branch_collection_report/'. $fileName. '.pdf');
				$msg = "Dear User, Please find attached details of collection.";
							
				if($whatsappfile == 1){
					$users = new User();
					$currentUser = Auth::user();

					$mobileNo = $currentUser->mobile_no;
					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
					
				// 	$statusofMessage = $status->status;
					// $Response = $status->response;
				
				// 	if($statusofMessage == "success"){
					
						return response()->json([
							'status' => 'success',
							'message' => 'Collection Report sent on your registered mobile number.',
							'dailycollectionFile' => $dailycollectionFile,
							'dailycollection' => $dataArr,
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
						'message' => 'reportData',
						'dailycollection' => $dataArr,
						'dailycollectionFile' => $dailycollectionFile
					]);
				}
			}else{
				return response()->json([
					'status' => 'success',
					'message' => 'reportData.',
					'dailycollection' => $dataArr,
				]);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function branchwiselabreport(Request $request){
	    set_time_limit(300);
	    if(Auth::user()){
    		$clinic_id = $request->clinic_id;
    		// $branch_id = $request->branch_id;
    		$pdffile = $request->pdffile;
    		$whatsappfile = $request->whatsappfile;
    		$arr = [];
    		$grand_amount = 0;
    		//$branchs = Branch::where('clinic_id','=',1)->get();
    		$branchs = Branch::where('clinic_id','=',1)->whereNotIn("branch_id",[18])->get();
    		foreach($branchs as $branch){
    		    $branchData = [];
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
        			->where(['labwork.istatus' => 0, 'labwork.clinic_id' => $clinic_id,'labwork.branch_id' => $branch->branch_id])
        			->join('branches', 'branches.branch_id', '=', 'labwork.branch_id')
        			->join('patients', 'patients.patient_id', '=', 'labwork.patient_id')
        			->join('labs','labs.lab_id', '=', 'labwork.lab_id')
        			->join('treatments','treatments.treatment_id', '=', 'labwork.treatment_id')
        			->join('material_master','material_master.material_id', '=', 'labwork.material_id')
        			->join('users', 'users.user_id', '=', 'labwork.doctor_id')
        			->join('order_detail', 'order_detail.labwork_master_id', '=', 'labwork.labwork_master_id')
        			->whereNull('patients.deleted_at')
        			->when($request->fromDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(labwork.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
    				})
        			->when($request->toDate, function ($query) use ($request) {
    					$query->where(DB::raw("DATE_FORMAT(labwork.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
                    })
        			->when($request->selected_date, function ($query) use ($request) {
                        $query->where(DB::raw("labwork.created_at"),'like',DB::raw("'".date('Y-m-d',strtotime($request->selected_date))."%'"));
    				})
        			->when($request->month, function ($query) use ($request) {
        			    $query->where(DB::raw("MONTH(labwork.created_at)"),'=',$request->month);
    				})
        			->when($request->year, function ($query) use ($request) {
        			    $query->where(DB::raw("YEAR(labwork.created_at)"),'=',$request->year);
    				})
        			->get();
        		
    			$LabworkData = DB::table('labs')->select('labs.lab_name')
        			->where(['clinic_id' => $clinic_id])
        		    ->where('labs.lab_name','LIKE', $request->labname)
        			->first();
        			
    // 			$branchData = Branch::where('branch_id','=',$request->branch_id)->first();
    // 			$branchName = $branchData->branch_name;
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
    			
    			if(count($treatmentDataLabwork) != 0){
    			    $branchData["branchName"] = $branch->branch_name;
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
    					
    					$branchData["List"][] = array(
    					
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
    				$branchData["total"] = $grand_amount;
    				$arr[] = $branchData;
    			}
    			
    			
    		}
			
			//$grand_amount = 0;
			if(count($arr) != 0){
				if($pdffile == 1){
							
					$pdf = PDF::loadView('branch_wise_labwork_report',['labWorkData' => $arr,"Duration" => $Duration,"LabworkData" => $LabworkData]);
					
					$fileName = date('d-m-Y')."_labwork";
					
					$content = $pdf->download()->getOriginalContent();
					Storage::put('public/assets/branch_wise_labwork_report/'.$fileName . '.pdf',$content);
					
					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
						$pdf->save(public_path('assets/branch_wise_labwork_report/')  . $fileName. '.pdf');	
					}else {
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/branch_wise_labwork_report/')  . $fileName. '.pdf');

					}
					
					$labFile = asset('assets/branch_wise_labwork_report/'. $fileName. '.pdf');
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
							'labworkData' => $arr,
							//'grand_amount' => $grand_amount
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
						//'grand_amount' => $grand_amount
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
    				//'grand_amount' => $grand_amount
    			]);
    	    }
	    }else{
		  return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
    		  
	    }
	}
	
	public function branchwisemedicinecollectionreport(Request $request){
			
		$clinic_id = $request->clinic_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		
		if(Auth::user()){
			//$branchs = Branch::join("treatments","branches.branch_id","=","treatments.branch_id")->where('branches.clinic_id','=',1)->where('name','like','Medicines')->get();
			$branchs = Branch::join("treatments","branches.branch_id","=","treatments.branch_id")->where('branches.clinic_id','=',1)->where('name','like','Medicines')->whereNotIn("branches.branch_id",[18])->get();
			$dataArr=[]; 
			$TotalCash = 0;
    			$TotalCheque = 0;
    			$TotalCard = 0;
    			$Totalonline = 0;
    			$i = 1;
			foreach($branchs as $branch){
			    
    			$Total = 0;
			    $where=" ";
			    if(isset($request->fromDate) && $request->fromDate != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') >= DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')";
					    //and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') <= DATE_FORMAT('2024-03-31','%Y-%m-%d')";
			    }
			    if(isset($request->toDate) && $request->toDate != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') <= DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')";
			    }
			    if(isset($request->selected_date) && $request->selected_date != ""){
			        $where .= " and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') = DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')";
			    }
			    
			    if(isset($request->month) && $request->month != ""){
			        $where .= " and MONTH(order_payment_detail.payment_date)=".$request->month."";
			    }
			    if(isset($request->year) && $request->year != ""){
			        $where .= " and YEAR(order_payment_detail.payment_date)=".$request->year."";
			    }
			    
			    $reportCollection = OrderPaymentDetail::select(
					DB::raw('(select ifnull(sum(total_amount),0) as amount from order_payment_detail inner join suggested_treatment_payment on suggested_treatment_payment.order_payment_detail_id=order_payment_detail.order_payment_detail_id
    					inner join suggested_treatments on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id inner join treatments on treatments.treatment_id=suggested_treatments.treatment_id
    					where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=1 and treatments.treatment_id = '.$branch->treatment_id.' '.$where.' and `order_payment_detail`.`deleted_at` is null  and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as CashAmount'),
    				DB::raw('(select ifnull(sum(total_amount),0) as amount from order_payment_detail inner join suggested_treatment_payment on suggested_treatment_payment.order_payment_detail_id=order_payment_detail.order_payment_detail_id
    					inner join suggested_treatments on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id inner join treatments on treatments.treatment_id=suggested_treatments.treatment_id
    					where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=2 and treatments.treatment_id = '.$branch->treatment_id.' '.$where.' and `order_payment_detail`.`deleted_at` is null  and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as ChequeAmount'),
					DB::raw('(select ifnull(sum(total_amount),0) as amount from order_payment_detail inner join suggested_treatment_payment on suggested_treatment_payment.order_payment_detail_id=order_payment_detail.order_payment_detail_id
    					inner join suggested_treatments on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id inner join treatments on treatments.treatment_id=suggested_treatments.treatment_id
    					where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=3 and treatments.treatment_id = '.$branch->treatment_id.' '.$where.' and `order_payment_detail`.`deleted_at` is null  and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as CardAmount'),
				    DB::raw('(select ifnull(sum(total_amount),0) as amount from order_payment_detail inner join suggested_treatment_payment on suggested_treatment_payment.order_payment_detail_id=order_payment_detail.order_payment_detail_id
    					inner join suggested_treatments on suggested_treatments.suggested_treatment_id=suggested_treatment_payment.suggested_treatments_id inner join treatments on treatments.treatment_id=suggested_treatments.treatment_id
    					where `order_payment_detail`.`clinic_id` = '.$clinic_id.' and `order_payment_detail`.`istatus` = 0 and `order_payment_detail`.`branch_id` = '.$branch->branch_id.' and order_payment_detail.payment_mode=8 and treatments.treatment_id = '.$branch->treatment_id.' '.$where.' and `order_payment_detail`.`deleted_at` is null  and order_payment_detail.patient_id in (select patient_id from patients where patients.deleted_at is null)) as OnlineAmount')
				)
				->where('order_master.is_paid',"!=", 0)
				->where(['order_payment_detail.clinic_id' => $clinic_id,'order_master.istatus' =>0])
				->join('order_master', 'order_master.order_master_id', '=', 'order_payment_detail.order_id')
				->where("order_payment_detail.istatus","=",0)->where("order_payment_detail.branch_id","=",$branch->branch_id)
				->groupBy('order_payment_detail.branch_id')
				->first(); 
				
				//dd($reportCollection);
				
				$Total= $Total + ($reportCollection->CashAmount ?? 0) + ($reportCollection->ChequeAmount ?? 0) + ($reportCollection->CardAmount ?? 0) + ($reportCollection->OnlineAmount ?? 0);
				$dataArr[] = array(
			        "branch_id" => $branch->branch_id,
			        "branch_name" => $branch->branch_name,
			        "Cash" => $reportCollection->CashAmount ?? 0,
			        "Cheque" => $reportCollection->ChequeAmount ?? 0,
			        "Card" => $reportCollection->CardAmount ?? 0,
			        "Online" => $reportCollection->OnlineAmount ?? 0,
			        "Total" => $Total ?? 0
			    ); 
			    
			    $TotalCash += $reportCollection->CashAmount ?? 0;
				$TotalCheque += $reportCollection->ChequeAmount ?? 0;
				$TotalCard += $reportCollection->CardAmount ?? 0;
				$Totalonline += $reportCollection->OnlineAmount ?? 0;
				
				$i++;
			}
			
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
	
			if($pdffile == 1 && !empty($dataArr)){
							
				$pdf = PDF::loadView('branch_wise_medicine_collection_report',['Collection' => $dataArr,"Duration" => $Duration]);
						
				$fileName = date('d-m-Y')."_branch_wise_medicine_collection";
				
				$content = $pdf->download()->getOriginalContent();
				Storage::put('public/assets/branch_wise_medicine_collection_report/'.$fileName . '.pdf',$content);
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
					$pdf->save(public_path('assets/branch_wise_medicine_collection_report/')  . $fileName. '.pdf');	
				}else {
					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/branch_wise_medicine_collection_report/')  . $fileName. '.pdf');

				}
		
				$dailycollectionFile = asset('assets/branch_wise_medicine_collection_report/'. $fileName. '.pdf');
		
				//return $pdf->download($fileName . '.pdf');
				
				$key = $_ENV['WHATSAPPKEY'];		
				$dailycollectionListFile = asset('assets/branch_wise_medicine_collection_report/'. $fileName. '.pdf');
				$msg = "Dear User, Please find attached details of collection.";
							
				if($whatsappfile == 1){
					$users = new User();
					$currentUser = Auth::user();

					$mobileNo = $currentUser->mobile_no;
					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
					
				// 	$statusofMessage = $status->status;
					// $Response = $status->response;
				
				// 	if($statusofMessage == "success"){
					
						return response()->json([
							'status' => 'success',
							'message' => 'Collection Report sent on your registered mobile number.',
							'dailycollectionFile' => $dailycollectionFile,
							'dailycollection' => $dataArr,
							"TotalCash" => $TotalCash,
        					"TotalCheque" => $TotalCheque,
        					"TotalCard" => $TotalCard,
        					"Totalonline" => $Totalonline
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
						'message' => 'reportData',
						'dailycollection' => $dataArr,
						'dailycollectionFile' => $dailycollectionFile,
						"TotalCash" => $TotalCash,
					"TotalCheque" => $TotalCheque,
					"TotalCard" => $TotalCard,
					"Totalonline" => $Totalonline
					]);
				}
			}else{
				return response()->json([
					'status' => 'success',
					'message' => 'reportData.',
					'dailycollection' => $dataArr,
					"TotalCash" => $TotalCash,
					"TotalCheque" => $TotalCheque,
					"TotalCard" => $TotalCard,
					"Totalonline" => $Totalonline
				]);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function facebookptrecordreport(Request $request){
	    $clinic_id = $request->clinic_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		
		if(Auth::user()){
			//$branchs = Branch::join("treatments","branches.branch_id","=","treatments.branch_id")->where('branches.clinic_id','=',1)->where('name','like','Medicines')->get();
			$dataArr=[]; 
			$TotalCash = 0;
    			$TotalCheque = 0;
    			$TotalCard = 0;
    			$Totalonline = 0;
    			$i = 1;
			//foreach($branchs as $branch){
			    
    			$Total = 0;
			    /*$where=" ";
			    if(isset($request->fromDate) && $request->fromDate != ""){
			        $where .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') >= DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')";
					    //and DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d') <= DATE_FORMAT('2024-03-31','%Y-%m-%d')";
			    }
			    if(isset($request->toDate) && $request->toDate != ""){
			        $where .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') <= DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')";
			    }
			    if(isset($request->selected_date) && $request->selected_date != ""){
			        $where .= " and DATE_FORMAT(patients.created_at,'%Y-%m-%d') = DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')";
			    }
			    
			    if(isset($request->month) && $request->month != ""){
			        $where .= " and MONTH(patients.created_at)=".$request->month."";
			    }
			    if(isset($request->year) && $request->year != ""){
			        $where .= " and YEAR(patients.created_at)=".$request->year."";
			    }*/
			    // 'suggested_treatments.treatment_name',
			    /*$reportCollections = Patient::select(
					'patients.patient_id','name_prefix','name','patients.branch_id',
					DB::raw('GROUP_CONCAT(suggested_treatments.treatment_name SEPARATOR ", ") as treatment_name'),
                    DB::raw('(select branches.branch_name from branches where branches.branch_id=patients.branch_id) as Branch_Name'),
                    DB::raw('(select GROUP_CONCAT(quotation.quotation_name SEPARATOR ", ") from quotation where quotation.patient_id=patients.patient_id) as QuotationName'),
                    DB::raw('(SELECT ifnull(sum(order_payment_detail.amount),0) FROM `suggested_treatment_payment` join order_payment_detail on order_payment_detail.order_payment_detail_id=suggested_treatment_payment.order_payment_detail_id where suggested_treatment_payment.suggested_treatments_id=suggested_treatments.suggested_treatment_id and suggested_treatment_payment.patient_id=patients.patient_id) as revenuAmount')
				)
				->where(['patients.clinic_id' => $clinic_id])
				->where(['suggested_treatments.istatus' => 1])
				->whereNull("branches.deleted_at")
				->whereIn('patients.group_id', function ($query)  use ($request) {
                    $query->select('groups.group_id')
                        ->from('groups')
                        ->where("group_name","like","%FACEBOOK%")
                        ->when($request->branch_id, function ($query) use ($request) {
                        	$query->whereIn("branch_id",$request->branch_id)
                        	->whereNotIn("branches.branch_id",[18]);
			            });
                        //->where('patients.group_id', '=', $request->doctor_id);
                })
				->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
				->join('branches','branches.branch_id','=','patients.branch_id')
				//->where("order_payment_detail.istatus","=",0)
				
				->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("patients.branch_id",$request->branch_id);
				})
				->when($request->fromDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->toDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(patients.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(patients.created_at)"),'=',$request->year);
				})
				->whereNotIn("branches.branch_id",[18])
				// ->where("patients.branch_id","=",$request->branch_id)
				//->groupBy('order_payment_detail.branch_id')
				->groupBy('patients.patient_id')
				->get(); */
				
				$reportCollections = OrderPaymentDetail::select(
                    'order_payment_detail.patient_id',
                    DB::raw('(SELECT name_prefix FROM patients WHERE patients.patient_id = order_payment_detail.patient_id) as name_prefix'),
                    DB::raw('(SELECT name FROM patients WHERE patients.patient_id = order_payment_detail.patient_id) as name'),
                    'order_payment_detail.branch_id',
                    DB::raw('(SELECT GROUP_CONCAT(DISTINCT suggested_treatments.treatment_name SEPARATOR ", ") FROM suggested_treatments WHERE suggested_treatments.patient_id = order_payment_detail.patient_id) as treatment_name'),
                    DB::raw('(SELECT branches.branch_name FROM branches WHERE branches.branch_id = order_payment_detail.branch_id) as Branch_Name'),
                    DB::raw('(SELECT GROUP_CONCAT(quotation.quotation_name SEPARATOR ", ") FROM quotation WHERE quotation.patient_id = order_payment_detail.patient_id) as QuotationName'),
                    DB::raw('IFNULL(SUM(order_payment_detail.amount), 0) as revenuAmount')
                )
                ->whereIn('order_payment_detail.patient_id', function($query) {
                    $query->select('patients.patient_id')
                        ->from('patients')
                        ->whereNull('patients.deleted_at')
                        ->where('patients.patient_id', DB::raw('order_payment_detail.patient_id'))
                        ->whereIn('patients.group_id', function($subQuery) {
                            $subQuery->select('groups.group_id')
                                ->from('groups')
                                ->where('groups.group_name', 'LIKE', '%FACEBOOK%');
                        });
                })
                ->whereNotIn('order_payment_detail.branch_id', [18])
                // ->whereDate('order_payment_detail.payment_date', '>=', '2025-01-01')
                // ->whereDate('order_payment_detail.payment_date', '<=', '2025-08-31')
                ->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("order_payment_detail.branch_id",$request->branch_id);
				})
				->when($request->fromDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->toDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_payment_detail.payment_date,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_payment_detail.payment_date)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_payment_detail.payment_date)"),'=',$request->year);
				})
				->whereNotIn("order_payment_detail.branch_id",[18])
                ->groupBy('order_payment_detail.patient_id')
                ->get();
				//->toSql();
				// dd($reportCollections);
				//dd($reportCollections);
				foreach($reportCollections as $reportCollection){
				    //dd($reportCollection);
    				$Total= $Total + ($reportCollection->revenuAmount ?? 0);
    				$dataArr[] = array(
    			        "PT_Name" => $reportCollection->name_prefix ." ". $reportCollection->name,
    			        "Branch" => $reportCollection->Branch_Name,
    			        "Suggest_Treatment" => $reportCollection->treatment_name ?? "",
    			        "Quotation" => $reportCollection->QuotationName ?? "",
    			        "Revenue_Amount" => $reportCollection->revenuAmount ?? 0
    			    );
				    $i++;
			    }
			
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
	
			if($pdffile == 1 && !empty($dataArr)){
							
				$pdf = PDF::loadView('facebook_pt_record_report',['Collection' => $dataArr,"Duration" => $Duration,"Total" => $Total]);
						
				$fileName = date('d-m-Y')."_facebook_pt_record_report";
				
				$content = $pdf->download()->getOriginalContent();
				Storage::put('public/assets/facebook_pt_record_report/'.$fileName . '.pdf',$content);
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
					$pdf->save(public_path('assets/facebook_pt_record_report/')  . $fileName. '.pdf');	
				}else {
					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/facebook_pt_record_report/')  . $fileName. '.pdf');

				}
		
				$dailycollectionFile = asset('assets/facebook_pt_record_report/'. $fileName. '.pdf');
		
				//return $pdf->download($fileName . '.pdf');
				
				$key = $_ENV['WHATSAPPKEY'];		
				$dailycollectionListFile = asset('assets/facebook_pt_record_report/'. $fileName. '.pdf');
				$msg = "Dear User, Please find attached details of collection.";
							
				if($whatsappfile == 1){
					$users = new User();
					$currentUser = Auth::user();

					$mobileNo = $currentUser->mobile_no;
				// 	$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
					
				// 	$statusofMessage = $status->status;
					// $Response = $status->response;
				
				// 	if($statusofMessage == "success"){
					
						return response()->json([
							'status' => 'success',
							'message' => 'Collection Report sent on your registered mobile number.',
							'dailycollectionFile' => $dailycollectionFile,
							'dailycollection' => $dataArr,
							"Total" => $Total
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
						'message' => 'reportData',
						'dailycollection' => $dataArr,
						'dailycollectionFile' => $dailycollectionFile,
						"Total" => $Total
					]);
				}
			}else{
				return response()->json([
					'status' => 'success',
					'message' => 'reportData.',
					'dailycollection' => $dataArr,
					"Total" => $Total
				]);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function consultdrreport(Request $request){
			
		if(Auth::user()){
			$date = date('Y-m-d');
			$pdffile = $request->pdffile;
		    $whatsappfile = $request->whatsappfile;
            $dueCollection = DB::table('order_detail')
                ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
                ->select(
                    'order_master.patient_id','order_detail.order_detail_id',
                    DB::raw("DATE_FORMAT(order_master.created_at, '%M %Y') AS created_month_year"),
                    DB::raw('(select branches.branch_name from branches where order_master.branch_id=branches.branch_id) as branchName'),
                    DB::raw('(SELECT CONCAT(patients.name_prefix," ",patients.name) FROM patients WHERE patients.patient_id=order_master.patient_id) AS patientsName'),
                    'order_master_id',
                    DB::raw('(SELECT users.user_name FROM users inner join suggested_treatments on users.user_id=suggested_treatments.treatmentBydoctor_id WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatmentBydoctor'),
                    DB::raw('(SELECT suggested_treatments.treatment_name FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatment_name'),
                    DB::raw('(SELECT SUM(amount) FROM suggested_treatment_payment WHERE suggested_treatment_payment.order_detail_id=order_detail.order_detail_id) AS PaidAmount'),
                    DB::raw('(SELECT SUM(suggested_treatments.total_amount) FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS TotalAmount')
                )
                ->where(['clinic_id' => $request->clinic_id])
                ->where('is_paid','!=',2)
                ->where('istatus','=',0)
                ->whereNotIn("branch_id",[18]);
                $dueCollection->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
				->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
				->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_master.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_master.created_at)"),'=',$request->year);
				})
				->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("branch_id",$request->branch_id);
				});
				if(isset($request->doctor_id)){
                    $dueCollection->whereIn('order_detail.suggested_treatment_id', function ($query)  use ($request) {
                        $query->select('suggested_treatments.suggested_treatment_id')
                            ->from('suggested_treatments')
                            ->where('suggested_treatments.treatmentBydoctor_id', '=', $request->doctor_id);
                    });
				}
                $dueCollectionAmounts = $dueCollection->get();
				
				
				// $dueCollectionAmounts = $dueCollection->toSql();
				// dd($dueCollectionAmounts);
			
				$totalDueCollection = 0;
				$totalAmount = 0;
				$paidAmount = 0;
				$dueAmount = 0;
				foreach($dueCollectionAmounts as $dueCollectionAmount){
    					/*if($dueCollectionAmount->is_paid == 1){
    						$totalDueCollection += $dueCollectionAmount->due_amount;
    					} else {
    						$totalDueCollection += $dueCollectionAmount->net_amount;
    					}*/
					$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					if($due_amount > 0){
					    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					}
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
					->where(['suggested_treatments.patient_id' => $dueCollectionAmount->patient_id,"is_billing" => 1, 'order_detail.order_id'=>$dueCollectionAmount->order_master_id,"order_detail_id" => $dueCollectionAmount->order_detail_id])
					->when($request->doctor_id, function ($query) use ($request) {
						$query->where("treatmentBydoctor_id",$request->doctor_id);
					})
					
				// 	->when($request->fromDate, function ($query) use ($request) {
				// 		$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				// 	})
				// 	->when($request->toDate, function ($query) use ($request) {
				// 		$query->where(DB::raw("DATE_FORMAT(suggested_treatments.treatment_date,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				// 	})
					->get();
				// 	->toSql();
				// 	echo $dueCollectionAmount->patient_id;
				// 	dd($treatmentviewlistList);
					    $arr = [];
    				// 	foreach($treatmentviewlistList as $TreatmentviewlistList){
    				// 		$treatmentBydoctor_id = $TreatmentviewlistList->treatmentBydoctor_id;
    				// 		 $userList = User::where([
    				// 		 'user_id' => $treatmentBydoctor_id
    				// 		 ])->first();
    				// 		$arr[] = array(
    				// 			"treatment_name" => $TreatmentviewlistList->treatment_name,
    				// 			"treatment_id" => $TreatmentviewlistList->treatment_id,
    				// 			"treatment_date" => $TreatmentviewlistList->treatment_date,
    				// 			"treatmentBydoctor_id" => $TreatmentviewlistList->treatmentBydoctor_id,
    				// 			"doctor_name" => $userList->user_name,
    				// 			"suggested_treatment_id" => $TreatmentviewlistList->suggested_treatment_id,
    				// 			"is_billing" => $TreatmentviewlistList->is_billing,
    				// 		); 		
    				// 	}
					// if($due_amount > 0){
    					$lastOrderData[] = array(
    					    "month" => $dueCollectionAmount->created_month_year,
						    "branchName" => $dueCollectionAmount->branchName,
						    "treatmentBydoctor" => $dueCollectionAmount->treatmentBydoctor,
						    "treatment_name" => $dueCollectionAmount->treatment_name,
    						"patient_id" => $dueCollectionAmount->patient_id,
    						"patientsName" => $dueCollectionAmount->patientsName,
    						//"TotalAmount" => $dueCollectionAmount->net_amount,
    						"TotalAmount" => $dueCollectionAmount->TotalAmount ?? 0,
    						//"PaidAmount" => $dueCollectionAmount->paid_amount,
    						"PaidAmount" => $dueCollectionAmount->PaidAmount ?? 0,
    						//"DueAmount" => ($dueCollectionAmount->is_paid == 1) ? $dueCollectionAmount->due_amount : $dueCollectionAmount->net_amount,
    						"DueAmount" => $due_amount,
    						//"SuggestedTreatmentList" => $arr
    					);
    					$totalAmount += $dueCollectionAmount->TotalAmount;
    					$paidAmount += $dueCollectionAmount->PaidAmount;
    					$dueAmount += $due_amount;
					// }
				}
				
			
			if(!empty($lastOrderData)){
			    if($pdffile == 1 && !empty($lastOrderData)){
    							
    				$pdf = PDF::loadView('consult_dr_report',['Collection' => $lastOrderData,"Total" => $totalAmount,"paidAmount" => $paidAmount,"dueAmount" => $dueAmount]);
    						
    				$fileName = date('d-m-Y')."_consult_dr_report";
    				
    				$content = $pdf->download()->getOriginalContent();
    				Storage::put('public/assets/consult_dr_report/'.$fileName . '.pdf',$content);
    				
    				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
    					$pdf->save(public_path('assets/consult_dr_report/')  . $fileName. '.pdf');	
    				}else {
    					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/consult_dr_report/')  . $fileName. '.pdf');
    
    				}
    		
    				$dailycollectionFile = asset('assets/consult_dr_report/'. $fileName. '.pdf');
    		
    				//return $pdf->download($fileName . '.pdf');
    				
    				$key = $_ENV['WHATSAPPKEY'];		
    				$dailycollectionListFile = asset('assets/consult_dr_report/'. $fileName. '.pdf');
    				$msg = "Dear User, Please find attached details of collection.";
    							
    				if($whatsappfile == 1){
    					$users = new User();
    					$currentUser = Auth::user();
    
    					$mobileNo = $currentUser->mobile_no;
    					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
    					
    				// 	$statusofMessage = $status->status;
    					// $Response = $status->response;
    				
    				// 	if($statusofMessage == "success"){
    					
    						return response()->json([
    							'status' => 'success',
    							'message' => 'Consult DR Report sent on your registered mobile number.',
    							'dailycollectionFile' => $dailycollectionFile,
    							'totalDueCollection' => $totalDueCollection,
    					        'tobecollectedlist' => $lastOrderData,
    					        "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
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
    						'message' => 'reportData',
    						'dailycollectionFile' => $dailycollectionFile,
    						'totalDueCollection' => $totalDueCollection,
    					    'tobecollectedlist' => $lastOrderData,
    					    "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    					]);
    				}
    			}else{
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Order Details.',
    					'totalDueCollection' => $totalDueCollection,
    					'tobecollectedlist' => $lastOrderData,
    					"Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    				]);
    			}
				
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
	
	public function cghsptreport(Request $request){
			
		if(Auth::user()){
			$date = date('Y-m-d');
			$pdffile = $request->pdffile;
		    $whatsappfile = $request->whatsappfile;
            $dueCollection = DB::table('order_detail')
                ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
                ->select(
                    'order_master.patient_id','order_detail.order_detail_id',
                    DB::raw("DATE_FORMAT(order_master.created_at, '%d-%m-%Y') AS created_month_year"),
                    DB::raw('(select branches.branch_name from branches where order_master.branch_id=branches.branch_id) as branchName'),
                    DB::raw('(SELECT CONCAT(patients.name_prefix," ",patients.name) FROM patients WHERE patients.patient_id=order_master.patient_id) AS patientsName'),
                    'order_master_id',
                    DB::raw('(SELECT users.user_name FROM users inner join suggested_treatments on users.user_id=suggested_treatments.treatmentBydoctor_id WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatmentBydoctor'),
                    DB::raw('(SELECT suggested_treatments.treatment_name FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatment_name'),
                    DB::raw('(SELECT SUM(amount) FROM suggested_treatment_payment WHERE suggested_treatment_payment.order_detail_id=order_detail.order_detail_id) AS PaidAmount'),
                    DB::raw('(SELECT SUM(suggested_treatments.total_amount) FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS TotalAmount'),
                    DB::raw('(SELECT group_name FROM groups inner join patients on patients.group_id=groups.group_id WHERE order_detail.patient_id=patients.patient_id) AS group_name')
                )
                ->where(['clinic_id' => $request->clinic_id])
                ->where('is_paid','!=',2)
                ->where('istatus','=',0)
                ->whereNotIn("branch_id",[18])
                ->whereIn('order_detail.patient_id', function ($query)  use ($request) {
                    $query->select('patients.patient_id')
                        ->from('patients')
                        ->join('groups','patients.group_id','=','groups.group_id')
                        ->where("group_name","like","%CGHS%")
                        ->when($request->branch_id, function ($query) use ($request) {
                        	$query->whereIn("groups.branch_id",$request->branch_id)
                        	->whereNotIn("groups.branch_id",[18]);
			            });
                        //->where('patients.group_id', '=', $request->doctor_id);
                });
                $dueCollection->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
				->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
				->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_master.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_master.created_at)"),'=',$request->year);
				})
				->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("branch_id",$request->branch_id);
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
				$totalAmount = 0;
				$paidAmount = 0;
				$dueAmount = 0;
				foreach($dueCollectionAmounts as $dueCollectionAmount){
					$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					if($due_amount > 0){
					    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					}
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
					->where(['suggested_treatments.patient_id' => $dueCollectionAmount->patient_id,"is_billing" => 1, 'order_detail.order_id'=>$dueCollectionAmount->order_master_id,"order_detail_id" => $dueCollectionAmount->order_detail_id])
					->when($request->doctor_id, function ($query) use ($request) {
						$query->where("treatmentBydoctor_id",$request->doctor_id);
					})
					
					->get();
				
    					$lastOrderData[] = array(
    					    "month" => $dueCollectionAmount->created_month_year,
						    "branchName" => $dueCollectionAmount->branchName,
						    "treatmentBydoctor" => $dueCollectionAmount->treatmentBydoctor,
						    "treatment_name" => $dueCollectionAmount->treatment_name,
    						"patient_id" => $dueCollectionAmount->patient_id,
    						"patientsName" => $dueCollectionAmount->patientsName,
    						"TotalAmount" => $dueCollectionAmount->TotalAmount ?? 0,
    						"PaidAmount" => $dueCollectionAmount->PaidAmount ?? 0,
    						"DueAmount" => $due_amount,
    						"group_name" => $dueCollectionAmount->group_name
    					);
    					$totalAmount += $dueCollectionAmount->TotalAmount;
    					$paidAmount += $dueCollectionAmount->PaidAmount;
    					$dueAmount += $due_amount;
				}
				
			
			if(!empty($lastOrderData)){
			    if($pdffile == 1 && !empty($lastOrderData)){
    							
    				$pdf = PDF::loadView('cghs_pt_report',['Collection' => $lastOrderData,"Total" => $totalAmount,"paidAmount" => $paidAmount,"dueAmount" => $dueAmount]);
    						
    				$fileName = date('d-m-Y')."_cghs_pt_report";
    				
    				$content = $pdf->download()->getOriginalContent();
    				Storage::put('public/assets/cghs_pt_report/'.$fileName . '.pdf',$content);
    				
    				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
    					$pdf->save(public_path('assets/cghs_pt_report/')  . $fileName. '.pdf');	
    				}else {
    					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/cghs_pt_report/')  . $fileName. '.pdf');
    
    				}
    		
    				$dailycollectionFile = asset('assets/cghs_pt_report/'. $fileName. '.pdf');
    		
    				//return $pdf->download($fileName . '.pdf');
    				
    				$key = $_ENV['WHATSAPPKEY'];		
    				$dailycollectionListFile = asset('assets/cghs_pt_report/'. $fileName. '.pdf');
    				$msg = "Dear User, Please find attached details of collection.";
    							
    				if($whatsappfile == 1){
    					$users = new User();
    					$currentUser = Auth::user();
    
    					$mobileNo = $currentUser->mobile_no;
    					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
    					
    				// 	$statusofMessage = $status->status;
    					// $Response = $status->response;
    				
    				// 	if($statusofMessage == "success"){
    					
    						return response()->json([
    							'status' => 'success',
    							'message' => 'Consult DR Report sent on your registered mobile number.',
    							'dailycollectionFile' => $dailycollectionFile,
    							'totalDueCollection' => $totalDueCollection,
    					        'tobecollectedlist' => $lastOrderData,
    					        "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
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
    						'message' => 'reportData',
    						'dailycollectionFile' => $dailycollectionFile,
    						'totalDueCollection' => $totalDueCollection,
    					    'tobecollectedlist' => $lastOrderData,
    					    "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    					]);
    				}
    			}else{
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Order Details.',
    					'totalDueCollection' => $totalDueCollection,
    					'tobecollectedlist' => $lastOrderData,
    					"Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    				]);
    			}
				
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
	
	public function newbranchwiselabreport(Request $request){
	    set_time_limit(300);
	    if(Auth::user()){
    		$clinic_id = $request->clinic_id;
    		// $branch_id = $request->branch_id;
    		$pdffile = $request->pdffile;
    		$whatsappfile = $request->whatsappfile;
    		$arr = [];
    		$grand_amount = 0;
    		//$branchs = Branch::where('clinic_id','=',1)->get();
    		$branchs = Branch::where('clinic_id','=',1)->whereNotIn("branch_id",[18])->get();
    		foreach($branchs as $branch){
    		    $branchData = [];
        		$treatmentDataLabwork = Lab_Work::select(
        			 DB::raw('DATE_FORMAT(lab_work.created_at, "%d-%M-%Y") as order_date'),
        			'patients.name_prefix as name_prefix',
        			'patients.name as name',
        			'material_master.product_name',
        			'lab_work.teeth_change as teeth',
        			'lab_work.material_price',
        			'lab_work.lab_price',
        			//'order_detail.rate',
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
                    ")
    			)
    			->where(['lab_work.istatus' => 0, 'lab_work.clinic_id' => $clinic_id,'lab_work.branch_id' => $branch->branch_id])
    			->join('branches', 'branches.branch_id', '=', 'lab_work.branch_id')
    			->join('patients', 'patients.patient_id', '=', 'lab_work.patient_id')
    			->join('labs','labs.lab_id', '=', 'lab_work.lab_id')
    			->join('material_master','material_master.material_id', '=', 'lab_work.material_id')
    			->join('users', 'users.user_id', '=', 'lab_work.doctor_id')
    			//->join('order_detail', 'order_detail.labwork_master_id', '=', 'lab_work.labwork_master_id')
    			->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(lab_work.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
				})
    			->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(lab_work.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
                })
    			->when($request->selected_date, function ($query) use ($request) {
                    $query->where(DB::raw("lab_work.created_at"),'like',DB::raw("'".date('Y-m-d',strtotime($request->selected_date))."%'"));
				})
    			->when($request->month, function ($query) use ($request) {
    			    $query->where(DB::raw("MONTH(lab_work.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
    			    $query->where(DB::raw("YEAR(lab_work.created_at)"),'=',$request->year);
				})
    			->get();
        		
    			$LabworkData = DB::table('labs')->select('labs.lab_name')
        			->where(['clinic_id' => $clinic_id])
        		    ->where('labs.lab_name','LIKE', $request->labname)
        			->first();
        			
    // 			$branchData = Branch::where('branch_id','=',$request->branch_id)->first();
    // 			$branchName = $branchData->branch_name;
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
    			
    			if(count($treatmentDataLabwork) != 0){
    			    $branchData["branchName"] = $branch->branch_name;
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
    					
    					$branchData["List"][] = array(
    					
    						"order_date" => $TreatmentDataLabwork->order_date,
    						"patient_name" => $TreatmentDataLabwork->name_prefix." ".$TreatmentDataLabwork->name,
    						"product_name" => $TreatmentDataLabwork->product_name,
    						"teeth" => $TreatmentDataLabwork->teeth,
    						"unit" => $teethCount,
    						"material_price" => $TreatmentDataLabwork->material_price,
    						"lab_price" => $labPrice,
    						//"rate" => $TreatmentDataLabwork->rate,
    						"branch_name" => $TreatmentDataLabwork->branch_name,
    						"lab_name" => $TreatmentDataLabwork->lab_name,
    						"iLabWorkStatus" => $TreatmentDataLabwork->iLabWorkStatus,
    						"strLabWorkStatus" => $TreatmentDataLabwork->strLabWorkStatus
    					); 
    				}
    				$branchData["total"] = $grand_amount;
    				$arr[] = $branchData;
    			}
    			
    			
    		}
			
			//$grand_amount = 0;
			if(count($arr) != 0){
				if($pdffile == 1){
							
					$pdf = PDF::loadView('new_branch_wise_labwork_report',['labWorkData' => $arr,"Duration" => $Duration,"LabworkData" => $LabworkData]);
					
					$fileName = date('d-m-Y')."_labwork";
					
					$content = $pdf->download()->getOriginalContent();
					Storage::put('public/assets/branch_wise_labwork_report/'.$fileName . '.pdf',$content);
					
					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
						$pdf->save(public_path('assets/branch_wise_labwork_report/')  . $fileName. '.pdf');	
					}else {
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/branch_wise_labwork_report/')  . $fileName. '.pdf');

					}
					
					$labFile = asset('assets/branch_wise_labwork_report/'. $fileName. '.pdf');
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
							'labworkData' => $arr,
							//'grand_amount' => $grand_amount
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
						//'grand_amount' => $grand_amount
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
    				//'grand_amount' => $grand_amount
    			]);
    	    }
	    }else{
		  return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
    		  
	    }
	}
    
    
    public function flyerRecordReport(Request $request){
	    $clinic_id = $request->clinic_id;
		$pdffile = $request->pdffile;
		$whatsappfile = $request->whatsappfile;
		
		if(Auth::user()){
			$dataArr=[]; 
			$TotalCash = 0;
    			$TotalCheque = 0;
    			$TotalCard = 0;
    			$Totalonline = 0;
    			$i = 1;
    			$Total = 0;
			    $reportCollections = Patient::select(
					'patients.patient_id','name_prefix','name','suggested_treatments.treatment_name','patients.branch_id',
                    DB::raw('(select branches.branch_name from branches where branches.branch_id=patients.branch_id) as Branch_Name'),
                    DB::raw('(select GROUP_CONCAT(quotation.quotation_name SEPARATOR ", ") from quotation where quotation.patient_id=patients.patient_id) as QuotationName'),
                    DB::raw('(SELECT ifnull(sum(order_payment_detail.amount),0) FROM `suggested_treatment_payment` join order_payment_detail on order_payment_detail.order_payment_detail_id=suggested_treatment_payment.order_payment_detail_id where suggested_treatment_payment.suggested_treatments_id=suggested_treatments.suggested_treatment_id and suggested_treatment_payment.patient_id=patients.patient_id) as revenuAmount')
				)
				->where(['patients.clinic_id' => $clinic_id])
				->whereNull("branches.deleted_at")
				->whereIn('patients.group_id', function ($query)  use ($request) {
                    $query->select('groups.group_id')
                        ->from('groups')
                        ->where("group_name","like","%Flyer%")
                        ->when($request->branch_id, function ($query) use ($request) {
                        	$query->whereIn("branch_id",$request->branch_id)
                        	->whereNotIn("branches.branch_id",[18]);
			            });
                })
				->join('suggested_treatments', 'suggested_treatments.patient_id', '=', 'patients.patient_id')
				->join('branches','branches.branch_id','=','patients.branch_id')
				
				
				->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("patients.branch_id",$request->branch_id);
				})
				->when($request->fromDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->fromDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->toDate, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->toDate))."','%Y-%m-%d')"));
			    })
			    ->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(patients.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(patients.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(patients.created_at)"),'=',$request->year);
				})
				->whereNotIn("branches.branch_id",[18])
				->get(); 
			
				foreach($reportCollections as $reportCollection){
					$Total= $Total + ($reportCollection->revenuAmount ?? 0);
    				$dataArr[] = array(
    			        "PT_Name" => $reportCollection->name_prefix ." ". $reportCollection->name,
    			        "Branch" => $reportCollection->Branch_Name,
    			        "Suggest_Treatment" => $reportCollection->treatment_name ?? "",
    			        "Quotation" => $reportCollection->QuotationName ?? "",
    			        "Revenue_Amount" => $reportCollection->revenuAmount ?? 0
    			    );
				    $i++;
			    }
			
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
	
			if($pdffile == 1 && !empty($dataArr)){
							
				$pdf = PDF::loadView('flyer_record_report',['Collection' => $dataArr,"Duration" => $Duration,"Total" => $Total]);
						
				$fileName = date('d-m-Y')."_flyer_record_report";
				
				$content = $pdf->download()->getOriginalContent();
				Storage::put('public/assets/flyer_record_report/'.$fileName . '.pdf',$content);
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
					$pdf->save(public_path('assets/flyer_record_report/')  . $fileName. '.pdf');	
				}else {
					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/flyer_record_report/')  . $fileName. '.pdf');

				}
		
				$dailycollectionFile = asset('assets/flyer_record_report/'. $fileName. '.pdf');
		
				//return $pdf->download($fileName . '.pdf');
				
				$key = $_ENV['WHATSAPPKEY'];		
				$dailycollectionListFile = asset('assets/flyer_record_report/'. $fileName. '.pdf');
				$msg = "Dear User, Please find attached details of collection.";
							
				if($whatsappfile == 1){
					$users = new User();
					$currentUser = Auth::user();

					$mobileNo = $currentUser->mobile_no;
					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
					
				// 	$statusofMessage = $status->status;
					// $Response = $status->response;
				
				// 	if($statusofMessage == "success"){
					
						return response()->json([
							'status' => 'success',
							'message' => 'Flyer Report sent on your registered mobile number.',
							'dailycollectionFile' => $dailycollectionFile,
							'dailycollection' => $dataArr,
							"Total" => $Total
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
						'message' => 'reportData',
						'dailycollection' => $dataArr,
						'dailycollectionFile' => $dailycollectionFile,
						"Total" => $Total
					]);
				}
			}else{
				return response()->json([
					'status' => 'success',
					'message' => 'reportData.',
					'dailycollection' => $dataArr,
					"Total" => $Total
				]);
			}
		}else{
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	
	public function HeavyWaterReport(Request $request){
			
		if(Auth::user()){
			$date = date('Y-m-d');
			$pdffile = $request->pdffile;
		    $whatsappfile = $request->whatsappfile;
            $dueCollection = DB::table('order_detail')
                ->join('order_master', 'order_master.order_master_id', '=', 'order_detail.order_id')
                ->select(
                    'order_master.patient_id','order_detail.order_detail_id',
                    DB::raw("DATE_FORMAT(order_master.created_at, '%d-%m-%Y') AS created_month_year"),
                    DB::raw('(select branches.branch_name from branches where order_master.branch_id=branches.branch_id) as branchName'),
                    DB::raw('(SELECT CONCAT(patients.name_prefix," ",patients.name) FROM patients WHERE patients.patient_id=order_master.patient_id) AS patientsName'),
                    'order_master_id',
                    DB::raw('(SELECT users.user_name FROM users inner join suggested_treatments on users.user_id=suggested_treatments.treatmentBydoctor_id WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatmentBydoctor'),
                    DB::raw('(SELECT suggested_treatments.treatment_name FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS treatment_name'),
                    DB::raw('(SELECT SUM(amount) FROM suggested_treatment_payment WHERE suggested_treatment_payment.order_detail_id=order_detail.order_detail_id) AS PaidAmount'),
                    DB::raw('(SELECT SUM(suggested_treatments.total_amount) FROM suggested_treatments WHERE suggested_treatments.suggested_treatment_id=order_detail.suggested_treatment_id) AS TotalAmount')
                )
                ->where(['clinic_id' => $request->clinic_id])
                ->where('is_paid','!=',2)
                ->where('istatus','=',0)
                ->whereNotIn("branch_id",[18])
                ->whereIn('order_detail.patient_id', function ($query)  use ($request) {
                    $query->select('patients.patient_id')
                        ->from('patients')
                        ->join('groups','patients.group_id','=','groups.group_id')
                        ->where("group_name","like","heavy water%")
                        ->when($request->branch_id, function ($query) use ($request) {
                        	$query->whereIn("groups.branch_id",$request->branch_id)
                        	->whereNotIn("groups.branch_id",[18]);
			            });
                        //->where('patients.group_id', '=', $request->doctor_id);
                });
                $dueCollection->when($request->fromDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'>=',DB::raw("DATE_FORMAT('".$request->fromDate."','%Y-%m-%d')"));
				})
				->when($request->toDate, function ($query) use ($request) {
					$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'<=',DB::raw("DATE_FORMAT('".$request->toDate."','%Y-%m-%d')"));
				})
				->when($request->selected_date, function ($query) use ($request) {
						$query->where(DB::raw("DATE_FORMAT(order_master.created_at,'%Y-%m-%d')"),'=',DB::raw("DATE_FORMAT('".date('Y-m-d',strtotime($request->selected_date))."','%Y-%m-%d')"));
			    })
			    ->when($request->month, function ($query) use ($request) {
					$query->where(DB::raw("MONTH(order_master.created_at)"),'=',$request->month);
				})
    			->when($request->year, function ($query) use ($request) {
					$query->where(DB::raw("YEAR(order_master.created_at)"),'=',$request->year);
				})
				->when($request->branch_id, function ($query) use ($request) {
					$query->whereIn("branch_id",$request->branch_id);
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
				$totalAmount = 0;
				$paidAmount = 0;
				$dueAmount = 0;
				foreach($dueCollectionAmounts as $dueCollectionAmount){
					$due_amount = $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					if($due_amount > 0){
					    $totalDueCollection += $dueCollectionAmount->TotalAmount - $dueCollectionAmount->PaidAmount;
					}
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
					->where(['suggested_treatments.patient_id' => $dueCollectionAmount->patient_id,"is_billing" => 1, 'order_detail.order_id'=>$dueCollectionAmount->order_master_id,"order_detail_id" => $dueCollectionAmount->order_detail_id])
					->when($request->doctor_id, function ($query) use ($request) {
						$query->where("treatmentBydoctor_id",$request->doctor_id);
					})
					
					->get();
				
    					$lastOrderData[] = array(
    					    "month" => $dueCollectionAmount->created_month_year,
						    "branchName" => $dueCollectionAmount->branchName,
						    "treatmentBydoctor" => $dueCollectionAmount->treatmentBydoctor,
						    "treatment_name" => $dueCollectionAmount->treatment_name,
    						"patient_id" => $dueCollectionAmount->patient_id,
    						"patientsName" => $dueCollectionAmount->patientsName,
    						"TotalAmount" => $dueCollectionAmount->TotalAmount ?? 0,
    						"PaidAmount" => $dueCollectionAmount->PaidAmount ?? 0,
    						"DueAmount" => $due_amount,
    					);
    					$totalAmount += $dueCollectionAmount->TotalAmount;
    					$paidAmount += $dueCollectionAmount->PaidAmount;
    					$dueAmount += $due_amount;
				}
				
			
			if(!empty($lastOrderData)){
			    if($pdffile == 1 && !empty($lastOrderData)){
    							
    				$pdf = PDF::loadView('heavy_water_report',['Collection' => $lastOrderData,"Total" => $totalAmount,"paidAmount" => $paidAmount,"dueAmount" => $dueAmount]);
    						
    				$fileName = date('d-m-Y')."_heavy_water_report";
    				
    				$content = $pdf->download()->getOriginalContent();
    				Storage::put('public/assets/heavy_water_report/'.$fileName . '.pdf',$content);
    				
    				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
    					$pdf->save(public_path('assets/heavy_water_report/')  . $fileName. '.pdf');	
    				}else {
    					$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/heavy_water_report/')  . $fileName. '.pdf');
    
    				}
    		
    				$dailycollectionFile = asset('assets/heavy_water_report/'. $fileName. '.pdf');
    		
    				//return $pdf->download($fileName . '.pdf');
    				
    				$key = $_ENV['WHATSAPPKEY'];		
    				$dailycollectionListFile = asset('assets/heavy_water_report/'. $fileName. '.pdf');
    				$msg = "Dear User, Please find attached details of collection.";
    							
    				if($whatsappfile == 1){
    					$users = new User();
    					$currentUser = Auth::user();
    
    					$mobileNo = $currentUser->mobile_no;
    					$status = $users->sendWhatsappMessage($mobileNo,$key,$msg,$dailycollectionFile);
    					
    				// 	$statusofMessage = $status->status;
    					// $Response = $status->response;
    				
    				// 	if($statusofMessage == "success"){
    					
    						return response()->json([
    							'status' => 'success',
    							'message' => 'Consult DR Report sent on your registered mobile number.',
    							'dailycollectionFile' => $dailycollectionFile,
    							'totalDueCollection' => $totalDueCollection,
    					        'tobecollectedlist' => $lastOrderData,
    					        "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
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
    						'message' => 'reportData',
    						'dailycollectionFile' => $dailycollectionFile,
    						'totalDueCollection' => $totalDueCollection,
    					    'tobecollectedlist' => $lastOrderData,
    					    "Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    					]);
    				}
    			}else{
    				return response()->json([
    					'status' => 'success',
    					'message' => 'Order Details.',
    					'totalDueCollection' => $totalDueCollection,
    					'tobecollectedlist' => $lastOrderData,
    					"Total" => $totalAmount,
    					        "paidAmount" => $paidAmount,
    					        "dueAmount" => $dueAmount
    				]);
    			}
				
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
	
}
