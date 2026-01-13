<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filters
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

        // Filter by category
        if ($request->has('category') && $request->category !== 'All Products') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Filter by keyboard size (only for keyboard kits)
        if ($request->has('size') && $request->size !== 'All Sizes') {
            $query->whereHas('keyboardKit', function ($q) use ($request) {
                $q->where('size', $request->size);
            });
        }

        // Filter by switch type (only for switches)
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
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        }

        $products = $query->get();

        // Transform response to include details
        return response()->json($products->map(function ($product) {
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category->name,
                'category_slug' => $product->category->slug,
                'price' => $product->price,
                'image' => $product->image,
                'description' => $product->description,
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'specs' => $product->specs->pluck('spec_value', 'spec_key'),
            ];

            // Add category-specific details
            switch ($product->category->slug) {
                case 'keyboard-kits':
                    if ($product->keyboardKit) {
                        $data['details'] = [
                            'size' => $product->keyboardKit->size,
                            'case_material' => $product->keyboardKit->case_material,
                            'mount_type' => $product->keyboardKit->mount_type,
                            'pcb_type' => $product->keyboardKit->pcb_type,
                            'has_rotary_encoder' => $product->keyboardKit->has_rotary_encoder,
                            'layout' => $product->keyboardKit->layout,
                        ];
                    }
                    break;

                case 'switches':
                    if ($product->switch) {
                        $data['details'] = [
                            'switch_type' => $product->switch->switch_type,
                            'actuation_force' => $product->switch->actuation_force,
                            'travel_distance' => $product->switch->travel_distance,
                            'quantity_per_pack' => $product->switch->quantity_per_pack,
                            'is_factory_lubed' => $product->switch->is_factory_lubed,
                            'housing_material' => $product->switch->housing_material,
                            'stem_material' => $product->switch->stem_material,
                        ];
                    }
                    break;

                case 'keycaps':
                    if ($product->keycap) {
                        $data['details'] = [
                            'profile' => $product->keycap->profile,
                            'material' => $product->keycap->material,
                            'printing_method' => $product->keycap->printing_method,
                            'key_count' => $product->keycap->key_count,
                            'color_scheme' => $product->keycap->color_scheme,
                        ];
                    }
                    break;

                case 'accessories':
                    if ($product->accessory) {
                        $data['details'] = [
                            'accessory_type' => $product->accessory->accessory_type,
                            'quantity' => $product->accessory->quantity,
                            'size_compatibility' => $product->accessory->size_compatibility,
                            'variant' => $product->accessory->variant,
                        ];
                    }
                    break;
            }

            return $data;
        }));
    }

    /**
     * Get single product with full details
     */
    public function show($slug)
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

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category->name,
            'category_slug' => $product->category->slug,
            'price' => $product->price,
            'image' => $product->image,
            'description' => $product->description,
            'in_stock' => $product->in_stock,
            'stock_quantity' => $product->stock_quantity,
            'specs' => $product->specs->map(function ($spec) {
                return [
                    'key' => $spec->spec_key,
                    'value' => $spec->spec_value,
                ];
            }),
        ];

        // Add category-specific details
        if ($product->keyboardKit) {
            $data['keyboard_kit'] = $product->keyboardKit;
        }
        if ($product->switch) {
            $data['switch'] = $product->switch;
        }
        if ($product->keycap) {
            $data['keycap'] = $product->keycap;
        }
        if ($product->accessory) {
            $data['accessory'] = $product->accessory;
        }

        return response()->json($data);
    }

    /**
     * Get all categories
     */
    public function categories()
    {
        $categories = Category::withCount('products')->get();
        return response()->json($categories);
    }

    /**
     * Get filter options for a specific category
     */
    public function filterOptions(Request $request)
    {
        $categorySlug = $request->get('category');

        $options = [];

        if ($categorySlug === 'keyboard-kits') {
            $options['sizes'] = Product::whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            })->whereHas('keyboardKit')->with('keyboardKit')
                ->get()->pluck('keyboardKit.size')->unique()->values();

            $options['mount_types'] = Product::whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            })->whereHas('keyboardKit')->with('keyboardKit')
                ->get()->pluck('keyboardKit.mount_type')->unique()->values();
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
}
