<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }
    // public function createCancel(): View
    // {
    //     return view('authcancel.login');
    // }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
        // Log the user out immediately
        auth()->logout();

        // Return back with an error message
        return back()->withErrors([
            'username' => 'You need to verify your email before logging in.',
        ])->withInput($request->only('username'));
    }

        // Check if user is newly verified (within last 24 hours)
        if ($user->email_verified_at && $user->email_verified_at->diffInHours(now()) < 24) {
            session()->flash('welcome', 'Welcome to PosCare. Your updates, notifications, and important announcements will appear here.');
        }

        $request->session()->regenerate();

        // Redirect based on role
        switch ($user->role) {
            case 1:
            case 5:
            case 8:
                return redirect()->route('dashboard');
            case 2:
            case 6:
            case 7:
                return redirect()->route('pos.index');
            case 3:
            case 4:
                return redirect()->route('inventory.index');
            case 9:
                return redirect()->route('request.index');
            default:
                return redirect()->intended(RouteServiceProvider::HOME);
        }


        // return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function storeCancel(LoginRequest $request1): RedirectResponse
    {
        $request1->authenticate();

        //$request1->session()->regenerate();

        if(auth()->user()->role == 1){
            return redirect()->route('orders.index');
        } else {
            return view('authcancel.login');
        }


        // return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
