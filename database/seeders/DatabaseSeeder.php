<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\KeyboardKit;
use App\Models\SwitchModel;
use App\Models\Keycap;
use App\Models\Accessory;
use App\Models\ProductSpec;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            ['name' => 'Keyboard Kits', 'slug' => 'keyboard-kits', 'description' => 'Custom keyboard DIY kits'],
            ['name' => 'Switches', 'slug' => 'switches', 'description' => 'Mechanical keyboard switches'],
            ['name' => 'Keycaps', 'slug' => 'keycaps', 'description' => 'Custom keycap sets'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Keyboard accessories and mods'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // 2. Keyboard Kits
        $keyboardKitsCategory = Category::where('slug', 'keyboard-kits')->first();

        $tofu65 = Product::create([
            'category_id' => $keyboardKitsCategory->id,
            'name' => 'TOFU65 Acrylic',
            'slug' => 'tofu65-acrylic',
            'price' => 1250000,
            'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500&q=80',
            'description' => 'Premium 65% keyboard kit with stunning acrylic case that shows off your RGB lighting',
            'in_stock' => true,
            'stock_quantity' => 15,
        ]);

        KeyboardKit::create([
            'product_id' => $tofu65->id,
            'size' => '65%',
            'case_material' => 'Acrylic',
            'mount_type' => 'Tray Mount',
            'pcb_type' => 'Hot-swap',
            'has_rotary_encoder' => false,
            'layout' => 'ANSI',
        ]);

        ProductSpec::create(['product_id' => $tofu65->id, 'spec_key' => 'Weight', 'spec_value' => '~700g', 'sort_order' => 1]);
        ProductSpec::create(['product_id' => $tofu65->id, 'spec_key' => 'Dimensions', 'spec_value' => '312 x 110 x 30mm', 'sort_order' => 2]);
        ProductSpec::create(['product_id' => $tofu65->id, 'spec_key' => 'RGB Support', 'spec_value' => 'Yes (Per-key)', 'sort_order' => 3]);

        $kbd67 = Product::create([
            'category_id' => $keyboardKitsCategory->id,
            'name' => 'KBD67 Lite R4',
            'slug' => 'kbd67-lite-r4',
            'price' => 950000,
            'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80',
            'description' => 'Budget-friendly gasket mount 65% keyboard kit with great typing feel',
            'in_stock' => true,
            'stock_quantity' => 25,
        ]);

        KeyboardKit::create([
            'product_id' => $kbd67->id,
            'size' => '65%',
            'case_material' => 'Polycarbonate',
            'mount_type' => 'Gasket Mount',
            'pcb_type' => 'Hot-swap',
            'has_rotary_encoder' => false,
            'layout' => 'ANSI',
        ]);

        $gmmkPro = Product::create([
            'category_id' => $keyboardKitsCategory->id,
            'name' => 'GMMK Pro',
            'slug' => 'gmmk-pro',
            'price' => 1850000,
            'image' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=500&q=80',
            'description' => 'Premium 75% aluminum gasket mount keyboard with rotary encoder',
            'in_stock' => true,
            'stock_quantity' => 10,
        ]);

        KeyboardKit::create([
            'product_id' => $gmmkPro->id,
            'size' => '75%',
            'case_material' => 'Aluminum',
            'mount_type' => 'Gasket Mount',
            'pcb_type' => 'Hot-swap',
            'has_rotary_encoder' => true,
            'layout' => 'ANSI',
        ]);

        // 3. Switches
        $switchesCategory = Category::where('slug', 'switches')->first();

        $gateronYellow = Product::create([
            'category_id' => $switchesCategory->id,
            'name' => 'Gateron Yellow (70pcs)',
            'slug' => 'gateron-yellow-70pcs',
            'price' => 120000,
            'image' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=500&q=80',
            'description' => 'Smooth budget linear switches, perfect for beginners',
            'in_stock' => true,
            'stock_quantity' => 100,
        ]);

        SwitchModel::create([
            'product_id' => $gateronYellow->id,
            'switch_type' => 'Linear',
            'actuation_force' => '50g',
            'travel_distance' => '4mm',
            'quantity_per_pack' => 70,
            'is_factory_lubed' => false,
            'housing_material' => 'Nylon',
            'stem_material' => 'POM',
        ]);

        $bobaU4T = Product::create([
            'category_id' => $switchesCategory->id,
            'name' => 'Boba U4T (70pcs)',
            'slug' => 'boba-u4t-70pcs',
            'price' => 420000,
            'image' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=500&q=80',
            'description' => 'Thocky tactile switches with strong tactile bump',
            'in_stock' => true,
            'stock_quantity' => 50,
        ]);

        SwitchModel::create([
            'product_id' => $bobaU4T->id,
            'switch_type' => 'Tactile',
            'actuation_force' => '62g',
            'travel_distance' => '4mm',
            'quantity_per_pack' => 70,
            'is_factory_lubed' => false,
            'housing_material' => 'Proprietary',
            'stem_material' => 'POM',
        ]);

        $cherryMxRed = Product::create([
            'category_id' => $switchesCategory->id,
            'name' => 'Cherry MX Red (110pcs)',
            'slug' => 'cherry-mx-red-110pcs',
            'price' => 550000,
            'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80',
            'description' => 'Classic linear switches from the original Cherry',
            'in_stock' => true,
            'stock_quantity' => 80,
        ]);

        SwitchModel::create([
            'product_id' => $cherryMxRed->id,
            'switch_type' => 'Linear',
            'actuation_force' => '45g',
            'travel_distance' => '4mm',
            'quantity_per_pack' => 110,
            'is_factory_lubed' => false,
            'housing_material' => 'Nylon',
            'stem_material' => 'POM',
        ]);

        // 4. Keycaps
        $keycapsCategory = Category::where('slug', 'keycaps')->first();

        $gmkWob = Product::create([
            'category_id' => $keycapsCategory->id,
            'name' => 'GMK White on Black',
            'slug' => 'gmk-white-on-black',
            'price' => 1850000,
            'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500&q=80',
            'description' => 'Premium ABS double-shot keycaps with classic white on black colorway',
            'in_stock' => false,
            'stock_quantity' => 0,
        ]);

        Keycap::create([
            'product_id' => $gmkWob->id,
            'profile' => 'Cherry',
            'material' => 'ABS',
            'printing_method' => 'Double-shot',
            'key_count' => 139,
            'color_scheme' => 'White on Black',
        ]);

        $akkoAsa = Product::create([
            'category_id' => $keycapsCategory->id,
            'name' => 'Akko ASA Profile Neon',
            'slug' => 'akko-asa-neon',
            'price' => 450000,
            'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80',
            'description' => 'Budget ASA profile PBT keycaps with vibrant neon colors',
            'in_stock' => true,
            'stock_quantity' => 40,
        ]);

        Keycap::create([
            'product_id' => $akkoAsa->id,
            'profile' => 'ASA',
            'material' => 'PBT',
            'printing_method' => 'Dye-sub',
            'key_count' => 158,
            'color_scheme' => 'Neon Multi-color',
        ]);

        // 5. Accessories
        $accessoriesCategory = Category::where('slug', 'accessories')->first();

        $krytox = Product::create([
            'category_id' => $accessoriesCategory->id,
            'name' => 'Krytox 205g0 (5ml)',
            'slug' => 'krytox-205g0-5ml',
            'price' => 180000,
            'image' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=500&q=80',
            'description' => 'Premium PFPE-based lubricant for smooth linear switches',
            'in_stock' => true,
            'stock_quantity' => 60,
        ]);

        Accessory::create([
            'product_id' => $krytox->id,
            'accessory_type' => 'Lube',
            'quantity' => '5ml',
            'size_compatibility' => 'Universal',
            'variant' => 'Grade 0',
        ]);

        $durockStabs = Product::create([
            'category_id' => $accessoriesCategory->id,
            'name' => 'Durock V2 Stabilizers',
            'slug' => 'durock-v2-stabilizers',
            'price' => 150000,
            'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500&q=80',
            'description' => 'Premium screw-in stabilizers for rattle-free typing',
            'in_stock' => true,
            'stock_quantity' => 45,
        ]);

        Accessory::create([
            'product_id' => $durockStabs->id,
            'accessory_type' => 'Stabilizer',
            'quantity' => '1 set (4x 2u + 1x 6.25u)',
            'size_compatibility' => 'Universal',
            'variant' => 'Smokey',
        ]);

        $txFilms = Product::create([
            'category_id' => $accessoriesCategory->id,
            'name' => 'TX Switch Films (110pcs)',
            'slug' => 'tx-switch-films-110pcs',
            'price' => 85000,
            'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80',
            'description' => 'Premium 0.15mm switch films to reduce wobble and improve sound',
            'in_stock' => true,
            'stock_quantity' => 120,
        ]);

        Accessory::create([
            'product_id' => $txFilms->id,
            'accessory_type' => 'Film',
            'quantity' => '110pcs',
            'size_compatibility' => 'Universal',
            'variant' => '0.15mm',
        ]);

        echo "✅ Database seeded successfully with relational data!\n";
    }
}
