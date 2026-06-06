<?php

namespace App\Models;

// use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Writeup extends Model
{
    // use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'writeups';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['student_info_id', 'writeup', 'edited_writeup', 'proofreader_id', 'is_done', 'date_of_proofread'];
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

    public function getUniversityId(){
        return $this->student_info->university_id;
    }

    public function getStudentyear(){
        return $this->student_info->year;
    }

    public function getProofreaderFullname()
    {
        if($this->proofreader_id) {
            return (isset($this->user->name))? ucwords($this->user->name) : "";
        }
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

    public function user()
    {
        return $this->belongsTo('\App\User', 'proofreader_id');
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
