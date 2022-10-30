<?php

namespace Fwcloud916\SimpleCart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpleProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Fwcloud916\SimpleCart\Database\Factories\SimpleProductFactory::new();
    }
}
