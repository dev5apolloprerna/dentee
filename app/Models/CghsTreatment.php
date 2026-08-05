<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CghsTreatment extends Model
{
    use SoftDeletes;

    protected $table = 'cghs_treatments';

    protected $primaryKey = 'cghs_treatment_id';

    protected $fillable = [
        'clinic_id',
        'branch_id',
        'cghs_treatment_name',
        'amount',
        'code',
        'cghs_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function type()
    {
        return $this->belongsTo(
            CghsTypeMaster::class,
            'cghs_type',
            'id'
        );
    }

    public function invoiceDetails()
    {
        return $this->hasMany(
            CghsPatientInvoiceDetail::class,
            'cghs_treatment_id',
            'cghs_treatment_id'
        );
    }
}