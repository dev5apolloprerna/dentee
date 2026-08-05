<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CghsTypeMaster extends Model
{
    protected $table = 'cghs_type_masters';

    protected $fillable = [
        'strCghsName',
    ];

    public function treatments()
    {
        return $this->hasMany(
            CghsTreatment::class,
            'cghs_type',
            'id'
        );
    }

    public function invoices()
    {
        return $this->hasMany(
            CghsPatientInvoice::class,
            'cghs_type',
            'id'
        );
    }
}