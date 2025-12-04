<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'member_id',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
