<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
// use Backpack\CRUD\CrudTrait;

class Concern extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'concerns';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['date', 'concern', 'name', 'email', 'fb_account', 'contact_number', 'status'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getStatus()
    {
        if($this->status == 'seen') {
            return ucfirst($this->status).' ('.\Carbon\Carbon::parse($this->seen_date)->format('M j, Y').')';
        } else if($this->status == 'replied') {
            return ucfirst($this->status).' ('.\Carbon\Carbon::parse($this->replied_date)->format('M j, Y').')';
        } else {
            return ucfirst($this->status);
        }
    }

    public function formatDateCreated()
    {
        return Carbon::parse($this->date)->format('M d, Y');
    }

    public function formatDateSeen()
    {
        return Carbon::parse($this->seen_date)->format('M d, Y');
    }

    public function formatDateReplied()
    {
        return Carbon::parse($this->replied_date)->format('M d, Y');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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
