<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'image',
        'description',
        'in_stock',
        'stock_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'in_stock' => 'boolean',
    ];

    protected $with = ['category']; // Eager load category by default

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function keyboardKit(): HasOne
    {
        return $this->hasOne(KeyboardKit::class);
    }

    public function switch(): HasOne
    {
        return $this->hasOne(SwitchModel::class);
    }

    public function keycap(): HasOne
    {
        return $this->hasOne(Keycap::class);
    }

    public function accessory(): HasOne
    {
        return $this->hasOne(Accessory::class);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    // Helper method to get specific details based on category
    public function getDetailsAttribute()
    {
        return match ($this->category->slug) {
            'keyboard-kits' => $this->keyboardKit,
            'switches' => $this->switch,
            'keycaps' => $this->keycap,
            'accessories' => $this->accessory,
            default => null,
        };
    }
}
