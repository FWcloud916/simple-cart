<?php

namespace Fwcloud916\SimpleCart\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpleCart extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Fwcloud916\SimpleCart\Database\Factories\SimpleCartFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
