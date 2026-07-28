<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SuggestedTreatmentPayment extends Model
{
    use HasFactory,Notifiable;
	protected $primaryKey = 'suggested_payment_id';
	protected $table = 'suggested_treatment_payment';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

		'suggested_payment_id',
		'patient_id',
		'clinic_id',
		'branch_id',
		'order_id',
        'order_detail_id',
        'order_payment_detail_id',
        'suggested_treatments_id',
		'amount',
		'discount',
		'istatus'
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
