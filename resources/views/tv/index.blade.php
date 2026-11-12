<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Filemai TV - Mod Pintar Android TV & Smart TV</title>

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
                        tv: {
                            bg: '#07080a',
                            surface: '#11141b',
                            card: '#181c26',
                            red: '#E50914',
                            redHover: '#F40612',
                            focus: '#E50914'
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

    <style>
        [x-cloak] { display: none !important; }

        html, body {
            background-color: #07080a;
            color: #ffffff;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        /* 10-Foot UI Focus Rings & Remote Navigation Highlights */
        .tv-focusable {
            outline: none;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tv-focusable:focus,
        .tv-focusable:focus-visible {
            transform: scale(1.06);
            z-index: 30;
            box-shadow: 0 0 0 4px #E50914, 0 15px 35px -5px rgba(229, 9, 20, 0.6);
            border-color: rgba(255, 255, 255, 0.8) !important;
        }

        .tv-focusable-btn:focus,
        .tv-focusable-btn:focus-visible {
            transform: scale(1.05);
            background-color: #E50914 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.8);
        }

        /* Hide Scrollbar for Horizontal TV Shelves */
        .tv-shelf::-webkit-scrollbar {
            display: none;
        }
        .tv-shelf {
            -ms-overflow-style: none;
            scrollbar-width: none;
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-tv-bg text-white min-h-screen flex flex-col selection:bg-red-600 pb-20"
      x-data="tvLeanbackApp()"
      @keydown.window="handleTvKeyNavigation($event)">

    <!-- Ambient Hero Background Backdrop -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 opacity-20 filter blur-3xl scale-110"
             :style="'background-image: url(' + activeHeroPoster + ');'"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-tv-bg via-tv-bg/85 to-transparent"></div>
    </div>

    <!-- 10-Foot Top TV Header Navigation -->
    <header class="relative z-20 px-8 lg:px-16 pt-8 pb-4 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <!-- Brand Logo -->
            <a href="{{ route('tv.index') }}" class="tv-focusable flex items-center gap-3 rounded-xl p-1">
                <span class="text-3xl lg:text-4xl font-black tracking-wider text-[#E50914] font-title">FILEMAI</span>
                <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-red-600/30 text-red-400 border border-red-500/50 flex items-center gap-1">
                    <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                    <span>TV MODE</span>
                </span>
            </a>

            <!-- Quick Nav Pills -->
            <nav class="hidden md:flex items-center gap-3 text-sm font-bold text-slate-300">
                <button class="tv-focusable-btn px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-black" tabindex="0">
                    Semua
                </button>
                <a href="#sambung-tonton" class="tv-focusable-btn px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300" tabindex="0">
                    Sambung Tonton
                </a>
                <a href="#trending" class="tv-focusable-btn px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300" tabindex="0">
                    Trending
                </a>
                <a href="#genre-list" class="tv-focusable-btn px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300" tabindex="0">
                    Koleksi Genre
                </a>
            </nav>
        </div>

        <!-- TV System Controls: Clock, Web Mode Shortcut -->
        <div class="flex items-center gap-4">
            <!-- TV Clock -->
            <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 font-mono text-sm font-bold flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-red-500"></i>
                <span x-text="currentTime">--:--</span>
            </div>

            <!-- Switch to Web Portal -->
            <a href="{{ route('home') }}" 
               class="tv-focusable-btn px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white text-xs font-bold border border-white/10 flex items-center gap-1.5"
               tabindex="0"
               title="Beralih ke paparan Web biasa">
                <i data-lucide="globe" class="w-4 h-4"></i>
                <span>Keluar ke Web</span>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="relative z-10 flex-1 space-y-12 pb-16">

        <!-- ======================================================== -->
        <!-- FEATURED HERO BILLBOARD CAROUSEL (BIG TV HIGHLIGHT)      -->
        <!-- ======================================================== -->
        @if ($featuredDramas->isNotEmpty())
            @php $hero = $featuredDramas->first(); @endphp
            <section class="relative px-8 lg:px-16 pt-4">
                <div class="relative w-full rounded-3xl overflow-hidden min-h-[380px] lg:min-h-[460px] flex items-end p-8 lg:p-14 border border-white/10 shadow-2xl">
                    <!-- Billboard Poster Image -->
                    <img src="{{ $hero->banner_url ?: $hero->poster_url }}" 
                         alt="{{ $hero->title }}"
                         class="absolute inset-0 w-full h-full object-cover object-center filter brightness-[0.75] contrast-105">
                    
                    <!-- Dark Gradients for 10-Foot Readability -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent"></div>

                    <!-- Billboard Info -->
                    <div class="relative z-10 max-w-2xl space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-md bg-[#E50914] text-white text-xs font-black uppercase tracking-wider">
                                🔥 Pilihan Utama TV
                            </span>
                            <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-xs font-bold">
                                {{ $hero->genre }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-black/60 text-slate-300 text-xs font-bold border border-white/10">
                                {{ $hero->age_rating ?? '13+' }}
                            </span>
                            <span class="text-xs text-slate-300 font-semibold">
                                {{ $hero->episodes_count }} Episod Lengkap
                            </span>
                        </div>

                        <h1 class="text-3xl lg:text-5xl font-black text-white font-title tracking-wide leading-tight drop-shadow-md">
                            {{ $hero->title }}
                        </h1>

                        <p class="text-sm lg:text-base text-slate-300 line-clamp-3 leading-relaxed max-w-xl">
                            {{ $hero->description ?: 'Tonton siri mini drama terhangat Filemai. Siri episod pendek penuh dengan drama, intrik korporat, cinta dan pembalasan dendam.' }}
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-4 pt-2">
                            <a href="{{ route('tv.watch', ['slug' => $hero->slug, 'episode' => 1]) }}"
                               class="tv-focusable-btn px-6 py-3.5 rounded-2xl bg-[#E50914] hover:bg-[#F40612] text-white font-black text-sm shadow-xl shadow-red-950/80 flex items-center gap-2"
                               tabindex="0"
                               autofocus>
                                <i data-lucide="play" class="w-5 h-5 fill-white"></i>
                                <span>Tonton Sekarang</span>
                            </a>

                            <a href="{{ route('drama.show', $hero->slug) }}"
                               class="tv-focusable-btn px-5 py-3.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white font-bold text-sm backdrop-blur-md border border-white/15 flex items-center gap-2"
                               tabindex="0">
                                <i data-lucide="info" class="w-5 h-5"></i>
                                <span>Info Drama</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- ======================================================== -->
        <!-- ROW 1: SAMBUNG TONTON (RESUME PLAYBACK)                  -->
        <!-- ======================================================== -->
        @if ($continueWatching->isNotEmpty())
            <section id="sambung-tonton" class="px-8 lg:px-16 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="history" class="w-5 h-5 text-red-500"></i>
                        <h2 class="text-xl lg:text-2xl font-black tracking-wide text-white font-title">
                            SAMBUNG TONTON
                        </h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Bermula dari saat terakhir anda</span>
                </div>

                <!-- Horizontal Scrollable Shelf -->
                <div class="tv-shelf flex gap-5 overflow-x-auto pb-4 pt-2 -mx-2 px-2" tabindex="-1">
                    @foreach ($continueWatching as $p)
                        <a href="{{ route('tv.watch', ['slug' => $p->drama->slug, 'episode' => $p->episode->episode_number]) }}"
                           class="tv-focusable group relative shrink-0 w-64 lg:w-72 rounded-2xl bg-tv-card border border-white/10 overflow-hidden cursor-pointer shadow-lg"
                           tabindex="0"
                           @focus="setActiveHero('{{ $p->drama->banner_url ?: $p->drama->poster_url }}')">
                            
                            <!-- Thumbnail / Banner -->
                            <div class="relative aspect-video w-full overflow-hidden bg-black">
                                <img src="{{ $p->episode->thumbnail_url ?: ($p->drama->banner_url ?: $p->drama->poster_url) }}" 
                                     alt="{{ $p->drama->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                                
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity bg-black/40">
                                    <div class="w-12 h-12 rounded-full bg-[#E50914] text-white flex items-center justify-center shadow-lg">
                                        <i data-lucide="play" class="w-6 h-6 fill-white ml-0.5"></i>
                                    </div>
                                </div>

                                <!-- Episode Badge -->
                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-md text-[10px] font-black text-white border border-white/15">
                                    Ep {{ $p->episode->episode_number }}
                                </div>

                                <!-- Timestamp Pill -->
                                <div class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-black/80 text-[10px] font-mono text-red-400 font-bold">
                                    {{ gmdate('i:s', $p->progress_seconds) }}
                                </div>

                                <!-- Progress Percentage Bar -->
                                <div class="absolute bottom-0 inset-x-0 h-1.5 bg-white/20">
                                    <div class="h-full bg-[#E50914]" style="width: {{ $p->percentage }}%;"></div>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="p-3.5 space-y-1">
                                <h3 class="text-xs font-bold text-white truncate group-hover:text-red-400 transition-colors">
                                    {{ $p->drama->title }}
                                </h3>
                                <p class="text-[11px] text-slate-400 truncate">
                                    {{ $p->episode->title }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- ======================================================== -->
        <!-- ROW 2: TRENDING SEKARANG                                -->
        <!-- ======================================================== -->
        @if ($trendingDramas->isNotEmpty())
            <section id="trending" class="px-8 lg:px-16 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="flame" class="w-5 h-5 text-red-500"></i>
                        <h2 class="text-xl lg:text-2xl font-black tracking-wide text-white font-title">
                            TRENDING SEKARANG DI FILEMAI
                        </h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Paling banyak ditonton minggu ini</span>
                </div>

                <div class="tv-shelf flex gap-5 overflow-x-auto pb-4 pt-2 -mx-2 px-2" tabindex="-1">
                    @foreach ($trendingDramas as $drama)
                        <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => 1]) }}"
                           class="tv-focusable group relative shrink-0 w-44 lg:w-52 rounded-2xl bg-tv-card border border-white/10 overflow-hidden cursor-pointer shadow-lg"
                           tabindex="0"
                           @focus="setActiveHero('{{ $drama->banner_url ?: $drama->poster_url }}')">
                            
                            <!-- 9:16 Vertical Poster -->
                            <div class="relative aspect-[9/14] w-full overflow-hidden bg-black">
                                <img src="{{ $drama->poster_url }}" 
                                     alt="{{ $drama->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                                <!-- Rank Badge -->
                                <div class="absolute top-2 left-2 w-7 h-7 rounded-lg bg-red-600/90 backdrop-blur-md text-white font-black text-xs flex items-center justify-center shadow">
                                    #{{ $loop->iteration }}
                                </div>

                                <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-[10px] text-slate-300 font-bold">
                                    <span class="px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-md border border-white/10">
                                        {{ $drama->episodes_count }} Ep
                                    </span>
                                    <span class="text-red-400">
                                        {{ number_format($drama->views_count) }} tontonan
                                    </span>
                                </div>
                            </div>

                            <div class="p-3">
                                <h3 class="text-xs font-bold text-white truncate group-hover:text-red-400 transition-colors">
                                    {{ $drama->title }}
                                </h3>
                                <p class="text-[10px] text-slate-400 truncate mt-0.5">
                                    {{ $drama->genre }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- ======================================================== -->
        <!-- ROW 3: RECENT / LATEST RELEASES                          -->
        <!-- ======================================================== -->
        @if ($latestDramas->isNotEmpty())
            <section class="px-8 lg:px-16 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="sparkles" class="w-5 h-5 text-amber-400"></i>
                        <h2 class="text-xl lg:text-2xl font-black tracking-wide text-white font-title">
                            KANDUNGAN TERKINI
                        </h2>
                    </div>
                </div>

                <div class="tv-shelf flex gap-5 overflow-x-auto pb-4 pt-2 -mx-2 px-2" tabindex="-1">
                    @foreach ($latestDramas as $drama)
                        <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => 1]) }}"
                           class="tv-focusable group relative shrink-0 w-44 lg:w-52 rounded-2xl bg-tv-card border border-white/10 overflow-hidden cursor-pointer shadow-lg"
                           tabindex="0"
                           @focus="setActiveHero('{{ $drama->banner_url ?: $drama->poster_url }}')">
                            
                            <div class="relative aspect-[9/14] w-full overflow-hidden bg-black">
                                <img src="{{ $drama->poster_url }}" 
                                     alt="{{ $drama->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                                <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[9px] font-black uppercase">
                                    BARU
                                </div>

                                <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-[10px] text-slate-300 font-bold">
                                    <span class="px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-md border border-white/10">
                                        {{ $drama->episodes_count }} Ep
                                    </span>
                                    <span>{{ $drama->genre }}</span>
                                </div>
                            </div>

                            <div class="p-3">
                                <h3 class="text-xs font-bold text-white truncate group-hover:text-red-400 transition-colors">
                                    {{ $drama->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- ======================================================== -->
        <!-- ROWS 4+: DYNAMIC GENRE SHELVES                           -->
        <!-- ======================================================== -->
        @foreach ($genreDramas as $genreTitle => $items)
            <section id="genre-list" class="px-8 lg:px-16 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl lg:text-2xl font-black tracking-wide text-white font-title flex items-center gap-2">
                        <span class="w-2 h-5 rounded bg-red-600"></span>
                        <span>KOLEKSI {{ strtoupper($genreTitle) }}</span>
                    </h2>
                </div>

                <div class="tv-shelf flex gap-5 overflow-x-auto pb-4 pt-2 -mx-2 px-2" tabindex="-1">
                    @foreach ($items as $drama)
                        <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => 1]) }}"
                           class="tv-focusable group relative shrink-0 w-44 lg:w-52 rounded-2xl bg-tv-card border border-white/10 overflow-hidden cursor-pointer shadow-lg"
                           tabindex="0"
                           @focus="setActiveHero('{{ $drama->banner_url ?: $drama->poster_url }}')">
                            
                            <div class="relative aspect-[9/14] w-full overflow-hidden bg-black">
                                <img src="{{ $drama->poster_url }}" 
                                     alt="{{ $drama->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                                <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-[10px] text-slate-300 font-bold">
                                    <span class="px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-md border border-white/10">
                                        {{ $drama->episodes_count }} Ep
                                    </span>
                                </div>
                            </div>

                            <div class="p-3">
                                <h3 class="text-xs font-bold text-white truncate group-hover:text-red-400 transition-colors">
                                    {{ $drama->title }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

    </main>

    <!-- TV Navigation Remote Control Legend / Footer -->
    <footer class="fixed bottom-0 inset-x-0 z-30 px-8 py-3 bg-black/90 backdrop-blur-xl border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-5">
            <span class="flex items-center gap-1.5 font-medium">
                <kbd class="px-2 py-0.5 rounded bg-white/15 text-white font-mono text-[11px] border border-white/20">▲ ▼ ◄ ►</kbd>
                <span>Navigasi D-Pad</span>
            </span>
            <span class="flex items-center gap-1.5 font-medium">
                <kbd class="px-2.5 py-0.5 rounded bg-[#E50914] text-white font-bold text-[11px]">OK / ENTER</kbd>
                <span>Pilih / Tonton</span>
            </span>
            <span class="flex items-center gap-1.5 font-medium">
                <kbd class="px-2 py-0.5 rounded bg-white/15 text-white font-mono text-[11px] border border-white/20">BACK / ESC</kbd>
                <span>Kembali</span>
            </span>
        </div>

        <div class="flex items-center gap-2 text-[11px] text-slate-500">
            <span>Filemai Leanback TV v2.1</span>
            <span>•</span>
            <span class="text-red-500 font-bold">Android TV & Smart TV Ready</span>
        </div>
    </footer>

    <!-- Alpine.js TV Controller & D-Pad Key Listener -->
    <script>
        function tvLeanbackApp() {
            return {
                currentTime: '',
                activeHeroPoster: '{{ $featuredDramas->isNotEmpty() ? ($featuredDramas->first()->banner_url ?: $featuredDramas->first()->poster_url) : "" }}',

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                        // Auto-focus first focusable element if none focused
                        const firstFocusable = document.querySelector('.tv-focusable-btn, .tv-focusable');
                        if (firstFocusable && !document.activeElement.classList.contains('tv-focusable')) {
                            firstFocusable.focus();
                        }
                    });
                },

                updateTime() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    this.currentTime = `${hours}:${minutes}`;
                },

                setActiveHero(poster) {
                    if (poster) {
                        this.activeHeroPoster = poster;
                    }
                },

                handleTvKeyNavigation(e) {
                    // D-Pad Remote Control Mapping
                    const key = e.key;
                    const focusables = Array.from(document.querySelectorAll('.tv-focusable, .tv-focusable-btn, [tabindex="0"]'));
                    const currentIndex = focusables.indexOf(document.activeElement);

                    if (key === 'ArrowRight') {
                        if (currentIndex !== -1 && currentIndex < focusables.length - 1) {
                            e.preventDefault();
                            focusables[currentIndex + 1].focus();
                            focusables[currentIndex + 1].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        }
                    } else if (key === 'ArrowLeft') {
                        if (currentIndex > 0) {
                            e.preventDefault();
                            focusables[currentIndex - 1].focus();
                            focusables[currentIndex - 1].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        }
                    } else if (key === 'Escape' || key === 'Backspace') {
                        // TV Back button
                        if (window.history.length > 1) {
                            window.history.back();
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
