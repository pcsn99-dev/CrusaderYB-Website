<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionIntro extends Model
{
    protected $table = 'subscription_intro';  // Table name
    protected $fillable = ['description', 'hashtag'];  

    public $timestamps = true;
}
