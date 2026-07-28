<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;
	protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'clinic_id',
		'branch_id',
        'user_name',
        'first_name',
		'last_name',
		'email',
		'password',
		'address',
		'mobile_no',
		'is_admin',
		'last_modify_by',
		'role_id',
		'date_of_birth'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
	public function sendWhatsappMessage($mobile,$key,$msg,$pdf) {
	   
	   if(!empty($pdf)){
		    //$data = "http://api.bulkcampaigns.com/api/wapi?json=true&apikey=".$key."&mobile=".$mobile."&msg=".urlencode($msg)."&pdf=".$pdf;
		    //$data ="https://newweb.technomantraa.com/api/send?number=".$mobile."&type=media&message=".urlencode($msg)."&media_url=".$pdf."&instance_id=65B0AA55DBFC6&access_token=65ae0fdc57bce";
		    //$data ="https://newweb.technomantraa.com/api/send?number=91".$mobile."&type=media&message=".urlencode($msg)."&media_url=".$pdf."&instance_id=65C48823AC1D6&access_token=65c486860588c";
		    
		    /*$data = "https://newweb.technomantraa.com/api/send?number=91".$mobile."&type=media&message=".urlencode($msg)."&media_url=".$pdf."&instance_id=690EDD8CCD25E&access_token=65c486860588c";*/
		    
	   }else{
		    //$data = "http://api.bulkcampaigns.com/api/wapi?json=true&apikey=".$key."&mobile=".$mobile."&msg=".urlencode($msg);
		    //$data = "https://newweb.technomantraa.com/api/send?number=".$mobile."&type=text&message=".urlencode($msg)."&instance_id=65B0AA55DBFC6&access_token=65ae0fdc57bce";
		    //$data = "https://newweb.technomantraa.com/api/send?number=91".$mobile."&type=text&message=".urlencode($msg)."&instance_id=65C48823AC1D6&access_token=65c486860588c";
		    
		    /*$data = "https://newweb.technomantraa.com/api/send?number=91".$mobile."&type=text&message=".urlencode($msg)."&instance_id=690EDD8CCD25E&access_token=65c486860588c";*/
	   }
	  
        /*$ret = file_get_contents($data);
		$result = json_decode($ret);
		return $result;*/
		return true;
	}

}