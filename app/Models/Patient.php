<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
     use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'patient_id';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'clinic_id',
		'branch_id',
		'doctor_id',
		'group_id',
		'case_no',
		'name_prefix',
		'name',
		'email',
		'mobile_no',
		'date_of_birth',
		'address',
		'gender',
		'occumpation',
		'language',
		'note',
		'isImportant'
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
	
	public function allPatient() {
        $patient = $this::all();
		return $patient;
	}
}
