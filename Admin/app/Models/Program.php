<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;

class Program extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'programs';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['program_name'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function colleges()
    {
        return $this->belongsToMany('App\Models\College', 'college_program', 'program_id', 'college_id');
    }

    public function majors()
    {
        return $this->belongsToMany('App\Models\Major', 'program_major', 'program_id', 'major_id');
    }

    public function getMajorsList($id, $major_id){

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
