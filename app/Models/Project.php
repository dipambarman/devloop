<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'color',
        'github_repo',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
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

    /**
     * Check if a user can edit project resources (owner or member with non-viewer role).
     */
    public function canEdit(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }

        $member = $this->members()->where('user_id', $user->id)->first();
        if ($member) {
            $role = $member->pivot->role ?? 'member';
            return $role !== 'viewer';
        }

        return false;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
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

    public function bugs()
    {
        return $this->hasMany(Bug::class);
    }

    public function bugEvents()
    {
        return $this->hasMany(BugEvent::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function activeSprint()
    {
        return $this->hasOne(Sprint::class)->where('status', \App\Enums\SprintStatus::Active);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function slackWebhooks()
    {
        return $this->hasMany(SlackWebhook::class);
    }

    public function totalLoggedHours(): float
    {
        $minutes = TimeLog::whereHas('task', fn($q) => $q->where('project_id', $this->id))
            ->sum('duration_minutes');

        return round($minutes / 60, 2);
    }

    /**
     * Generate a new plaintext Ingest API key and save its SHA-256 hash.
     * Returns the plaintext key (displayed only once).
     */
    public function generateIngestKey(): string
    {
        $plainKey = 'dl_ingest_' . bin2hex(random_bytes(24));
        $this->forceFill([
            'ingest_key_hash' => hash('sha256', $plainKey),
        ])->save();

        return $plainKey;
    }

    /**
     * Revoke the current Ingest API key.
     */
    public function revokeIngestKey(): void
    {
        $this->forceFill([
            'ingest_key_hash' => null,
        ])->save();
    }
}
