<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'description',
        'cost',
        'performed_by',
        'performed_at',

    ];



    public function asset(): BelongsTo
    {

        return $this->belongsTo(Asset::class);
    }

    public function performedByUser(): BelongsTo
    {

        return $this->belongsTo(User::class, 'performed_by');
    }
}
