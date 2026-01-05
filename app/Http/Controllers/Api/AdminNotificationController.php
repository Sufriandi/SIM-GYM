<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    // POST /api/admin/notifications
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'target' => 'required|in:all,user',
            'target_user_id' => 'nullable|integer',
            'type' => 'nullable|string|max:50',
            'data' => 'nullable|array',
        ]);

        if ($validated['target'] === 'user' && empty($validated['target_user_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'target_user_id wajib untuk target=user'
            ], 422);
        }

        $notif = Notification::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'target' => $validated['target'],
            'target_user_id' => $validated['target'] === 'user' ? $validated['target_user_id'] : null,
            'type' => $validated['type'] ?? null,
            'data' => $validated['data'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'data' => $notif]);
    }
}
