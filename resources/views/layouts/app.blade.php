<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Finanzas Personales') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-nav {
            background: rgba(30, 58, 138, 0.95);
            backdrop-filter: blur(10px);
        }
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col">
    
    <!-- Navbar -->
    <nav class="glass-nav text-white shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 bg-blue-500 rounded-lg p-1.5 shadow-inner">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg tracking-tight">ASES - Finanzas personales</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->routeIs('home') ? 'bg-blue-900 text-white' : '' }}">Dashboard</a>
                    <a href="{{ route('download.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->routeIs('download*') ? 'bg-blue-900 text-white' : '' }}">Descargar XML</a>
                    <a href="{{ route('boveda.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->routeIs('boveda*') ? 'bg-blue-900 text-white' : '' }}">Bóveda XML</a>
                    <a href="#" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150">Configuración</a>
                </div>

                <!-- Right Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- License Badge -->
                    <div class="flex flex-col items-end mr-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-blue-300">Plan actual</span>
                        <div class="flex items-center gap-1.5">
                            @if(auth()->user()->plan_type === 'paid')
                                <span class="bg-green-500 w-2 h-2 rounded-full animate-pulse"></span>
                                <span class="text-xs font-bold text-white uppercase">Premium</span>
                            @else
                                <span class="bg-yellow-500 w-2 h-2 rounded-full"></span>
                                <span class="text-xs font-bold text-white uppercase">Gratuito</span>
                            @endif
                        </div>
                    </div>

                    <div class="h-8 w-px bg-blue-800 mx-2"></div>

                    <!-- User Profile & Logout -->
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-bold text-white">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-blue-300">{{ auth()->user()->email }}</span>
                        </div>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500/20 hover:bg-red-500/40 text-red-100 px-4 py-1.5 rounded-full text-sm font-medium transition flex items-center gap-2 border border-red-500/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Salir
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-300 hover:text-white p-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-blue-900 border-t border-blue-800"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                <a href="#" class="bg-blue-950 text-white block rounded-md px-3 py-2 text-base font-medium">Dashboard</a>
                <a href="{{ route('download.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Descargar XML</a>
                <a href="#" class="text-gray-300 hover:bg-blue-800 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Reportes</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-red-300 hover:bg-red-900/30 block rounded-md px-3 py-2 text-base font-medium mt-4">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-start p-6 overflow-y-auto w-full max-w-7xl mx-auto fade-in">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
            <div class="flex justify-center space-x-6 md:order-2">
                <p class="text-center text-xs leading-5 text-gray-500">v1.0.0 &bull; Conectado a SAT Webservice</p>
            </div>
            <div class="mt-4 md:order-1 md:mt-0">
                <p class="text-center text-xs leading-5 text-gray-400">&copy; {{ date('Y') }} Finanzas Personales App. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    
    @stack('scripts')
</body>
</html>
