<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'bio', 'skills', 'github_url', 'portfolio_url', 'linkedin_url', 'notification_preferences'])]
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
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Default notification preferences configuration.
     */
    public static function defaultNotificationPreferences(): array
    {
        return [
            'task_assigned' => ['mail' => true, 'database' => true],
            'comment_added' => ['mail' => true, 'database' => true],
            'discussion_replied' => ['mail' => true, 'database' => true],
            'member_added' => ['mail' => true, 'database' => true],
        ];
    }

    /**
     * Get user's active notification preferences merged with defaults.
     */
    public function getNotificationPreferences(): array
    {
        $defaults = self::defaultNotificationPreferences();
        $stored = $this->notification_preferences ?? [];

        return array_replace_recursive($defaults, $stored);
    }

    /**
     * Check if user wants a specific notification via a specific channel ('mail' or 'database').
     */
    public function wantsNotification(string $type, string $channel): bool
    {
        $prefs = $this->getNotificationPreferences();

        return !empty($prefs[$type][$channel]);
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
        return Project::where('owner_id', $this->id)
            ->orWhereHas('members', function ($query) {
                $query->where('user_id', $this->id);
            });
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
