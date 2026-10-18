<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        ////ken 250623001 ken added here: Exclude menu change quantity from CSRF verification to avoid 419 errors when submitting from the sidebar.
        'menu/change-quantity',
    ];
}
