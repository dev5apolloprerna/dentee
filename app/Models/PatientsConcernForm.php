<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientsConcernForm extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'iPatientId',
        'iConcernFormId',
        'strFileName',
        'clinic_id',
        'branch_id',
        'isSubmit',
        'submitedDateTime',
        'strIP'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
