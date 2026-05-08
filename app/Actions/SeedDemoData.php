<?php

namespace App\Actions;

use App\Models\Blog\Author;
use App\Models\Blog\Category as BlogCategory;
use App\Models\Blog\Post;
use App\Models\Comment;
use App\Models\Shop\Brand;
use App\Models\Shop\Category as ShopCategory;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\Shop\Payment;
use App\Models\Team;
use App\Models\User;
use App\Notifications\LowStockAlertNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\LogBatch;

class SeedDemoData
{
    public static function run(Team $team, User $user): void
    {
        $teamId = $team->getKey();

        // Seed brands
        $brands = Brand::factory()
            ->count(15)
            ->create(['team_id' => $teamId])
            ->each(function (Brand $brand): void {
                $brand->addresses()->create([
                    'country' => fake()->countryCode(),
                    'street' => fake()->streetAddress(),
                    'state' => fake()->state(),
                    'city' => fake()->city(),
                    'zip' => fake()->postcode(),
                ]);

                $brand->update(['sort' => $brand->getKey()]);
            });

        // Seed shop categories
        $shopCategories = ShopCategory::factory()
            ->count(15)
            ->create(['team_id' => $teamId])
            ->each(function (ShopCategory $category) use ($teamId): void {
                ShopCategory::factory()
                    ->count(3)
                    ->create([
                        'team_id' => $teamId,
                        'parent_id' => $category->getKey(),
                    ]);
            });

        $allShopCategories = ShopCategory::where('team_id', $teamId)->get();

        // Seed customers
        $customers = Customer::factory()
            ->count(100)
            ->create(['team_id' => $teamId])
            ->each(function (Customer $customer): void {
                $customer->addresses()->create([
                    'country' => fake()->countryCode(),
                    'street' => fake()->streetAddress(),
                    'state' => fake()->state(),
                    'city' => fake()->city(),
                    'zip' => fake()->postcode(),
                ]);
            });

        // Seed products
        $products = \App\Models\Shop\Product::factory()
            ->count(40)
            ->create([
                'team_id' => $teamId,
                'shop_brand_id' => fn () => $brands->random()->getKey(),
            ])
            ->each(function (\App\Models\Shop\Product $product) use ($allShopCategories, $customers): void {
                $product->categories()->attach(
                    $allShopCategories->random(rand(3, 6))->pluck('id')
                );

                Comment::factory()
                    ->count(rand(5, 10))
                    ->create([
                        'commentable_type' => \App\Models\Shop\Product::class,
                        'commentable_id' => $product->getKey(),
                        'customer_id' => $customers->random()->getKey(),
                    ]);
            });

        // Seed orders
        $orders = Order::factory()
            ->count(200)
            ->create([
                'team_id' => $teamId,
                'shop_customer_id' => fn () => $customers->random()->getKey(),
            ])
            ->each(function (Order $order) use ($products): void {
                Payment::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'order_id' => $order->getKey(),
                    ]);

                collect(range(1, rand(2, 5)))->each(function (int $index) use ($order, $products): void {
                    OrderItem::factory()->create([
                        'shop_order_id' => $order->getKey(),
                        'shop_product_id' => $products->random()->getKey(),
                        'sort' => $index,
                    ]);
                });
            });

        // Seed activity log entries
        static::seedActivityLog($orders, $products, $user);

        // Seed blog
        $blogCategories = BlogCategory::factory()
            ->count(15)
            ->create(['team_id' => $teamId]);

        $blogAuthors = Author::factory()
            ->count(15)
            ->create(['team_id' => $teamId]);

        $blogAuthors->each(function (Author $author) use ($blogCategories, $customers, $teamId): void {
            Post::factory()
                ->count(rand(3, 5))
                ->create([
                    'team_id' => $teamId,
                    'blog_author_id' => $author->getKey(),
                    'blog_category_id' => $blogCategories->random()->getKey(),
                ])
                ->each(function (Post $post) use ($customers): void {
                    Comment::factory()
                        ->count(rand(3, 8))
                        ->create([
                            'commentable_type' => Post::class,
                            'commentable_id' => $post->getKey(),
                            'customer_id' => $customers->random()->getKey(),
                        ]);
                });
        });

        // Seed notifications
        static::seedNotifications($user, $orders, $products);
    }

    /**
     * @return array<int, string>
     */
    protected static function getGradientImages(): array
    {
        $dir = database_path('seeders/gradients');

        $images = [];

        for ($i = 0; $i < 40; $i++) {
            $images[] = "{$dir}/gradient-{$i}.jpg";
        }

        return collect($images)->shuffle()->all();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, Order> $orders
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop\Product> $products
     */
    protected static function seedActivityLog($orders, $products, User $user): void
    {
        // Simulate order status changes
        $orders->take(50)->each(function (Order $order) use ($user): void {
            activity()
                ->performedOn($order)
                ->causedBy($user)
                ->event('created')
                ->log('Order created');

            activity()
                ->performedOn($order)
                ->causedBy($user)
                ->event('updated')
                ->withProperties([
                    'old' => ['status' => 'new'],
                    'attributes' => ['status' => 'processing'],
                ])
                ->log('Status changed to processing');
        });

        // Simulate product updates
        $products->take(10)->each(function (\App\Models\Shop\Product $product) use ($user): void {
            activity()
                ->performedOn($product)
                ->causedBy($user)
                ->event('updated')
                ->withProperties([
                    'old' => ['price' => $product->old_price],
                    'attributes' => ['price' => $product->price],
                ])
                ->log('Price updated');
        });

        // Batch activity demo
        LogBatch::startBatch();
        $orders->take(5)->each(function (Order $order) use ($user): void {
            activity()
                ->performedOn($order)
                ->causedBy($user)
                ->event('updated')
                ->withProperties([
                    'old' => ['status' => 'processing'],
                    'attributes' => ['status' => 'shipped'],
                ])
                ->log('Bulk status update to shipped');
        });
        LogBatch::endBatch();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, Order> $orders
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shop\Product> $products
     */
    protected static function seedNotifications(User $user, $orders, $products): void
    {
        // Order status notifications
        $orders->take(5)->each(function (Order $order) use ($user): void {
            $user->notify(new OrderStatusChangedNotification($order));
        });

        // Low stock alerts
        $products->where('qty', '<', 5)->take(2)->each(function (\App\Models\Shop\Product $product) use ($user): void {
            $user->notify(new LowStockAlertNotification($product));
        });

        // Welcome notification
        $user->notify(new WelcomeNotification());
    }
}
