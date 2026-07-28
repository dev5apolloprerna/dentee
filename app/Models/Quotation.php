<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
     use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'quotation_id';
	protected $table = 'quotation';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

		'quotation_id',
		'quotation_treatment_id',
		'quotation_name',
		'treatment_name',
        'treatment_id',
        'clinic_id',
        'branch_id',
		'patient_id',
		'SuggestedBydoctor_id',
		'treatmentBydoctor_id',
		'rate',
		'selected_teeth',
		'amount',
		'discount',
		'discount_type',
		'discount_amount',
		'total_amount',
		'selected_teeth_count',
		'treatment_status',
		'treatment_date',
		'strComments',
		'is_billing',
		'ref_id',
		'is_completed_by_doctorId',
		'completed_datetime',
		'strnote',
		'istatus',
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
