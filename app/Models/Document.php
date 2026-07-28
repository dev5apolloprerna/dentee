<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'document_id';
	protected $table = 'document';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'document_id',
		'document_name',
		'clinic_id',
		'branch_id',
		'patient_id',
		'image',
		'uploaded_date',
		'image_type',
		'image_size',
		'istatus',
		'deleted_at',
		'created_at',
		'updated_at'
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
