<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;

class AccountSubscription extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'account_subscription';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['account_subs_id', 'student_info_id', 'subs_id', 'status', 'receipt_id'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getFullname()
    {
        return $this->student_info->first_name.' '.$this->student_info->middle_name.' '.$this->student_info->last_name.' '.$this->student_info->suffix;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function student_info()
    {
        return $this->belongsTo('\App\Models\Studentinfo', 'student_info_id');
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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
