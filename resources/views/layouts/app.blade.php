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
                    <a href="/" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->is('/') ? 'bg-blue-900 text-white' : '' }}">Dashboard</a>
                    <a href="/descargas" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->is('descargas*') ? 'bg-blue-900 text-white' : '' }}">Descargar XML</a>
                    <a href="/boveda" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->is('boveda*') ? 'bg-blue-900 text-white' : '' }}">Bóveda XML</a>
                    <a href="/proveedores" class="text-gray-300 hover:bg-blue-800 hover:text-white rounded-md px-3 py-2 text-sm font-medium transition duration-150 {{ request()->is('proveedores*') ? 'bg-blue-900 text-white' : '' }}">Proveedores</a>
                </div>

                <!-- Right Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-3 p-1 rounded-xl hover:bg-white/10 transition duration-150 focus:outline-none border border-transparent hover:border-blue-400/30">
                            <!-- User Icon / Avatar -->
                            <div class="h-9 w-9 rounded-full bg-blue-600 flex items-center justify-center text-white border-2 border-blue-400/30 shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            
                            <div class="flex flex-col items-start sr-only lg:not-sr-only">
                                <span class="text-sm font-bold text-white leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-blue-300 leading-tight">{{ auth()->user()->email }}</span>
                            </div>

                            <!-- Arrow -->
                            <svg class="w-4 h-4 text-blue-300 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 mt-3 w-64 rounded-2xl bg-white shadow-2xl border border-blue-100/20 py-2 z-50 overflow-hidden" 
                             x-cloak>
                            
                            <!-- Header / Plan Info -->
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                <div class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mb-1">Plan contratado</div>
                                <div class="flex items-center gap-2">
                                    @if(auth()->user()->plan_type === 'paid')
                                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-green-100 border border-green-200">
                                            <span class="bg-green-500 w-1.5 h-1.5 rounded-full animate-pulse"></span>
                                            <span class="text-[10px] font-bold text-green-700 uppercase">Premium</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-100 border border-amber-200">
                                            <span class="bg-amber-500 w-1.5 h-1.5 rounded-full"></span>
                                            <span class="text-[10px] font-bold text-amber-700 uppercase">Gratuito</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-1.5 space-y-0.5">
                                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition group">
                                    <div class="p-1.5 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition">
                                        <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="font-medium">Mi Perfil</span>
                                </a>
                                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition group">
                                    <div class="p-1.5 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition">
                                        <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <span class="font-medium">Configuración</span>
                                </a>
                            </div>

                            <!-- Logout -->
                            <div class="p-1.5 border-t border-gray-100">
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition group">
                                        <div class="p-1.5 rounded-lg bg-red-50 group-hover:bg-red-100 transition">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        </div>
                                        <span class="font-bold">Cerrar Sesión</span>
                                    </button>
                                </form>
                            </div>
                        </div>
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
            
            <!-- User Info Mobile -->
            <div class="px-4 py-4 border-b border-blue-800/50 bg-blue-950/30">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center text-white border-2 border-blue-400/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-base font-bold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-blue-300">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    @if(auth()->user()->plan_type === 'paid')
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-500/10 border border-green-500/20">
                            <span class="bg-green-500 w-2 h-2 rounded-full animate-pulse"></span>
                            <span class="text-xs font-bold text-green-400 uppercase">Plan Premium</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20">
                            <span class="bg-amber-500 w-2 h-2 rounded-full"></span>
                            <span class="text-xs font-bold text-amber-400 uppercase">Plan Gratuito</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                <a href="#" class="bg-blue-950 text-white block rounded-md px-3 py-2 text-base font-medium">Dashboard</a>
                <a href="{{ route('download.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Descargar XML</a>
                <a href="{{ route('boveda.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Bóveda XML</a>
                <a href="{{ route('proveedores.index') }}" class="text-gray-300 hover:bg-blue-800 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Proveedores</a>
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

    <script>
        // Heartbeat para mantener la sesión viva en la app de escritorio
        setInterval(function() {
            fetch('{{ route("dashboard") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                // Si la respuesta fue redirigida (ej. al login), recargamos la página
                if (response.redirected) {
                    window.location.href = '{{ route("login") }}';
                }
            }).catch(err => console.error('Keep-alive failed', err));
        }, 15 * 60 * 1000); // Cada 15 minutos
    </script>
</body>
</html>
