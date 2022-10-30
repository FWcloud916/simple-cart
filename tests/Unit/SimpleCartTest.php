<?php

namespace Fwcloud916\SimpleCart\Tests\Unit;

use Fwcloud916\SimpleCart\Enums\SimpleCouponType;
use Fwcloud916\SimpleCart\Exceptions\UseTooManyCoupon;
use Fwcloud916\SimpleCart\Models\SimpleCart as SimpleCartModel;
use Fwcloud916\SimpleCart\SimpleCart;
use Fwcloud916\SimpleCart\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TypeError;

class SimpleCartTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->cartService = new SimpleCart();
    }

    /** @test */
    public function can_create_simple_products()
    {
        $product = $this->cartService->createSimpleProduct('Test Product', 1000);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(1000, $product->price);
    }

    /** @test */
    public function can_create_simple_coupon()
    {
        $coupon = $this->cartService->createSimpleCoupon('Test Coupon', SimpleCouponType::FIXED, 1000);

        $this->assertEquals('Test Coupon', $coupon->name);
        $this->assertEquals(1000, $coupon->value);
        $this->assertEquals(SimpleCouponType::FIXED, $coupon->type);
    }

    /** @test */
    public function cannot_create_simple_coupon_with_wrong_type()
    {
        $this->expectException(TypeError::class);
        $this->cartService->createSimpleCoupon('Test Coupon', 'fixed', 1000);
    }

    /** @test */
    public function can_add_product_to_cart()
    {
        $user = $this->setNewUser();
        $product = $this->setNewSimpleProduct("Test Product", 500);

        $cart = $this->cartService->addProductToCart($user, $product, 1);

        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(1, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(500, $cart->price);
        $this->assertEquals(500, $cart->total);
    }

    /** @test */
    public function can_add_same_product_to_cart()
    {
        $user = $this->setNewUser();
        $product = $this->setNewSimpleProduct("Test Product", 500);

        $cart = $this->cartService->addProductToCart($user, $product, 1);

        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(1, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(500, $cart->price);
        $this->assertEquals(500, $cart->total);

        $cart = $this->cartService->addProductToCart($user, $product, 1);

        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(500, $cart->price);
        $this->assertEquals(1000, $cart->total);

    }

    /** @test */
    public function can_update_product_quantity()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);

        $this->assertEquals(2, $cart->quantity);

        $cart = $this->cartService->updateProductQuantity($user, $cart->product, 3);
        $this->assertEquals(3, $cart->quantity);

        $cart = $this->cartService->updateProductQuantity($user, $cart->product, 1);
        $this->assertEquals(1, $cart->quantity);

    }

    /** @test */
    public function can_add_coupon_to_product()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);
        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(2000, $cart->total);

        $coupon = $this->setNewSimpleCoupon("Test Coupon", 100, SimpleCouponType::FIXED);

        $cart = $this->cartService->addCouponToProduct($user, $cart->product, $coupon);

        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(100, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(1800, $cart->total);
    }

    /** @test */
    public function cannot_more_than_one_coupon()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);
        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(2000, $cart->total);

        $coupon = $this->setNewSimpleCoupon("Test Coupon", 100, SimpleCouponType::FIXED);

        $cart = $this->cartService->addCouponToProduct($user, $cart->product, $coupon);

        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(100, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(1800, $cart->total);

        $coupon = $this->setNewSimpleCoupon("Test Coupon 2", 100, SimpleCouponType::FIXED);

        $this->expectException(UseTooManyCoupon::class);
        $this->cartService->addCouponToProduct($user, $cart->product, $coupon);
    }

    /** @test */
    public function can_remove_coupon_from_product()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);
        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(2000, $cart->total);

        $coupon = $this->setNewSimpleCoupon("Test Coupon", 100, SimpleCouponType::FIXED);

        $cart = $this->cartService->addCouponToProduct($user, $cart->product, $coupon);

        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(100, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(1800, $cart->total);

        $cart = $this->cartService->removeCouponFromProduct($user, $cart->product);

        $this->assertEquals(2, $cart->quantity);
        $this->assertEquals(0, $cart->discount);
        $this->assertEquals(1000, $cart->price);
        $this->assertEquals(2000, $cart->total);
    }

    /** @test */
    public function can_add_product_to_cart_with_percentage_coupon()
    {
        $user = $this->setNewUser();
        $product = $this->setNewSimpleProduct("Test Product", 500);
        $coupon = $this->setNewSimpleCoupon("Test Coupon", 10, SimpleCouponType::PERCENTAGE);

        $cart = $this->cartService->addProductToCart($user, $product, 1, $coupon);

        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(1, $cart->quantity);
        $this->assertEquals(50, $cart->discount);
        $this->assertEquals(500, $cart->price);
        $this->assertEquals(450, $cart->total);
    }

    /** @test */
    public function can_add_product_to_cart_with_fixed_coupon()
    {
        $user = $this->setNewUser();
        $product = $this->setNewSimpleProduct("Test Product", 500);
        $coupon = $this->setNewSimpleCoupon("Test Coupon", 10, SimpleCouponType::FIXED);

        $cart = $this->cartService->addProductToCart($user, $product, 1, $coupon);

        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(1, $cart->quantity);
        $this->assertEquals(10, $cart->discount);
        $this->assertEquals(500, $cart->price);
        $this->assertEquals(490, $cart->total);
    }

    /** @test */
    public function can_remove_product_from_cart()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);
        $product = $cart->product;

        $this->assertEquals(2, $cart->quantity);

        $this->cartService->removeProductFromCart($user, $cart->product);

        $this->expectException(ModelNotFoundException::class);
        SimpleCartModel::where('user_id', $user->id)->where('product_id', $product->id)->firstOrFail();
    }

    /** @test */
    public function can_get_cart_total_amount()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);
        $this->assertEquals(2000, $cart->total);

        $this->assertEquals(2000, $this->cartService->getCartTotalAmount($user));

        $product = $this->setNewSimpleProduct("Test Product 2", 500);

        $cart = $this->cartService->addProductToCart($user, $product, 1);
        $this->assertEquals(500, $cart->total);

        $this->assertEquals(2500, $this->cartService->getCartTotalAmount($user));

    }

    /** @test */
    public function can_get_cart_product_details()
    {
        $user = $this->setNewUser();
        $cart = $this->setNewSimpleCart($user->id, 2);

        $cart_details = $this->cartService->getCartDetails($user);
        $product_details = $cart_details['product_details'];
        $cart_total_amount = $cart_details['total_amount'];

        $this->assertEquals(1, count($product_details));

        $this->assertEquals($cart->product->name, $product_details[0]['product_name']);
        $this->assertEquals($cart->product->price, $product_details[0]['product_price']);
        $this->assertEquals('', $product_details[0]['coupon_name']);
        $this->assertEquals(0, $product_details[0]['discount']);
        $this->assertEquals($cart->quantity, $product_details[0]['quantity']);
        $this->assertEquals($cart->total, $product_details[0]['total']);

        $this->assertEquals($cart->total, $cart_total_amount);
        $total_1 = $cart->total;


        $coupon = $this->setNewSimpleCoupon("Test Coupon", 100, SimpleCouponType::FIXED);
        $product = $this->setNewSimpleProduct("Test Product 2", 500);

        $cart = $this->cartService->addProductToCart($user, $product, 2, $coupon);
        $cart_details = $this->cartService->getCartDetails($user);
        $product_details = $cart_details['product_details'];
        $cart_total_amount = $cart_details['total_amount'];

        $this->assertEquals(2, count($product_details));
        $this->assertEquals($cart->product->name, $product_details[1]['product_name']);
        $this->assertEquals($cart->product->price, $product_details[1]['product_price']);
        $this->assertEquals($coupon->name, $product_details[1]['coupon_name']);
        $this->assertEquals(100, $product_details[1]['discount']);
        $this->assertEquals($cart->quantity, $product_details[1]['quantity']);
        $this->assertEquals($cart->total, $product_details[1]['total']);
        $this->assertEquals($cart->total + $total_1, $cart_total_amount);
    }
}
