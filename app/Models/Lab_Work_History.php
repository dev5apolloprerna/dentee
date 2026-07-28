<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Lab_Work_History extends Model
{
     use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'labwork_history_id';
	protected $table = 'lab_work_history';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'labwork_master_id',
		'lab_id',
		'title',
		'description'
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
