<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'asset_tag',
        'serial_number',
        'model',
        'brand',
        'category_id',
        'status_id',
        'location_id',
        'assigned_to',
        'purchase_date',
        'deployed_at',
        'purchase_cost',
        'supplier_id',
        'warranty_expiry',
        'notes',
        'specifications',
        'image',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'deployed_at' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    protected $appends = ['years_since_purchase'];

    // Calculate years since purchase
    public function getYearsSincePurchaseAttribute()
    {
        if (!$this->purchase_date) {
            return null;
        }

        $purchaseDate = Carbon::parse($this->purchase_date);
        $now = now();
        
        // Calculate total months difference
        $totalMonths = $purchaseDate->diffInMonths($now);
        
        // Calculate years and remaining months
        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;
        
        // Format output
        if ($years === 0 && $months === 0) {
            return 'New';
        } elseif ($years === 0) {
            return $months . 'm';
        } elseif ($months === 0) {
            return $years . 'y';
        } else {
            return $years . 'y ' . $months . 'm';
        }
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'status_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function checkinCheckoutLogs(): HasMany
    {
        return $this->hasMany(CheckinCheckoutLog::class);
    }
}
