<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    protected $fillable = ['pictorial_id', 'student_info_id', 'is_present', 'is_reschedule', 'is_present_date', 'reschedule_date'];
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

        return Carbon::parse($this->pictorial->date)->format('M j, Y').' '.Carbon::parse($this->pictorial->start_time)->format('h:i A');
    }

    public function getFullname()
    {
        // return $this->first_name.' '.$this->student_info->middle_name.' '.$this->student_info->last_name.' '.$this->student_info->suffix;
        return $this->student_info->first_name . " ". $this->student_info->middle_name . " " . $this->student_info->last_name;
    }

    public function getReservationStatus()
    {
        if (!is_null($this->is_present)) {
            if ($this->is_present == 1) {
                // return '<label class="label label-success">Present</label>';
                return '<span class="badge badge-success ms-auto">Present</span>';
            } else if ($this->is_present == 0) {
                // return '<label class="label label-danger">Absent</label>';
                return '<span class="badge badge-danger ms-auto">Absent</span>';
            } else {
                return '<span class="badge badge-warning ms-auto">Late</span>';
            }
        } else {
            return '<span class="badge badge-secondary ms-auto">NA</span>';
        }
    }

    public function getTimestamp() {
        if (is_null($this->timestamp)) {
            return '<span class="badge badge-secondary ms-auto">NA</span>';
        } else {
            return '<span class="ms-auto">{{ $this->timestamp }}</span>';
        }
    }

    public function getReservationLink()
    {
        if(isset($this->id)){
            $url = url("reservation/".$this->id);
            return '<a class="btn btn-link text-info btn-sm px-3 mb-0" href="'.$url.'" target="_blank" data-bs-toggle="tooltip" data-bs-original-title="Click to view reservation details"><i class="fas fa-eye me-2" aria-hidden="true"></i>View Details</a>';
        }
    }

    public function ifReschedule(){
        if(isset($this->id)){
            if($this->reschedule_date != NULL && $this->is_reschedule == 1){
                return '<span class="badge badge-secondary ms-auto">(RESCHEDULED)</span>';
            }else{
                return '<span class="badge badge-success ms-auto">CURRENT RESERVATION SCHED.</span>';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function pictorial(): BelongsTo
    {
        return $this->belongsTo(Pictorial::class, 'pictorial_id');
    }

    public function student_info()
    {
        return $this->belongsTo('\App\Models\Studentinfo'); //, 'student_info_id', 'user_id'
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
