<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryCollection;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filters
     * 
     * @param Request $request
     * @return ProductCollection
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'category',
                'keyboardKit',
                'switch',
                'keycap',
                'accessory',
                'specs'
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Pagination (optional)
        if ($request->boolean('paginate', false)) {
            $perPage = $request->input('per_page', 12);
            $products = $query->paginate($perPage);
            return new ProductCollection($products);
        }

        // Return all results
        $products = $query->get();
        return ProductResource::collection($products);
    }

    /**
     * Get single product with full details
     * 
     * @param string $slug
     * @return ProductDetailResource
     */
    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->with([
                'category',
                'keyboardKit',
                'switch',
                'keycap',
                'accessory',
                'specs'
            ])
            ->firstOrFail();

        return new ProductDetailResource($product);
    }

    /**
     * Get all categories
     * 
     * @return CategoryCollection
     */
    public function categories()
    {
        $categories = Category::withCount('products')->get();
        return new CategoryCollection($categories);
    }

    /**
     * Get filter options for a specific category
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterOptions(Request $request)
    {
        $categorySlug = $request->get('category');

        $options = [
            'sizes' => [],
            'mount_types' => [],
            'switch_types' => [],
            'profiles' => [],
            'materials' => [],
            'accessory_types' => [],
        ];

        if ($categorySlug === 'keyboard-kits') {
            $kits = Product::whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            })->with('keyboardKit')->get();

            $options['sizes'] = $kits->pluck('keyboardKit.size')
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $options['mount_types'] = $kits->pluck('keyboardKit.mount_type')
                ->filter()
                ->unique()
                ->sort()
                ->values();
        }

        if ($categorySlug === 'switches') {
            $options['switch_types'] = ['Linear', 'Tactile', 'Clicky'];
        }

        if ($categorySlug === 'keycaps') {
            $options['profiles'] = ['Cherry', 'OEM', 'SA', 'XDA', 'ASA', 'MT3', 'DSA', 'KAT'];
            $options['materials'] = ['ABS', 'PBT', 'POM'];
        }

        if ($categorySlug === 'accessories') {
            $options['accessory_types'] = ['Lube', 'Stabilizer', 'Film', 'Tool', 'Cable', 'Foam', 'Other'];
        }

        return response()->json($options);
    }

    /**
     * Apply filters to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    protected function applyFilters($query, Request $request)
    {
        // Filter by category
        if ($request->has('category') && $request->category !== 'All Products') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Filter by keyboard size
        if ($request->has('size') && $request->size !== 'All Sizes') {
            $query->whereHas('keyboardKit', function ($q) use ($request) {
                $q->where('size', $request->size);
            });
        }

        // Filter by switch type
        if ($request->has('switch_type') && $request->switch_type !== 'All Types') {
            $query->whereHas('switch', function ($q) use ($request) {
                $q->where('switch_type', $request->switch_type);
            });
        }

        // Filter by keycap profile
        if ($request->has('profile')) {
            $query->whereHas('keycap', function ($q) use ($request) {
                $q->where('profile', $request->profile);
            });
        }

        // Filter by keycap material
        if ($request->has('material')) {
            $query->whereHas('keycap', function ($q) use ($request) {
                $q->where('material', $request->material);
            });
        }

        // Filter by accessory type
        if ($request->has('accessory_type')) {
            $query->whereHas('accessory', function ($q) use ($request) {
                $q->where('accessory_type', $request->accessory_type);
            });
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != 'Infinity') {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock
        if ($request->boolean('in_stock_only')) {
            $query->where('in_stock', true);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Apply sorting to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    protected function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['price', 'name', 'created_at', 'stock_quantity'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
