<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Filemai.my - Mini-Drama Menegak')</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0b0c0f">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="bg-[#0b0c0f] text-white min-h-screen flex flex-col font-sans selection:bg-red-600 selection:text-white">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 bg-[#0b0c0f]/90 backdrop-blur-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 font-['Bebas_Neue','Inter',sans-serif]">FILEMAI</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-red-600/20 text-red-400 border border-red-500/30">MY</span>
                </a>
            </div>

            <div class="flex items-center gap-4">
                <a href="/creator" class="text-xs font-bold text-slate-300 hover:text-white transition-colors">
                    Creator Studio
                </a>
                <a href="/subscribe" class="px-4 py-2 rounded-full text-xs font-black bg-red-600 hover:bg-red-500 text-white shadow-lg shadow-red-950/80 transition-all flex items-center gap-1.5">
                    <span>VIP RM5</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 bg-[#07080a] py-8 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Filemai.my. Hak Cipta Terpelihara. Platform Penstriman Mini Drama Menegak Malaysia.</p>
    </footer>

</body>
</html>
