<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Labwork;
use App\Models\LabworkHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Lab_Work_History;

class LabworkHistoryController extends Controller
{
     public function addlabworkhistory(Request $request){
		
			if(Auth::user()){
				
					$labworkhistory = LabworkHistory::create([
					'labwork_master_id' => $request->labwork_master_id,
					'lab_id' => $request->lab_id,
					'title' => $request->title,
					'description' => $request->description
				]);
				
				/* if($patient_send_flag == 1)
				{
					$patientData = Patient::where(['patient_id'=> $patient_id])->first();
					$patientMobileNo = $patientData->mobile_no;
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "testing";
						$users = new User();
						$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,'');
						
						$statusofMessage = $status->status;
					
						if($statusofMessage == 1){
							return response()->json([
								'status' => 'success',
								'message' => 'Note created and sent on patients registered mobile number.',
							], 401);
						}else{
							
							return response()->json([
								'status' => 'error',
								'message' => 'Mobile number not registered.',
							], 401);
						}
				
				}else{ */
					
					return response()->json([
					'status' => 'success',
					'message' => 'Labwork history added successfully',
					'labworkHistory' => $labworkhistory
				
					
				]);
				//}
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		 public function labworkhistoryview(Request $request, $id){
			 
				if(Auth::user()){
				$labworkHistory = LabworkHistory::select(
				'title',
				'description',
				'created_at'
				)
				->where(['labwork_master_id' => $id])
				->get();
				
				
				$arr = [];
				foreach($labworkHistory as $LabworkHistory){
					
					$orgDate = $LabworkHistory->created_at;  
					$newCreatedDate = date("d-M-Y", strtotime($orgDate));
						
					$arr[] = array(
							"title" => $LabworkHistory->title,
							"description" => $LabworkHistory->description,
							"created_at" => $newCreatedDate,
						); 
				}
				
					return response()->json([
					'status' => 'success',
					'labworkHistory' => $arr
				]);
				
				}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		 }
		 
	
	public function addnewlabworkhistory(Request $request){
		if(Auth::user()){
			$labworkhistory = Lab_Work_History::create([
				'labwork_master_id' => $request->labwork_master_id,
				'lab_id' => $request->lab_id,
				'title' => $request->title,
				'description' => $request->description
			]);	
			return response()->json([
				'status' => 'success',
				'message' => 'Labwork history added successfully',
				'labworkHistory' => $labworkhistory
			]);
		}else{
		    return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
		
	 public function newlabworkhistoryview(Request $request, $id){
		 
		if(Auth::user()){
			$labworkHistory = Lab_Work_History::select(
    			'title',
    			'description',
    			'created_at'
			)
			->where(['labwork_master_id' => $id])
			->get();
			
			$arr = [];
			foreach($labworkHistory as $LabworkHistory){
				$orgDate = $LabworkHistory->created_at;  
				$newCreatedDate = date("d-M-Y", strtotime($orgDate));
				$arr[] = array(
					"title" => $LabworkHistory->title,
					"description" => $LabworkHistory->description,
					"created_at" => $newCreatedDate,
				); 
			}
			return response()->json([
				'status' => 'success',
				'labworkHistory' => $arr
			]);
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
    }	 

	
}
