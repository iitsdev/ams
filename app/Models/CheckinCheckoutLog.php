<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinCheckoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'action',
        'timestamp',
    ];

    public function asset(): BelongsTo {

        return $this->belongsTo(Asset::class);

    }

    public function user(): BelongsTo {

        return $this->belongsTo(User::class);
    }
}
