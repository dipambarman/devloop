<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bug extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'fingerprint',
        'title',
        'error_type',
        'status',
        'severity',
        'assignee_id',
        'occurrence_count',
        'regressions',
        'last_seen_at',
        'raw_message',
        'raw_stack',
        'version',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'occurrence_count' => 'integer',
        'regressions' => 'integer',
        'version' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BugEvent::class)->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    public function isFixed(): bool
    {
        return $this->status === 'fixed';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }
}
