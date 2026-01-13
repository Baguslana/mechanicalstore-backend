<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keycap extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'profile',
        'material',
        'printing_method',
        'key_count',
        'color_scheme',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
