<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        // Generate unique slug
        do {
            $tranSlug = Str::random(60);
            $exists = User::where('slug', $tranSlug)->exists();
        } while ($exists);

        // Generate store_code 
        $storeCode = $this->generateStoreCode(); // added by 20250623001 ken 01-09-2026
            
        $user = User::create([
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'suffix' => $request->suffix,
            'business_name' => $request->company,
            'email' => $request->email,
            'username' => $request->email,
            'slug' => $tranSlug,
            'role' => 1,
            'expired_at' => now()->addMonth(),
            'password' => Hash::make($request->password),
            'store_code' => $storeCode, // added by 20250623001 ken 01-09-2026
        ]);

        DB::table('discounts')->insert([
            'customer_with_discount' => '0',
            'total_customer' => '0',
            'user_id' => $user->id
        ]);

        DB::table('lime_discount')->insert([
            'percent' => '0',
            'user_id' => $user->id
        ]);

        DB::table('service_charge')->insert([
            'percent' => '10',
            'user_id' => $user->id
        ]);
        DB::table('servers')->insert([
            'cashier' => '0',
            'user_id' => $user->id
        ]);

        /* event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME); */
        // Send verification email
        $user->sendEmailVerificationNotification();

        // Optional: do NOT log in yet
        // Auth::login($user);

            return redirect()->route('login')
            ->with('status', 'Registration successful! Please check your email to verify your account.');
        }


        // added start here by 20250623001 ken 01-09-2026
        // generate store code method added by ken 1-09-2026
        private function generateStoreCode(): string
        {
            return DB::transaction(function () {
                $storeType = '01'; // Could come from configuration
                $year = now()->format('Y');
                $month = str_pad(now()->format('n'), 3, '0', STR_PAD_LEFT);
                
                // Lock the users table to prevent race conditions
                $lastStore = User::where('store_code', 'like', "store-{$storeType}{$year}{$month}-%")
                    ->lockForUpdate()
                    ->orderBy('store_code', 'desc')
                    ->first();
                
                if ($lastStore) {
                    $parts = explode('-', $lastStore->store_code);
                    $lastSequence = intval(end($parts));
                    $sequence = str_pad($lastSequence + 1, 6, '0', STR_PAD_LEFT);
                } else {
                    $sequence = '000001';
                }
                
                return "store-{$storeType}{$year}{$month}-{$sequence}";
            });
        } 
        // added end here by 20250623001 ken 01-09-2026       
}
