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
        if(isset($this->reservation->id)){
            $url = url("reservation/".$this->reservation->id);
            return '<a class="btn btn-link text-info btn-sm px-3 mb-0" href="'.$url.'" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Click to view reservation details"><i class="fas fa-eye me-2" aria-hidden="true"></i>View Details</a>';
        }
        
        // return '<a href="'.$url.'" target="_blank">View Details</a>';
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

    public function receipts()
    {
        return $this->belongsTo('\App\Models\Receipts', 'receipt_id');
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
