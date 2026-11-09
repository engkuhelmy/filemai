@extends('layouts.app')

@section('title', 'Admin Analytics Dashboard - Filemai.my')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-8 space-y-8">

    <!-- Top Executive Header & Timeframe Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border-b border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-widest flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-purple-400"></i>
                    SUPER ADMIN DASHBOARD
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Statistik Sebenar Langsung
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1.5">
                Pusat Analitik & Pengurusan Platform
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                Metrik tontonan sebenar, analitik penonton, hasil langganan VIP, dan pemantauan kandungan Filemai.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Timeframe Filter Buttons -->
            <div class="p-1 rounded-2xl bg-slate-900 border border-white/10 flex items-center text-xs font-bold text-slate-400">
                <a href="{{ route('admin.dashboard', ['range' => 'today']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-all {{ $timeframe === 'today' ? 'bg-[#E50914] text-white shadow-md shadow-red-950/80 font-black' : 'hover:text-white' }}">
                    Hari Ini
                </a>
                <a href="{{ route('admin.dashboard', ['range' => '7days']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-all {{ $timeframe === '7days' ? 'bg-[#E50914] text-white shadow-md shadow-red-950/80 font-black' : 'hover:text-white' }}">
                    7 Hari
                </a>
                <a href="{{ route('admin.dashboard', ['range' => '30days']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-all {{ $timeframe === '30days' ? 'bg-[#E50914] text-white shadow-md shadow-red-950/80 font-black' : 'hover:text-white' }}">
                    30 Hari
                </a>
                <a href="{{ route('admin.dashboard', ['range' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-all {{ $timeframe === 'all' ? 'bg-[#E50914] text-white shadow-md shadow-red-950/80 font-black' : 'hover:text-white' }}">
                    Semua
                </a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('creator.dramas.create') }}" 
                   class="px-4 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-white text-xs font-black shadow-lg shadow-red-950/80 transition-all hover:scale-105 flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Cipta Drama</span>
                </a>
                <a href="{{ route('creator.dashboard') }}" 
                   class="px-3.5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs font-bold border border-white/10 transition-colors flex items-center gap-1.5">
                    <i data-lucide="clapperboard" class="w-4 h-4 text-red-500"></i>
                    <span>Creator Studio</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 5 Master KPI Executive Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- KPI 1: Total Real Views -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-red-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tontonan Sebenar</span>
                <div class="w-10 h-10 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center">
                    <i data-lucide="eye" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ number_format($totalRealViews) }}
                </h3>
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="text-emerald-400 font-bold flex items-center gap-0.5">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        +{{ number_format($viewsToday) }} hari ini
                    </span>
                    <span class="text-slate-500">• disahkan</span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-red-600 to-red-400 opacity-40"></div>
        </div>

        <!-- KPI 2: Unique Viewers -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-cyan-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penonton Unik</span>
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ number_format($uniqueViewers) }}
                </h3>
                <div class="flex items-center gap-1.5 text-[11px] text-cyan-400 font-semibold">
                    <i data-lucide="fingerprint" class="w-3.5 h-3.5"></i>
                    <span>{{ $uniqueViewersToday }} pelawat unik hari ini</span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-cyan-600 to-cyan-400 opacity-40"></div>
        </div>

        <!-- KPI 3: VIP Revenue -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hasil Langganan VIP</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i data-lucide="crown" class="w-5 h-5 fill-current"></i>
                </div>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-2xl sm:text-3xl font-black text-amber-300 tracking-tight">
                    RM {{ number_format($totalRevenue, 2) }}
                </h3>
                <div class="flex items-center gap-1.5 text-[11px] text-amber-400 font-semibold">
                    <span>{{ $successfulSubsCount }} transaksi berjaya • RM5/bln</span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-amber-600 to-amber-400 opacity-40"></div>
        </div>

        <!-- KPI 4: VIP Conversion & Users -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-purple-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kadar Ahli VIP</span>
                <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ $vipConversionRate }}%
                </h3>
                <div class="text-[11px] text-purple-300 font-medium">
                    {{ $vipUsersCount }} ahli VIP daripada {{ $totalUsers }} pengguna
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-purple-600 to-purple-400 opacity-40"></div>
        </div>

        <!-- KPI 5: Content Catalog -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kandungan Aktif</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="film" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ $totalDramas }} Siri
                </h3>
                <div class="text-[11px] text-slate-400 flex items-center gap-1">
                    <span>{{ $activeEpisodes }} episod aktif • {{ $comingSoonEpisodes }} Coming Soon</span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-600 to-emerald-400 opacity-40"></div>
        </div>

    </div>

    <!-- Visual Analytics Charts Row (3 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Chart 1: Daily Views Trend (8 Cols) -->
        <div class="lg:col-span-8 rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-7 shadow-xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-red-500"></i>
                        <span>Trend Tontonan Harian Sebenar (14 Hari)</span>
                    </h2>
                    <p class="text-xs text-slate-400">Statistik sebenar setiap hari bagi tontonan video episod menegak.</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5 text-slate-300">
                        <span class="w-3 h-3 rounded bg-red-600"></span>
                        Jumlah Tontonan
                    </span>
                    <span class="flex items-center gap-1.5 text-amber-300">
                        <span class="w-3 h-3 rounded bg-amber-500"></span>
                        Tontonan VIP
                    </span>
                </div>
            </div>

            <!-- Visual Bar Chart -->
            <div class="h-64 sm:h-72 flex items-end justify-between gap-2 sm:gap-3 pt-6 border-b border-white/10 px-2">
                @foreach ($dailyTrend as $day)
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end group relative">
                        
                        <!-- Tooltip Popup -->
                        <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-30 pointer-events-none">
                            <div class="bg-black/95 text-white text-[10px] py-1.5 px-3 rounded-xl shadow-2xl border border-white/15 whitespace-nowrap space-y-0.5">
                                <p class="font-bold text-red-400">{{ $day['date'] }} ({{ $day['day'] }})</p>
                                <p>Jumlah: <strong>{{ $day['total'] }}</strong> tontonan</p>
                                <p class="text-amber-400">VIP: <strong>{{ $day['vip'] }}</strong></p>
                                <p class="text-slate-400">Percuma: <strong>{{ $day['free'] }}</strong></p>
                            </div>
                            <div class="w-2 h-2 bg-black border-r border-b border-white/15 transform rotate-45 -mt-1"></div>
                        </div>

                        <!-- Stacked Bar Column -->
                        <div class="w-full max-w-[28px] rounded-t-lg overflow-hidden bg-slate-800 transition-all duration-300 group-hover:brightness-125 flex flex-col justify-end"
                             style="height: {{ $day['height_percent'] }}%;">
                            <div class="bg-red-600 w-full flex-1 transition-all"></div>
                            @if ($day['vip'] > 0)
                                <div class="bg-amber-500 w-full" style="height: {{ round(($day['vip'] / max(1, $day['total'])) * 100) }}%;"></div>
                            @endif
                        </div>

                        <!-- Date Label below bar -->
                        <span class="text-[10px] font-semibold text-slate-400 group-hover:text-white transition-colors truncate max-w-[35px]">
                            {{ $day['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Footer Insight -->
            <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                <span>Puncak Tertinggi: <strong>{{ $maxDailyViews }} tontonan/hari</strong></span>
                <span class="text-emerald-400 font-semibold flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    Data disahkan dari rekod tontonan langsung
                </span>
            </div>
        </div>

        <!-- Chart 2: Genre Breakdown & Device Stats (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Genre Breakdown Card -->
            <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-purple-400"></i>
                        <span>Tontonan Mengikut Genre</span>
                    </h2>
                    <span class="text-xs text-slate-400">{{ count($genreShare) }} Genre</span>
                </div>

                <div class="space-y-3 pt-2">
                    @foreach ($genreShare as $gs)
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white">{{ $gs['genre'] }}</span>
                                <span class="text-slate-400 font-mono">{{ number_format($gs['count']) }} ({{ $gs['percent'] }}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-red-600 to-purple-600" 
                                     style="width: {{ $gs['percent'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Device Distribution Card -->
            <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="smartphone" class="w-4 h-4 text-cyan-400"></i>
                        <span>Pecahan Peranti Penonton</span>
                    </h2>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-md">
                        9:16 Menegak
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-2.5 pt-1 text-center">
                    <div class="p-3 rounded-2xl bg-slate-950 border border-white/5 space-y-1">
                        <i data-lucide="smartphone" class="w-5 h-5 text-red-500 mx-auto"></i>
                        <p class="text-[11px] text-slate-400">Mobile</p>
                        <p class="text-base font-black text-white">{{ $deviceStats['mobile']['percent'] }}%</p>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $deviceStats['mobile']['count'] }} view</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-white/5 space-y-1">
                        <i data-lucide="monitor" class="w-5 h-5 text-cyan-400 mx-auto"></i>
                        <p class="text-[11px] text-slate-400">Desktop</p>
                        <p class="text-base font-black text-white">{{ $deviceStats['desktop']['percent'] }}%</p>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $deviceStats['desktop']['count'] }} view</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-white/5 space-y-1">
                        <i data-lucide="tablet" class="w-5 h-5 text-purple-400 mx-auto"></i>
                        <p class="text-[11px] text-slate-400">Tablet</p>
                        <p class="text-base font-black text-white">{{ $deviceStats['tablet']['percent'] }}%</p>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $deviceStats['tablet']['count'] }} view</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Master Table 1: Drama Series Performance Ranking -->
    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-7 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
            <div>
                <h2 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="trophy" class="w-5 h-5 text-amber-400"></i>
                    <span>Kedudukan & Prestasi Siri Drama (Real View Ranking)</span>
                </h2>
                <p class="text-xs text-slate-400">Kedudukan siri berdasarkan jumlah tontonan sebenar dari penstriman penonton.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 bg-white/5 px-3 py-1.5 rounded-xl border border-white/10">
                Jumlah Siri: {{ $dramasRanking->count() }} Drama
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-white/10 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-3">Kedudukan</th>
                        <th class="py-3 px-3">Siri Drama</th>
                        <th class="py-3 px-3">Pembikin (Creator)</th>
                        <th class="py-3 px-3 text-center">Genre</th>
                        <th class="py-3 px-3 text-center">Episod</th>
                        <th class="py-3 px-3 text-right">Tontonan Sebenar</th>
                        <th class="py-3 px-3 text-right">Penonton Unik</th>
                        <th class="py-3 px-3 text-center">Kongsi Tontonan</th>
                        <th class="py-3 px-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($dramasRanking as $index => $drama)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            
                            <!-- Rank Badge -->
                            <td class="py-3.5 px-3">
                                @if ($index === 0)
                                    <span class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center justify-center font-black text-xs">1</span>
                                @elseif ($index === 1)
                                    <span class="w-6 h-6 rounded-full bg-slate-300/20 text-slate-200 border border-slate-300/30 flex items-center justify-center font-black text-xs">2</span>
                                @elseif ($index === 2)
                                    <span class="w-6 h-6 rounded-full bg-amber-800/20 text-amber-500 border border-amber-800/30 flex items-center justify-center font-black text-xs">3</span>
                                @else
                                    <span class="text-slate-500 font-bold px-2">{{ $index + 1 }}</span>
                                @endif
                            </td>

                            <!-- Drama Poster & Title -->
                            <td class="py-3.5 px-3 min-w-[220px]">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-14 rounded-xl overflow-hidden bg-slate-800 shrink-0 border border-white/10 shadow-md">
                                        <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <p class="font-bold text-white text-sm truncate max-w-xs">{{ $drama->title }}</p>
                                        <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ Str::limit($drama->synopsis, 45) }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Creator -->
                            <td class="py-3.5 px-3">
                                <span class="text-slate-300 font-semibold">{{ $drama->creator->name ?? 'Filemai Creator' }}</span>
                            </td>

                            <!-- Genre -->
                            <td class="py-3.5 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-red-600/20 text-red-400 border border-red-500/30 uppercase">
                                    {{ $drama->genre }}
                                </span>
                            </td>

                            <!-- Episodes Breakdown -->
                            <td class="py-3.5 px-3 text-center">
                                <span class="font-bold text-white">{{ $drama->active_episodes_count }} Aktif</span>
                                @if ($drama->coming_soon_count > 0)
                                    <span class="block text-[10px] text-amber-400">({{ $drama->coming_soon_count }} Coming Soon)</span>
                                @endif
                            </td>

                            <!-- Real Views Count -->
                            <td class="py-3.5 px-3 text-right">
                                <span class="font-black text-white text-sm font-mono">{{ number_format($drama->views_count) }}</span>
                            </td>

                            <!-- Unique Viewers -->
                            <td class="py-3.5 px-3 text-right">
                                <span class="font-bold text-cyan-400 font-mono">{{ number_format($drama->unique_viewers) }}</span>
                            </td>

                            <!-- Views Share Percentage Bar -->
                            <td class="py-3.5 px-3 text-center min-w-[120px]">
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-16 h-2 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-red-600 rounded-full" style="width: {{ $drama->views_share }}%;"></div>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $drama->views_share }}%</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-3 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('creator.dramas.manage', $drama) }}" 
                                   class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] transition-colors inline-flex items-center gap-1">
                                    <i data-lucide="settings" class="w-3 h-3"></i>
                                    <span>Urus</span>
                                </a>
                                <a href="{{ route('creator.dramas.edit', $drama) }}" 
                                   class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors inline-block"
                                   title="Edit Drama">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('drama.show', $drama->slug) }}" target="_blank"
                                   class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors inline-block"
                                   title="Tonton di Web">
                                    <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dual Analytics Rows: Real-Time View Stream (7 Cols) & VIP Transactions (5 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Live View Log Stream (7 Cols) -->
        <div class="lg:col-span-7 rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div>
                    <h2 class="text-base font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Log Tontonan Masa Nyata Terkini (Live Stream)</span>
                    </h2>
                    <p class="text-xs text-slate-400">Setiap rekod tontonan langsung yang direkodkan ke pelayan.</p>
                </div>
                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-1 rounded-full uppercase">
                    Aktif Merekod
                </span>
            </div>

            <div class="divide-y divide-white/5 max-h-[420px] overflow-y-auto space-y-1 pr-1">
                @forelse ($recentViewLogs as $viewLog)
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs hover:bg-white/[0.02] px-2 rounded-xl transition-colors">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Device Icon -->
                            <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 text-slate-300 flex items-center justify-center shrink-0">
                                @if ($viewLog->device_type === 'mobile')
                                    <i data-lucide="smartphone" class="w-4 h-4 text-red-400"></i>
                                @elseif ($viewLog->device_type === 'tablet')
                                    <i data-lucide="tablet" class="w-4 h-4 text-purple-400"></i>
                                @else
                                    <i data-lucide="monitor" class="w-4 h-4 text-cyan-400"></i>
                                @endif
                            </div>

                            <div class="min-w-0 space-y-0.5">
                                <p class="font-bold text-white truncate max-w-xs">
                                    {{ $viewLog->drama->title ?? 'Siri Drama' }} • Episod {{ $viewLog->episode->episode_number ?? '1' }}
                                </p>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 flex-wrap">
                                    <span>IP: {{ $viewLog->ip_address }}</span>
                                    <span>•</span>
                                    <span>{{ $viewLog->browser }} ({{ $viewLog->platform }})</span>
                                    <span>•</span>
                                    <span>{{ $viewLog->user ? $viewLog->user->name : 'Pelawat Web' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0 space-y-1">
                            @if ($viewLog->is_vip)
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1 justify-end">
                                    <i data-lucide="crown" class="w-2.5 h-2.5 fill-current"></i> VIP
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-slate-800 text-slate-400 border border-white/10">
                                    Percuma (Ad)
                                </span>
                            @endif
                            <p class="text-[10px] text-slate-500">{{ $viewLog->viewed_at->diffForHumans() }}</p>
                        </div>

                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 text-xs">
                        Belum ada rekod tontonan langsung.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- VIP Subscriptions Transactions (5 Cols) -->
        <div class="lg:col-span-5 rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div>
                    <h2 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                        <span>Transaksi Langganan VIP</span>
                    </h2>
                    <p class="text-xs text-slate-400">Bayaran pas VIP penonton Filemai (ToyyibPay/Stripe).</p>
                </div>
                <span class="text-xs font-black text-amber-300">
                    RM {{ number_format($totalRevenue, 2) }}
                </span>
            </div>

            <div class="divide-y divide-white/5 space-y-2">
                @forelse ($recentTransactions as $trx)
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white truncate">{{ $trx->user_name ?? 'Pelanggan VIP' }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ ucfirst($trx->status) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-mono truncate">{{ $trx->transaction_ref }}</p>
                            <p class="text-[10px] text-slate-500">{{ $trx->payment_channel }} • {{ \Carbon\Carbon::parse($trx->paid_at)->diffForHumans() }}</p>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-sm font-black text-amber-300">RM {{ number_format($trx->amount, 2) }}</span>
                            <span class="block text-[10px] text-slate-500">30 Hari Akses</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 text-xs">
                        Belum ada rekod transaksi.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Master Table 3: Creators & Partners Overview -->
    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-7 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <h2 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="video" class="w-5 h-5 text-red-500"></i>
                    <span>Pembikin Siri Drama Berdaftar (Creator Partners)</span>
                </h2>
                <p class="text-xs text-slate-400">Prestasi pembikin kandungan yang memuat naik siri drama di Filemai.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                {{ $creators->count() }} Pembikin
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            @foreach ($creators as $creator)
                <div class="p-4 rounded-2xl bg-slate-950 border border-white/10 flex items-center justify-between gap-4 hover:border-white/20 transition-all">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-red-600/20 text-red-400 border border-red-500/30 flex items-center justify-center font-black text-base shrink-0">
                            {{ substr($creator->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 space-y-0.5">
                            <p class="font-bold text-white text-sm truncate">{{ $creator->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $creator->email }}</p>
                            <span class="text-[10px] text-slate-500">{{ $creator->dramas_count }} siri • {{ $creator->total_episodes }} episod</span>
                        </div>
                    </div>

                    <div class="text-right shrink-0 space-y-0.5">
                        <span class="text-xs font-black text-white font-mono">{{ number_format($creator->total_views) }}</span>
                        <span class="block text-[9px] uppercase tracking-wider text-slate-500">Tontonan</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

