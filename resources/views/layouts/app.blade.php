<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Filemai.my - Platform Drama Menegak')</title>
    <meta name="description" content="Tonton siri mini drama menegak terbaik tempatan dengan paparan ala Netflix. Format 9:16 sinematik dioptimumkan untuk telefon pintar.">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#E50914">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind Fallback CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#E50914',
                            redHover: '#F40612',
                            dark: '#0b0c0f',
                            card: '#181818',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        title: ['Bebas Neue', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="/assets/css/style.css?v=4">
</head>
<body class="bg-[#0b0c0f] text-[#e5e5e5] min-h-screen flex flex-col font-sans selection:bg-red-600 selection:text-white pb-20 md:pb-0" x-data="{ searchOpen: false, userMenu: false }">

    <!-- Flash Toast Notification -->
    @if (session('success') || session('info') || session('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-[-10px]"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:exit="transition ease-in duration-200"
             x-transition:exit-start="opacity-100 translate-y-0"
             x-transition:exit-end="opacity-0 translate-y-[-10px]"
             class="fixed top-20 right-4 z-50 max-w-sm w-full p-4 rounded-2xl shadow-2xl backdrop-blur-xl border flex items-start gap-3 
             {{ session('success') ? 'bg-emerald-950/90 border-emerald-500/50 text-emerald-200' : (session('error') ? 'bg-red-950/90 border-red-500/50 text-red-200' : 'bg-slate-900/90 border-white/20 text-slate-200') }}">
            <div class="shrink-0 mt-0.5">
                @if (session('success'))
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                @elseif (session('error'))
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-400"></i>
                @else
                    <i data-lucide="info" class="w-5 h-5 text-blue-400"></i>
                @endif
            </div>
            <div class="flex-1 text-xs font-medium leading-relaxed">
                {{ session('success') ?? session('error') ?? session('info') }}
            </div>
            <button @click="show = false" class="text-white/60 hover:text-white shrink-0 p-1">
                ✕
            </button>
        </div>
    @endif

    <!-- Netflix Style Header Navigation -->
    <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-gradient-to-b from-black/90 via-black/70 to-transparent backdrop-blur-md border-b border-white/[0.08]">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 h-16 sm:h-20 flex items-center justify-between">
            
            <div class="flex items-center gap-6 sm:gap-10">
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <span class="text-2xl sm:text-3xl font-black tracking-wider text-[#E50914] group-hover:scale-105 transition-transform font-['Bebas_Neue','Inter',sans-serif]">FILEMAI</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/30">MY</span>
                </a>

                <!-- Nav Links -->
                <nav class="hidden md:flex items-center gap-6 text-xs sm:text-sm font-semibold text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors {{ request()->routeIs('home') ? 'text-white font-bold' : '' }}">Utama</a>
                    <a href="/?tab=trends" class="hover:text-white transition-colors">Trending</a>
                    <a href="/?tab=popular" class="hover:text-white transition-colors">Popular</a>
                    <a href="/creator/dramas/1/episodes/create" class="hover:text-white text-slate-400 hover:text-white transition-colors">Upload Episod</a>
                </nav>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center gap-2.5 sm:gap-4">
                
                <!-- Search Button -->
                <button @click="searchOpen = !searchOpen" class="p-2 text-slate-300 hover:text-white transition-colors" title="Cari drama">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>

                @auth
                    <!-- Creator Studio Button (Only for Creator or Admin) -->
                    @if (auth()->user()->isCreator())
                        <a href="{{ route('creator.dramas.episodes.create', ['drama' => 1]) }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-bold text-white transition-colors border border-white/10">
                            <i data-lucide="video" class="w-4 h-4 text-red-500"></i>
                            <span>Upload</span>
                        </a>
                    @endif

                    <!-- VIP Subscription Pill (If not premium) -->
                    @if (!auth()->user()->isPremium())
                        <a href="{{ route('subscribe') }}" class="hidden sm:inline-flex px-3.5 py-1.5 rounded-full text-xs font-black bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black shadow-lg shadow-amber-950/40 transition-all items-center gap-1.5 hover:scale-105">
                            <i data-lucide="crown" class="w-3.5 h-3.5 fill-current"></i>
                            <span>VIP RM5</span>
                        </a>
                    @else
                        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <i data-lucide="crown" class="w-3 h-3 fill-current"></i>
                            <span>VIP</span>
                        </span>
                    @endif

                    <!-- User Account Dropdown -->
                    <div class="relative" @click.away="userMenu = false">
                        <button @click="userMenu = !userMenu" 
                                class="flex items-center gap-2 p-1 pl-1.5 pr-2 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 transition-colors focus:outline-none">
                            <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=E50914&color=fff' }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-7 h-7 rounded-full object-cover border border-white/20">
                            <span class="hidden md:inline-block text-xs font-bold text-slate-200 max-w-[100px] truncate">
                                {{ auth()->user()->name }}
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="userMenu" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:exit="transition ease-in duration-100"
                             x-transition:exit-start="opacity-100 scale-100"
                             x-transition:exit-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-[#141414] border border-white/10 shadow-2xl p-2 z-50 text-xs space-y-1">
                            
                            <!-- User Card Header -->
                            <div class="px-3 py-2.5 border-b border-white/10">
                                <p class="font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    @if (auth()->user()->isAdmin())
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase">Super Admin</span>
                                    @elseif (auth()->user()->isCreator())
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-red-500/20 text-red-300 border border-red-500/30 uppercase">Creator</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-slate-800 text-slate-300 border border-white/10 uppercase">Penonton</span>
                                    @endif

                                    @if (auth()->user()->isPremium())
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase flex items-center gap-1">
                                            <i data-lucide="crown" class="w-2.5 h-2.5 fill-current"></i> VIP
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if (auth()->user()->isCreator())
                                <a href="{{ route('creator.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-200 hover:bg-white/10 hover:text-white transition-colors">
                                    <i data-lucide="clapperboard" class="w-4 h-4 text-red-500"></i>
                                    <span>Creator Studio</span>
                                </a>
                                <a href="{{ route('creator.dramas.episodes.create', ['drama' => 1]) }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-200 hover:bg-white/10 hover:text-white transition-colors">
                                    <i data-lucide="plus-circle" class="w-4 h-4 text-red-400"></i>
                                    <span>Upload Episod Baharu</span>
                                </a>
                            @endif

                            @if (!auth()->user()->isPremium())
                                <a href="{{ route('subscribe') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-amber-300 hover:bg-amber-500/10 transition-colors font-medium">
                                    <i data-lucide="crown" class="w-4 h-4 fill-current"></i>
                                    <span>Langgan Pas VIP (RM5)</span>
                                </a>
                            @endif

                            <div class="border-t border-white/10 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors text-left font-semibold">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Log Keluar</span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>

                @else
                    <!-- Guest Actions -->
                    <a href="{{ route('subscribe') }}" class="hidden sm:inline-flex px-3 py-1.5 rounded-full text-xs font-black bg-[#E50914] hover:bg-[#F40612] text-white shadow-lg shadow-red-950/80 transition-all items-center gap-1.5 hover:scale-105">
                        <i data-lucide="crown" class="w-3.5 h-3.5 fill-current"></i>
                        <span>VIP RM5</span>
                    </a>

                    <a href="{{ route('login') }}" 
                       class="text-xs font-bold text-slate-300 hover:text-white px-2.5 py-1.5 transition-colors">
                        Log Masuk
                    </a>

                    <a href="{{ route('register') }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-[#E50914] hover:bg-[#F40612] text-white shadow-md shadow-red-950/60 transition-all hover:scale-105">
                        Daftar
                    </a>
                @endauth

            </div>
        </div>

        <!-- Search Bar Drawer -->
        <div x-show="searchOpen" x-transition.origin.top class="border-t border-white/10 bg-black/95 px-4 sm:px-8 py-3">
            <form action="{{ route('home') }}" method="GET" class="max-w-xl mx-auto flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari tajuk drama, pelakon, atau genre..." class="w-full bg-transparent text-sm text-white focus:outline-none placeholder-slate-500">
                <button type="submit" class="px-3 py-1 rounded bg-[#E50914] text-xs font-bold text-white">Cari</button>
                <button type="button" @click="searchOpen = false" class="text-xs text-slate-400 hover:text-white">✕</button>
            </form>
        </div>
    </header>

    <!-- Main Body Content -->
    <main class="flex-1 pt-16 sm:pt-20">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-[#0b0c0f]/95 backdrop-blur-lg border-t border-white/10 flex items-center justify-around py-2.5 px-4 text-[10px] font-bold text-slate-400">
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('home') ? 'text-[#E50914]' : 'hover:text-white' }}">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span>Utama</span>
        </a>
        <a href="/?tab=trends" class="flex flex-col items-center gap-1 hover:text-white">
            <i data-lucide="trending-up" class="w-5 h-5"></i>
            <span>Trending</span>
        </a>
        @auth
            @if (auth()->user()->isCreator())
                <a href="{{ route('creator.dramas.episodes.create', ['drama' => 1]) }}" class="flex flex-col items-center gap-1 hover:text-white">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-red-500"></i>
                    <span>Upload</span>
                </a>
            @else
                <a href="{{ route('subscribe') }}" class="flex flex-col items-center gap-1 text-amber-400 hover:text-amber-300">
                    <i data-lucide="crown" class="w-5 h-5 fill-current"></i>
                    <span>VIP</span>
                </a>
            @endif
            <button @click="userMenu = !userMenu" class="flex flex-col items-center gap-1 hover:text-white focus:outline-none">
                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=E50914&color=fff' }}" 
                     alt="{{ auth()->user()->name }}" 
                     class="w-5 h-5 rounded-full object-cover border border-white/30">
                <span class="truncate max-w-[45px]">{{ auth()->user()->name }}</span>
            </button>
        @else
            <a href="{{ route('subscribe') }}" class="flex flex-col items-center gap-1 text-amber-400 hover:text-amber-300">
                <i data-lucide="crown" class="w-5 h-5 fill-current"></i>
                <span>VIP</span>
            </a>
            <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 hover:text-white {{ request()->routeIs('login') ? 'text-[#E50914]' : '' }}">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span>Masuk</span>
            </a>
        @endauth
    </nav>

    <!-- Footer -->
    <footer class="border-t border-white/10 bg-[#07080a] py-10 px-4 sm:px-8 text-center text-xs text-slate-500 space-y-3">
        <div class="flex items-center justify-center gap-6 text-slate-400 font-semibold">
            <a href="/terms" class="hover:text-white">Syarat Perkhidmatan</a>
            <span>•</span>
            <a href="/privacy" class="hover:text-white">Dasar Privasi</a>
            <span>•</span>
            <a href="/contact" class="hover:text-white">Hubungi Kami</a>
            <span>•</span>
            <a href="/faq" class="hover:text-white">Soalan Lazim</a>
        </div>
        <p>&copy; {{ date('Y') }} Filemai.my. Hak Cipta Terpelihara. Platform Penstriman Mini Drama Menegak Pertama Malaysia.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
