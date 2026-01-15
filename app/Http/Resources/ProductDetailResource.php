<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $baseData = (new ProductResource($this->resource))->toArray($request);

        // Add extra details for single product view
        return array_merge($baseData, [
            // Full specs list
            'specs_detailed' => $this->when(
                $this->relationLoaded('specs'),
                $this->specs->map(function ($spec) {
                    return [
                        'key' => $spec->spec_key,
                        'value' => $spec->spec_value,
                        'sort_order' => $spec->sort_order,
                    ];
                })
            ),

            // Related products (same category)
            'related_products' => $this->when(
                $request->input('include_related'),
                function () {
                    return ProductResource::collection(
                        $this->category->products()
                            ->where('id', '!=', $this->id)
                            ->where('in_stock', true)
                            ->limit(4)
                            ->get()
                    );
                }
            ),

            // SEO meta
            'meta' => [
                'title' => $this->name . ' - MechKey Store',
                'description' => substr($this->description, 0, 160),
                'og_image' => $this->image,
            ],
        ]);
    }
}
