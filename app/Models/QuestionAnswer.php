<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswer extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'id';
	protected $table = 'questionanswer';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'id',
		'iDoctorId',
		'iSupervisorId',
		'iPatientId',
		'iClinicId',
		'iBranchId',
		'strQuestion',
		'strAnswer',
		'iEntryBy',
		'strEntryDate',
		'strIP',
		'created_at',
		'updated_at',
		'deleted_at',
		'strReplyDatetime'
    ];
	
	 /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
		'created_at' => 'datetime:Y-m-d H:i:s',
		'updated_at' => 'datetime:Y-m-d H:i:s',
		'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];
}
