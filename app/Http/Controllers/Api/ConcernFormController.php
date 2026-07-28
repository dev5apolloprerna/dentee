<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ConcernForm;
use Illuminate\Support\Facades\DB;

class ConcernFormController extends Controller
{
    public function addConcernForm(Request $request){
		if(Auth::user()){
			$Concern_Form = ConcernForm::where(['strConcernFormTitle' => $request->strConcernFormTitle])->first();
			
			if(!empty($Concern_Form)){
    			return response()->json([
    				'status' => 'fail',
    				'message' => 'Concern Form Already Exist.'
    			]);
			}else{
				$ConcernForm = ConcernForm::create([
    				'strConcernFormTitle' => $request->strConcernFormTitle,
    				'clinic_id' => $request->clinic_id,
    				//'branch_id' => $request->branch_id,
    				'strConcernFormText' => $request->strConcernFormText,
    				'strIP' => $request->ip()
    			]);
    			if($ConcernForm){
    				return response()->json([
        				'status' => 'success',
        				'message' => 'Concern Form Added Successfully'
        			]);
    			} else{
    			    return response()->json([
        				'status' => 'error',
        				'message' => 'Something Went Wrong!'
        			]);
    			}
			}
		}else{
		    return response()->json([
    			'status' => 'error',
    			'message' => 'User is not Authorised.',
			], 401);
		}
	}
			
	//update Medicine
	public function updateConcernForm(Request $request)
	{
		if(Auth::user()){
			$existConcernForm = ConcernForm::where('strConcernFormTitle', '=', $request->strConcernFormTitle)->where('iConcernFormId', '<>', $request->iConcernFormId)->first();
			if(!empty($existConcernForm)){
				return response()->json([
					'status' => 'fail',
					'message' => 'Concern Form Already Exist.'
				]);
			}else{
    			$concernForm= ConcernForm::find($request->iConcernFormId);
    			$concernForm->update($request->all());
    			//return $user;
    			return response()->json(['status' => 'success','message' => 'Concern Form Updated Successfully.','concernForm' => $concernForm]);
			}
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
		
	//destroy frequncy
	public function destroyConcernForm(Request $request)
	{
	   if(Auth::user()){
    		$concernformdata = ConcernForm::where('iConcernFormId',$request->iConcernFormId)->count();
			if($concernformdata){
				$concernformDelete = ConcernForm::find($request->iConcernFormId);
				$concernformDelete->delete();
				return response()->json([
					'status' => 'success',
					'message' => 'Concern Form Deleted Successfully.',]);
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
	
	public function allConcernForm(Request $request){
		$clinic_id = $request->clinic_id;
		//$branch_id = $request->branch_id;
		if(Auth::user()){
			$allConcernForm = ConcernForm::where(['clinic_id' =>$clinic_id])->get();
			$arr = [];
			foreach($allConcernForm as $ConcernForm){
			    $arr[] = array(
		            "iConcernFormId" => $ConcernForm->iConcernFormId,
                    "clinic_id" => $ConcernForm->clinic_id,
                    "branch_id" => $ConcernForm->branch_id,
                    "strConcernFormTitle" => $ConcernForm->strConcernFormTitle,
                    "strConcernFormText" => $ConcernForm->strConcernFormText,
                    "deleted_at" => $ConcernForm->deleted_at,
                    "created_at" => $ConcernForm->created_at,
                    "updated_at" => $ConcernForm->updated_at,
                    "strIP" => $ConcernForm->strIP
		        );
			}
			return response()->json([
    			'status' => 'success',
    			'message' => 'List All Concern Form',
    			'allConcernForm' =>$arr
    		]);
			//return $allConcernForm;
		}else{
			return response()->json([
				'status' => 'error',
				'message' => 'User is not Authorised.',
			], 401);
		}
	}
		
}
