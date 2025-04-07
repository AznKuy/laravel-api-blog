<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ], 200);
    }
}
