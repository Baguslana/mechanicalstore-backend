<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'price' => (float) $this->price,
            'formatted_price' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            'image' => $this->image,
            'description' => $this->description,
            'in_stock' => $this->in_stock,
            'stock_quantity' => $this->stock_quantity,

            // Stock status badge
            'stock_status' => $this->getStockStatus(),

            // Specs as key-value pairs
            'specs' => $this->when(
                $this->relationLoaded('specs'),
                $this->specs->mapWithKeys(function ($spec) {
                    return [$spec->spec_key => $spec->spec_value];
                })
            ),

            // Category-specific details
            'details' => $this->getDetailsFormatted(),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get formatted details based on product category
     */
    protected function getDetailsFormatted()
    {
        if (!$this->category) {
            return null;
        }

        return match ($this->category->slug) {
            'keyboard-kits' => $this->getKeyboardKitDetails(),
            'switches' => $this->getSwitchDetails(),
            'keycaps' => $this->getKeycapDetails(),
            'accessories' => $this->getAccessoryDetails(),
            default => null,
        };
    }

    protected function getKeyboardKitDetails()
    {
        if (!$this->relationLoaded('keyboardKit') || !$this->keyboardKit) {
            return null;
        }

        $kit = $this->keyboardKit;
        return [
            'type' => 'keyboard_kit',
            'size' => $kit->size,
            'case_material' => $kit->case_material,
            'mount_type' => $kit->mount_type,
            'pcb_type' => $kit->pcb_type,
            'has_rotary_encoder' => $kit->has_rotary_encoder,
            'layout' => $kit->layout,
            'badges' => $this->getKeyboardBadges($kit),
        ];
    }

    protected function getSwitchDetails()
    {
        if (!$this->relationLoaded('switch') || !$this->switch) {
            return null;
        }

        $switch = $this->switch;
        return [
            'type' => 'switch',
            'switch_type' => $switch->switch_type,
            'actuation_force' => $switch->actuation_force,
            'travel_distance' => $switch->travel_distance,
            'quantity_per_pack' => $switch->quantity_per_pack,
            'is_factory_lubed' => $switch->is_factory_lubed,
            'housing_material' => $switch->housing_material,
            'stem_material' => $switch->stem_material,
            'badges' => $this->getSwitchBadges($switch),
        ];
    }

    protected function getKeycapDetails()
    {
        if (!$this->relationLoaded('keycap') || !$this->keycap) {
            return null;
        }

        $keycap = $this->keycap;
        return [
            'type' => 'keycap',
            'profile' => $keycap->profile,
            'material' => $keycap->material,
            'printing_method' => $keycap->printing_method,
            'key_count' => $keycap->key_count,
            'color_scheme' => $keycap->color_scheme,
            'badges' => $this->getKeycapBadges($keycap),
        ];
    }

    protected function getAccessoryDetails()
    {
        if (!$this->relationLoaded('accessory') || !$this->accessory) {
            return null;
        }

        $accessory = $this->accessory;
        return [
            'type' => 'accessory',
            'accessory_type' => $accessory->accessory_type,
            'quantity' => $accessory->quantity,
            'size_compatibility' => $accessory->size_compatibility,
            'variant' => $accessory->variant,
        ];
    }

    /**
     * Get stock status badge info
     */
    protected function getStockStatus()
    {
        if (!$this->in_stock) {
            return [
                'label' => 'Out of Stock',
                'color' => 'red',
            ];
        }

        if ($this->stock_quantity <= 5) {
            return [
                'label' => 'Low Stock',
                'color' => 'yellow',
            ];
        }

        return [
            'label' => 'In Stock',
            'color' => 'green',
        ];
    }

    /**
     * Get keyboard kit badges
     */
    protected function getKeyboardBadges($kit)
    {
        $badges = [];

        if ($kit->pcb_type === 'Hot-swap') {
            $badges[] = 'Hot-swap';
        }

        if ($kit->has_rotary_encoder) {
            $badges[] = 'Rotary Encoder';
        }

        if (in_array($kit->mount_type, ['Gasket Mount', 'O-ring Mount'])) {
            $badges[] = $kit->mount_type;
        }

        return $badges;
    }

    /**
     * Get switch badges
     */
    protected function getSwitchBadges($switch)
    {
        $badges = [$switch->switch_type];

        if ($switch->is_factory_lubed) {
            $badges[] = 'Factory Lubed';
        }

        if ($switch->actuation_force) {
            $badges[] = $switch->actuation_force;
        }

        return $badges;
    }

    /**
     * Get keycap badges
     */
    protected function getKeycapBadges($keycap)
    {
        return [
            $keycap->profile . ' Profile',
            $keycap->material,
            $keycap->printing_method,
        ];
    }
}
