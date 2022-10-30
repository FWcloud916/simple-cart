<?php

namespace Fwcloud916\SimpleCart;

use Exception;
use Fwcloud916\SimpleCart\Enums\SimpleCouponType;
use Fwcloud916\SimpleCart\Exceptions\ProductNotFoundInCart;
use Fwcloud916\SimpleCart\Exceptions\UseTooManyCoupon;
use Fwcloud916\SimpleCart\Models\SimpleCoupon;
use Fwcloud916\SimpleCart\Models\SimpleProduct;
use Fwcloud916\SimpleCart\Models\SimpleCart as SimpleCartModel;
use Fwcloud916\SimpleCart\Tests\User;

class SimpleCart
{
    public function getCartTotalAmount($user)
    {
        $carts = $user->carts()->with('product', 'coupon')->get();
        if (!$carts->count()) {
            return 0;
        }
        return $carts->sum('total');
    }

    /**
     * @param  $user
     *
     * @return array
     */
    public function getCartDetails($user): array
    {
        $carts = $user->carts()->with('product', 'coupon')->get();
        if (!$carts->count()) {
            return [
                'product_details' => [],
                'total_amount' => 0,
            ];
        }
        $details = [];
        $total_amount = 0;
        foreach ($carts as $cart) {
            $details[] = [
                'product_name' => $cart->product->name,
                'product_price' => $cart->product->price,
                'coupon_name' => $cart->coupon->name ?? '',
                'discount' => $cart->discount,
                'quantity' => $cart->quantity,
                'total' => $cart->total,
            ];
            $total_amount += $cart->total;
        }
        return [
            'product_details' => $details,
            'total_amount' => $total_amount,
        ];
    }

    /**
     * @param  User               $user
     * @param  SimpleProduct      $product
     * @param  int                $quantity
     * @param  SimpleCoupon|null  $coupon
     *
     * @return SimpleCartModel
     * @throws Exception
     */
    public function addProductToCart(
        $user,
        SimpleProduct $product,
        int $quantity = 1,
        SimpleCoupon $coupon = null
    ): SimpleCartModel {
        if ($quantity < 0) {
            throw new Exception('Quantity must be greater than 0', 422);
        }

        try {
            $cart = $user->carts()->where('product_id', $product->id)->first();
            if ($cart) {
                $cart->quantity += $quantity;
                $cart->discount = $this->calDiscount($cart->product->price, $cart->coupon);
                $cart->total = $this->calProductTotalPrice($cart->product->price, $cart->discount, $cart->quantity);
                $cart->save();
            } else {
                $discount = $this->calDiscount($product->price, $coupon);
                $cart = $user->carts()->create([
                    'product_id' => $product->id,
                    'coupon_id' => $coupon->id ?? null,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'discount' => $discount,
                    'total' => $this->calProductTotalPrice($product->price, $discount, $quantity),
                ]);
            }
            return $cart->makeHidden(['product', 'coupon']);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param  User           $user
     * @param  SimpleProduct  $product
     *
     * @return bool
     * @throws Exception
     */
    public function removeProductFromCart(User $user, SimpleProduct $product): bool
    {
        $cart = $user->carts()->where('product_id', $product->id)->first();

        if (!$cart) {
            throw new ProductNotFoundInCart();
        }

        try {
            $cart->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting product from cart', 500);
        }

        return true;
    }

    /**
     * @param  User           $user
     * @param  SimpleProduct  $product
     * @param  int            $quantity
     *
     * @return SimpleCartModel
     * @throws ProductNotFoundInCart
     */
    public function updateProductQuantity(
        User $user,
        SimpleProduct $product,
        int $quantity
    ) : SimpleCartModel {
        $cart = $user->carts()->where('product_id', $product->id)->first();
        if (!$cart) {
            throw new ProductNotFoundInCart();
        }

        try {
            if ($quantity < 0) {
                throw new Exception('Quantity must be greater than 0', 422);
            }

            $cart->quantity = $quantity;
            $cart->discount = $this->calDiscount($cart->product->price, $cart->coupon);
            $cart->total = $this->calProductTotalPrice($cart->product->price, $cart->discount, $cart->quantity);
            $cart->save();
        } catch (Exception $e) {
            throw new Exception('Error updating product quantity', 500);
        }

        return $cart;
    }

    /**
     * @param  User           $user
     * @param  SimpleProduct  $product
     * @param  SimpleCoupon   $coupon
     *
     * @return SimpleCartModel
     * @throws ProductNotFoundInCart|UseTooManyCoupon
     */
    public function addCouponToProduct(User $user, SimpleProduct $product, SimpleCoupon $coupon): SimpleCartModel
    {
        $cart = $user->carts()->where('product_id', $product->id)->firstOrFail();

        if (!$cart) {
            throw new ProductNotFoundInCart();
        }

        if ($cart->coupon) {
            throw new UseTooManyCoupon();
        }

        try {
            $cart->coupon_id = $coupon->id;
            $cart->discount = $this->calDiscount($product->price, $coupon);
            $cart->total = $this->calProductTotalPrice($product->price, $cart->discount, $cart->quantity);
            $cart->save();
        } catch (Exception $e) {
            throw new Exception('Error adding coupon to product', 500);
        }
        return $cart;
    }

    public function removeCouponFromProduct($user, $product): SimpleCartModel
    {
        $cart = $user->carts()->where('product_id', $product->id)->first();

        if (!$cart) {
            throw new ProductNotFoundInCart();
        }

        try {
            $cart->coupon_id = null;
            $cart->discount = 0;
            $cart->total = $this->calProductTotalPrice($product->price, $cart->discount, $cart->quantity);
            $cart->save();
        } catch (Exception $e) {
            throw new Exception('Error removing coupon from product', 500);
        }

        return $cart;
    }

    /**
     * @param  string  $name
     * @param  int     $price
     *
     * @return SimpleProduct
     */
    public function createSimpleProduct(string $name, int $price): SimpleProduct
    {
        return SimpleProduct::create([
            'name' => $name,
            'price' => $price,
        ]);
    }

    /**
     * @param  string            $name
     * @param  SimpleCouponType  $type
     * @param  int               $value
     *
     * @return SimpleCoupon
     */
    public function createSimpleCoupon(string $name, SimpleCouponType $type, int $value): SimpleCoupon
    {
        return SimpleCoupon::create([
            'name' => $name,
            'type' => $type,
            'value' => $value,
        ]);
    }

    /**
     * @param  int                $price
     * @param  SimpleCoupon|null  $coupon
     *
     * @return int
     */
    private function calDiscount(int $price, SimpleCoupon $coupon = null): int
    {
        if ($coupon === null) {
            return 0;
        }

        if ($coupon->type === SimpleCouponType::PERCENTAGE) {
            $discount = round($price * $coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        return (int) $discount;
    }

    /**
     * @param  int  $price
     * @param  int  $discount
     * @param  int  $quantity
     *
     * @return int
     */
    private function calProductTotalPrice(int $price, int $discount, int $quantity): int
    {
        return ($price - $discount) * $quantity;
    }
}
