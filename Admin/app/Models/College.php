<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class College extends Model
{

    protected $table = 'colleges';

    public $timestamps = false;

    protected $fillable = ['college_name'];


    public function students()
    {
        return $this->hasMany(StudentInfo::class, 'college_id');
    }
    
    public function programs()
    {
        return $this->belongsToMany('\App\Models\Program', 'college_program', 'college_id', 'program_id');
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
