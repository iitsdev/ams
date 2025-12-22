<?php
// filepath: c:\Users\daveg\Desktop\Projects\ams\app\Models\Asset.php

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
        'brand_id',
        'model',
        'specifications',
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
        'image',
        'created_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'deployed_at' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    protected $appends = ['years_since_purchase', 'age', 'depreciation'];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->latest();
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class)->latest();
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Accessors
    public function getYearsSincePurchaseAttribute(): ?int
    {
        if (!$this->purchase_date) return null;
        return Carbon::parse($this->purchase_date)->diffInYears(now());
    }

    public function getAgeAttribute(): ?array
    {
        if (!$this->purchase_date) return null;
        $diff = Carbon::parse($this->purchase_date)->diff(now());
        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'total_months' => Carbon::parse($this->purchase_date)->diffInMonths(now()),
        ];
    }

    public function getDepreciationAttribute(): ?array
    {
        if (!$this->purchase_cost || !$this->purchase_date || !$this->category?->lifespan_months) {
            return null;
        }

        $lifespanMonths = (int) $this->category->lifespan_months;
        if ($lifespanMonths <= 0) return null;

        $totalMonthsUsed = Carbon::parse($this->purchase_date)->diffInMonths(now());
        $monthly = (float) $this->purchase_cost / $lifespanMonths;

        $accumulated = min($monthly * $totalMonthsUsed, (float) $this->purchase_cost);
        $current = max((float) $this->purchase_cost - $accumulated, 0);

        return [
            'monthly_depreciation' => round($monthly, 2),
            'accumulated_depreciation' => round($accumulated, 2),
            'current_value' => round($current, 2),
        ];
    }
}
