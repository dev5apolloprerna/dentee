<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Template;
use App\Models\TemplateMedicines;
use App\Models\Medicines;
use App\Models\Frequency;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use DateTime;
use App\Models\ConcernForm;
use App\Models\PatientsConcernForm;
use App\Services\AuthkeyWhatsAppService;

class PrescriptionController extends Controller
{
	
    public function addPrescription(Request $request){

					if(Auth::user()){
						$user_login = Auth::user();
						
						
						$medicineArr = $request->medicine_id;
						//$medicinestr = str_replace(array('[',']') ,'' , $medicines);
						//$medicineArr = explode(',' , $medicinestr);
						
						$prescription_id = $request->prescription_id;
						$edit = $request->edit;
						$update_medicine_id = $request->update_medicine_id;
						$new_template = $request->new_template;
						
						if(!empty($prescription_id) && empty($edit)){
														
								//one medicine added using edit and another one saved directry without edit
								
								$templateExist = Template::where(['template_name'=>$request->template_name,'deleted_at' => NULL])->first();
								if(empty($templateExist) && $new_template == 1){
								
									$template = Template::create([
										'template_name' => $request->template_name,
										'clinic_id' => $request->clinic_id,
										'istatus' => 1
									]);

									$lasttemplateId = $template->template_id;
									
									$medicineArrforList = [];
									 foreach ($medicineArr as  $value) {
										 
										 $medicineData = Medicines::where(['medicine_id'=>$value])->first();
										 
										 $name = $medicineData->name;
										 $dosage = $medicineData->dosage;
										 $frequency = $medicineData->frequency;
										 $duration = $medicineData->duration;
										 
										 $templateMedicine = TemplateMedicines::create([
											'template_id' => $lasttemplateId,
											'medicine_id' => $value,
											'dosage' => $dosage,
											'frequency' => $frequency,
											'duration' => $duration,
											'istatus' => 1
										]);
									 }
								}
										
									$prescription = PrescriptionMedicine::where(['prescription_id'=>$prescription_id])->update([
										'istatus' => 1
									]);
									
									
									$orgPrescriptionDate = $request->prescription_date;  
									$newPrescriptionDate = date("Y-m-d", strtotime($orgPrescriptionDate));  
													
									$prescription = Prescription::where(['prescription_id'=>$prescription_id])->update([
										'clinic_id' => $request->clinic_id,
										'doctor_id' => $request->doctor_id,
										'note' => $request->note,
										'prescription_date' => $newPrescriptionDate,
										'istatus' => 1
									]);
									
									return response()->json(['status' => 'success',
															'message' => 'Prescription Medicine Saved Successfully.'
									]);
							
						}
						else if(!empty($edit) && !empty($update_medicine_id))
						{
							$prescription = PrescriptionMedicine::where(['medicine_id' => $update_medicine_id,'prescription_id'=>$prescription_id])->update([
										'dosage' => $request->dosage,
										'frequency' => $request->frequency,
										'duration' => $request->duration,
										'notes' => $request->medicinenote
		
									]);
							
									
							/* $orgPrescriptionDate = $request->prescription_date;  
									$newPrescriptionDate = date("Y-m-d", strtotime($orgPrescriptionDate));  
													
									$prescription = Prescription::where(['prescription_id'=>$prescription_id])->update([
										'clinic_id' => $request->clinic_id,
										'doctor_id' => $request->doctor_id,
										'prescription_date' => $newPrescriptionDate,
										'istatus' => 1
									]); */
									
							$lastInsertprescriptionID = $prescription_id;
							return response()->json([
							
									'status' => 'success',
									'message' => 'Medicine Details Updated Successfully.',
									'prescription_id' => $prescription_id
							
							]);
							
							
							
						}
						
						
						/* else{
							$PrescriptionExist = Prescription::where(['template_name'=>$request->template_name,'deleted_at' => NULL])->first();
							if(empty($templateExist)){
							
								$template = Prescription::create([
									'template_name' => $request->template_name,
									'istatus' => 0
								]);
								
								$lasttemplateId = $template->template_id;

								
								$medicineArrforList = [];
								 foreach ($medicineArr as  $value) {
									 
									 $medicineData = Medicines::where(['medicine_id'=>$value])->first();
									 
									 $name = $medicineData->name;
									 $dosage = $medicineData->dosage;
									 $frequency = $medicineData->frequency;
									 $duration = $medicineData->duration;
									 
									 $templateMedicine = TemplateMedicines::create([
										'template_id' => $template->template_id,
										'medicine_id' => $value,
										'dosage' => $dosage,
										'frequency' => $frequency,
										'duration' => $duration,
										'istatus' => 0
									]);
									
								
									
									 $medicineArrforList[] = array(
										"medicine_id" => $value,
										"name" => $name,
										"dosage" => $dosage,
										"frequency" => $frequency,
										"duration" => $duration
									); 
								
								}
								return response()->json([
									'status' => 'success',
									'template_id' => $lasttemplateId,
									'medicineList' => $medicineArrforList
								]);
							} else {
								return response()->json([
									'status' => 'error',
									'message' => 'Template with this name is already exist.',
								], 401);
							}
						} */
						
					}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
		}
				
    public function saveMedicineDatabypresIds(Request $request){
			 
			 $medicines = $request->medicine_id;		
			 $patient_id = $request->patient_id;		
			 if(Auth::user()){
						 
						 if(!empty($request->prescription_id)){
							 
							 $lastprescriptionId = $request->prescription_id;
							 
							 //medicines we add from first page are already selected in add template so removing old data.
							  $medicinepredata = PrescriptionMedicine::where(['prescription_id' => $lastprescriptionId]);
							  
								if(!empty($medicinepredata)){
									$medicinepredata->delete();
								}
								
								
								$medicineArrforList = [];
										  foreach ($medicines as $value) {
											 
											$prescriptionmedicinesexist = PrescriptionMedicine::where(['medicine_id' => $value,
											'prescription_id' => $lastprescriptionId])->first();
											
											if(!$prescriptionmedicinesexist){
												
														$medicineData = Medicines::where(['medicine_id'=>$value])->first();
														$name = $medicineData->name;
														 $dosage = $medicineData->dosage;
														 $frequency = $medicineData->frequency;
														 $duration = $medicineData->duration;
														 $notes = $medicineData->notes;
														 
														 $frequencyData = Frequency::where(['frequency_id'=>$frequency])->first();
														 
														 $templateMedicine = PrescriptionMedicine::create([
															'prescription_id' => $lastprescriptionId,
															'medicine_id' => $value,
															'dosage' => $dosage,
															'frequency' => $frequency,
															'duration' => $duration,
															'notes' => $notes,
															'istatus' => 0
														]);
											
										
											
														 $medicineArrforList[] = array(
															"medicine_id" => $value,
															"name" => $name,
															"dosage" => $dosage,
															"frequency" => $frequency,
															"frequency_name" => $frequencyData->name,
															"duration" => $duration,
															"notes" => $notes
														); 
											
											}else{
												
													continue;
											}
										} 
								
						 }else{
							 
								
							//removing temporary data before adding new entry
								
							 $prescriptionData = Prescription::where(['patient_id' => $request->patient_id,'istatus' => 0]);
							  
								if(!empty($prescriptionData)){
									$prescriptionData->delete();
								}
								
							  $prescription = Prescription::create([
									'template_name' => "",
									'clinic_id' => $request->clinic_id,
									'branch_id' => $request->branch_id,
									'patient_id' => $request->patient_id,
									'istatus' => 0
								]);
								
							$lastprescriptionId = $prescription->prescription_id;
						 }
						 
							return response()->json([
								'status' => 'success',
								'prescription_id' => $lastprescriptionId,
								'message' => 'prescription added scucessfully.'
							]);
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
		 }
	
// for list of medicine on prescription add page	
    public function getmedicinebyPrescriptionid(Request $request,$id){
			 
			 if(Auth::user()){

					 $medicineData = PrescriptionMedicine::select(
							'medicines.name as medicine_name',
							'prescription_medicine.medicine_id',
							'prescription_medicine.dosage',
							'prescription_medicine.frequency',
							'prescription_medicine.duration',
							'prescription_medicine.notes',
							'prescription_medicine.template_id',
							
							)
							->where(['prescription_medicine.prescription_id' => $id])
							
							->join('prescription', 'prescription.prescription_id', '=', 'prescription_medicine.prescription_id')
							->join('medicines', 'medicines.medicine_id', '=', 'prescription_medicine.medicine_id')
							->get();
							
							if(count($medicineData) != 0){
							
							foreach($medicineData as  $MedicineData){
								
								if(!empty($MedicineData->frequency)){
									
									$frequencyData = Frequency::where(['frequency_id'=>$MedicineData->frequency])->first();
									$frequencyName = $frequencyData['name'];
								}else{
									$frequencyName = "";
								}
								
								$medicineDataArr[] = array(
								'medicine_name' => $MedicineData->medicine_name,
								'medicine_id' => $MedicineData->medicine_id,
								'dosage' => $MedicineData->dosage,
								'frequency' => $MedicineData->frequency,
								'duration' => $MedicineData->duration." day",
								'notes' => $MedicineData->notes,
								'frequency_name' => $frequencyName,
								'template_id' => $MedicineData->template_id
								);
								
							}
							
							return response()->json([
										'status' => 'success',
										'prescription_id' => $id,
										'message' => 'Medicine List',
										'medicineList' => $medicineDataArr
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
		 
    // after selecting template id added medicine in medicine list add prescription page
	public function savemedicinebytemplateid(Request $request,$id){
			 if(Auth::user()){
				 
					$prescriptionId = $request->prescription_id;
					
					$patientId = $request->patient_id;
					
					 $temporaryPrescriptionData = PrescriptionMedicine::where(['prescription_id' => $prescriptionId,'template_id' => $id])->count();
							 								
								if($temporaryPrescriptionData == 0){
									
									
									 $medicineData = TemplateMedicines::select(
										'medicine_id',
										'dosage',
										'frequency',
										'duration',
										'notes'
							
									)
									->where(['template_id' => $id])
									->get();
							
									foreach($medicineData as  $MedicineData){

										$templateMedicine = PrescriptionMedicine::create([
												'prescription_id' => $prescriptionId,
												'medicine_id' => $MedicineData->medicine_id,
												'dosage' => $MedicineData->dosage,
												'frequency' => $MedicineData->frequency,
												'duration' => $MedicineData->duration,
												'notes' => $MedicineData->notes,
												'template_id' => $id,
												'istatus' => 0
										]);
										
									}
							
									return response()->json([
										'status' => 'success',
										'template_id' => $id,
										'message' => 'template added successfully.'
								]);
								}else{
									
									return response()->json([
										'status' => 'error',
										'message' => 'template already added.'
										]);
								}
								
			}else{
					return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
			}
		 }
		 

		 
	//destroy Meidicine form list
	public function destroymedicinefromPrescription(Request $request,$id)
	{
	   $templateId = $request->template_id;
	   if(Auth::user()){

		 $prescriptionData = PrescriptionMedicine::where(['prescription_id' => $request->prescription_id,'medicine_id' => $id])->count();
			if($prescriptionData){
				
				if($templateId != 0){
						$prescriptionMedicinesDelete = PrescriptionMedicine::where(['prescription_id' => $request->prescription_id,'medicine_id' => $id,
						'template_id' => $templateId
					]);
					
					$prescriptionMedicinesDelete->delete();
				
					return response()->json([
						'status' => 'success',
						'message' => 'Medicine removed Successfully.'
					]);
				}else{
					
					$prescriptionMedicinesDelete = PrescriptionMedicine::where([
						'prescription_id' => $request->prescription_id,
						'medicine_id' => $id,
						'template_id' => 0
					]);
					
					$prescriptionMedicinesDelete->delete();
				
					return response()->json([
						'status' => 'success',
						'message' => 'Medicine removed Successfully.'
					]);
				}
				
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
		
		
		
	// prescription List
	public function getallprescription(Request $request, $id){
		
		$clinic_id = $request->clinic_id;
		$branch_id = $request->branch_id;
		//$patient_id = $request->patient_id;
		
		if(Auth::user()){
				
				$prescriptionData = Prescription::select(
				'prescription.prescription_date',
				'prescription.doctor_id',
				'users.user_name as doctor_name',
				'prescription.prescription_id',
				'prescription.note'
				)
				->where([
				'prescription.patient_id' => $id,
				'prescription.clinic_id' => $clinic_id,
				'prescription.branch_id' => $branch_id,
				'prescription.istatus' => 1])
				
				->join('users', 'users.user_id', '=', 'prescription.doctor_id')
			//	->join('prescription_medicine', 'prescription.prescription_id', '=', 'prescription_medicine.prescription_id')
				//->groupBy('prescription.prescription_date')
				->orderBy('prescription.prescription_date', 'desc')
				->get();
				
				$arr = [];
				
				foreach($prescriptionData as $PrescriptionData){
					
					$prescriptionDate = $PrescriptionData->prescription_date;
					
					 $medicineList = PrescriptionMedicine::select(
						'prescription_medicine.medicine_id',
						'prescription_medicine.dosage',
						'prescription_medicine.frequency',
						'prescription_medicine.duration as duration',
						'prescription_medicine.notes',
						'medicines.name as medicine_name',
						'frequencies.name as frequency_name'
						)
						
					->where([
					
					 'prescription_medicine.prescription_id' => $PrescriptionData->prescription_id,
					 'prescription_medicine.istatus' => 1
					 
					 ])
					 ->join('medicines', 'prescription_medicine.medicine_id', '=', 'medicines.medicine_id')
					 ->join('frequencies', 'frequencies.frequency_id', '=', 'prescription_medicine.frequency')
					//->groupBy('prescription.doctor_id')
					->get();
					
					
					$medicineListarr = [];
					foreach($medicineList as $MedicineList){
						
						$medicineListarr[] = array(
						"medicine_id" => $MedicineList->medicine_id,
						"dosage" => $MedicineList->dosage,
						"frequency" => $MedicineList->frequency,
						"duration" => $MedicineList->duration." day",
						"notes" => $MedicineList->notes,
						"medicine_name" => $MedicineList->medicine_name,
						"frequency_name" => $MedicineList->frequency_name
					); 
					}
					
					$arr[] = array(
						"prescription_date" => $PrescriptionData->prescription_date,
						"doctor_id" => $PrescriptionData->doctor_id,
						"doctor_name" => $PrescriptionData->doctor_name,
						"prescription_id" => $PrescriptionData->prescription_id,
						"notes" => $PrescriptionData->notes,
						"medicineList" => $medicineListarr 
					); 
						
				}
				
					return response()->json([
						'status' => 'success',
						'prescriptionData' => $arr
					]);

				
			}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
	}
		
	public function getprescriptiondetails(Request $request,$id){
		 
		 if(Auth::user()){
			 
			$prescriptionData = Prescription::select(
			
				'prescription.prescription_date',
				'prescription.doctor_id',
				'users.user_name as doctor_name',
				'prescription.note',
				'prescription.template_id',
			)
			
			->where(['prescription_id'=>$id])
			->join('users', 'users.user_id', '=', 'prescription.doctor_id')
			
			->first();
			
			$prescriptiondataArr = [];
			$template_id = $prescriptionData->template_id;
			if($template_id != 0){
				$templateData = Template::where(['template_id'=>$request->template_id,'deleted_at' => NULL])->first();
				$template_name = $templateData->template_name;
			}else{
				$template_name = "";
			}
			
			$prescriptiondataArr = array(
				'template_name' => $template_name,
				'prescription_date' => $prescriptionData->prescription_date,
				'doctor_id' => $prescriptionData->doctor_id,
				'doctor_name' => $prescriptionData->doctor_name,
				'note' => $prescriptionData->note,
				'template_id' => $prescriptionData->template_id
			
			);
			//die;
			
			
			return response()->json([
								'status' => 'success',
								'prescriptionData' => $prescriptiondataArr
							]);
			
			}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
					], 401);
				}
	 }
		 
    //destroy Meidicine form list
	public function destroyPrescription(Request $request,$id)
	{
		if(Auth::user()){

		 $prescriptionDatacount = Prescription::where(['prescription_id' => $id])->count();
			if($prescriptionDatacount){
				
				$prescriptionDelete = PrescriptionMedicine::where(['prescription_id' => $id]);
				$prescriptionDelete->delete();
				
				$prescriptionData = Prescription::where(['prescription_id' => $id]);
				$prescriptionData->delete();
				
				return response()->json([
					'status' => 'success',
					'message' => 'Prescription removed Successfully.',]);
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

	public function prescriptionwhatsapp(Request $request){
		
		if(Auth::user()){

				$prescription_id = $request->prescription_id;
				$patient_id = $request->patient_id;
				
					$patientData = Patient::where(['patient_id'=> $patient_id])->first();
					$patientMobileNo = $patientData->mobile_no;
					$date_of_birth = $patientData->date_of_birth;
					$name_prefix = $patientData->name_prefix;
					$patient_name = $patientData->name;
					$address = $patientData->address;
					$case_no = $patientData->case_no;
					$gender = $patientData->gender;
					$printfile = $request->printfile;
					
					$from = new DateTime($date_of_birth);
					$to   = new DateTime('today');
					$age =  $from->diff($to)->y;
					
					if($gender == "Female")
					{
						$genderName = "F";
					}else{
						$genderName = "M";
					}
			// to get prescription data outside loop
			
				$prescriptionData = Prescription::where(['prescription_id'=> $prescription_id])->first();
				
					$prescriptionDate = $prescriptionData->prescription_date; 
					$newprescriptionDate = date("d-M-Y", strtotime($prescriptionDate));
					
					 $note = $prescriptionData->note;
					 
				$medicineData = PrescriptionMedicine::select(
						'medicines.name as medicine_name',
						'medicines.notes as notes',
						'prescription_medicine.medicine_id',
						'prescription_medicine.dosage',
						'prescription_medicine.frequency',
						'prescription_medicine.duration',
						'prescription_medicine.template_id',
						'prescription.prescription_date'
						
						)
						->where(['prescription_medicine.prescription_id' => $prescription_id,'prescription_medicine.istatus' => 1,
						'prescription.istatus' => 1])
						
						->join('prescription', 'prescription.prescription_id', '=', 'prescription_medicine.prescription_id')
						->join('medicines', 'medicines.medicine_id', '=', 'prescription_medicine.medicine_id')
						->get();
						
						if(count($medicineData) != 0){
						
						foreach($medicineData as  $MedicineData){
							
							if(!empty($MedicineData->frequency)){
								
								$frequencyData = Frequency::where(['frequency_id'=>$MedicineData->frequency])->first();
								$frequencyName = $frequencyData['name'];
							}else{
								$frequencyName = "";
							}
							$duration = $MedicineData->duration;
							$frequencyarr = explode("-",$frequencyName);
									 
							$noofMedicine = ($duration*$frequencyarr[0]) + ($duration*$frequencyarr[1]) + ($duration*$frequencyarr[2]);	
							
							$medicineDataArr[] = array(
							'medicine_name' => $MedicineData->medicine_name,
							'notes' => $MedicineData->notes,
							'medicine_id' => $MedicineData->medicine_id,
							'dosage' => $MedicineData->dosage,
							'frequency' => $MedicineData->frequency,
							'duration' => $MedicineData->duration." days",
							'frequency_name' => $frequencyName,
							'template_id' => $MedicineData->template_id,
							'numberofMedicine' => $noofMedicine
							);
							
						}
						
					
					//send bill detail pdf 
			
					$key = $_ENV['WHATSAPPKEY'];
					$msg = "Dear User, Please find attached prescription.";
					$fileName = trim($case_no)."_".date('d-m-Y');
					$today = date('d-m-Y');
					 

					$pdf = PDF::loadView('prescription',['medicineDataList' => $medicineDataArr,
					'name_prefix' => $name_prefix,
					'patient_name' => $patient_name,
					'case_no' => $case_no,
					'address' => $address,
					'age' => $age,
					'genderName' => $genderName,
					'prescriptionDate' => $newprescriptionDate,
					'note' => $note,
					
					]);
					
					
					$content = $pdf->download()->getOriginalContent();
					Storage::put('public/prescription/'.$fileName . '.pdf',$content);
					
					if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
						$pdf->save(public_path('assets/prescription/')  . $fileName. '.pdf');	
					}else {
						$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/prescription/')  . $fileName. '.pdf');

					}
					
					
					
					$prescriptionFile = asset('assets/prescription/'. $fileName. '.pdf');
					
					//return $pdf->download($fileName . '.pdf');
					
					if($printfile == 1){
						$users = new User();
				// 		$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,$prescriptionFile);
        				
        				$whatsappService = new AuthkeyWhatsAppService();
        				$wid = "42471"; // template id
        				$bodyValues = [
        					"1" => $prescriptionFile,
        				];
        				$statusofMessage = $whatsappService->sendText($patientMobileNo, $wid, $bodyValues);
				// 		$statusofMessage = $status->status;
						// $Response = $status->response;
					
				// 		if($statusofMessage == "success"){
							return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $prescriptionFile,
								'message' => 'Prescription sent on your registered mobile number.',
							], 401);
				// 		}else{
							
				// 			return response()->json([
				// 				'status' => 'error',
				// 				'message' => $Response.'.Please contact admin.',
				// 			], 401);
				// 		}
					}else{
						return response()->json([
								'status' => 'success',
								'pdfFileUrl' => $prescriptionFile,
								'message' => 'Prescription sent.'
							], 401);
					}
						
						return response()->json([
									'status' => 'success',
									'prescription_id' => $prescription_id,
									'message' => 'Medicine List',
									'medicineList' => $medicineDataArr
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
	
	public function concentform(Request $request,$id,$iConcernFormId, $PatientsConcernFormId){
			//echo "sdsfsd";
			//$patient_id = $request->patient_id;
			//$clinic_id = $request->clinic_id;
			//$branch_id = $request->branch_id;
			
			$arr[] = "";
			$patientData = Patient::where(['patient_id' => $id])->first();
			$ConcernForm = ConcernForm::where(['iConcernFormId'=>$iConcernFormId])->first();
			
			$patientNamePrefix = $patientData->name_prefix ?? "";
			$patientName = $patientData->name ?? "";
			$patientAddress = $patientData->address ?? "";
			$patientEmail = $patientData->email ?? "";
			$dateOfBirth = $patientData->date_of_birth ?? "";
			
			$from = new DateTime($dateOfBirth);
			$to   = new DateTime('today');
			$ageYear =  $from->diff($to)->y;
			$ageMonth =  $from->diff($to)->m;
			$today = date('m-d-Y');
			$time = date('H:i');
			
			$age = $ageYear." years";
			if($ageYear == 0){
				
				$age = $ageMonth." months";
			}
						
			$patient[] = array(
				"name_prefix" => $patientNamePrefix,
				"name" => $patientName,
				"address" => $patientAddress,
				"email" => $patientEmail,
				"date_of_birth" => $dateOfBirth,
				"age" => $age,
				"today" => $today,
				"time" => $time,
				"patient_id" => $id
			);
			
				$patient = compact('patient');
				//dd($patient);
    		/* 	echo "dddddddddddddddddd";
    			echo "<pre>";
    			print_r($patient);
    			echo "cccccc";
    			print_r($patient['patient'][0]['name_prefix']);
    			die; */
			
			//return view('patient.Concentform', ['patient' => $arr]);
			return view('patient.Concentform', compact('patient','ConcernForm','PatientsConcernFormId'));
		}
		
		
	public function upload(Request $request)
	{
	   // dd($request);
		$patient_id = $request->patient_id;
		$iConcernFormId = $request->iConcernFormId;
		$patientData = Patient::where(['patient_id' => $patient_id])->first();
		$ConcernForm = ConcernForm::where(['iConcernFormId'=>$iConcernFormId])->first();
		
		$case_no = $patientData->case_no ?? "";
		$patientName = $patientData->name ?? "";
		$patientNamePrefix = $patientData->name_prefix ?? "";
		$patientAddress = $patientData->address ?? "";
		$patientEmail = $patientData->email ?? "";
		$dateOfBirth = $patientData->date_of_birth ?? "";
		$mobile_no = $patientData->mobile_no ?? "";
		
		$from = new DateTime($dateOfBirth);
		$to   = new DateTime('today');
		$ageYear =  $from->diff($to)->y;
		$ageMonth =  $from->diff($to)->m;
		$today = date('m-d-Y');
		$time = date('H:i');
		
		$age = $ageYear." years";
		if($ageYear == 0){
			
			$age = $ageMonth." months";
		}
					
		$patient[] = array(
			"name_prefix" => $patientNamePrefix,
			"name" => $patientName,
			"address" => $patientAddress,
			"email" => $patientEmail,
			"date_of_birth" => $dateOfBirth,
			"age" => $age,
			"today" => $today,
			"time" => $time,
			"patient_id" => $patient_id
		);
		$patient = compact('patient');
		
		set_time_limit(300);
		$folderPath = public_path('assets/signature/');
		if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, $mode = 0777, true, true);
        }
		$image_parts = explode(";base64,", $request->signed);
		$image_type_aux = explode("image/", $image_parts[0]);
		$image_type = $image_type_aux[1];
		$image_base64 = base64_decode($image_parts[1]);
		$file = $folderPath . $case_no."_".$patientName . '.'.$image_type;
		
		file_put_contents($file, $image_base64);
		$fileName = $request->PatientsConcernFormId."_".$case_no."_".str_replace(' ', '_',$patientName);
		
		$arr = array(
			"strFileName" => $fileName.'.pdf',
			"submitedDateTime" => date('Y-m-d H:i:s'),
			"isSubmit" => 1,
		);
		PatientsConcernForm::where('id', $request->PatientsConcernFormId)->update($arr);
		
		$pdf = PDF::loadView('patient/Savedconcentform',['patient' => $patient,'fileName' => $file,'ConcernForm' => $ConcernForm]);
					
					
        $content = $pdf->download()->getOriginalContent();
        Storage::put('public/signatureform/'.$fileName . '.pdf',$content);
        
        if($_SERVER['SERVER_NAME'] == "127.0.0.1"){
        	$pdf->save(public_path('assets/signatureform/')  . $fileName. '.pdf');	
        }else {
        	$pdf->save(public_path('../../vgdcapp.vrajdentalclinic.com/assets/signatureform/')  . $fileName. '.pdf');
        
        }
        
        
        $key = $_ENV['WHATSAPPKEY'];		
		$signatureformFile = asset('assets/signatureform/'. $fileName. '.pdf');
		$msg = "Signature form sent on your registered mobile number.";
					
		//if($whatsappfile == 1){
			$users = new User();
			//$currentUser = Auth::user();

// 			$status = $users->sendWhatsappMessage($mobile_no,$key,$msg,$signatureformFile);
            $whatsappService = new AuthkeyWhatsAppService();
			$wid = "42476"; // template id
			$bodyValues = [
				"1" => $signatureformFile,
			];
			$statusofMessage = $whatsappService->sendText($mobile_no, $wid, $bodyValues);
			
		// 	$statusofMessage = $status->status;
			// $Response = $status->response;
		
		// 	if($statusofMessage == "success"){
				return back()->with('success', 'signature uploaded scucessfully.Form will be sent on your whatsapp number.');
		// 	}else{
				
		// 		return back()->with('success', 'signature uploaded scucessfully But message not sent on mobile number.Please contact admin.');
		// 	}
		/* }else{
			return response()->json([
				'status' => 'success',
				'treatmentDataFile' => $signatureformFile
			]);
		} */

		

		

	}
	
	public function concernformwhatsapp(Request $request){
	    if(Auth::user()){
            $patient_id = $request->patient_id;
			$iConcernFormId = $request->iConcernFormId;
			$patientData = Patient::where(['patient_id'=> $patient_id])->first();
			$patientMobileNo = $patientData->mobile_no;
			
			PatientsConcernForm::where(['iPatientId' => $request->patient_id,
				'iConcernFormId' => $request->iConcernFormId,
				'clinic_id' => $patientData->clinic_id])->delete();
			$arr = array(
				'iPatientId' => $request->patient_id,
				'iConcernFormId' => $request->iConcernFormId,
				'clinic_id' => $patientData->clinic_id,
				'branch_id' => $patientData->branch_id,
				'isSubmit' => 0,
				'strIP' => $request->ip()
			);
			$patientConcernForm = PatientsConcernForm::create($arr);
			$id = $patientConcernForm->id;
			
			$ConcernForm = ConcernForm::where(['iConcernFormId'=>$iConcernFormId])->first();
			$url = "https://vgdcapp.vrajdentalclinic.com/concentform/".$patient_id."/".$iConcernFormId . "/" . $id;
			$key = $_ENV['WHATSAPPKEY'];
			$msg = "Dear User, Please click on below link and sign the concern form.
			
			Link:".$url."";
			
			$users = new User();
// 			$status = $users->sendWhatsappMessage($patientMobileNo,$key,$msg,"");
            $whatsappService = new AuthkeyWhatsAppService();
			$wid = "42464"; // template id
			$bodyValues = [
				"1" => $url
			];
			$statusofMessage = $whatsappService->sendText($patientMobileNo, $wid, $bodyValues);
// 			$statusofMessage = $status->status;
		
// 			if($statusofMessage == "success"){
				return response()->json([
					'status' => 'success',
					'pdfFileUrl' => $url,
					'message' => 'Concern form sent on your registered mobile number.',
				], 401);
// 			}else{
// 				return response()->json([
// 					'status' => 'error',
// 					'message' => $Response.'.Please contact admin.',
// 				], 401);
// 			}		
		} else {
			return response()->json([
					'status' => 'error',
					'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	public function concernformsubmitedlist(Request $request)
	{
		if (Auth::user()) {
			$patient_id = $request->patient_id;
			$patientDatas = PatientsConcernForm::select('patients_concern_forms.id', 'strFileName', 'concern_forms.strConcernFormTitle','submitedDateTime')
				->join('concern_forms', 'concern_forms.iConcernFormId', '=', 'patients_concern_forms.iConcernFormId')
				->where(['patients_concern_forms.iPatientId' => $patient_id, 'patients_concern_forms.clinic_id' => $request->clinic_id, "isSubmit" => 1])
				->get();
			
			$data = [];
			if (!$patientDatas->isEmpty()) {
				foreach ($patientDatas as $PCData) {
					$strFileName = "";
					if ($_SERVER['SERVER_NAME'] == "127.0.0.1") {
						$strFileName = 'http://127.0.0.1:8000/assets/signatureform/' . $PCData->strFileName;
					} else {
						$strFileName = 'https://getdemo.in/dentee/assets/signatureform/' . $PCData->strFileName;
					}
					$data[] = array(
						"PatientsConcernFormId" => $PCData->id,
						"strFileName" => $strFileName,
						"strConcernFormTitle" => $PCData->strConcernFormTitle,
						"submitedDateTime" => date('d-m-Y', strtotime($PCData->submitedDateTime))
					);
				}
				return response()->json([
					'status' => 'success',
					'list' => $data,
					'message' => 'Patients Concern form List.',
				], 200);
			} else {
				return response()->json([
					'status' => 'error',
					'message' => 'No Data Found.',
				], 401);
			}
		} else {
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
	
	
	
}
