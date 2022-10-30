<?php

namespace Fwcloud916\SimpleCart\Exceptions;

use Exception;

class ProductNotFoundInCart extends Exception
{
    public function __construct()
    {
        parent::__construct('Product not found in cart', 404);
    }
}
