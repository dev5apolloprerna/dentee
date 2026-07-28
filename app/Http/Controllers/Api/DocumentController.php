<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
	public function docuementAdd(Request $request){
			
			if(Auth::user()){
				/* $request->validate([
				'branch_name' => 'required|string|max:255|unique:branches,branch_name,NULL,id,deleted_at,NULL',
				],
				[
					'branch_name.required' => 'Branch name is required',
				]); */
			
				//$existProduct = Document::where(['product_name'=>trim($request->product_name),'deleted_at' => NULL])->first();
				
				/* if(!empty($existProduct)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Product already exist.'
						]);
					
				}else{ */
				$uploaded_date = $request->uploaded_date;
				$uploaded_date = date("Y-m-d", strtotime($uploaded_date));
				$patientId = $request->patient_id;
				$imagesize = $request->file('image')->getSize();
				$imageName = $patientId.'_'.time().'.'.$request->image->extension();  
       
				
				
				if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$request->image->move(public_path('assets/document'), $imageName);
						}else {
							$request->image->move(public_path('../../vgdcapp.vrajdentalclinic.com/assets/document'), $imageName);
							
						}
				$imageFilePath = asset('assets/document/'. $imageName);
				
					$addDocument = Document::create([
					'document_name' => $imageName,
					'clinic_id' => $request->clinic_id,
					'branch_id' => $request->branch_id,
					'patient_id' => $request->patient_id,
					'image' => $imageName,
					'uploaded_date' => $uploaded_date,
					'image_type' => $request->image_type,
					'image_size' => $imagesize
				]);
				//}
				
				return response()->json([
					'status' => 'success',
					'message' => 'image added successfully',
					'docuementData' => $addDocument,
					'imagepath' => $imageFilePath 
					
					]);
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		public function destroyDocuement(Request $request,$id){
			
					if(Auth::user()){
							
							$data = Document::where('document_id',$id)->count();
								
							if($data){
								$documentDelete = Document::find($id);
								$documentDelete->delete();
								
							return response()->json([
									'status' => 'success',
									'message' => 'image removed successfully.'
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
		
		public function getallDocuement(Request $request){
			
			$clinic_id = $request->clinic_id;
			$patient_id = $request->patient_id;
			$branch_id = $request->branch_id;
			$image_type = $request->image_type;
			/* $note = new Note();
				$allnotelist = $note->allNote();
				return $allnotelist; */
				if(Auth::user()){
					
					if($image_type != 0){
							
						$document = Document::select(
						'document.document_id',
						'document.patient_id',
						'document.image',
						'document.uploaded_date',
						'patients.name as patient_name'
						)
						->where([
							'document.clinic_id' =>$request->clinic_id,
							'document.branch_id' =>$request->branch_id,
							'document.patient_id' =>$request->patient_id,
							'document.image_type' =>$image_type
							
						])
						->join('patients', 'document.patient_id', '=', 'patients.patient_id')
						->get();
					
					}else{
						
						$document = Document::select(
						'document.document_id',
						'document.patient_id',
						'document.image',
						'document.uploaded_date',
						'patients.name as patient_name'
						)
					->where([
						'document.clinic_id' =>$request->clinic_id,
						'document.branch_id' =>$request->branch_id,
						'document.patient_id' =>$request->patient_id
						
					])
					->join('patients', 'document.patient_id', '=', 'patients.patient_id')
					->get();
						
					}
					
					//$queries = \DB::getQueryLog();
						//dd($queries);
					
					$arr = [];
					foreach($document as $Document){
						
						$document_id = $Document->document_id;
						$date = $Document->uploaded_date;
						$image = $Document->image;
						$imageFilePath = asset('assets/document/'. $image);
						
						$arr[] = array(
						'uploaded_date' => $date,
						'document_id' => $document_id,
						'image' => $image,
						'image_path' => $imageFilePath
						);
					}
					
					return response()->json([
										'status' => 'success',
										'image_path' => $arr
							]);
					
				}else{
					
					return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
   
}
