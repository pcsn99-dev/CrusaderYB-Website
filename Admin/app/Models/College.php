<?php

namespace App\Models;

use App\Models\StudentInfo;
use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;

class College extends Model
{
    // use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'colleges';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['college_name'];
    // protected $hidden = [];
    // protected $dates = [];

    public function programs()
    {
        return $this->hasMany(Program::class, 'college_id');
    }

    public function students()
    {
        return $this->hasMany(StudentInfo::class, 'college_id');
    }

    public function writeups()
    {
        return $this->hasManyThrough(
            Writeup::class,
            StudentInfo::class,
            'college_id',
            'student_info_id',
            'id',
            'id'
        );
    }


}
