<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Verify email from the link for not-logged-in users.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // Get the ID and hash from the URL
        $userId = $request->route('id');
        $hash = $request->route('hash'); // The signed hash from URL

        // Find the user
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('status', 'User not found.');
        }

        // Optional: verify that the hash matches the user's email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('status', 'Invalid verification link.');
        }

        // If already verified
        if ($user->hasVerifiedEmail()) {
            
            return redirect()->route('login')
                ->with('status', 'Your email is already verified. You can login now.');
        }

        // Mark email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        // Redirect to login with success message
        return redirect()->route('login')
            ->with('status', 'Your email has been verified! You can now login.');
    }
}
