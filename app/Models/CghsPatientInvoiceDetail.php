<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CghsPatientInvoiceDetail extends Model
{
    use SoftDeletes;

    protected $table = 'cghs_patient_invoice_details';

    protected $fillable = [
        'iCghsPatientInvoiceId',
        'cghs_treatment_id',
        'cghs_treatment_name',
        'iQty',
        'iAmount',
        'iEnterBy',
        'iUpdatedBy',
    ];

    protected $casts = [
        'iAmount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(
            CghsPatientInvoice::class,
            'iCghsPatientInvoiceId',
            'id'
        );
    }

    public function treatment()
    {
        return $this->belongsTo(
            CghsTreatment::class,
            'cghs_treatment_id',
            'cghs_treatment_id'
        );
    }
}