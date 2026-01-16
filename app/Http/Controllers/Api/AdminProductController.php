<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\KeyboardKit;
use App\Models\SwitchModel;
use App\Models\Keycap;
use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'image' => 'required|url',
            'description' => 'required|string',
            'in_stock' => 'boolean',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create product
            $product = Product::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'image' => $request->image,
                'description' => $request->description,
                'in_stock' => $request->in_stock ?? true,
                'stock_quantity' => $request->stock_quantity,
            ]);

            // Create category-specific details if provided
            $category = Category::find($request->category_id);

            if ($category) {
                switch ($category->slug) {
                    case 'keyboard-kits':
                        if ($request->has('details')) {
                            KeyboardKit::create([
                                'product_id' => $product->id,
                                'size' => $request->details['size'] ?? null,
                                'case_material' => $request->details['case_material'] ?? null,
                                'mount_type' => $request->details['mount_type'] ?? null,
                                'pcb_type' => $request->details['pcb_type'] ?? 'Hot-swap',
                                'has_rotary_encoder' => $request->details['has_rotary_encoder'] ?? false,
                                'layout' => $request->details['layout'] ?? null,
                            ]);
                        }
                        break;

                    case 'switches':
                        if ($request->has('details')) {
                            SwitchModel::create([
                                'product_id' => $product->id,
                                'switch_type' => $request->details['switch_type'] ?? 'Linear',
                                'actuation_force' => $request->details['actuation_force'] ?? null,
                                'travel_distance' => $request->details['travel_distance'] ?? null,
                                'quantity_per_pack' => $request->details['quantity_per_pack'] ?? 70,
                                'is_factory_lubed' => $request->details['is_factory_lubed'] ?? false,
                                'housing_material' => $request->details['housing_material'] ?? null,
                                'stem_material' => $request->details['stem_material'] ?? null,
                            ]);
                        }
                        break;

                    case 'keycaps':
                        if ($request->has('details')) {
                            Keycap::create([
                                'product_id' => $product->id,
                                'profile' => $request->details['profile'] ?? 'Cherry',
                                'material' => $request->details['material'] ?? 'PBT',
                                'printing_method' => $request->details['printing_method'] ?? 'Dye-sub',
                                'key_count' => $request->details['key_count'] ?? 104,
                                'color_scheme' => $request->details['color_scheme'] ?? null,
                            ]);
                        }
                        break;

                    case 'accessories':
                        if ($request->has('details')) {
                            Accessory::create([
                                'product_id' => $product->id,
                                'accessory_type' => $request->details['accessory_type'] ?? 'Other',
                                'quantity' => $request->details['quantity'] ?? null,
                                'size_compatibility' => $request->details['size_compatibility'] ?? null,
                                'variant' => $request->details['variant'] ?? null,
                            ]);
                        }
                        break;
                }
            }

            DB::commit();

            // Load relationships
            $product->load([
                'category',
                'keyboardKit',
                'switch',
                'keycap',
                'accessory'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:products,slug,' . $id,
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'sometimes|required|url',
            'description' => 'sometimes|required|string',
            'in_stock' => 'boolean',
            'stock_quantity' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update product
            $product->update($request->only([
                'name',
                'slug',
                'category_id',
                'price',
                'image',
                'description',
                'in_stock',
                'stock_quantity',
            ]));

            // Update category-specific details if provided
            if ($request->has('details')) {
                $category = $product->category;

                switch ($category->slug) {
                    case 'keyboard-kits':
                        if ($product->keyboardKit) {
                            $product->keyboardKit->update($request->details);
                        } else {
                            KeyboardKit::create(array_merge(
                                ['product_id' => $product->id],
                                $request->details
                            ));
                        }
                        break;

                    case 'switches':
                        if ($product->switch) {
                            $product->switch->update($request->details);
                        } else {
                            SwitchModel::create(array_merge(
                                ['product_id' => $product->id],
                                $request->details
                            ));
                        }
                        break;

                    case 'keycaps':
                        if ($product->keycap) {
                            $product->keycap->update($request->details);
                        } else {
                            Keycap::create(array_merge(
                                ['product_id' => $product->id],
                                $request->details
                            ));
                        }
                        break;

                    case 'accessories':
                        if ($product->accessory) {
                            $product->accessory->update($request->details);
                        } else {
                            Accessory::create(array_merge(
                                ['product_id' => $product->id],
                                $request->details
                            ));
                        }
                        break;
                }
            }

            DB::commit();

            // Reload relationships
            $product->load([
                'category',
                'keyboardKit',
                'switch',
                'keycap',
                'accessory'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        try {
            $productName = $product->name;

            // Delete will cascade to related tables due to foreign key constraints
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => "Product '{$productName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updated = 0;
            foreach ($request->updates as $update) {
                $product = Product::find($update['product_id']);
                if ($product) {
                    $product->update([
                        'stock_quantity' => $update['stock_quantity'],
                        'in_stock' => $update['stock_quantity'] > 0,
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updated} products updated successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
