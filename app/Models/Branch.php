<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;


class Branch extends Authenticatable
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'branch_id';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'clinic_id',
        'branch_name',
		'state',
		'city',
		'zipcode',
		'address',
		'mobile_no',
		'last_case_no',
		'istatus',
		'strAddressLink'
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
	
	public function allBranch() {
        $branch = $this::all();
		return $branch;
	}

}
