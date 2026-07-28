<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
     public function addGroup(Request $request){
		
				if(Auth::user()){
					
					$existGroup = Group::where(['group_name' => $request->group_name,'branch_id' => $request->branch_id])->first();
					
					if(!empty($existGroup)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Group name already exist.'
							]);
						
					}else{
						
						$group = Group::create([
						'group_name' => $request->group_name,
						'clinic_id' => $request->clinic_id,
						'branch_id' => $request->branch_id,
					]);
					
						
						return response()->json([
						'status' => 'success',
						'message' => 'Group added successfully'
					]);
					}
					
				}else{
					return response()->json([
										'status' => 'error',
										'message' => 'User is not Authorised.',
								], 401);
					}
			}
			
	//update Medicine
		public function updateGroup(Request $request,$id)
		{
				if(Auth::user()){

					$existGroup = Group::where('group_name', '=', $request->group_name)->where('group_id', '<>', $id)->first();
				
					if(!empty($existGroup)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Group already exist.'
							]);
						
					}else{
					
					$group= Group::find($id);
					$group->update($request->all());
					//return $user;
					return response()->json(['status' => 'success','message' => 'Group updated successfully.','group' => $group]);
					}
					
					
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		//destroy frequncy
		public function destroyGroup($id)
		{
		   if(Auth::user()){

			 $groupdata = Group::where('group_id',$id)->count();
				if($groupdata){
					$groupDelete = Group::find($id);
					$groupDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Group deleted Successfully.',]);
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
		
		public function allGroup(Request $request){

			$clinic_id = $request->clinic_id;
			$branch_id = $request->branch_id;
			
			if(Auth::user()){
				$allGroup = Group::where(['clinic_id' =>$clinic_id,'branch_id' =>$branch_id])->get()->toArray();
				
				return $allGroup;
			}else{
				return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
			}
		}
}
