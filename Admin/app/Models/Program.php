<?php

namespace App\Models;

use App\Models\StudentInfo;
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

    public $timestamps = false;

    protected $fillable = [
        'program_name',
        'college_id',
        'is_graduate_program'
    ];



    public function students()
    {
        return $this->hasMany(StudentInfo::class, 'program_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }


}
