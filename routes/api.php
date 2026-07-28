
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
   // return $request->user();
//});
Route::post('login', 'App\Http\Controllers\Api\AuthController@login');
Route::post('register', 'App\Http\Controllers\Api\AuthController@register');
Route::get('logout', 'App\Http\Controllers\Api\AuthController@logout');
Route::post('refresh', 'App\Http\Controllers\Api\AuthController@refresh');
Route::get('me', 'App\Http\Controllers\Api\AuthController@me');


Route::post('clinic-register', 'App\Http\Controllers\Api\ClinicController@clinicRegister');
Route::get('getclinic/{id}', 'App\Http\Controllers\Api\ClinicController@getclinic');
Route::post('updateclinic/{id}', 'App\Http\Controllers\Api\ClinicController@updateclinic');

Route::post('useradd', 'App\Http\Controllers\Api\UserController@userRegister');

Route::post('all-user', 'App\Http\Controllers\Api\UserController@allUser');
Route::post('user-register-before-login', 'App\Http\Controllers\Api\UserController@userRegisterBeforeLogin');
Route::post('update-user/{id}', 'App\Http\Controllers\Api\UserController@updateUser');
Route::get('destroy-user/{id}','App\Http\Controllers\Api\UserController@destroyUser');
Route::post('myprofile','App\Http\Controllers\Api\UserController@myprofile');
Route::post('forgotpassword','App\Http\Controllers\Api\UserController@forgotpassword');
Route::post('changepassword','App\Http\Controllers\Api\UserController@changepassword');

/*********************************************************/
Route::post('supervisoradd', 'App\Http\Controllers\Api\SupervisorController@supervisorRegister');
Route::post('all-supervisor', 'App\Http\Controllers\Api\SupervisorController@allSupervisor');
// Route::post('supervisor-register-before-login', 'App\Http\Controllers\Api\SupervisorController@supervisorRegisterBeforeLogin');
Route::post('update-supervisor/{id}', 'App\Http\Controllers\Api\SupervisorController@updateSupervisor');
Route::get('destroy-supervisor/{id}','App\Http\Controllers\Api\SupervisorController@destroySupervisor');
Route::post('mysupervisorprofile','App\Http\Controllers\Api\SupervisorController@mysupervisorprofile');
Route::post('supervisorforgotpassword','App\Http\Controllers\Api\SupervisorController@supervisorforgotpassword');
Route::post('supervisorchangepassword','App\Http\Controllers\Api\SupervisorController@supervisorchangepassword');

Route::post('supervisorDashboardCount','App\Http\Controllers\Api\SupervisorController@supervisorDashboardCount');
Route::post('supervisorNewPatient','App\Http\Controllers\Api\SupervisorController@supervisorNewPatient');
Route::post('supervisorOnGoingPatient','App\Http\Controllers\Api\SupervisorController@supervisorOnGoingPatient');

Route::post('AddSupervisorQuestionAnswer','App\Http\Controllers\Api\SupervisorController@AddSupervisorQuestionAnswer');
Route::post('SupervisorQuestionAnswerList','App\Http\Controllers\Api\SupervisorController@SupervisorQuestionAnswerList');
Route::post('DoctorQuestionAnswerList','App\Http\Controllers\Api\SupervisorController@DoctorQuestionAnswerList');
Route::post('DoctorQuestionAnswerReplay','App\Http\Controllers\Api\SupervisorController@DoctorQuestionAnswerReplay');
Route::post('SupervisorPendingQuestionAnswerListAllPatient','App\Http\Controllers\Api\SupervisorController@SupervisorPendingQuestionAnswerListAllPatient');

Route::post('SuperAdminQuestionAnswerListAllPatient','App\Http\Controllers\Api\SupervisorController@SuperAdminQuestionAnswerListAllPatient');

Route::post('addtoImportantList','App\Http\Controllers\Api\SupervisorController@addtoImportantList');
Route::post('importantPatientList','App\Http\Controllers\Api\SupervisorController@importantPatientList');

Route::post('pricelist', 'App\Http\Controllers\Api\SupervisorController@pricelist');

/*********************************************************/
Route::post('add-role', 'App\Http\Controllers\Api\RoleController@addRole');
Route::get('destroy-role/{id}', 'App\Http\Controllers\Api\RoleController@destroyRole');

Route::post('addpatient', 'App\Http\Controllers\Api\PatientController@patientRegister');
Route::post('update-patient/{id}', 'App\Http\Controllers\Api\PatientController@updatePatient');
Route::any('allpatient', 'App\Http\Controllers\Api\PatientController@allPatient');
Route::get('export-patients/{clinic_id?}/{branch_id?}', 'App\Http\Controllers\Api\PatientController@PatientExport');

Route::get('destroypatient/{id}','App\Http\Controllers\Api\PatientController@destroyPatient');
Route::get('patientdeshboardcount/{id}','App\Http\Controllers\Api\PatientController@patientdeshboardcount');
Route::post('patientrecentlyregisterd','App\Http\Controllers\Api\PatientController@patientrecentlyregisterd');
Route::post('patientwithappointment','App\Http\Controllers\Api\PatientController@patientwithappointment');
Route::post('patientwithbirthday','App\Http\Controllers\Api\PatientController@patientwithbirthday');
Route::post('patientdetailbyId','App\Http\Controllers\Api\PatientController@patientdetailbyId');
Route::any('patientreport', 'App\Http\Controllers\Api\PatientController@patientreport');

Route::post('add-branch', 'App\Http\Controllers\Api\BranchController@addBranch');
Route::post('update-branch/{id}', 'App\Http\Controllers\Api\BranchController@updateBranch');
Route::post('all-branch', 'App\Http\Controllers\Api\BranchController@allBranch');
Route::post('add-case-prefix', 'App\Http\Controllers\Api\BranchController@addCasePrefix');
Route::post('get-case-prefix/{id}','App\Http\Controllers\Api\BranchController@getCasePrefix');
Route::post('updatecaseprefix/{id}', 'App\Http\Controllers\Api\BranchController@updateCasePrefix');
Route::get('destroycaseprefix/{id}', 'App\Http\Controllers\Api\BranchController@destroyCasePrefix');
Route::post('allcaseprefix', 'App\Http\Controllers\Api\BranchController@allCaseprefix');
Route::get('destroy-branch/{id}','App\Http\Controllers\Api\BranchController@destroyBranch');
Route::post('branch-user/{id}','App\Http\Controllers\Api\BranchController@branchByUser');
Route::post('branchdeshboardcount','App\Http\Controllers\Api\BranchController@branchdeshboardcount');
Route::post('deshboardcashonhand','App\Http\Controllers\Api\BranchController@deshboardcashonhand');

Route::post('add-doctor', 'App\Http\Controllers\Api\DoctorController@addDoctor');
Route::post('update-doctor/{id}', 'App\Http\Controllers\Api\DoctorController@updateDoctor');
Route::post('alldoctor', 'App\Http\Controllers\Api\DoctorController@allDoctor');
Route::get('destroydoctor/{id}', 'App\Http\Controllers\Api\DoctorController@destroyDoctor');
Route::post('doctorbybranch/{id}', 'App\Http\Controllers\Api\DoctorController@doctorbybranch');

Route::post('activeinactivedoctor', 'App\Http\Controllers\Api\DoctorController@activeinactivedoctor');
Route::post('inactivedoctorlist', 'App\Http\Controllers\Api\DoctorController@inactivedoctorlist');


Route::post('add-lab', 'App\Http\Controllers\Api\LabController@addLab');
Route::post('update-lab/{id}', 'App\Http\Controllers\Api\LabController@updateLab');
Route::post('alllab', 'App\Http\Controllers\Api\LabController@allLab');
Route::get('destroylab/{id}', 'App\Http\Controllers\Api\LabController@destroyLab');

Route::post('addmedicine', 'App\Http\Controllers\Api\MedicinesController@addMedicine');
Route::post('updatemedicine/{id}', 'App\Http\Controllers\Api\MedicinesController@updateMedicine');
Route::post('allmedicine', 'App\Http\Controllers\Api\MedicinesController@allMedicine');
Route::get('destroymedicine/{id}', 'App\Http\Controllers\Api\MedicinesController@destroyMedicine');

Route::post('addfrequency', 'App\Http\Controllers\Api\FrequencyController@addFrequency');
Route::post('updatefrequency/{id}', 'App\Http\Controllers\Api\FrequencyController@updateFrequency');
Route::post('allfrequency', 'App\Http\Controllers\Api\FrequencyController@allFrequency');
Route::get('destroyfrequency/{id}', 'App\Http\Controllers\Api\FrequencyController@destroyFrequency');

Route::post('addgroup', 'App\Http\Controllers\Api\GroupController@addGroup');
Route::post('updategroup/{id}', 'App\Http\Controllers\Api\GroupController@updateGroup');
Route::post('allgroup', 'App\Http\Controllers\Api\GroupController@allGroup');
Route::get('destroygroup/{id}', 'App\Http\Controllers\Api\GroupController@destroyGroup');


Route::post('add-treatment', 'App\Http\Controllers\Api\TreatmentsController@addTreatment');
Route::get('destroyTreatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@destroyTreatment');
Route::post('update-treatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@updateTreatment');
Route::any('treatmentlist', 'App\Http\Controllers\Api\TreatmentsController@allTreatment');
Route::any('treatmentlistlabworkwise', 'App\Http\Controllers\Api\TreatmentsController@allTreatmentLabWorkWise');
Route::post('addpatienttreatments', 'App\Http\Controllers\Api\TreatmentsController@suggestedTreatment');

Route::post('addsuggestedTreatment', 'App\Http\Controllers\Api\TreatmentsController@addsuggestedTreatment');
Route::post('submitsuggestedTreatment', 'App\Http\Controllers\Api\TreatmentsController@submitsuggestedTreatment');
Route::post('editsuggestedTreatment', 'App\Http\Controllers\Api\TreatmentsController@editsuggestedTreatment');
Route::post('deletesuggestedTreatment', 'App\Http\Controllers\Api\TreatmentsController@deletesuggestedTreatment');

Route::post('allpatienttreatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@getallpatienttreatment');
Route::post('getallpatienttreatmentforlabwork', 'App\Http\Controllers\Api\TreatmentsController@getallpatienttreatmentforlabwork');
Route::post('getpatienttreatmentbydate', 'App\Http\Controllers\Api\TreatmentsController@getpatienttreatmentbydate');
Route::post('updatepatienttreatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@updatepatienttreatment');
Route::get('destroypatienttreatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@destroypatienttreatment');
Route::post('updatesuggestedtreatment/{id}', 'App\Http\Controllers\Api\TreatmentsController@updatesuggestedtreatment');
Route::get('getpatienttreatmentbyId/{id}', 'App\Http\Controllers\Api\TreatmentsController@getpatienttreatmentbyId');
Route::any('getalltreatmentnotbilling', 'App\Http\Controllers\Api\TreatmentsController@getalltreatmentnotbilling');
Route::post('updatealltreatmentnotbilling', 'App\Http\Controllers\Api\TreatmentsController@updatealltreatmentnotbilling');
Route::post('addtoexistorderpayment', 'App\Http\Controllers\Api\TreatmentsController@addtoexistorderpayment');
Route::post('getlastorderDatabypatient', 'App\Http\Controllers\Api\TreatmentsController@getlastorderDatabypatient');
Route::post('updateStatus/{id}', 'App\Http\Controllers\Api\TreatmentsController@updateStatus');
Route::post('treatmentDataForPrintScreen', 'App\Http\Controllers\Api\TreatmentsController@treatmentDataForPrintScreen');
Route::post('treatmentpdfFilelink', 'App\Http\Controllers\Api\TreatmentsController@treatmentpdfFilelink');
Route::any('treatmentreport', 'App\Http\Controllers\Api\TreatmentsController@treatmentreport');
Route::any('noofstartedlist', 'App\Http\Controllers\Api\TreatmentsController@NoOfStartedlist');
Route::any('noofstartedlistLessThen700', 'App\Http\Controllers\Api\TreatmentsController@NoOfStartedLessThen700list');
Route::any('noofstartedlistGreaterThen700', 'App\Http\Controllers\Api\TreatmentsController@NoOfStartedGreaterThen700list');
Route::any('noBillGenrated', 'App\Http\Controllers\Api\TreatmentsController@noBillGenratedlist');

Route::post('lab_treatment_charges', 'App\Http\Controllers\Api\LabController@labTreatmentCharges');

Route::post('addappointment', 'App\Http\Controllers\Api\AppointmentsController@addAppointment');
Route::post('updateappointment/{id}', 'App\Http\Controllers\Api\AppointmentsController@updateAppointment');
Route::post('destroyappointment/{id}', 'App\Http\Controllers\Api\AppointmentsController@destroyAppointment');
Route::any('allappointment', 'App\Http\Controllers\Api\AppointmentsController@allAppointment');
Route::post('appointmenttime', 'App\Http\Controllers\Api\AppointmentsController@appointmenttime');
Route::post('updateAppointmentStatus/{id}', 'App\Http\Controllers\Api\AppointmentsController@updateAppointmentStatus');
Route::post('countappointmentbystatus', 'App\Http\Controllers\Api\AppointmentsController@countappointmentbystatus');
Route::get('getappointmentdetailbypatient/{id}', 'App\Http\Controllers\Api\AppointmentsController@getappointmentDetailbyPatient');

Route::post('addnote', 'App\Http\Controllers\Api\NoteController@addNote');
Route::post('updatenote/{id}', 'App\Http\Controllers\Api\NoteController@updateNote');
Route::post('allnote', 'App\Http\Controllers\Api\NoteController@allNote');
Route::post('Pdfnote', 'App\Http\Controllers\Api\NoteController@PdfNote');
Route::get('destroynote/{id}', 'App\Http\Controllers\Api\NoteController@destroyNote');

Route::post('addpayment', 'App\Http\Controllers\Api\OrderMasterController@addPayment');
Route::post('addPayment_new', 'App\Http\Controllers\Api\OrderMasterController@addPayment_new');
Route::post('getpayment', 'App\Http\Controllers\Api\OrderMasterController@getpayment');
Route::post('billlist', 'App\Http\Controllers\Api\OrderMasterController@billlist');
Route::post('cancelbill', 'App\Http\Controllers\Api\OrderMasterController@cancelbill');
Route::post('billorderdetaillist', 'App\Http\Controllers\Api\OrderMasterController@billorderdetaillist');
Route::post('treatmentviewlistbybillId', 'App\Http\Controllers\Api\OrderMasterController@treatmentviewlistbybillId');
Route::post('billingorderdetailchangedocotor', 'App\Http\Controllers\Api\OrderMasterController@billingorderdetailchangedocotor');
Route::post('orderdetailwhatsappdata', 'App\Http\Controllers\Api\OrderDetailController@orderdetailwhatsappdata');
Route::post('paymentlist', 'App\Http\Controllers\Api\OrderMasterController@paymentlist');
Route::post('cancelpayment', 'App\Http\Controllers\Api\OrderMasterController@cancelpayment');
Route::any('reportdailycollection', 'App\Http\Controllers\Api\OrderMasterController@reportdailycollection');
Route::post('gettreatmentdatabyOrderId', 'App\Http\Controllers\Api\OrderMasterController@gettreatmentdatabyOrderId');
Route::post('lastorderidbypatient', 'App\Http\Controllers\Api\OrderMasterController@lastorderIdbyPatient');
Route::post('tobecollectedlist', 'App\Http\Controllers\Api\OrderMasterController@tobecollectedlist');
Route::post('paymentcollctionlist', 'App\Http\Controllers\Api\OrderMasterController@paymentcollctionlist');

Route::post('new_bill_generated_amount', 'App\Http\Controllers\Api\OrderMasterController@newBillGeneratedAmount');

Route::any('reportpatientcollection', 'App\Http\Controllers\Api\OrderMasterController@reportpatientcollection');

Route::post('addmaterial', 'App\Http\Controllers\Api\MaterialMasterController@addMaterial');
Route::post('updatematerial/{id}', 'App\Http\Controllers\Api\MaterialMasterController@updateMaterial');
Route::get('destroymaterial/{id}', 'App\Http\Controllers\Api\MaterialMasterController@destroyMaterial');
Route::post('allmaterial', 'App\Http\Controllers\Api\MaterialMasterController@allMaterial');
Route::post('allproductlistforLabwork', 'App\Http\Controllers\Api\MaterialMasterController@allproductlistforLabwork');

Route::post('addlabwork', 'App\Http\Controllers\Api\LabworkController@addLabwork');
Route::post('listlabWork', 'App\Http\Controllers\Api\LabworkController@listlabWork');
Route::post('updatelabwork/{id}', 'App\Http\Controllers\Api\LabworkController@updateLabwork');
Route::any('labworkreport', 'App\Http\Controllers\Api\LabworkController@labworkreport');
Route::post('deletelabWork/{id}', 'App\Http\Controllers\Api\LabworkController@deletelabWork');
Route::post('whatsapplabwork/{id}', 'App\Http\Controllers\Api\LabworkController@whatsappLabWork');

Route::post('addNewLabwork', 'App\Http\Controllers\Api\LabworkController@addNewLabwork');
Route::any('newlabworkreport', 'App\Http\Controllers\Api\LabworkController@newlabworkreport');
Route::post('listnewlabWork', 'App\Http\Controllers\Api\LabworkController@listnewlabWork');
Route::post('updatenewlabwork/{id}', 'App\Http\Controllers\Api\LabworkController@updateNewLabwork');
Route::post('deletenewlabWork/{id}', 'App\Http\Controllers\Api\LabworkController@deleteNewlabWork');
Route::post('whatsappnewlabwork/{id}', 'App\Http\Controllers\Api\LabworkController@whatsappNewLabWork');
Route::post('lab_order_status', 'App\Http\Controllers\Api\LabworkController@lab_order_status');
Route::post('changeLabWork', 'App\Http\Controllers\Api\LabworkController@changeLabWork');
Route::post('LabWorkHistory', 'App\Http\Controllers\Api\LabworkController@LabWorkHistory');


//addLabworkHistory
Route::post('addLabworkHistory', 'App\Http\Controllers\Api\LabworkHistoryController@addlabworkhistory');
Route::get('labworkHistoryView/{id}', 'App\Http\Controllers\Api\LabworkHistoryController@labworkhistoryview');

Route::post('addNewLabworkHistory', 'App\Http\Controllers\Api\LabworkHistoryController@addnewlabworkhistory');
Route::get('newlabworkHistoryView/{id}', 'App\Http\Controllers\Api\LabworkHistoryController@newlabworkhistoryview');


//addtemplate
Route::post('addtemplate', 'App\Http\Controllers\Api\TemplateController@addTemplate');
Route::post('getmedicinedetails/{id}', 'App\Http\Controllers\Api\TemplateController@getMedicinedetails');
Route::post('savemedicinedatabyids', 'App\Http\Controllers\Api\TemplateController@saveMedicineDatabyIds');
Route::get('getmedicinebytemplateid/{id}', 'App\Http\Controllers\Api\TemplateController@getmedicinebytemplateId');
Route::post('listtemplate', 'App\Http\Controllers\Api\TemplateController@listTemplate');
Route::post('destroymedicinefromtemplate/{id}', 'App\Http\Controllers\Api\TemplateController@destroymedicinefromTemplate');
Route::get('destroytemplate/{id}', 'App\Http\Controllers\Api\TemplateController@destroyTemplate');

//prescription
Route::post('savemedicinedatabypresids', 'App\Http\Controllers\Api\PrescriptionController@saveMedicineDatabypresIds');
Route::get('getmedicinebyprescriptionid/{id}', 'App\Http\Controllers\Api\PrescriptionController@getmedicinebyPrescriptionid');
Route::post('savemedicinebytemplateid/{id}', 'App\Http\Controllers\Api\PrescriptionController@savemedicinebytemplateid');
Route::post('addprescription', 'App\Http\Controllers\Api\PrescriptionController@addPrescription');
Route::post('destroymedicinefromprescription/{id}', 'App\Http\Controllers\Api\PrescriptionController@destroymedicinefromPrescription');
Route::post('gallprescription/{id}', 'App\Http\Controllers\Api\PrescriptionController@getallprescription');
Route::get('getprescriptiondetails/{id}', 'App\Http\Controllers\Api\PrescriptionController@getprescriptiondetails');
Route::get('destroyprescription/{id}', 'App\Http\Controllers\Api\PrescriptionController@destroyPrescription');
Route::post('prescriptionwhatsapp', 'App\Http\Controllers\Api\PrescriptionController@prescriptionwhatsapp');

Route::post('concernformwhatsapp', 'App\Http\Controllers\Api\PrescriptionController@concernformwhatsapp');
Route::post('concernformsubmitedlist', 'App\Http\Controllers\Api\PrescriptionController@concernformsubmitedlist');

//document
Route::post('docuementadd', 'App\Http\Controllers\Api\DocumentController@docuementAdd');
Route::get('destroydocuement/{id}', 'App\Http\Controllers\Api\DocumentController@destroyDocuement');
Route::post('alldocument', 'App\Http\Controllers\Api\DocumentController@getallDocuement');

//
Route::post('addquotation', 'App\Http\Controllers\Api\QuotationController@addQuotation');
Route::post('updatequotation/{id}', 'App\Http\Controllers\Api\QuotationController@updatequotation');
Route::get('getquotationbyId/{id}', 'App\Http\Controllers\Api\QuotationController@getquotationbyId');
Route::get('destroyquotation/{id}', 'App\Http\Controllers\Api\QuotationController@destroyquotation');
Route::post('getallquotation/{id}', 'App\Http\Controllers\Api\QuotationController@getallquotation');
Route::post('getquotationbydate', 'App\Http\Controllers\Api\QuotationController@getquotationbydate');
Route::post('quotationDataForPrintScreen', 'App\Http\Controllers\Api\QuotationController@quotationDataForPrintScreen');
Route::post('movetosuggestedtreatments', 'App\Http\Controllers\Api\QuotationController@movetosuggestedtreatments');

Route::post('deleteQuatation', 'App\Http\Controllers\Api\QuotationController@deleteQuatation');

Route::post('addconcernform', 'App\Http\Controllers\Api\ConcernFormController@addConcernForm');
Route::post('updateconcernform', 'App\Http\Controllers\Api\ConcernFormController@updateConcernForm');
Route::post('allconcernform', 'App\Http\Controllers\Api\ConcernFormController@allConcernForm');
Route::post('destroyconcernform', 'App\Http\Controllers\Api\ConcernFormController@destroyConcernForm');


Route::post('branch-wise-superadmin-collection', 'App\Http\Controllers\Api\SuperAdminController@branchwisesuperadmincollection');
Route::post('daily-branch-wise-superadmin-collection', 'App\Http\Controllers\Api\SuperAdminController@dailybranchwisesuperadmincollection');
Route::post('branch-wise-lab-report', 'App\Http\Controllers\Api\SuperAdminController@branchwiselabreport');
Route::post('branch-wise-medicine-collection-report', 'App\Http\Controllers\Api\SuperAdminController@branchwisemedicinecollectionreport');
Route::post('facebook-pt-record-report', 'App\Http\Controllers\Api\SuperAdminController@facebookptrecordreport');
Route::post('consult-dr-report', 'App\Http\Controllers\Api\SuperAdminController@consultdrreport');
Route::post('cghs_pt_report', 'App\Http\Controllers\Api\SuperAdminController@cghsptreport');
Route::post('new-branch-wise-lab-report', 'App\Http\Controllers\Api\SuperAdminController@newbranchwiselabreport');

Route::post('flyer-report', 'App\Http\Controllers\Api\SuperAdminController@flyerRecordReport');
Route::post('heavy_water_report', 'App\Http\Controllers\Api\SuperAdminController@HeavyWaterReport');

Route::post('cash-expenses', 'App\Http\Controllers\Api\CashExpenseController@index');
Route::post('cash-expenses/store', 'App\Http\Controllers\Api\CashExpenseController@store');
// Route::post('cash-expenses/show', 'App\Http\Controllers\Api\CashExpenseController@show');
Route::post('cash-expenses/update', 'App\Http\Controllers\Api\CashExpenseController@update');
Route::post('cash-expenses/destroy', 'App\Http\Controllers\Api\CashExpenseController@destroy');

Route::post('cash-expenses/voucher', 'App\Http\Controllers\Api\CashExpenseController@CashExpensesVoucher');

Route::post('cash-collection', 'App\Http\Controllers\Api\CashCollectionController@index');
Route::post('cash-collection/store', 'App\Http\Controllers\Api\CashCollectionController@store');
// Route::post('cash-expenses/show', 'App\Http\Controllers\Api\CashCollectionController@show');
Route::post('cash-collection/update', 'App\Http\Controllers\Api\CashCollectionController@update');
Route::post('cash-collection/destroy', 'App\Http\Controllers\Api\CashCollectionController@destroy');

Route::post('reset-opening', 'App\Http\Controllers\Api\CashCollectionController@resetOpening');

Route::post('cash_collection_report', 'App\Http\Controllers\Api\CashCollectionController@CashCollectionReport');
Route::post('cash_ledger', 'App\Http\Controllers\Api\CashCollectionController@CashLedger');

Route::post('cash_list', 'App\Http\Controllers\Api\CashCollectionController@CashList');

