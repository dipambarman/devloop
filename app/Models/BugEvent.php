<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'bug_id',
        'project_id',
        'type',
        'occurrence_count',
        'from_status',
        'to_status',
        'user_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurrence_count' => 'integer',
        'created_at' => 'datetime',
    ];

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
