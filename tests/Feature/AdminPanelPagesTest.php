<?php

namespace Tests\Feature;

use App\Filament\Clusters\Products\Resources\Brands\BrandResource;
use App\Filament\Clusters\Products\Resources\Brands\Pages\CreateBrand;
use App\Filament\Clusters\Products\Resources\Brands\Pages\EditBrand;
use App\Filament\Clusters\Products\Resources\Brands\Pages\ListBrands;
use App\Filament\Clusters\Products\Resources\Categories\Pages\CreateCategory;
use App\Filament\Clusters\Products\Resources\Categories\Pages\EditCategory;
use App\Filament\Clusters\Products\Resources\Categories\Pages\ListCategories;
use App\Filament\Clusters\Products\Resources\Categories\CategoryResource;
use App\Filament\Clusters\Products\Resources\Products\Pages\CreateProduct;
use App\Filament\Clusters\Products\Resources\Products\Pages\EditProduct;
use App\Filament\Clusters\Products\Resources\Products\Pages\ListProducts;
use App\Filament\Clusters\Products\Resources\Products\ProductResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\UploadDemo;
use App\Filament\Resources\Blog\Authors\Pages\ManageAuthors;
use App\Filament\Resources\Blog\Categories\Pages\ManageCategories;
use App\Filament\Resources\Blog\Posts\Pages\CreatePost;
use App\Filament\Resources\Blog\Posts\Pages\EditPost;
use App\Filament\Resources\Blog\Posts\Pages\ListPosts;
use App\Filament\Resources\Blog\Posts\Pages\ManagePostComments;
use App\Filament\Resources\Blog\Posts\Pages\ViewPost;
use App\Filament\Resources\Shop\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Shop\Customers\Pages\EditCustomer;
use App\Filament\Resources\Shop\Customers\Pages\ListCustomers;
use App\Filament\Resources\Shop\Orders\Pages\CreateOrder;
use App\Filament\Resources\Shop\Orders\Pages\EditOrder;
use App\Filament\Resources\Shop\Orders\Pages\ListOrders;
use App\Models\Blog\Author;
use App\Models\Blog\Category as BlogCategory;
use App\Models\Blog\Post;
use App\Models\Shop\Brand;
use App\Models\Shop\Category as ShopCategory;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->team = Team::create(['name' => 'Test Team']);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->user->teams()->attach($this->team);

        $this->actingAs($this->user);

        Filament::setTenant($this->team);
    }

    public function testLoginPageLoads(): void
    {
        auth()->logout();

        $this->get('/login')->assertOk();
    }

    public function testDashboardPageLoads(): void
    {
Livewire::test(Dashboard::class)
            ->assertSuccessful();
    }

    public function testUploadDemoPageLoads(): void
    {
Livewire::test(UploadDemo::class)
            ->assertSuccessful();
    }

    // Shop - Orders

    public function testListOrdersPageLoads(): void
    {
Livewire::test(ListOrders::class)
            ->assertSuccessful();
    }

    public function testCreateOrderPageLoads(): void
    {
Livewire::test(CreateOrder::class)
            ->assertSuccessful();
    }

    public function testEditOrderPageLoads(): void
    {
$customer = Customer::factory()->create(['team_id' => $this->team->getKey()]);

        $order = Order::factory()->create([
            'team_id' => $this->team->getKey(),
            'shop_customer_id' => $customer->getKey(),
        ]);

        Livewire::test(EditOrder::class, ['record' => $order->getKey()])
            ->assertSuccessful();
    }

    // Shop - Customers

    public function testListCustomersPageLoads(): void
    {
Livewire::test(ListCustomers::class)
            ->assertSuccessful();
    }

    public function testCreateCustomerPageLoads(): void
    {
Livewire::test(CreateCustomer::class)
            ->assertSuccessful();
    }

    public function testEditCustomerPageLoads(): void
    {
$customer = Customer::factory()->create(['team_id' => $this->team->getKey()]);

        Livewire::test(EditCustomer::class, ['record' => $customer->getKey()])
            ->assertSuccessful();
    }

    // Shop - Products (Cluster)

    public function testListProductsPageLoads(): void
    {
Livewire::test(ListProducts::class)
            ->assertSuccessful();
    }

    public function testCreateProductPageLoads(): void
    {
Livewire::test(CreateProduct::class)
            ->assertSuccessful();
    }

    public function testEditProductPageLoads(): void
    {
$brand = Brand::factory()->create(['team_id' => $this->team->getKey()]);

        $product = Product::factory()->create([
            'team_id' => $this->team->getKey(),
            'shop_brand_id' => $brand->getKey(),
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->assertSuccessful();
    }

    // Shop - Brands (Cluster)

    public function testListBrandsPageLoads(): void
    {
Livewire::test(ListBrands::class)
            ->assertSuccessful();
    }

    public function testCreateBrandPageLoads(): void
    {
Livewire::test(CreateBrand::class)
            ->assertSuccessful();
    }

    public function testEditBrandPageLoads(): void
    {
$brand = Brand::factory()->create(['team_id' => $this->team->getKey()]);

        Livewire::test(EditBrand::class, ['record' => $brand->getKey()])
            ->assertSuccessful();
    }

    // Shop - Categories (Cluster)

    public function testListCategoriesPageLoads(): void
    {
Livewire::test(ListCategories::class)
            ->assertSuccessful();
    }

    public function testCreateCategoryPageLoads(): void
    {
Livewire::test(CreateCategory::class)
            ->assertSuccessful();
    }

    public function testEditCategoryPageLoads(): void
    {
$category = ShopCategory::factory()->create(['team_id' => $this->team->getKey()]);

        Livewire::test(EditCategory::class, ['record' => $category->getKey()])
            ->assertSuccessful();
    }

    // Blog - Posts

    public function testListPostsPageLoads(): void
    {
Livewire::test(ListPosts::class)
            ->assertSuccessful();
    }

    public function testCreatePostPageLoads(): void
    {
Livewire::test(CreatePost::class)
            ->assertSuccessful();
    }

    public function testEditPostPageLoads(): void
    {
$author = Author::factory()->create(['team_id' => $this->team->getKey()]);
        $category = BlogCategory::factory()->create(['team_id' => $this->team->getKey()]);

        $post = Post::factory()->create([
            'team_id' => $this->team->getKey(),
            'blog_author_id' => $author->getKey(),
            'blog_category_id' => $category->getKey(),
        ]);

        Livewire::test(EditPost::class, ['record' => $post->getKey()])
            ->assertSuccessful();
    }

    public function testViewPostPageLoads(): void
    {
$author = Author::factory()->create(['team_id' => $this->team->getKey()]);
        $category = BlogCategory::factory()->create(['team_id' => $this->team->getKey()]);

        $post = Post::factory()->create([
            'team_id' => $this->team->getKey(),
            'blog_author_id' => $author->getKey(),
            'blog_category_id' => $category->getKey(),
        ]);

        Livewire::test(ViewPost::class, ['record' => $post->getKey()])
            ->assertSuccessful();
    }

    public function testManagePostCommentsPageLoads(): void
    {
$author = Author::factory()->create(['team_id' => $this->team->getKey()]);
        $category = BlogCategory::factory()->create(['team_id' => $this->team->getKey()]);

        $post = Post::factory()->create([
            'team_id' => $this->team->getKey(),
            'blog_author_id' => $author->getKey(),
            'blog_category_id' => $category->getKey(),
        ]);

        Livewire::test(ManagePostComments::class, ['record' => $post->getKey()])
            ->assertSuccessful();
    }

    // Blog - Authors

    public function testManageAuthorsPageLoads(): void
    {
Livewire::test(ManageAuthors::class)
            ->assertSuccessful();
    }

    // Blog - Categories

    public function testManageBlogCategoriesPageLoads(): void
    {
Livewire::test(ManageCategories::class)
            ->assertSuccessful();
    }
}
