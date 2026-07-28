<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Authenticatable
{
   use HasFactory,Notifiable;
	protected $primaryKey = 'doctor_id';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'doctor_id',
        'name',
		'clinic_id',
		'branch_id',
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
