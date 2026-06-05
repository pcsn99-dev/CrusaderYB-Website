<?php

namespace App\Models;

use Carbon\Carbon;
// use Backpack\CRUD\CrudTrait;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pictorial extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'pictorials';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['year', 'college_id', 'date', 'start_time', 'end_time', 'no_of_slots', 'is_hidden'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getRemainingSlots()
    {
        $reservations = \App\Models\Reservation::where('pictorial_id', $this->id)
            ->where('is_reschedule', 0)
            ->count();

        return $this->no_of_slots - $reservations;
    }
    
    public function getPictorialDate()
    {
        return Carbon::parse($this->date)->format('M d, Y');
    }

    public function getPictorialTime()
    {
        return Carbon::parse($this->start_time)->format('h:i A').' - '.Carbon::parse($this->end_time)->format('h:i A');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'pictorial_id');
    }

    public function colleges()
    {
        return $this->belongsToMany('App\Models\College', 'college_id');
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
