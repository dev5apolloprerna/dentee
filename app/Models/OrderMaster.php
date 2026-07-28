<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMaster extends Model
{
    use HasFactory,SoftDeletes;
	protected $primaryKey = 'order_master_id';
	protected $table = 'order_master';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'bill_no',
		'clinic_id',
        'branch_id',
		'patient_id',
		'is_paid',
		'net_amount',
		'discount',
		'paid_amount',
		'due_amount',
		'payment_sms',
		'istatus',
		'adjusted_amount',
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
