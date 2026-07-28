<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Lab_Work extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'labwork_master_id';
	protected $table = 'lab_work';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'labwork_master_id',
		'clinic_id',
		'branch_id',
		'patient_id',
		'lab_id',
		'doctor_id',
		'material_id',
		'material_price',
		'lab_price',
		'teeth_change',
		'shade',
		'notes',
		'file_name',
		'istatus',
		'iLabWorkStatus'
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
