<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MaterialMaster extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'material_id';
	protected $table = 'material_master';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'material_id',
		'lab_id',
		'branch_id',
		'clinic_id',
		'treatment_id',
		'product_name',
		'price',
		'istatus'
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
