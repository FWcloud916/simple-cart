<?php

namespace Fwcloud916\SimpleCart\Tests;

use Fwcloud916\SimpleCart\Enums\SimpleCouponType;
use Fwcloud916\SimpleCart\Models\SimpleCart;
use Fwcloud916\SimpleCart\Models\SimpleCoupon;
use Fwcloud916\SimpleCart\Models\SimpleProduct;
use Fwcloud916\SimpleCart\SimpleCartServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        include_once __DIR__ . '/../database/migrations/create_simple_carts_table.php.stub';
        (new \CreateSimpleCartsTable())->up();

        include_once __DIR__ . '/../database/migrations/create_simple_coupons_table.php.stub';
        (new \CreateSimpleCouponsTable())->up();

        include_once __DIR__ . '/../database/migrations/create_simple_products_table.php.stub';
        (new \CreateSimpleProductsTable())->up();

        include_once __DIR__ . '/../database/migrations/create_users_table.php.stub';
        (new \CreateUsersTable())->up();
    }


    protected function getPackageProviders($app): array
    {
        return [
            SimpleCartServiceProvider::class,
        ];
    }

    protected function setNewUser()
    {
        return User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Create Test Product.
     *
     * @param  string  $name
     * @param  int     $price
     *
     * @return SimpleProduct
     */
    protected function setNewSimpleProduct(
        string $name = 'Test Product',
        int $price = 1000
    ): SimpleProduct {
        return SimpleProduct::factory()->create([
            'name'  => $name,
            'price' => $price,
        ]);
    }

    /**
     * Create Test Cart.
     *
     * @param  string            $name
     * @param  int               $value
     * @param  SimpleCouponType  $type
     *
     * @return SimpleCoupon
     */
    protected function setNewSimpleCoupon(
        string $name = '15% off',
        int $value = 15,
        SimpleCouponType $type = SimpleCouponType::PERCENTAGE
    ): SimpleCoupon {
        return SimpleCoupon::factory()->create([
            'name'  => $name,
            'value' => $value,
            'type'  => $type,
        ]);
    }

    /**
     * Create Test Cart.
     *
     * @param  int|null  $user_id
     * @param  int       $quantity
     *
     * @return SimpleCart
     */
    protected function setNewSimpleCart(int $user_id = null, int $quantity = 1): SimpleCart
    {
        if (!$user_id) {
            $user_id = $this->setNewUser()->id;
        }
        $product = $this->setNewSimpleProduct();

        return SimpleCart::factory()->create([
            'user_id'    => $user_id,
            'product_id' => $product->id,
            'coupon_id'  => null,
            'quantity'   => $quantity,
            'price'      => $product->price,
            'discount'   => 0,
            'total'      => $product->price * $quantity,
        ]);
    }
}
