<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealMenuCatalogSeeder extends Seeder
{
    /** Add an idempotent catalog of real dishes to the active tenant database. */
    public function run(): int
    {
        $db = DB::connection('tenant');
        $catalog = [
            'Burgers' => [
                ['Classic Beef Burger', 340], ['Smoky BBQ Chicken Burger', 320], ['Spicy Zinger Burger', 300], ['Mushroom Swiss Burger', 390], ['Double Cheeseburger', 450], ['Crispy Fish Burger', 310], ['Grilled Chicken Burger', 330], ['Jalapeno Beef Burger', 380],
            ],
            'Pizza' => [
                ['Margherita Pizza', 480], ['BBQ Chicken Pizza', 620], ['Four Cheese Pizza', 680], ['Pepperoni Pizza', 650], ['Vegetable Supreme Pizza', 560], ['Hawaiian Chicken Pizza', 630], ['Beef Sausage Pizza', 640], ['Tandoori Chicken Pizza', 660],
            ],
            'Rice and Biryani' => [
                ['Kacchi Biryani', 390], ['Morog Polao', 350], ['Beef Kala Bhuna with Rice', 420], ['Chicken Roast with Polao', 380], ['Mutton Biryani', 480], ['Egg Fried Rice', 220], ['Chicken Fried Rice', 290], ['Beef Fried Rice', 340], ['Prawn Fried Rice', 390], ['Beef Bhuna Khichuri', 360],
            ],
            'Fast Food' => [
                ['Chicken Shawarma', 240], ['Beef Shawarma', 280], ['Chicken Cheese Sandwich', 220], ['Club Sandwich', 280], ['Chicken Submarine Sandwich', 310], ['Chicken Momos', 250], ['Beef Momos', 290], ['Chicken Spring Rolls', 180], ['Vegetable Spring Rolls', 150], ['Chicken Nuggets', 220], ['Fish and Chips', 420], ['Chicken Parmesan', 390],
            ],
            'Set Meals' => [
                ['Grilled Chicken with Mashed Potato', 420], ['Beef Steak with Pepper Sauce', 680], ['Chicken Alfredo Pasta', 390], ['Spaghetti Bolognese', 420], ['Penne Arrabbiata', 330], ['Grilled Salmon with Vegetables', 850], ['Fish Curry Rice Plate', 320], ['Chicken Curry Rice Plate', 300],
            ],
            'Beverages' => [
                ['Mango Lassi', 160], ['Fresh Orange Juice', 180], ['Watermelon Juice', 150], ['Iced Americano', 170], ['Cappuccino', 190], ['Caramel Macchiato', 240], ['Masala Chai', 90], ['Mint Lemonade', 140],
            ],
            'Desserts' => [
                ['New York Cheesecake', 280], ['Chocolate Lava Cake', 260], ['Caramel Custard', 150], ['Mango Mousse', 190], ['Tiramisu', 300], ['Chocolate Fudge Sundae', 240],
            ],
        ];

        return $db->transaction(function () use ($db, $catalog): int {
            $now = now();
            $added = 0;

            foreach ($catalog as $categoryName => $items) {
                $category = $db->table('menu_categories')->where('category_name', $categoryName)->first();
                if ($category) {
                    $categoryId = $category->id;
                    if ($category->deleted_at !== null) {
                        $db->table('menu_categories')->where('id', $categoryId)->update(['deleted_at' => null, 'is_available' => true, 'updated_at' => $now]);
                    }
                } else {
                    $categoryId = $db->table('menu_categories')->insertGetId([
                        'category_name' => $categoryName,
                        'description' => 'Freshly prepared restaurant favourites.',
                        'is_available' => true,
                        'display_order' => 20 + $added,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ($items as [$name, $price]) {
                    $existing = $db->table('menu_items')->where('menu_category_id', $categoryId)->where('menu_name', $name)->first();
                    if ($existing) {
                        if ($existing->deleted_at !== null) {
                            $db->table('menu_items')->where('id', $existing->id)->update(['deleted_at' => null, 'is_available' => true, 'updated_at' => $now]);
                        }
                        continue;
                    }

                    $db->table('menu_items')->insert([
                        'menu_category_id' => $categoryId,
                        'menu_name' => $name,
                        'item_type' => 'regular',
                        'price' => $price,
                        'description' => $name . ' freshly prepared to order.',
                        'is_available' => true,
                        'is_featured' => false,
                        'preparation_time' => 15,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $added++;
                }
            }

            return $added;
        });
    }

}
