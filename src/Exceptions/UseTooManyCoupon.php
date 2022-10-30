<?php

namespace Fwcloud916\SimpleCart\Exceptions;

use Exception;

class UseTooManyCoupon extends Exception
{
    public function __construct()
    {
        parent::__construct('Only one coupon can be used for the product at a time', 422);
    }
}
