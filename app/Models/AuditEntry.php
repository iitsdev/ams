<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_session_id',
        'asset_id',
        'scanned_by',
        'scanned_at',
        'found_location_id',
        'notes',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AuditSession::class, 'audit_session_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function foundLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'found_location_id');
    }
}
