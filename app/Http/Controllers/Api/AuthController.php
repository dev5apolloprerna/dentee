<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserBranches;
use App\Models\UserDashboradRights;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile_no' => 'required|string',
            'password' => 'required|string',
        ]);
        // $user = User::where('mobile_no', $request->mobile_no)->first();
        // dd($user);
        // if (!$user || $user->istatus ==0) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unauthorized',
        //     ], 401);
        // }
        
        $credentials = $request->only('mobile_no', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
		//need to add clinic id in where condition if we have multiple clinic
		$user_id = $user->user_id;
		$branchList = UserBranches::select(
					'user_branches.branch_id',
					'branches.branch_name'
				)
            ->where(['user_branches.user_id' => $user_id, 'branches.clinic_id' => 1])
            ->join('branches', 'user_branches.branch_id', '=', 'branches.branch_id')
            ->get();
		if ($user->role_id != 1) {
            $UserDashboradRights = UserDashboradRights::where("userid", $user_id)->first();
        }
			//$queries = \DB::getQueryLog();
				//dd($queries);
			//echo "<pre>";
			$branchCount =  count($branchList);
		
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'branchList' => $branchList,
                'branchCount' => $branchCount,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
                "isAccessDashboard" => $user->role_id != 1 ? $UserDashboradRights->isAccessDashboard ?? 0 : 1,
                "isAccessPatients" => $user->role_id != 1 ? $UserDashboradRights->isAccessPatients ?? 0 : 1,
                "isAccessAppointment" => $user->role_id != 1 ? $UserDashboradRights->isAccessAppointment ?? 0 : 1,
                "isAccessSetting" => $user->role_id != 1 ? $UserDashboradRights->isAccessSetting ?? 0 : 1,
                "isAccessReport" => $user->role_id != 1 ? $UserDashboradRights->isAccessReport ?? 0 : 1,
                "isAccessLabwork" => $user->role_id != 1 ? $UserDashboradRights->isAccessLabwork ?? 0 : 1
            ]);

    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}