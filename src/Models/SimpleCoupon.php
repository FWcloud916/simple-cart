<?php

namespace Fwcloud916\SimpleCart\Models;

use Fwcloud916\SimpleCart\Enums\SimpleCouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SimpleCoupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => SimpleCouponType::class,
    ];

    protected static function newFactory()
    {
        return \Fwcloud916\SimpleCart\Database\Factories\SimpleCouponFactory::new();
    }
}
