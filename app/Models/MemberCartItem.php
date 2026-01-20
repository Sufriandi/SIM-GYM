<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCartItem extends Model
{
    protected $table = 'member_cart_items';

    protected $fillable = [
        'cart_id',
        'produk_id',
        'quantity',
        'price',
        'name',
        'photo',
        'category',
    ];

    public function cart()
    {
        return $this->belongsTo(MemberCart::class, 'cart_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
