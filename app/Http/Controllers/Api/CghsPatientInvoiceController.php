<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CghsPatientInvoice;
use App\Models\CghsPatientInvoiceDetail;
use App\Models\CghsTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CghsPatientInvoiceController extends Controller
{
    public function addCghsPatientInvoice(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'clinic_id' => 'required',
            'branch_id' => 'required',
            'patient_id' => 'required',
            'patient_name' => 'required|string|max:255',
            'cghs_date' => 'nullable|date',
            'cghs_type' => 'nullable',
            // 'strCghsGUID' => 'nullable|string|max:255',
        ]);

        $invoice = CghsPatientInvoice::create([
            'clinic_id' => $request->clinic_id,
            'branch_id' => $request->branch_id,
            'patient_id' => $request->patient_id,
            'patient_name' => $request->patient_name,
            'cghs_date' => $request->cghs_date ? date('Y-m-d', strtotime($request->cghs_date)) : date('Y-m-d'),
            'amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'cghs_type' => $request->cghs_type,
            'strCghsGUID' => (string) Str::uuid(),
            'isCghsSubmit' => 0,
            'isCghsSubmitBy' => 0,
            'isSharedWithAdmin' => $request->isSharedWithAdmin ?? 0,
            'iEnterBy' => $user->user_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice created successfully.',
            // 'invoice' => $invoice->load(['patient', 'details.treatment', 'cghsType']),
            'invoice' => $this->loadInvoiceForResponse($invoice),
        ]);
    }

    public function updateCghsPatientInvoice(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'id' => 'required',
            'clinic_id' => 'required',
            'branch_id' => 'required',
            'patient_id' => 'required',
            'patient_name' => 'required|string|max:255',
            // 'cghs_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'cghs_type' => 'nullable',
            // 'strCghsGUID' => 'nullable|string|max:255',
        ]);

        $invoice = CghsPatientInvoice::find($request->id);

        if (!$invoice) {
            return $this->notFoundResponse('CGHS patient invoice not found.');
        }

        $invoice->fill($request->only([
            'clinic_id',
            'branch_id',
            'patient_id',
            'patient_name',
            // 'cghs_date',
            'discount_amount',
            'cghs_type',
            // 'strCghsGUID',
            'isSharedWithAdmin',
        ]));
        $invoice->save();

        $this->refreshInvoiceTotals($invoice);

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice updated successfully.',
            //'invoice' => $invoice->fresh()->load(['patient', 'details.treatment', 'cghsType']),
            'invoice' => $this->loadInvoiceForResponse($invoice),
        ]);
    }

    public function allCghsPatientInvoice(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        //$query = CghsPatientInvoice::with(['patient', 'details.treatment', 'cghsType']);
        $query = CghsPatientInvoice::with($this->invoiceRelations());

        $id = $this->filledRequestValue($request, 'id');
        if ($id !== null) {
            $query->where('id', $id);
        }

        $clinicId = $this->filledRequestValue($request, 'clinic_id');
        if ($clinicId !== null) {
            $query->where('clinic_id', $clinicId);
        }

        $branchId = $this->filledRequestValue($request, 'branch_id');
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $patientId = $this->filledRequestValue($request, 'patient_id');
        if ($patientId !== null) {
            $query->where('patient_id', $patientId);
        }

        $isCghsSubmit = $this->filledRequestValue($request, 'isCghsSubmit');
        if ($isCghsSubmit !== null) {
            $query->where('isCghsSubmit', $isCghsSubmit);
        }

        $search = $this->filledRequestValue($request, 'search');
        if ($search !== null) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('patient_name', 'like', '%' . $search . '%')
                    ->orWhere('strCghsGUID', 'like', '%' . $search . '%');
            });
        }

        $invoices = $query->orderByDesc('id')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice list fetched successfully.',
            'invoices' => $invoices,
        ]);
    }

    public function destroyCghsPatientInvoice(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $invoice = CghsPatientInvoice::find($request->id);

        if (!$invoice) {
            return $this->notFoundResponse('CGHS patient invoice not found.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->details()->delete();
            $invoice->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice deleted successfully.',
        ]);
    }

    public function addCghsPatientInvoiceDetail(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'iCghsPatientInvoiceId' => 'required',
            'cghs_treatment_id' => 'required',
            'cghs_treatment_name' => 'nullable|string|max:255',
            'iQty' => 'required|numeric|min:0',
            'iAmount' => 'required|numeric|min:0',
        ]);

        $invoice = CghsPatientInvoice::find($request->iCghsPatientInvoiceId);

        if (!$invoice) {
            return $this->notFoundResponse('CGHS patient invoice not found.');
        }

        $detail = DB::transaction(function () use ($request, $user, $invoice) {
            $treatment = CghsTreatment::find($request->cghs_treatment_id);

            $detail = CghsPatientInvoiceDetail::create([
                'iCghsPatientInvoiceId' => $invoice->id,
                'cghs_treatment_id' => $request->cghs_treatment_id,
                'cghs_treatment_name' => $request->cghs_treatment_name ?? optional($treatment)->cghs_treatment_name,
                'iQty' => $request->iQty,
                'iAmount' => $request->iAmount,
                'iEnterBy' => $user->user_id,
                'iUpdatedBy' => $user->user_id,
            ]);

            $this->refreshInvoiceTotals($invoice);

            return $detail;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice detail created successfully.',
            'detail' => $detail->load('treatment'),
            //'invoice' => $invoice->fresh()->load(['patient', 'details.treatment', 'cghsType']),
            'invoice' => $this->loadInvoiceForResponse($invoice),
        ]);
    }

    public function cghsPatientInvoiceDetailList(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'iCghsPatientInvoiceId' => 'required',
        ]);

        $invoice = CghsPatientInvoice::find($request->iCghsPatientInvoiceId);

        if (!$invoice) {
            return $this->notFoundResponse('CGHS patient invoice not found.');
        }

        $details = CghsPatientInvoiceDetail::with('treatment')
            ->where('iCghsPatientInvoiceId', $invoice->iCghsPatientInvoiceId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice detail list fetched successfully.',
            'details' => $details,
        ]);
    }

    public function destroyCghsPatientInvoiceDetail(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorisedResponse();
        }

        $detail = CghsPatientInvoiceDetail::find($request->id);

        if (!$detail) {
            return $this->notFoundResponse('CGHS patient invoice detail not found.');
        }

        $invoice = $detail->invoice;

        DB::transaction(function () use ($detail, $invoice) {
            $detail->delete();

            if ($invoice) {
                $this->refreshInvoiceTotals($invoice);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice detail deleted successfully.',
            //'invoice' => $invoice ? $invoice->fresh()->load(['patient', 'details.treatment', 'cghsType']) : null,
            'invoice' => $invoice ? $this->loadInvoiceForResponse($invoice) : null,
        ]);
    }

    public function submitCghsPatientInvoice(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->unauthorisedResponse();
        }

        $request->validate([
            'id' => 'required',
            'amount' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $invoice = CghsPatientInvoice::find($request->id);

        if (!$invoice) {
            return $this->notFoundResponse('CGHS patient invoice not found.');
        }

        $invoice->update([
            'amount' => $request->amount,
            'discount_amount' => $request->discount_amount,
            'total_amount' => $request->total_amount,
            'isCghsSubmit' => 1,
            'isCghsSubmitBy' => $user->user_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'CGHS patient invoice submitted successfully.',
            //'invoice' => $invoice->fresh()->load(['patient', 'details.treatment', 'cghsType']),
            'invoice' => $this->loadInvoiceForResponse($invoice),
        ]);
    }

    private function invoiceRelations()
    {
        return [
            'patient',
            'details' => function ($query) {
                $query->orderBy('id');
            },
            'details.treatment',
            'cghsType',
        ];
    }

    private function loadInvoiceForResponse(CghsPatientInvoice $invoice)
    {
        return CghsPatientInvoice::with($this->invoiceRelations())->find($invoice->id);
    }

    private function filledRequestValue(Request $request, $key)
    {
        if (!$request->has($key)) {
            return null;
        }
        $value = $request->input($key);
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value === '' ? null : $value;
    }

    private function refreshInvoiceTotals(CghsPatientInvoice $invoice)
    {
        $invoice->refresh();

        $amount = (float) CghsPatientInvoiceDetail::where('iCghsPatientInvoiceId', $invoice->id)
            ->selectRaw('COALESCE(SUM(iQty * iAmount), 0) as invoice_amount')
            ->value('invoice_amount');

        $discountAmount = (float) ($invoice->discount_amount ?? 0);

        $invoice->forceFill([
            'amount' => $amount,
            'total_amount' => max(0, $amount - $discountAmount),
        ])->save();
    }

    private function unauthorisedResponse()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'User is not Authorised.',
        ], 401);
    }

    private function notFoundResponse($message)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 404);
    }
}