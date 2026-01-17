<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'payment_method' => 'required|in:bank_transfer,e_wallet,credit_card,cod',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_notes' => 'nullable|string',
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

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::with(['category', 'keyboardKit', 'switch', 'keycap', 'accessory'])
                    ->find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }

                // Check stock
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                // Prepare product details snapshot
                $productDetails = [
                    'category' => $product->category->name,
                ];

                // Add category-specific details
                if ($product->keyboardKit) {
                    $productDetails['keyboard_kit'] = $product->keyboardKit->toArray();
                } elseif ($product->switch) {
                    $productDetails['switch'] = $product->switch->toArray();
                } elseif ($product->keycap) {
                    $productDetails['keycap'] = $product->keycap->toArray();
                } elseif ($product->accessory) {
                    $productDetails['accessory'] = $product->accessory->toArray();
                }

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'product_image' => $product->image,
                    'product_description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                    'product_details' => $productDetails,
                ];
            }

            // Calculate shipping (simplified - flat rate Rp 20.000)
            $shippingCost = 20000;

            // Calculate tax (PPN 11%)
            $tax = $subtotal * 0.11;

            // Total
            $total = $subtotal + $shippingCost + $tax;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'shipping_address' => $request->shipping_address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'customer_notes' => $request->customer_notes,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);

                // Reduce stock
                Product::find($item['product_id'])->decrement('stock_quantity', $item['quantity']);
            }

            DB::commit();

            // Load relationships
            $order->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order by order number
     */
    public function show($orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Get all orders (admin)
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or customer
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        $orders = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Update order status (admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'admin_notes' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'shipping_courier' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $updateData = ['status' => $request->status];

        if ($request->has('admin_notes')) {
            $updateData['admin_notes'] = $request->admin_notes;
        }

        if ($request->status === 'shipped') {
            $updateData['shipped_at'] = now();
            $updateData['tracking_number'] = $request->tracking_number;
            $updateData['shipping_courier'] = $request->shipping_courier;
        }

        if ($request->status === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'data' => $order
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($order->items as $item) {
                Product::find($item->product_id)->increment('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order'
            ], 500);
        }
    }
}
