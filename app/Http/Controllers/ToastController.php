<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ToastController extends BaseController
{
    /**
     * API endpoint to push a toast message into session store and return JSON.
     * Accepts: type=success|error|info|warning, message, duration (optional ms)
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:success,error,info,warning',
            'message' => 'required|string',
            'duration' => 'nullable|integer'
        ]);

        $toast = [
            'type' => $data['type'],
            'message' => $data['message'],
            'options' => [
                'duration' => $data['duration'] ?? 4000
            ]
        ];

        // push onto session toasts array
        session()->push('toasts', $toast);

        return response()->json(['success' => true, 'toast' => $toast]);
    }

    /**
     * Helper to push toast from server code (controllers) easily.
     */
    public static function push($type, $message, $options = [])
    {
        $toast = ['type' => $type, 'message' => $message, 'options' => $options];
        session()->push('toasts', $toast);
    }
}
