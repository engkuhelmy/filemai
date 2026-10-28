<?php
use App\Auth;
$currentUser = Auth::user();
$isPremium = Auth::isPremium();
$role = Auth::role();
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($title ?? 'Filemai - Platform Drama Menegak') ?></title>
    <meta name="description" content="Tonton siri mini drama menegak terbaik tempatan dengan paparan ala Netflix. Nikmati percuma dengan iklan pendek atau langgan VIP RM5/bulan.">
    
    <!-- Google Fonts: Bebas Neue (Netflix style titles) & Inter -->
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
                        netflix: {
                            red: '#E50914',
                            redHover: '#F40612',
                            black: '#0b0c0f',
                            dark: '#141414',
                            card: '#181818',
                            muted: '#808080',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        brand: ['Bebas Neue', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Custom Netflix Theme CSS -->
    <link rel="stylesheet" href="/assets/css/style.css?v=3">
</head>
<body class="bg-[#0b0c0f] text-[#e5e5e5] min-h-screen flex flex-col pb-20 md:pb-0" x-data="{ searchOpen: false }">

    <!-- Netflix Style Header Navigation Bar -->
    <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-gradient-to-b from-black/90 via-black/60 to-transparent backdrop-blur-md border-b border-white/[0.06]">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Left: Brand Logo & Center Navigation -->
                <div class="flex items-center space-x-8 sm:space-x-12">
                    
                    <!-- Netflix Red Brand Logo -->
                    <a href="/" class="flex items-center space-x-1.5 group">
                        <span class="font-['Bebas_Neue'] text-3xl sm:text-4xl tracking-wider text-[#E50914] group-hover:scale-105 transition-transform duration-200">
                            FILEMAI
                        </span>
                    </a>

                    <!-- Center Navigation Links (with active indicator dot beneath) -->
                    <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                        
                        <!-- Home link with active red dot indicator -->
                        <div class="relative flex flex-col items-center">
                            <a href="/" class="transition-colors <?= ($currentUri === '/' || str_contains($currentUri, '?tab=')) ? 'text-white font-bold' : 'text-slate-400 hover:text-slate-200' ?>">
                                Home
                            </a>
                            <?php if ($currentUri === '/' || str_contains($currentUri, '?tab=')): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E50914] mt-1"></span>
                            <?php endif; ?>
                        </div>

                        <!-- Movies / Telefilem -->
                        <div class="relative flex flex-col items-center">
                            <a href="/?genre=Action" class="transition-colors <?= str_contains($currentUri, 'genre=Action') ? 'text-white font-bold' : 'text-slate-400 hover:text-slate-200' ?>">
                                Movies
                            </a>
                            <?php if (str_contains($currentUri, 'genre=Action')): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E50914] mt-1"></span>
                            <?php endif; ?>
                        </div>

                        <!-- Series / Siri Menegak -->
                        <div class="relative flex flex-col items-center">
                            <a href="/?genre=Romance" class="transition-colors <?= str_contains($currentUri, 'genre=Romance') ? 'text-white font-bold' : 'text-slate-400 hover:text-slate-200' ?>">
                                Series
                            </a>
                            <?php if (str_contains($currentUri, 'genre=Romance')): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E50914] mt-1"></span>
                            <?php endif; ?>
                        </div>

                        <!-- My List Dropdown -->
                        <div class="relative flex items-center gap-1 cursor-pointer text-slate-400 hover:text-slate-200" x-data="{ listOpen: false }" @click="listOpen = !listOpen" @click.away="listOpen = false">
                            <span>My List</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>

                            <div x-show="listOpen" class="absolute top-8 left-0 w-48 bg-[#181818] border border-white/10 rounded-xl shadow-2xl p-2 z-50 text-xs" style="display: none;">
                                <a href="/?genre=Romance" class="block px-3 py-2 rounded-lg hover:bg-white/5 text-slate-300 hover:text-white">Drama Kegemaran</a>
                                <a href="/subscribe" class="block px-3 py-2 rounded-lg hover:bg-white/5 text-amber-400">Koleksi VIP Anda</a>
                            </div>
                        </div>

                    </nav>
                </div>

                <!-- Right Side: Search, Notifications, Demo Switcher, User Profile -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    
                    <!-- Search Icon & Expandable Bar -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open; $nextTick(() => $refs.searchInput?.focus())" 
                                class="p-2 text-slate-300 hover:text-white transition-colors">
                            <i data-lucide="search" class="w-4 sm:w-5 h-4 sm:h-5"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 top-12 w-72 sm:w-80 bg-[#181818] border border-white/15 rounded-xl p-2 shadow-2xl z-50"
                             style="display: none;"
                             x-transition>
                            <form action="/" method="GET" class="relative">
                                <input type="text" name="q" x-ref="searchInput" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" 
                                       placeholder="Cari tajuk, pelakon, genre..." 
                                       class="w-full bg-[#111215] text-xs text-white placeholder-slate-500 pl-8 pr-3 py-2 rounded-lg border border-white/10 focus:outline-none focus:border-[#E50914]">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                            </form>
                        </div>
                    </div>

                    <!-- Notification Bell -->
                    <a href="/subscribe" class="p-2 text-slate-300 hover:text-white transition-colors relative" title="Pemberitahuan">
                        <i data-lucide="bell" class="w-4 sm:w-5 h-4 sm:h-5"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#E50914]"></span>
                    </a>

                    <!-- VIP RM5 Badge -->
                    <?php if ($isPremium): ?>
                        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            ★ VIP
                        </span>
                    <?php else: ?>
                        <a href="/subscribe" class="hidden sm:inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-[#E50914] hover:bg-[#F40612] text-white shadow-md shadow-red-900/40 transition-transform hover:scale-105">
                            VIP RM5
                        </a>
                    <?php endif; ?>

                    <!-- Quick Demo Account Switcher (Discreet Netflix styled pill) -->
                    <div class="relative" x-data="{ demoOpen: false }">
                        <button @click="demoOpen = !demoOpen" 
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white/10 hover:bg-white/15 text-slate-200 border border-white/10 flex items-center gap-1.5 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="hidden md:inline">Demo</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </button>

                        <div x-show="demoOpen" @click.away="demoOpen = false" 
                             class="absolute right-0 mt-2 w-64 bg-[#181818] border border-white/15 rounded-xl shadow-2xl p-2 z-50 divide-y divide-white/5"
                             style="display: none;"
                             x-transition>
                            <div class="px-3 py-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tukar Akaun Demo (1-Klik):</p>
                            </div>
                            <div class="py-1">
                                <a href="/demo/switch/1" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs hover:bg-white/5 text-slate-200">
                                    <div class="w-6 h-6 rounded-md bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">A</div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white">Super Admin</span>
                                        <span class="text-[10px] text-slate-400">admin@filemai.my</span>
                                    </div>
                                </a>
                                <a href="/demo/switch/2" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs hover:bg-white/5 text-slate-200">
                                    <div class="w-6 h-6 rounded-md bg-[#E50914]/20 text-[#E50914] flex items-center justify-center font-bold">C</div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white">Sir Helmy (Creator)</span>
                                        <span class="text-[10px] text-slate-400">creator@filemai.my</span>
                                    </div>
                                </a>
                                <a href="/demo/switch/4" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs hover:bg-white/5 text-slate-200">
                                    <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">V</div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white">Ali Penonton (Free)</span>
                                        <span class="text-[10px] text-slate-400">Dengan Iklan 15s</span>
                                    </div>
                                </a>
                                <a href="/demo/switch/5" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs hover:bg-white/5 text-slate-200">
                                    <div class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">★</div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-amber-300">Sara VIP (Premium)</span>
                                        <span class="text-[10px] text-slate-400">Bebas Iklan RM5</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Creator / Admin shortcut links -->
                    <?php if ($role === 'creator' || $role === 'admin'): ?>
                        <a href="/creator" class="hidden lg:flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition-colors">
                            <i data-lucide="video" class="w-3.5 h-3.5 text-[#E50914]"></i>
                            Studio
                        </a>
                    <?php endif; ?>

                    <?php if ($role === 'admin'): ?>
                        <a href="/admin" class="hidden lg:flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-950/60 border border-purple-500/30 text-purple-300 hover:bg-purple-900/60 transition-colors">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-purple-400"></i>
                            Admin
                        </a>
                    <?php endif; ?>

                    <!-- User Circular Avatar Dropdown (Exact Netflix profile style) -->
                    <?php if ($currentUser): ?>
                        <div class="relative" x-data="{ profileOpen: false }">
                            <button @click="profileOpen = !profileOpen" 
                                    class="w-8 sm:w-9 h-8 sm:h-9 rounded-md overflow-hidden bg-gradient-to-tr from-[#E50914] to-red-800 flex items-center justify-center font-bold text-white text-xs shadow hover:ring-2 hover:ring-white transition-all">
                                <?= substr($currentUser['name'], 0, 1) ?>
                            </button>

                            <div x-show="profileOpen" @click.away="profileOpen = false" 
                                 class="absolute right-0 mt-2 w-56 bg-[#181818] border border-white/15 rounded-xl shadow-2xl p-2 z-50 divide-y divide-white/10"
                                 style="display: none;"
                                 x-transition>
                                <div class="px-3 py-2.5">
                                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($currentUser['name']) ?></p>
                                    <p class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                                    <span class="inline-block mt-1 text-[10px] uppercase font-black px-2 py-0.5 rounded bg-white/10 text-slate-300">
                                        <?= $currentUser['role'] ?> <?= $isPremium ? '• VIP' : '' ?>
                                    </span>
                                </div>
                                <div class="py-1">
                                    <?php if ($role === 'creator' || $role === 'admin'): ?>
                                        <a href="/creator" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-300 hover:text-white hover:bg-white/5 rounded-lg">
                                            <i data-lucide="clapperboard" class="w-3.5 h-3.5 text-[#E50914]"></i>
                                            Creator Studio
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($role === 'admin'): ?>
                                        <a href="/admin" class="flex items-center gap-2 px-3 py-2 text-xs text-purple-300 hover:text-white hover:bg-white/5 rounded-lg">
                                            <i data-lucide="shield" class="w-3.5 h-3.5 text-purple-400"></i>
                                            Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <a href="/subscribe" class="flex items-center gap-2 px-3 py-2 text-xs text-amber-300 hover:bg-amber-500/10 rounded-lg">
                                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-400"></i>
                                        Langganan VIP RM5
                                    </a>
                                </div>
                                <div class="py-1">
                                    <a href="/logout" class="flex items-center gap-2 px-3 py-2 text-xs text-red-400 hover:bg-red-500/10 rounded-lg">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                        Log Keluar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="px-4 py-1.5 rounded bg-[#E50914] hover:bg-[#F40612] text-white font-bold text-xs transition-colors">
                            Masuk
                        </a>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </header>

    <!-- Global Alert / Flash Messages -->
    <div class="fixed top-20 right-4 z-50 max-w-md w-full">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-emerald-950/90 border border-emerald-500/40 text-emerald-200 text-xs shadow-2xl backdrop-blur-md mb-2" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    <div><?= $_SESSION['success'] ?></div>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-red-950/90 border border-red-500/40 text-red-200 text-xs shadow-2xl backdrop-blur-md mb-2" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0"></i>
                    <div><?= $_SESSION['error'] ?></div>
                </div>
                <button @click="show = false" class="text-red-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow">
        <?= $content ?>
    </main>

    <!-- Netflix Style Minimal Footer -->
    <footer class="mt-24 border-t border-white/[0.08] bg-[#0b0c0f] text-slate-500 text-xs py-12">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-6">
            <div class="flex items-center space-x-2">
                <span class="font-['Bebas_Neue'] text-2xl tracking-wider text-[#E50914]">FILEMAI</span>
                <span class="text-slate-600">|</span>
                <span class="text-[11px] text-slate-400">Platform Penstriman Drama Mini Menegak</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-[11px] text-slate-400">
                <ul class="space-y-2">
                    <li><a href="/" class="hover:text-white transition-colors">Laman Utama</a></li>
                    <li><a href="/?genre=Romance" class="hover:text-white transition-colors">Drama Siri Romantik</a></li>
                    <li><a href="/?genre=Action" class="hover:text-white transition-colors">Drama Aksi & Dendam</a></li>
                </ul>
                <ul class="space-y-2">
                    <li><a href="/subscribe" class="text-amber-400 hover:text-amber-300">Pas VIP Bebas Iklan (RM5)</a></li>
                    <li><a href="/creator" class="hover:text-white transition-colors">Creator Studio</a></li>
                    <li><a href="/admin" class="hover:text-white transition-colors">Admin Panel</a></li>
                </ul>
                <ul class="space-y-2">
                    <li><a href="/register?role=creator" class="hover:text-white transition-colors">Daftar Pembikin Filem</a></li>
                    <li><a href="/login" class="hover:text-white transition-colors">Log Masuk Penonton</a></li>
                    <li><a href="/subscribe/checkout" class="hover:text-white transition-colors">ToyyibPay FPX Gateway</a></li>
                </ul>
                <ul class="space-y-2">
                    <li><span>MariaDB: <strong class="text-emerald-400">Tersambung</strong></span></li>
                    <li><span>Enjin Iklan: <strong class="text-[#E50914]">15s Countdown</strong></span></li>
                    <li><span>Format: <strong class="text-white">9:16 Menegak</strong></span></li>
                </ul>
            </div>

            <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between text-slate-600 text-[11px]">
                <p>&copy; <?= date('Y') ?> Filemai.my - Rekaan UI Netflix Berprestasi Tinggi. Hak Cipta Terpelihara.</p>
                <p class="mt-2 sm:mt-0">Dikuasakan oleh PHP 8.4 & MariaDB.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation (Netflix Mobile App Style) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-[#121212]/95 backdrop-blur-xl border-t border-white/10 px-6 py-2.5 flex items-center justify-around text-[10px] font-medium text-slate-400">
        <a href="/" class="flex flex-col items-center gap-1 <?= ($currentUri === '/') ? 'text-[#E50914]' : 'hover:text-white' ?>">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span>Home</span>
        </a>
        <a href="/?genre=Action" class="flex flex-col items-center gap-1 hover:text-white">
            <i data-lucide="flame" class="w-5 h-5"></i>
            <span>Trending</span>
        </a>
        <a href="/subscribe" class="flex flex-col items-center gap-1 text-amber-400">
            <div class="relative">
                <i data-lucide="crown" class="w-5 h-5 fill-amber-400"></i>
                <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-[#E50914] animate-ping"></span>
            </div>
            <span>VIP RM5</span>
        </a>
        <?php if ($role === 'creator' || $role === 'admin'): ?>
            <a href="/creator" class="flex flex-col items-center gap-1 hover:text-white">
                <i data-lucide="clapperboard" class="w-5 h-5"></i>
                <span>Studio</span>
            </a>
        <?php else: ?>
            <a href="/creator/drama/new" class="flex flex-col items-center gap-1 hover:text-white">
                <i data-lucide="upload" class="w-5 h-5"></i>
                <span>Upload</span>
            </a>
        <?php endif; ?>
        <?php if ($currentUser): ?>
            <a href="/logout" class="flex flex-col items-center gap-1 text-slate-400">
                <div class="w-5 h-5 rounded bg-[#E50914] flex items-center justify-center text-[10px] font-bold text-white">
                    <?= substr($currentUser['name'], 0, 1) ?>
                </div>
                <span>Profil</span>
            </a>
        <?php else: ?>
            <a href="/login" class="flex flex-col items-center gap-1 hover:text-white">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span>Masuk</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
