<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'plan',
        'status',
        'amount',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'stripe_subscription_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isActive()
    {
        return $this->status === 'active' &&
            $this->current_period_end &&
            $this->current_period_end->isFuture();
    }

    public function daysUntilRenewal()
    {
        if (!$this->current_period_end) return null;
        return now()->diffInDays($this->current_period_end);
    }
}
