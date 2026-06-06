<?php

namespace App\Models;

use App\Models\StudentInfo;
use Illuminate\Database\Eloquent\Model;
// use Backpack\CRUD\CrudTrait;

class Major extends Model
{


    protected $table = 'majors';

    public $timestamps = false;

    protected $fillable = [
        'program_id',
        'major_name',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function students()
    {
        return $this->hasMany(StudentInfo::class, 'major_id');
    }
}
