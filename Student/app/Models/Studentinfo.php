<?php

namespace App\Models;

// use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studentinfo extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student_info';
    protected $fillable = [
        'user_id', 
        'year', 
        'university_id', 
        'first_name', 
        'middle_name', 
        'last_name', 
        'suffix', 
        'contact_number', 
        'current_address', 
        'permanent_address', 
        'expected_graduation_date', 
        'college_id', 
        'program_id', 
        'major_id',
        'is_agree_contract', 
        'is_subscribed', 
        'subscribe_date', 
        'orgs', 
        'is_yb_paid', 
        'yb_preview', 
        'claim_yb', 
        'claim_yb_date', 
        'claim_pic', 
        'claim_pic_date', 
        'subscribe_date', 
        'unsubscribe_date',
        'free_same_day_resched'];


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getFullname()
    {
        return $this->last_name . ', '. $this->first_name.' '.$this->middle_name.' '.$this->suffix;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    
    public function user()
    {
        return $this->belongsTo('\App\User');
    }

    public function college()
    {
        return $this->belongsTo('\App\Models\College');
    }

    public function program()
    {
        return $this->belongsTo('\App\Models\Program');
    }

    public function major()
    {
        return $this->belongsTo('\App\Models\Major');
    }

    public function penalties()
    {
        return $this->hasMany('\App\Models\Penalty', 'student_info_id');
    }

    public function reservation()
    {
        return $this->hasOne('\App\Models\Reservation', 'student_info_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getEmailAttribute() {
        if ($this->user) {
            return $this->user->email;
        } else {
            return null;
        }
    }

    public function getFullnameAttribute() {
        return $this->last_name . ', '. $this->first_name;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    
  



}
