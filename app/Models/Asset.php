<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asset_tag',
        'description',
        'serial_number',
        'category_id',
        'location_id',
        'status_id',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'assigned_to',
        'created_by',
    ];

    /**
     * Get the category that owns the asset.
     */

    public function category(): BelongsTo
    {

        return $this->belongsTo(Category::class);
    }

    /**
     * Get the location of the asset.
     */

    public function location(): BelongsTo
    {

        return $this->belongsTo(Location::class);
    }

    /**
     * Get the status of the asset.
     */

    public function status(): BelongsTo
    {

        return $this->belongsTo(AssetStatus::class);
    }

    /**
     * Get the user who assigned the asset to.
     */

    public function assignedToUser(): BelongsTo
    {

        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the asset.
     */

    public function createdBy(): BelongsTo
    {

        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the maintenace logs history for the asset.
     */

    public function maintenanceLogs(): HasMany
    {

        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Get the checkin/checkout logs history for the asset.
     */

    public function checkinCheckoutLogs(): HasMany
    {

        return $this->hasMany(CheckinCheckoutLog::class);
    }

    protected function depreciation(): Attribute
    {
        $purchaseCost = $this->purchase_cost;
        $purchaseDate = Carbon::parse($this->purchase_date);

        return Attribute::make();
    }
}
