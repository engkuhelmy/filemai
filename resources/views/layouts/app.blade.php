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
<body class="bg-[#0b0c0f] text-[#e5e5e5] min-h-screen flex flex-col font-sans selection:bg-red-600 selection:text-white pb-20 md:pb-0" x-data="{ searchOpen: false }">

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
                    <a href="{{ route('creator.dashboard') }}" class="hover:text-white text-slate-400 transition-colors {{ request()->routeIs('creator.*') ? 'text-white font-bold' : '' }}">Studio Creator</a>
                </nav>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center gap-3 sm:gap-5">
                
                <!-- Search Button -->
                <button @click="searchOpen = !searchOpen" class="p-2 text-slate-300 hover:text-white transition-colors" title="Cari drama">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>

                <!-- Creator Studio Button -->
                <a href="{{ route('creator.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-bold text-white transition-colors border border-white/10">
                    <i data-lucide="video" class="w-4 h-4 text-red-500"></i>
                    <span>Studio Creator</span>
                </a>

                <!-- VIP Subscription Pill -->
                <a href="/subscribe" class="px-4 py-2 rounded-full text-xs font-black bg-[#E50914] hover:bg-[#F40612] text-white shadow-lg shadow-red-950/80 transition-all flex items-center gap-1.5 hover:scale-105">
                    <i data-lucide="crown" class="w-3.5 h-3.5 fill-current"></i>
                    <span>VIP RM5</span>
                </a>
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
        <a href="{{ route('creator.dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('creator.*') ? 'text-[#E50914]' : 'hover:text-white' }}">
            <i data-lucide="video" class="w-5 h-5"></i>
            <span>Creator</span>
        </a>
        <a href="/subscribe" class="flex flex-col items-center gap-1 text-amber-400 hover:text-amber-300">
            <i data-lucide="crown" class="w-5 h-5 fill-current"></i>
            <span>VIP</span>
        </a>
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
