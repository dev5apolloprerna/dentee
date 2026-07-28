<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;


class RoleController extends Controller
{
    public function addRole(Request $request){
		
		if(Auth::user()){
			$request->validate([
				'role_type' => 'required|string|max:255|unique:roles,role_type,NULL,id,deleted_at,NULL',
				'istatus' => 'required|numeric|max:255',
			],
			[
				'role_type.required' => 'Role type is required',
				//'istatus.required' => 'User name is required',
			]);

			$role = Role::create([
				'role_type' => $request->role_type,
				'istatus' => $request->istatus,
			]);
			
			return response()->json([
				'status' => 'success',
				'message' => 'Role created successfully',
				'role' => $role
			]);
		}else{
			return response()->json([
								'status' => 'error',
								'message' => 'User is not Authorised.',
						], 401);
			}
	}
	
		//destroy user
	public function destroyRole($id)
    {
       if(Auth::user()){
		$role = new Role();

        $data = Role::where('role_id',$id)->count();

				//if($data == 0){
			if($data){
				$role->deleteRole($id);
				//}
				return response()->json([
					'status' => 'success',
					'message' => 'Role deleted Successfully.',]);
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
