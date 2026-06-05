<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;
use Carbon\Carbon;

class Reservation extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'reservations';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['pictorial_id', 'student_info_id', 'is_present', 'is_reschedule', 'is_present_date', 'reschedule_date', 'created_at', 'updated_at'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getPictorialSchedule()
    {
        if (!$this->pictorial)
            return null;
            
        return Carbon::parse($this->pictorial->date)->format('M j, Y').' '.Carbon::parse($this->pictorial->start_time)->format('h:i A').'-'.Carbon::parse($this->pictorial->end_time)->format('h:i A');
    }

    public function getFullname()
    {
        return $this->student_info->first_name.' '.$this->student_info->middle_name.' '.$this->student_info->last_name.' '.$this->student_info->suffix;
    }

    public function getReservationStatus()
    {
        if (!is_null($this->is_present)) {
            if ($this->is_present) {
                return '<label class="label label-success">Present</label>';
            } else {
                return '<label class="label label-danger">Absent</label>';
            }
        } else {
            return "";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function pictorial()
    {
        return $this->belongsTo('\App\Models\Pictorial');
    }

    public function student_info()
    {
        return $this->belongsTo('\App\Models\Studentinfo');
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
