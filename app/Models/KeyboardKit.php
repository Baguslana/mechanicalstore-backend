<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyboardKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'case_material',
        'mount_type',
        'pcb_type',
        'has_rotary_encoder',
        'layout',
    ];

    protected $casts = [
        'has_rotary_encoder' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
