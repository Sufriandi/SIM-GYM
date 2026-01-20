<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCart extends Model
{
    protected $table = 'member_carts';

    protected $fillable = [
        'member_id',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function items()
    {
        return $this->hasMany(MemberCartItem::class, 'cart_id');
    }
}
