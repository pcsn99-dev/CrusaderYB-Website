<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Backpack\CRUD\CrudTrait;

class AccountSubscription extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    use HasFactory;

    protected $table = 'account_subscription';
    protected $primaryKey = 'account_subs_id';
    public $timestamps = false;


    protected $fillable = [
        'student_info_id',
        'subs_id',
        'status',
        'receipt_id',
    ];

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

    public function receipts()
    {
        return $this->belongsTo('\App\Models\Receipts', 'receipt_id');
    }

    public function subscription()
    {
        return $this->belongsTo('\App\Models\SubscriptionPackage', 'subs_id');
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
