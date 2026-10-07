<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $table = 'years';

    protected $fillable = [
        'year',
        'theme',
        'status',
        'subscription_start',
        'subscription_end',
    ];

    protected $casts = [
        'status' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status;
    }

    public function isSubscriptionOpen(): bool
    {
        if (!$this->status) {
            return false;
        }

        $today = today();

        if (
            $this->subscription_start &&
            $today->lt($this->subscription_start)
        ) {
            return false;
        }

        if (
            $this->subscription_end &&
            $today->gt($this->subscription_end)
        ) {
            return false;
        }

        return true;
    }
}