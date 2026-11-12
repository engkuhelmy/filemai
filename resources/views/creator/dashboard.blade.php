@extends('layouts.app')

@section('title', 'Creator Studio - Filemai.my')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-red-600/20 text-red-400 border border-red-500/30 uppercase tracking-widest">
                    CREATOR STUDIO
                </span>
                <span class="text-xs text-slate-400">Portal Pembikin Siri Drama Filemai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Pengurusan Kandungan Siri</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('creator.wallet') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 font-bold text-xs border border-emerald-500/30 transition-colors">
                <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                <span>Dompet Royalti</span>
            </a>

            <a href="{{ route('creator.dramas.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-white font-black text-xs shadow-lg shadow-red-950/80 transition-all hover:scale-105">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Cipta Siri Drama Baharu</span>
            </a>

            @if ($dramas->isNotEmpty())
                <a href="{{ route('creator.dramas.episodes.create', $dramas->first()) }}" 
                   class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs border border-white/10 transition-colors">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-red-500"></i>
                    <span>Muat Naik Episod</span>
                </a>
            @endif
        </div>
    </div>

    <!-- SEKSYEN KOLEKSI DUIT CREATOR (Terkumpul, Dikeluarkan, Baki) -->
    @if (isset($wallet))
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h2 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Koleksi Pendapatan & Royalti Tontonan</span>
                    </h2>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400 hidden sm:inline">Kadar:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                        RM {{ number_format($ratePerBlock ?? 0.0001, 6) }} / blok (1 minit)
                    </span>
                    <a href="{{ route('creator.wallet') }}" class="text-xs text-red-400 hover:text-red-300 font-bold flex items-center gap-1 transition-colors ml-2">
                        Penyata Penuh <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <!-- 1. Duit Terkumpul -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-emerald-500/30 shadow-xl relative overflow-hidden group hover:border-emerald-500/50 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Duit Terkumpul</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="mt-2 space-y-0.5">
                        <h3 class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono tracking-tight">
                            RM {{ number_format($wallet->total_earned, 4) }}
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            Daripada <strong class="text-slate-300">{{ number_format($wallet->total_blocks) }}</strong> blok tontonan layak
                        </p>
                    </div>
                </div>

                <!-- 2. Telah Dikeluarkan -->
                <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl relative overflow-hidden group hover:border-white/20 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telah Dikeluarkan</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="mt-2 space-y-0.5">
                        <h3 class="text-2xl sm:text-3xl font-black text-amber-400 font-mono tracking-tight">
                            RM {{ number_format($wallet->total_withdrawn, 2) }}
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            Telah dipindahkan ke akaun bank
                        </p>
                    </div>
                </div>

                <!-- 3. Baki Semasa -->
                <div class="p-5 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 border border-red-500/40 shadow-xl relative overflow-hidden group hover:border-red-500/70 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Baki Semasa Tersedia</span>
                        <a href="{{ route('creator.wallet') }}" 
                           class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-500 text-white font-bold text-[10px] transition-all flex items-center gap-1 shadow-md">
                            <span>Tuntut</span>
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <div class="mt-2 space-y-0.5">
                        <h3 class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight">
                            RM {{ number_format($wallet->current_balance, 4) }}
                        </h3>
                        <p class="text-[11px] text-slate-400">
                            Boleh dimohon keluar ke bank bila-bila masa
                        </p>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- Analytics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Tontonan</span>
                <h3 class="text-2xl sm:text-3xl font-black text-white">{{ number_format($totalViews) }}</h3>
                <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Statistik langsung penonton
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center">
                <i data-lucide="eye" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Siri Drama Anda</span>
                <h3 class="text-2xl sm:text-3xl font-black text-white">{{ $dramas->count() }}</h3>
                <span class="text-[11px] text-slate-400">Koleksi karya siri berdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <i data-lucide="film" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Episod Terbit</span>
                <h3 class="text-2xl sm:text-3xl font-black text-white">{{ $totalEpisodes }}</h3>
                <span class="text-[11px] text-slate-400">Episod aktif distrim</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Dramas List Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="clapperboard" class="w-5 h-5 text-red-500"></i>
                    <span>Siri Drama Dalam Studio</span>
                </h2>
                <p class="text-xs text-slate-400">Pilih siri drama untuk menambah atau menguruskan senarai episod.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($dramas as $drama)
                <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-5 shadow-xl flex flex-col justify-between gap-4 hover:border-white/20 transition-all">
                    
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-28 rounded-2xl overflow-hidden bg-slate-800 shrink-0 border border-white/10 shadow-md">
                            <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
                        </div>

                        <div class="min-w-0 space-y-1.5 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-red-600/20 text-red-400 border border-red-500/30 uppercase">
                                    {{ $drama->genre }}
                                </span>
                                @if ($drama->release_status === 'completed' || $drama->status !== 'published')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase flex items-center gap-1" title="Drama ini disembunyikan daripada paparan awam">
                                        <i data-lucide="eye-off" class="w-3 h-3"></i>
                                        {{ $drama->release_status === 'completed' ? 'Tamat (Tersembunyi)' : 'Draf' }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                        Awam
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400">
                                    {{ $drama->episodes_count ?? $drama->episodes()->count() }} Episod
                                </span>
                                <span>•</span>
                                <span class="text-xs text-slate-400">
                                    {{ number_format($drama->views_count) }} Tontonan
                                </span>
                                @if ($drama->canEditIdentity())
                                    <span>•</span>
                                    <span class="text-[10px] font-bold text-emerald-400 flex items-center gap-1" title="Peraturan 24 Jam: Boleh edit tajuk & poster">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Baki: {{ $drama->remainingEditTime() ?? '24j' }}
                                    </span>
                                @else
                                    <span>•</span>
                                    <span class="text-[10px] font-medium text-slate-500 flex items-center gap-1" title="Tajuk & poster dikunci selepas 24 jam">
                                        <i data-lucide="lock" class="w-2.5 h-2.5 text-amber-500/80"></i>
                                        Dikunci
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-white truncate">{{ $drama->title }}</h3>
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $drama->synopsis }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-3 border-t border-white/5 text-xs">
                        <a href="{{ route('drama.show', $drama->slug) }}" target="_blank"
                           class="text-slate-400 hover:text-white transition-colors flex items-center gap-1 font-semibold">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            <span>Tonton</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('creator.dramas.edit', $drama) }}" 
                               class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/15 text-slate-200 hover:text-white font-bold transition-colors flex items-center gap-1 border border-white/5"
                               title="Kemaskini Maklumat Siri Drama">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-red-500"></i>
                                <span>Edit</span>
                            </a>

                            <a href="{{ route('creator.dramas.episodes.create', $drama) }}" 
                               class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition-colors flex items-center gap-1">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Upload</span>
                            </a>

                            <a href="{{ route('creator.dramas.manage', $drama) }}" 
                               class="px-4 py-1.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-white font-black transition-all shadow-md shadow-red-950/80 flex items-center gap-1">
                                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                                <span>Urus Episod</span>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

