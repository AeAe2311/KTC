<?php

use App\Http\Controllers\ToastController;

if (!function_exists('toast')) {
    /**
     * Push a toast notification to the session to be shown on next request.
     * @param string $type one of success|error|info|warning
     * @param string $message
     * @param array $options optional options like ['duration'=>4000]
     */
    function toast(string $type, string $message, array $options = []) {
        // Normalize
        $type = in_array($type, ['success','error','info','warning']) ? $type : 'info';
        $toast = ['type' => $type, 'message' => $message, 'options' => $options];
        session()->push('toasts', $toast);
    }
}

if (!function_exists('toast_success')) {
    function toast_success(string $message, array $options = []){
        toast('success', $message, $options);
    }
}

if (!function_exists('toast_error')) {
    function toast_error(string $message, array $options = []){
        toast('error', $message, $options);
    }
}
