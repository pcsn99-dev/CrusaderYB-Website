<?php

namespace App\Models;

// use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studentinfo extends Model
{
    // use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student_info';

    protected $fillable = [
        'user_id',
        'slmis_id',
        'year',
        'university_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'contact_number',
        'current_address',
        'permanent_address',
        'expected_graduation_date',
        'college_id',
        'program_id',
        'major_id',
        'is_agree_contract',
        'is_subscribe',
        'subscribe_date',
        'unsubscribe_date',
        'orgs',
        'is_yb_paid',
        'yb_preview',
        'claim_yb',
        'claim_pic',
        'claim_yb_date',
        'claim_pic_date',
    ];


    protected $casts = [
        'slmis_id' => 'integer',
        'is_agree_contract' => 'integer',
        'is_subscribe' => 'boolean',
        'subscribe_date' => 'date',
        'unsubscribe_date' => 'date',
        'is_yb_paid' => 'integer',
        'claim_yb' => 'boolean',
        'claim_pic' => 'boolean',
        'claim_yb_date' => 'date',
        'claim_pic_date' => 'date',
        'free_same_day_resched' => 'boolean',
    ];

    public $timestamps = true;

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function writeup()
    {
        return $this->hasOne(Writeup::class, 'student_info_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
            $this->suffix,
        ])
            ->filter()
            ->implode(' ');
    }

    public function getFormattedFullNameAttribute(): string
    {
        $firstPart = trim(collect([
            $this->first_name,
            $this->middle_name,
        ])->filter()->implode(' '));

        $lastPart = trim(collect([
            $this->last_name,
            $this->suffix,
        ])->filter()->implode(' '));

        return trim($firstPart.' '.$lastPart);
    }




}
