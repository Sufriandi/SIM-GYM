<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin.{id}', function ($user, $id) {
    return $user
        && $user->role === 'admin'
        && (int) $user->id === (int) $id;
});
