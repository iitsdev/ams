<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    protected $appends = ['duration_days', 'is_current'];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->assigned_at) {
            return null;
        }

        $endDate = $this->returned_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }

    public function getIsCurrentAttribute(): bool
    {
        return is_null($this->returned_at);
    }
}
