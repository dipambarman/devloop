<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'bio', 'skills', 'github_url', 'portfolio_url', 'linkedin_url'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array',
        ];
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Projects the user has been invited to as a member.
     */
    public function memberProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * All projects accessible to the user (owned + member).
     */
    public function allProjects()
    {
        $ownedIds = $this->projects()->pluck('id');
        $memberIds = $this->memberProjects()->pluck('projects.id');
        $allIds = $ownedIds->merge($memberIds)->unique();

        return Project::whereIn('id', $allIds);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function snippets()
    {
        return $this->hasMany(Snippet::class);
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'creator_id');
    }
}
