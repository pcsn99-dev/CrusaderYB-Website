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

    public $timestamps = true;

    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getFullname()
    {
        return $this->last_name . ', '. $this->first_name.' '.$this->middle_name.' '.$this->suffix;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function college()
    {
        return $this->belongsTo('\App\Models\College');
    }

    public function program()
    {
        return $this->belongsTo('\App\Models\Program');
    }

    public function major()
    {
        return $this->belongsTo('\App\Models\Major');
    }

    public function penalties()
    {
        return $this->hasMany('\App\Models\Penalty', 'student_info_id');
    }

    public function reservation()
    {
        return $this->hasOne('\App\Models\Reservation', 'student_info_id');
    }

    public function account_subscription()
    {
        return $this->hasOne('\App\Models\AccountSubscription', 'student_info_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeSearch($query, $term)
    {
        $term = trim($term);
        if ($term === '') return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
            ->orWhere('middle_name', 'LIKE', "%{$term}%")
            ->orWhere('last_name', 'LIKE', "%{$term}%")
            ->orWhere('university_id', 'LIKE', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getEmailAttribute() {
        if ($this->user) {
            return $this->user->email;
        } else {
            return null;
        }
    }

    public function getVerification() {
        if ($this->user) {
            return $this->user->verified;
        } else {
            return 0;
        }
    }

    public function getFullnameAttribute() {
        return $this->last_name . ', '. $this->first_name;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    
}
