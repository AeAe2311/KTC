<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- <title>LEN'S INVENTORY</title> --}}
        <title>POSCARE - Groceries by Helpline Management Corp</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/solid.css">
        {{-- <link rel="stylesheet" href="{{asset('assets/css/flowbite.css')}}"> --}}
        {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css"  rel="stylesheet" /> --}}
        <link rel="shortcut icon" href="{{ asset('storage/images/ico/poscare_logo_1.png') }}">


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="{{asset('assets/js/jquery.js')}}"></script>
        <script src="{{asset('assets/js/datepicker.js')}}"></script>
        {{-- <script src="{{asset('assets/js/flowbite.js')}}"></script> --}}
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script> --}}

        <style>
            /* width */
            ::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }
          
            /* Track */
            ::-webkit-scrollbar-track {
                box-shadow: inset 0 0 2px grey; 
                border-radius: 10px;
            }
            
            /* Handle */
            ::-webkit-scrollbar-thumb {
                background: #4B5563; 
                border-radius: 10px;
            }
          
            /* Handle on hover */
            ::-webkit-scrollbar-thumb:hover {
                background: rgb(95, 95, 110); 
            }
        </style>

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @php
                $settings = DB::table('settings')->where('id', 1)->first();
            @endphp
            @if (auth()->user()->role == 1)
                @include('layouts.navigation')
            @elseif(auth()->user()->role == 2 )
                @include('layouts.cashier-navigation')
            @elseif(auth()->user()->role == 3)
                @include('layouts.cook-navigation')
            @elseif(auth()->user()->role == 4)
                @include('layouts.receiver-navigation')
            @elseif(auth()->user()->role == 5)
                @include('layouts.navigation')
            @elseif(auth()->user()->role == 6)
                @include('layouts.cashier-navigation')
            @elseif(auth()->user()->role == 7)
                @include('layouts.cashier-navigation')
            @elseif(auth()->user()->role == 8)
                @include('layouts.navigation')
            @elseif(auth()->user()->role == 9)
                @include('layouts.stock-navigation')
            @endif
            <!-- Page Content -->
            {{ $slot }}

            {{-- Global toasts --}}
            @include('components.toast')

            {{-- Server flash toasts wiring: create a JS var used by the toast component --}}
            <script>
                window.__serverToast = window.__serverToast || [];
                @if(session('message'))
                    window.__serverToast.push({type: 'success', message: {!! json_encode(session('message')) !!}});
                @endif
                @if(session('error'))
                    window.__serverToast.push({type: 'error', message: {!! json_encode(session('error')) !!}});
                @endif
                @if(session('warning'))
                    window.__serverToast.push({type: 'warning', message: {!! json_encode(session('warning')) !!}});
                @endif
                @if(session('info'))
                    window.__serverToast.push({type: 'info', message: {!! json_encode(session('info')) !!}});
                @endif

                @if(session()->has('toasts'))
                    @foreach(session('toasts') as $t)
                        window.__serverToast.push({ type: {!! json_encode($t['type']) !!}, message: {!! json_encode($t['message']) !!}, options: {!! json_encode($t['options'] ?? []) !!} });
                    @endforeach
                @endif

                @php
                    $categoryCount = DB::table('categories')->where('is_deleted', 0)->count();
                    $branchCount = 0;
                    $storeCode = auth()->check() ? auth()->user()->store_code : null;
                    if ($storeCode) {
                        $branchCount = DB::table('settings')->where('is_deleted', 0)->where('store_code', $storeCode)->count();
                    }
                @endphp

                @if(auth()->check() && ($branchCount == 0 || $categoryCount == 0))
                    @php
                        $parts = [];
                        if($branchCount == 0) {
                            $parts[] = '<a class="underline font-semibold" href="'.url('/system-management/settings/add').'">Add Branch</a>';
                        }
                        if($categoryCount == 0) {
                            $parts[] = '<a class="underline font-semibold" href="'.route('inventory.category.add').'">Add Category</a>';
                        }
                        $links = implode(' | ', $parts);
                        $msg = "You must add at least one Branch and Category before creating items or importing CSV. ".($links);
                    @endphp
                    window.__serverToast.push({ type: 'warning', message: {!! json_encode($msg) !!}, options: { duration: 0, allowClose: false, allowHtml: true } });
                @endif
            </script>
        </div>
    </body>
</html>
