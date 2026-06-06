<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;

class Penalty extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'penalties';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['student_info_id', 'penalty_type', 'reservation_id', 'amount', 'is_paid', 'status', 'receipt_id'];
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

    public function getReservationLink()
    {
        $url = backpack_url("reservation/".$this->reservation->id);
        return '<a href="'.$url.'" target="_blank">View Details</a>';
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

    public function reservation()
    {
        return $this->belongsTo('\App\Models\Reservation');
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
