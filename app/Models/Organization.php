<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'plan',
        'project_limit',
        'user_limit',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);

                $count = 1;
                while (static::where('slug', $organization->slug)->exists()) {
                    $organization->slug = Str::slug($organization->name) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    public function canAddProject()
    {
        return $this->projects()->count() < $this->project_limit;
    }

    public function canAddUser()
    {
        return $this->users()->count() < $this->user_limit;
    }

    public function isOnTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getPlanLimits()
    {
        return [
            'free' => ['projects' => 3, 'users' => 5],
            'pro' => ['projects' => 50, 'users' => 20],
            'enterprise' => ['projects' => -1, 'users' => -1], // unlimited
        ][$this->plan] ?? ['projects' => 3, 'users' => 5];
    }
}
