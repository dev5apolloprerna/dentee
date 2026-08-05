<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CghsTreatment;
use App\Models\CghsTypeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CghsTreatmentController extends Controller
{
    public function addCghsTreatment(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'clinic_id' => 'required',
            'branch_id' => 'required',
            'cghs_treatment_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cghs_treatments', 'cghs_treatment_name')
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('deleted_at'),
            ],
            'amount' => 'required|numeric|min:0',
            'code' => 'nullable|string|max:255',
        ]);

        $cghsTreatment = CghsTreatment::create($request->only([
            'clinic_id',
            'branch_id',
            'cghs_treatment_name',
            'amount',
            'code',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS Treatment created successfully.',
            'cghs_treatment' => $cghsTreatment,
        ]);
    }

    public function updateCghsTreatment(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $cghsTreatment = CghsTreatment::find($request->id);

        if (!$cghsTreatment) {
            return response()->json([
                'status' => 'error',
                'message' => 'CGHS Treatment not found.',
            ], 404);
        }

        $request->validate([
            'clinic_id' => 'required',
            'branch_id' => 'required',
            'cghs_treatment_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cghs_treatments', 'cghs_treatment_name')
                    ->ignore($request->id, 'cghs_treatment_id')
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('deleted_at'),
            ],
            'amount' => 'required|numeric|min:0',
            'code' => 'nullable|string|max:255',
        ]);

        $cghsTreatment->update($request->only([
            'clinic_id',
            'branch_id',
            'cghs_treatment_name',
            'amount',
            'code',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS Treatment updated successfully.',
            'cghs_treatment' => $cghsTreatment,
        ]);
    }

    public function allCghsTreatment(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $query = CghsTreatment::query();

        if ($request->filled('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('cghs_treatment_name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $cghsTreatments = $query->orderBy('cghs_treatment_name')->get();
        $cghsTypeMaster = CghsTypeMaster::orderBy('strCghsName')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS Treatment list fetched successfully.',
            'cghs_treatments' => $cghsTreatments,
            'cghs_type_master' => $cghsTypeMaster,
        ]);
    }

    public function destroyCghsTreatment(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $cghsTreatment = CghsTreatment::find($request->id);

        if (!$cghsTreatment) {
            return response()->json([
                'status' => 'error',
                'message' => 'CGHS Treatment not found.',
            ], 404);
        }

        $cghsTreatment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS Treatment deleted successfully.',
        ]);
    }

    private function unauthorisedResponse()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'User is not Authorised.',
        ], 401);
    }
}