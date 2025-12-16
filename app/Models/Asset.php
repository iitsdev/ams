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

    protected $appends = ['years_since_purchase', 'age'];

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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->orderBy('assigned_at', 'desc');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function checkinCheckoutLogs(): HasMany
    {
        return $this->hasMany(CheckinCheckoutLog::class);
    }

    // Computed Attributes - FIXED VERSION
    public function getYearsSincePurchaseAttribute()
    {
        if (!$this->purchase_date) {
            return 'N/A';
        }

        $purchaseDate = Carbon::parse($this->purchase_date)->startOfDay();
        $now = Carbon::now()->startOfDay();
        
        // Check if purchase date is in the future
        if ($purchaseDate->isFuture()) {
            return 'Future';
        }
        
        // Calculate total months difference
        $totalMonths = $purchaseDate->diffInMonths($now);
        
        // If less than a month old
        if ($totalMonths < 1) {
            $days = (int) $purchaseDate->diffInDays($now);
            if ($days === 0) {
                return 'Today';
            }
            return "{$days}d";
        }
        
        // Calculate years and remaining months
        $years = (int) floor($totalMonths / 12);
        $months = (int) ($totalMonths % 12);
        
        // Format the output
        if ($years > 0 && $months > 0) {
            return "{$years}y {$months}m";
        } elseif ($years > 0) {
            return "{$years}y";
        } else {
            return "{$months}m";
        }
    }

    // Alternative: More detailed age attribute
    public function getAgeAttribute()
    {
        if (!$this->purchase_date) {
            return [
                'formatted' => 'N/A',
                'short' => 'N/A',
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'total_days' => 0,
                'total_months' => 0,
                'is_future' => false
            ];
        }

        $purchaseDate = Carbon::parse($this->purchase_date)->startOfDay();
        $now = Carbon::now()->startOfDay();
        
        // Check if purchase date is in the future
        if ($purchaseDate->isFuture()) {
            $daysUntil = (int) $now->diffInDays($purchaseDate);
            return [
                'formatted' => "Future purchase (in {$daysUntil} days)",
                'short' => 'Future',
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'total_days' => 0,
                'total_months' => 0,
                'is_future' => true,
                'days_until' => $daysUntil
            ];
        }
        
        $totalMonths = (int) $purchaseDate->diffInMonths($now);
        $totalDays = (int) $purchaseDate->diffInDays($now);
        
        $years = (int) floor($totalMonths / 12);
        $months = (int) ($totalMonths % 12);
        
        // Calculate remaining days after years and months
        $dateAfterMonths = $purchaseDate->copy()->addMonths($totalMonths);
        $days = (int) $dateAfterMonths->diffInDays($now);
        
        // Format string
        $formatted = '';
        if ($years > 0) {
            $formatted .= "{$years} " . ($years == 1 ? 'year' : 'years');
        }
        if ($months > 0) {
            if ($formatted) $formatted .= ', ';
            $formatted .= "{$months} " . ($months == 1 ? 'month' : 'months');
        }
        if (!$formatted || ($years == 0 && $months == 0)) {
            if ($totalDays === 0) {
                $formatted = 'Purchased today';
            } else {
                $formatted = "{$totalDays} " . ($totalDays == 1 ? 'day' : 'days');
            }
        }
        
        return [
            'formatted' => $formatted,
            'short' => $this->getYearsSincePurchaseAttribute(),
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'total_days' => $totalDays,
            'total_months' => $totalMonths,
            'is_future' => false
        ];
    }

    // Depreciation calculation
    public function getDepreciationAttribute()
    {
        if (!$this->purchase_cost || !$this->purchase_date) {
            return null;
        }

        $purchaseDate = Carbon::parse($this->purchase_date)->startOfDay();
        
        // Don't calculate depreciation for future purchases
        if ($purchaseDate->isFuture()) {
            return [
                'purchase_cost' => number_format($this->purchase_cost, 2),
                'monthly_depreciation' => '0.00',
                'accumulated_depreciation' => '0.00',
                'current_value' => number_format($this->purchase_cost, 2),
                'depreciation_percentage' => 0,
                'is_future' => true
            ];
        }
        
        $monthsSincePurchase = (int) $purchaseDate->diffInMonths(now());
        $usefulLifeMonths = 60; // 5 years
        
        $monthlyDepreciation = $this->purchase_cost / $usefulLifeMonths;
        $accumulatedDepreciation = min(
            $monthlyDepreciation * $monthsSincePurchase,
            $this->purchase_cost
        );
        $currentValue = max(0, $this->purchase_cost - $accumulatedDepreciation);

        return [
            'purchase_cost' => number_format($this->purchase_cost, 2),
            'monthly_depreciation' => number_format($monthlyDepreciation, 2),
            'accumulated_depreciation' => number_format($accumulatedDepreciation, 2),
            'current_value' => number_format($currentValue, 2),
            'depreciation_percentage' => $this->purchase_cost > 0 
                ? round(($accumulatedDepreciation / $this->purchase_cost) * 100, 2) 
                : 0,
            'is_future' => false
        ];
    }
}
