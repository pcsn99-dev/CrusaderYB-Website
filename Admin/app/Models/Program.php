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
        'college_id',
        'program_name',
        'is_graduate_program',
    ];

    protected $casts = [
        'is_graduate_program' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function majors()
    {
        return $this->hasMany(Major::class, 'program_id');
    }

    public function students()
    {
        return $this->hasMany(StudentInfo::class, 'program_id');
    }


}
