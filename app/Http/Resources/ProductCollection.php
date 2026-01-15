<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => ProductResource::collection($this->collection),
            'meta' => [
                'total' => $this->total() ?? $this->collection->count(),
                'current_page' => $this->currentPage() ?? 1,
                'last_page' => $this->lastPage() ?? 1,
                'per_page' => $this->perPage() ?? $this->collection->count(),
            ],
            'filters_applied' => $this->getAppliedFilters($request),
        ];
    }

    protected function getAppliedFilters($request)
    {
        $filters = [];

        if ($request->has('category') && $request->category !== 'All Products') {
            $filters['category'] = $request->category;
        }

        if ($request->has('size') && $request->size !== 'All Sizes') {
            $filters['size'] = $request->size;
        }

        if ($request->has('switch_type') && $request->switch_type !== 'All Types') {
            $filters['switch_type'] = $request->switch_type;
        }

        if ($request->boolean('in_stock_only')) {
            $filters['in_stock_only'] = true;
        }

        if ($request->has('search')) {
            $filters['search'] = $request->search;
        }

        return $filters;
    }
}
