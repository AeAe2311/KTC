<x-guest-layout>
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Session Status -->
    {{-- <x-auth-session-status class="mb-4" :status="session('status')" /> --}}
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-6">
          <label for="username" class="block mb-2 text-sm font-medium text-gray-900">Username</label>
          <input type="text" id="username" name="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
        </div>
        <div class="mb-6">
          <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
          <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
        </div>
        <x-primary-button style="display: block !important;" class="w-full py-3 text-center">
            {{ __('Log in') }}
        </x-primary-button>
        {{-- <button type="submit" class="focus:outline-none text-white bg-emerald-500 hover:bg-emerald-600 focus:ring-4 focus:ring-green-300 rounded-lg text-sm px-5 py-2.5 w-full tracking-wider font-semibold shadow-lg mb-2">LOGIN</button> --}}
        <div class="mt-6 flex justify-center text-sm text-gray-600">
          Register Now!  <a href="{{ route('register') }}" class="ml-2 text-blue-600 hover:underline"> Sign Up </a>
        </div>
    </form>
</x-guest-layout>
