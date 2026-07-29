<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'color',
        'github_repo',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Users who are members of this project (via pivot).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Check if a user can access this project (owner or member).
     */
    public function isAccessibleBy(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->members()->where('user_id', $user->id)->exists();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function snippets()
    {
        return $this->hasMany(Snippet::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }
}
