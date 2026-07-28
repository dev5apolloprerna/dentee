<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointments extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'appointment_id';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'patient_id',
        'doctor_id',
		'clinic_id',
		'branch_id',
		'treatment_id',
		'suggested_treatment_id',
		'status',
		'notes',
		'appointment_date',
		'appointment_time',
		'patient_reminder',
		'doctor_reminder',
		'duration',
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
	
	public function allAppointments() {
        $appointments = $this::all();
		return $appointments;
	}
}
