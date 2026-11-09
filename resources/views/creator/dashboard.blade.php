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
                                <span class="text-xs text-slate-400">
                                    {{ $drama->episodes_count ?? $drama->episodes()->count() }} Episod
                                </span>
                                <span>•</span>
                                <span class="text-xs text-slate-400">
                                    {{ number_format($drama->views_count) }} Tontonan
                                </span>
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

