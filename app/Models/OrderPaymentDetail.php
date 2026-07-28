<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPaymentDetail extends Model
{
    use HasFactory,SoftDeletes;
	protected $primaryKey = 'order_payment_detail_id';
	protected $table = 'order_payment_detail';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
		'patient_id',
		'branch_id',
		'clinic_id',
		'amount',
		'payment_mode',
		'comment',
		'payment_date'
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
