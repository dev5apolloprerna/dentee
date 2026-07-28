<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use App\Models\User;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use DateTime;

class NoteController extends Controller
{
           public function addNote(Request $request){
		
			$patient_id = $request->patient_id;
			$patient_send_flag = $request->patient_send_flag;
			$currentDate = date('d-M-Y');
			
			if(Auth::user()){
				//$existNote = Note::where(['doctor_id'=>$request->doctor_id,'deleted_at' => NULL])->first();
				
				/* if(!empty($existNote)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Note already exist.'
						]);
					
				}else{ */
					
					$note = Note::create([
					'doctor_id' => $request->doctor_id,
					'branch_id' => $request->branch_id,
					'clinic_id' => $request->clinic_id,
					'patient_id' => $patient_id,
					'note_date' => $request->note_date,
					'note' => $request->note
				]);
				
				
				if($patient_send_flag == 1)
				{
					$patientData = Patient::where(['patient_id'=> $patient_id])->first();
					$patientMobileNo = $patientData->mobile_no;
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "testing";
						$users = new User();
						$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,'');
						
						$statusofMessage = $status->status;
						// $Response = $status->response;
					
						if($statusofMessage == "success"){
							return response()->json([
								'status' => 'success',
								'message' => 'Note created and sent on patients registered mobile number.',
							], 401);
						}else{
							
							return response()->json([
								'status' => 'error',
								'message' => 'Note created successfully. Whatsapp Message'.$Response.'.Please contact admin.',
							], 401);
						}
				
				}else{
					
					return response()->json([
					'status' => 'success',
					'message' => 'Note created successfully',
					'note' => $note,
					'current_date' => $currentDate,
				
					
				]);
				}
				//}
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
		//update branch
		public function updateNote(Request $request,$id)
		{
				if(Auth::user()){

					$existNote = Note::where('doctor_id', '=', $request->doctor_id)->where('deleted_at', '=', NULL)->where('note_id', '<>', $id)->first();
				
					/* if(!empty($existNote)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Note already exist.'
							]);
						
					}else{ */
					
					$note= Note::find($id);
					$note->update($request->all());
					//return $user;
					return response()->json(['status' => 'success','message' => 'Note Updated Successfully.','note' => $note]);
					//}
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function allNote(Request $request){
				/* $note = new Note();
				$allnotelist = $note->allNote();
				return $allnotelist; */
				if(Auth::user()){
				$note = Note::select(
				'notes.note_id',
				'notes.doctor_id',
				'notes.branch_id',
				'notes.patient_id',
				'notes.note_date',
				'notes.note',
				'users.user_name as doctor_name'
				)
				->where(['notes.clinic_id' =>$request->clinic_id,'notes.branch_id' =>$request->branch_id,
				'notes.patient_id' =>$request->patient_id])
				->join('users', 'notes.doctor_id', '=', 'users.user_id')
				->get();
				
				//$queries = \DB::getQueryLog();
					//dd($queries);
				
				return $note;
				
				}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
		
			//destroy Note
		public function destroyNote($id)
		{
		   if(Auth::user()){

			 $data = Note::where('note_id',$id)->count();
				if($data){
					$LabDelete = Note::find($id);
					$LabDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Note deleted Successfully.',]);
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
		
		public function PdfNote(Request $request){
				/* $note = new Note();
				$allnotelist = $note->allNote();
				return $allnotelist; */
				
				$noteIds = $request->noteids;
				$clinic_id = $request->clinic_id;
				$branch_id = $request->branch_id;
				$patient_id = $request->patient_id;
				$patient_send_flag = $request->patient_send_flag;
				$print_flag = $request->print_flag;
				$notearr = [];

				if(Auth::user()){
					
				
				
				foreach($noteIds as $noteId){
					 
					 $note = Note::select(
						'notes.note_id',
						'notes.doctor_id',
						'notes.branch_id',
						'notes.patient_id',
						'notes.note_date',
						'notes.note',
						'users.user_name as doctor_name'
						)
						->where(['notes.clinic_id' =>$request->clinic_id,'notes.branch_id' =>$request->branch_id,
						'notes.patient_id' =>$request->patient_id,'notes.note_id' =>$noteId])
						->join('users', 'notes.doctor_id', '=', 'users.user_id')
						->first();
						
						 $notearr[] = array("note_date" => $note->note_date,"note" => $note->note);
				 }
				
				//$queries = \DB::getQueryLog();
					//dd($queries);
				
				
				
				
					$patientData = Patient::where(['patient_id'=> $patient_id])->first();
					$patientMobileNo = $patientData->mobile_no;
					$patientNamePrefix = $patientData->name_prefix;
					$patient_name = $patientData->name;
					$gender = $patientData->gender;
					$dateOfBirth = $patientData->date_of_birth;
					$case_no = $patientData->case_no;
					
					$from = new DateTime($dateOfBirth);
						$to   = new DateTime('today');
						$age =  $from->diff($to)->y;
						
						/* if($gender == "Female")
						{
							$genderName = "F";
						}else{
							$genderName = "M";
						} */
					
						$key = $_ENV['WHATSAPPKEY'];
						$msg = "Dear User, Please find attached Clinical Notes.";
						$users = new User();
						
						$fileName = trim($case_no)."_".date('d-m-Y');
						$today = date('d-m-Y');
						 
						//set_time_limit(300);
						$pdf = PDF::loadView('note',['note' => $notearr,
						'name_prefix' => $patientNamePrefix,
						'patient_name' => $patient_name,
						'case_no' => $case_no,
						'age' => $age,
						'genderName' => $gender,
						
						]);
						
						
						$content = $pdf->download()->getOriginalContent();
						Storage::put('public/note/'.$fileName . '.pdf',$content);
						
						if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
							$pdf->save(public_path('assets/note/')  . $fileName. '.pdf');	
						}else {
							$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/note/')  . $fileName. '.pdf');

						}
						
						
						
						$noteFile = asset('assets/note/'. $fileName. '.pdf');
						
						//return $pdf->download($fileName . '.pdf');
				if($patient_send_flag == 1)
				{
						
						$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$noteFile);
						
						$statusofMessage = $status->status;
						// $Response = $status->response;
					    $Response = $status->message;
						if($statusofMessage == "success"){
							return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $noteFile,
								'message' => 'Note created and sent on patients registered mobile number.',
							], 401);
						}else{
							
							return response()->json([
								'status' => 'error',
								'message' => 'Note created successfully. Whatsapp Message '.$Response.'.Please contact admin.',
							], 401);
						}
				
				}else{
					
					return response()->json([
					'status' => 'success',
					'message' => 'Notes',
					'pdfFileUrl' => $noteFile,
					'note' => $note
					//'current_date' => $currentDate,
				
					
				]);
				}
				
				}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
}
