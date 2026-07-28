<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
//use Illuminate\Database\Eloquent\SoftDeletes;


class Prescription extends Model
{
    use HasFactory,Notifiable;
	protected $primaryKey = 'prescription_id';
	protected $table = 'prescription';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'prescription_id',
		'clinic_id',
		'branch_id',
		'patient_id',
		'doctor_id',
		'prescription_date',
		'template_id',
		'note',
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
