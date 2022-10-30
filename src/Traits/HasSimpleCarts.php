<?php

namespace Fwcloud916\SimpleCart\Traits;

use Fwcloud916\SimpleCart\Models\SimpleCart;

trait HasSimpleCarts
{
    public function carts()
    {
        return $this->hasMany(SimpleCart::class);
    }
}
