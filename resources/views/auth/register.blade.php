<x-guest-layout>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <!-- Firstname -->
        <div>
            <x-input-label for="firstname">
                {{ __('Firstname') }} <span class="text-red-600">*</span>
            </x-input-label>

            <x-text-input
                id="firstname"
                class="block mt-1 w-full"
                type="text"
                name="firstname"
                :value="old('firstname')"
                required
                autofocus
                autocomplete="given-name"
            />

            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
        </div>

        <!-- Middlename -->
        <div>
            <x-input-label for="middlename" :value="__('Middlename')" />
            
            <x-text-input id="middlename" class="block mt-1 w-full" type="text" name="middlename" :value="old('middlename')" autofocus autocomplete="middlename" />
            <x-input-error :messages="$errors->get('middlename')" class="mt-2" />
        </div>

        <!-- Lastname -->
        <div>
            <x-input-label for="lastname">
                {{ __('Lastname') }}
            <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="lastname" class="block mt-1 w-full" type="text" name="lastname" :value="old('lastname')" required autofocus autocomplete="lastname" />
            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
        </div>

        <!-- Suffix -->
        <div>
            <x-input-label for="suffix" :value="__('Suffix')" />
            <select
                id="suffix"
                name="suffix"
                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
            >
                <option value="">None</option>
                <option value="Jr" {{ old('suffix') == 'Jr' ? 'selected' : '' }}>Jr.</option>
                <option value="Sr" {{ old('suffix') == 'Sr' ? 'selected' : '' }}>Sr.</option>
                <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
            </select>
            <x-input-error :messages="$errors->get('suffix')" class="mt-2" />
        </div>

        <!-- Company -->
        <div>
            <x-input-label for="company" >
                {{ __('Company') }}
                <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="company" class="block mt-1 w-full" type="text" name="company" :value="old('company')" required autofocus autocomplete="company" />
            <x-input-error :messages="$errors->get('company')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" >
                {{ __('Email') }}
                <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password">
            {{ __('Password') }}
                <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" >
            {{ __('Confirm Password') }}
                <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
