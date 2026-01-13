<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwitchModel extends Model
{
    use HasFactory;

    protected $table = 'switches';

    protected $fillable = [
        'product_id',
        'switch_type',
        'actuation_force',
        'travel_distance',
        'quantity_per_pack',
        'is_factory_lubed',
        'housing_material',
        'stem_material',
    ];

    protected $casts = [
        'is_factory_lubed' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
