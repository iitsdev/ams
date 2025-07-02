<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable =[
        'name',    
    ];


    /**
     * Get the assets that belong to this category.
     */

     public function assets(): HasMany 
     {
        return $this->hasMany(Asset::class);
     }

    
}
