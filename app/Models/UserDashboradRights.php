<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDashboradRights extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = [
        'userid',
        'isAccessDashboard',
        'isAccessPatients',
        'isAccessAppointment',
        'isAccessSetting',
        'isAccessReport',
        'isAccessLabwork',
        'strIP'
    ];
}
