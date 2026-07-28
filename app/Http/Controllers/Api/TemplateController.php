<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Template;
use App\Models\TemplateMedicines;
use App\Models\Medicines;
use App\Models\Frequency;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
      public function addTemplate(Request $request){

					if(Auth::user()){
						$user_login = Auth::user();
						
						
						$medicineArr = $request->medicine_id;
						//$medicinestr = str_replace(array('[',']') ,'' , $medicines);
						//$medicineArr = explode(',' , $medicinestr);
						
						$template_id = $request->template_id;
						$branch_id = $request->branch_id;
						$edit = $request->edit;
						$update_medicine_id = $request->update_medicine_id;
						
						if(!empty($template_id) && empty($edit)){
														
								//one medicine added using edit and another one saved directry without edit
									/* foreach ($medicineArr as $medicine_id) {
													
													$templatemedicinesexist = TemplateMedicines::where(['medicine_id' => $medicine_id,
													'template_id' => $template_id])->first();
			
													
													if($templatemedicinesexist){
														$medicinedalreadyexist = $templatemedicinesexist->template_medicine_id;
													
														$SuggestedTreatmentUpdateStatus = TemplateMedicines::where('template_medicine_id','=',$medicinedalreadyexist)->update([
														'istatus' => 1
														]);
													}
													
													if(!$templatemedicinesexist){
														
															 $medicineData = Medicines::where(['medicine_id'=>$medicine_id])->first();
															 $dosage = $medicineData->dosage;
															 $frequency = $medicineData->frequency;
															 $duration = $medicineData->duration;
															 
															 $templateMedicine = TemplateMedicines::create([
																'template_id' => $template_id,
																'medicine_id' => $medicine_id,
																'dosage' => $dosage,
																'frequency' => $frequency,
																'duration' => $duration,
																'istatus' => 1
															]);
																					
													}
										} */
										
									$existTemplate = Template::where('template_name', '=', $request->template_name)->where('deleted_at', '=', NULL)
									->where('template_id', '<>', $template_id)
									->where('branch_id', '=', $branch_id)
									->first();
									
									if(empty($existTemplate)){
										
										$template = TemplateMedicines::where(['template_id'=>$template_id])->update([
											'istatus' => 1
										]);
														
										$template = Template::where(['template_id'=>$template_id])->update([
											'clinic_id' => $request->clinic_id,
											'branch_id' => $request->branch_id,
											'template_name' => $request->template_name,
											'istatus' => 1
										]);
										
										return response()->json(['status' => 'success',
																'message' => 'Template Medicine Saved Successfully.'
										]);
										
									}else{
										
										return response()->json([
										'status' => 'error',
										'message' => 'Template with this name is already exist.',
										], 401);
										
									}
							
						}
						else if(!empty($edit) && !empty($update_medicine_id))
						{

							$medicinesUpdate =  TemplateMedicines::where([
							'medicine_id' => $update_medicine_id,
							'template_id' => $template_id])->update([

							'dosage' => $request->dosage,
							'frequency' => $request->frequency,
							'duration' => $request->duration,
							'notes' => $request->notes,
							
							]);
							
							$template = Template::where(['template_id'=>$template_id])->update([
										'template_name' => $request->template_name
									]);
									
							$lastInserttemplateID = $template_id;
							return response()->json([
							
									'status' => 'success',
									'message' => 'Medicine Details Updated Successfully.',
									'template_id' => $template_id
							
							]);
							
							
							
						}else{
							
							$templateExist = Template::where(['template_name'=>$request->template_name,'deleted_at' => NULL])
							->where('branch_id', '=', $branch_id)
							->first();
							
							
							if(empty($templateExist)){
							
								$template = Template::create([
									'clinic_id' => $request->clinic_id,
									'branch_id' => $request->branch_id,
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
						}
						
					}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
				}
				
		 public function getMedicinedetails(Request $request,$id){
			 
			 $template_id = $request->template_id;
			 if(Auth::user()){
				 
				$medicineData = TemplateMedicines::where(['medicine_id'=>$id,'template_id'=>$template_id])->first();
				$dosage = $medicineData->dosage;
				$frequency = $medicineData->frequency;
				$duration = $medicineData->duration;
				$notes = $medicineData->notes;

				$medicineArr = [];
				$frequencyData = Frequency::where(['frequency_id'=>$frequency])->first();
				$MedicinemasterData = Medicines::where(['medicine_id'=>$id])->first();

				
				$medicineArr = array(
				
					'medicine_id' => $medicineData->medicine_id,
					'clinic_id' => $MedicinemasterData->clinic_id,
					'branch_id' => $MedicinemasterData->branch_id,
					'name' => $MedicinemasterData->name,
					//'molecule' => $medicineData->molecule,
					'dosage' => $medicineData->dosage,
					'frequency' => $medicineData->frequency,
					'duration' => $medicineData->duration." day",
					'frequency_name' => $frequencyData->name,
					'notes' => $medicineData->notes,
				);
				
				
				return response()->json([
									'status' => 'success',
									'medicineData' => $medicineArr
								]);
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
		 }
		 
		public function saveMedicineDatabyIds(Request $request){
			 
			 $medicines = $request->medicine_id;		
			 if(Auth::user()){
						 
						 if(!empty($request->template_id)){
							 
							 $lasttemplateId = $request->template_id;
							 
							 //medicines we add from first page are already selected in add template so removing old data.
							  $medicinedata = TemplateMedicines::where(['template_id' => $lasttemplateId]);
							  
								if(!empty($medicinedata)){
									$medicinedata->delete();
								}
								
						 }else{
							  
							 //removing temporary data before adding new entry
							  $temporaryTemplateData = Template::where(['istatus' => 0]);
							  
								if(!empty($temporaryTemplateData)){
									$temporaryTemplateData->delete();
								}
								
							$temporaryTemplatemedicinedata = TemplateMedicines::where(['istatus' => 0]);
							  
								if(!empty($temporaryTemplatemedicinedata)){
									$temporaryTemplatemedicinedata->delete();
								}
								
							//removing temporary data before adding new entry
								
							  $template = Template::create([
									'template_name' => "temp",
									'istatus' => 0
								]);
								
							$lasttemplateId = $template->template_id;
						 }
						 
								$medicineArrforList = [];
										 foreach ($medicines as $value) {
											 
											$templatemedicinesexist = TemplateMedicines::where(['medicine_id' => $value,
											'template_id' => $lasttemplateId])->first();
											
											if(!$templatemedicinesexist){
												
														$medicineData = Medicines::where(['medicine_id'=>$value])->first();
														$name = $medicineData->name;
														 $dosage = $medicineData->dosage;
														 $frequency = $medicineData->frequency;
														 $duration = $medicineData->duration;
														 $notes = $medicineData->notes;
														 
														 $frequencyData = Frequency::where(['frequency_id'=>$frequency])->first();
														 
														 $templateMedicine = TemplateMedicines::create([
															'template_id' => $lasttemplateId,
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
										return response()->json([
											'status' => 'success',
											'message' => 'medicine added successfully.',
											'template_id' => $lasttemplateId,
											'medicineList' => $medicineArrforList
										]);
				
				}else{
						return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
		 }
		 public function getmedicinebytemplateid(Request $request,$id){
			 
			 if(Auth::user()){
				 
				 
					 $medicineData = TemplateMedicines::select(
							'templates.template_name',
							'medicines.name as medicine_name',
							'template_medicines.medicine_id',
							'template_medicines.dosage',
							'template_medicines.frequency',
							'template_medicines.duration',
							'template_medicines.notes'
							
							)
							->where(['template_medicines.template_id' => $id])
							
							->join('templates', 'templates.template_id', '=', 'template_medicines.template_id')
							->join('medicines', 'medicines.medicine_id', '=', 'template_medicines.medicine_id')
							->get();
							
							foreach($medicineData as  $MedicineData){
								
								if(!empty($MedicineData->frequency)){
									
									$frequencyData = Frequency::where(['frequency_id'=>$MedicineData->frequency])->first();
									$frequencyName = $frequencyData['name'];
								}else{
									$frequencyName = "";
								}
								$medicineDataArr[] = array(
								'template_name' => $MedicineData->template_name,
								'medicine_name' => $MedicineData->medicine_name,
								'medicine_id' => $MedicineData->medicine_id,
								'dosage' => $MedicineData->dosage,
								'frequency' => $MedicineData->frequency,
								'notes' => $MedicineData->notes,
								'duration' => $MedicineData->duration." day",
								'frequency_name' => $frequencyName
								);
								
							}
							
					return response()->json([
										'status' => 'success',
										'template_id' => $id,
										'medicineList' => $medicineDataArr
								]);
								
			}else{
					return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
					}
					
		 }
		 
		  public function listTemplate(Request $request){
			  
			  if(Auth::user()){
				 
				 
					 $medicineData = Template::select(
							'template_name',
							'template_id'	
							)
							->where(['istatus' => 1,'branch_id' => $request->branch_id])
							->orderBy('template_name')
							->get();
							
					$templateListData = [];
					
					if(count($medicineData) != 0){
						
						foreach($medicineData as $MedicineData){
							
							$templateId = $MedicineData->template_id;
							 $medicineCount = TemplateMedicines::where(['template_id' => $templateId])->count();
							 
							 $templateListData[] = array(
							 'template_id' => $templateId,
							 'template_name' => $MedicineData->template_name,
							 'medicineCount' => $medicineCount
							 );
							 
						}
						 return response()->json([
										'status' => 'success',
										'templateList' => $templateListData
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
		  
		//destroy Meidicine form list
		public function destroymedicinefromTemplate(Request $request,$id)
		{
		   if(Auth::user()){

			 $templateData = TemplateMedicines::where(['template_id' => $request->template_id,'medicine_id' => $id])->count();
				if($templateData){
					$templateMedicinesDelete = TemplateMedicines::where(['template_id' => $request->template_id,'medicine_id' => $id]);
					$templateMedicinesDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Medicine removed Successfully.',]);
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
		
		//destroy Meidicine form list
		public function destroyTemplate(Request $request,$id)
		{
		   if(Auth::user()){

			 $templateDatacount = Template::where(['template_id' => $id])->count();
				if($templateDatacount){
					
					$templateDelete = TemplateMedicines::where(['template_id' => $id]);
					$templateDelete->delete();
					
					$templateData = Template::where(['template_id' => $id]);
					$templateData->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Template removed Successfully.',]);
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
}
