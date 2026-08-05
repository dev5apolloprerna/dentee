<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CghsPatientInvoice extends Model
{
    use SoftDeletes;

    protected $table = 'cghs_patient_invoices';

    protected $fillable = [
        'clinic_id',
        'branch_id',
        'patient_id',
        'cghs_date',
        'amount',
        'discount_amount',
        'total_amount',
        'cghs_type',
        'patient_name',
        'iEnterBy',
        'strCghsGUID',
        'isCghsSubmit',
        'isCghsSubmitBy',
        'isSharedWithAdmin',
    ];

    protected $casts = [
        'cghs_date' => 'date',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(
            CghsPatientInvoiceDetail::class,
            'iCghsPatientInvoiceId',
            'id'
        );
    }

    public function cghsType()
    {
        return $this->belongsTo(
            CghsTypeMaster::class,
            'cghs_type',
            'id'
        );
    }
}