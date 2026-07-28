<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialMaster;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class MaterialMasterController extends Controller
{
    public function addMaterial(Request $request){
			
			if(Auth::user()){
				/* $request->validate([
				'branch_name' => 'required|string|max:255|unique:branches,branch_name,NULL,id,deleted_at,NULL',
			],
			[
				'branch_name.required' => 'Branch name is required',
			]); */
			
				$existProduct = MaterialMaster::where(['product_name'=>trim($request->product_name),'deleted_at' => NULL,'branch_id' => $request->branch_id])->first();
				
				if(!empty($existProduct)){
						
						return response()->json([
							'status' => 'fail',
							'message' => 'Product already exist.'
						]);
					
				}else{
					
					$addMaterial = MaterialMaster::create([
					'lab_id' => $request->lab_id,
					'clinic_id' => $request->clinic_id,
					'branch_id' => $request->branch_id,
					'treatment_id' => $request->treatment_id ?? 0,
					'product_name' => $request->product_name,
					'price' => $request->price
				]);
				}
				
				return response()->json([
					'status' => 'success',
					'message' => 'Product added successfully',
					'materialData' => $addMaterial
					
					]);
				
			}else{
				return response()->json([
									'status' => 'error',
									'message' => 'User is not Authorised.',
							], 401);
				}
		}
		
	
	//update material
		public function updateMaterial(Request $request,$id)
		{
				if(Auth::user()){
					
				$existProduct = MaterialMaster::where(['product_name' => trim($request->product_name),'branch_id' => $request->branch_id])->where('deleted_at', '=', NULL)->where('material_id', '<>', $id)->first();
				
					if(!empty($existProduct)){
							
							return response()->json([
								'status' => 'fail',
								'message' => 'Product already exist.'
							]);
						
					}else{
					
					$materialMaster = MaterialMaster::find($id);
					$materialMaster->update($request->all());
					
					return response()->json(['status' => 'success','message' => 'Product Name Updated Successfully.','product' => $materialMaster,]);
					}
					
				}else{
					return response()->json([
							'status' => 'error',
							'message' => 'User is not Authorised.',
						], 401);
				}

		}
		
		public function allMaterial(Request $request){
				if(Auth::user()){
			        $branch_id = $request->branch_id;
    				$materialmaster = MaterialMaster::select(
    				'material_master.material_id',
    				'labs.lab_name as lab_name',
    				'material_master.lab_id',
    				'material_master.treatment_id',
    				'material_master.clinic_id',
    				//'treatments.name as treatment_name',
    				'material_master.product_name',
    				'material_master.price',
    				'material_master.branch_id'
    				)
    				->where(['material_master.clinic_id' =>$request->clinic_id])
    				// ->where(function ($query) use ($branch_id) {
        //                 $query->where('material_master.branch_id', '=', $branch_id)
        //                       ->orWhere('material_master.branch_id', '=', 0);
        //             })
    				->where('material_master.branch_id', '=', $branch_id)
    				->join('labs', 'material_master.lab_id', '=', 'labs.lab_id')
    				//->join('treatments', 'material_master.treatment_id', '=', 'treatments.treatment_id')
    				->get();
    				
    				//$queries = \DB::getQueryLog();
    					//dd($queries);
    				return response()->json([
								'status' => 'success',
								'materialmaster' => $materialmaster
							]);
				
				}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
		
		//destroy Note
		public function destroyMaterial($id)
		{
		   if(Auth::user()){

			 $data = MaterialMaster::where('material_id',$id)->count();
				if($data){
					$MaterialDelete = MaterialMaster::find($id);
					$MaterialDelete->delete();
					
					return response()->json([
						'status' => 'success',
						'message' => 'Product deleted Successfully.',]);
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
		
		public function allproductlistforLabwork(Request $request){
			
				if(Auth::user()){
				$materialmaster = MaterialMaster::select(
    				'material_id',
    				'product_name',
    				'price'
				)
				->where(['clinic_id' =>$request->clinic_id,'lab_id' =>$request->lab_id])
				->when($request->branch_id, fn ($query, $branch_id) => $query
                    ->where('branch_id', '=', $branch_id))
				//'treatment_id' =>$request->treatment_id, "branch_id" => $request->branch_id
				->get();
				
				//$queries = \DB::getQueryLog();
					//dd($queries);
				return response()->json([
								'status' => 'success',
								'productList' => $materialmaster
							]);
				
				}else{
				return response()->json([
						'status' => 'error',
						'message' => 'User is not Authorised.',
					], 401);
			}
		
		}
}
