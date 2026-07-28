<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
//use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionMedicine extends Model
{
    use HasFactory,Notifiable;
	protected $primaryKey = 'prescription_medicine_id';
	protected $table = 'prescription_medicine';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'prescription_medicine_id',
		'prescription_id',
		'medicine_id',
		'dosage',
		'frequency',
		'duration',
		'notes',
		'template_id',
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
