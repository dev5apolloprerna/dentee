<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchCaseNumber extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
	protected $primaryKey = 'branch_case_number_id';
	protected $table = 'branch_case_number';
	/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'clinic_id',
		'branch_id',
        'case_pre',
		'case_no',
		'case_suf',
		'bill_no'
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
	
	public function allBranchCaseNumber() {
        $branchCaseNumber = $this::all();
		return $branchCaseNumber;
	}
}
