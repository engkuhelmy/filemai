@extends('layouts.app')

@section('title', 'Urus Episod: ' . $drama->title . ' - Creator Studio')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('creator.dashboard') }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Creator Studio
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-sm">{{ $drama->title }}</span>
    </div>

    <!-- Drama Overview Banner Card -->
    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl backdrop-blur-xl">
        <div class="flex items-start sm:items-center gap-5 min-w-0">
            <div class="w-20 h-28 sm:w-24 sm:h-32 rounded-2xl overflow-hidden bg-slate-800 shrink-0 border border-white/10 shadow-lg">
                <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>
            <div class="space-y-2 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-red-600/20 text-red-400 border border-red-500/30 uppercase tracking-wider">
                        {{ $drama->genre }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-slate-300">
                        {{ $episodes->count() }} Episod
                    </span>
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        {{ number_format($drama->views_count) }} Tontonan
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white truncate">{{ $drama->title }}</h1>
                <p class="text-xs sm:text-sm text-slate-400 line-clamp-2 max-w-2xl leading-relaxed">
                    {{ $drama->synopsis }}
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto justify-end">
            <a href="{{ route('drama.show', $drama->slug) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-xs font-bold border border-white/10 transition-colors flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Tonton di Web</span>
            </a>
            <a href="{{ route('creator.dramas.episodes.create', $drama) }}" 
               class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-white text-xs font-black shadow-lg shadow-red-950/80 transition-all flex items-center gap-1.5 hover:scale-105">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Episod Baharu</span>
            </a>
        </div>
    </div>

    <!-- Episodes Section Header -->
    <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i data-lucide="layers" class="w-5 h-5 text-red-500"></i>
                <span>Senarai Episod Siri Ini</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Semua episod yang telah dimuat naik dan diterbitkan untuk tontonan</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/5 border border-white/10 text-slate-300">
            Jumlah: {{ $episodes->count() }} Episod
        </span>
    </div>

    <!-- Episodes Grid / List -->
    @if ($episodes->isEmpty())
        <div class="rounded-3xl bg-slate-900/60 border border-white/10 p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-600/10 text-red-500 flex items-center justify-center mx-auto">
                <i data-lucide="film" class="w-8 h-8"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-white">Belum Ada Episod Dimuat Naik</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Mulakan siri drama ini dengan memuat naik episod pertama melalui YouTube atau fail storan.
                </p>
            </div>
            <a href="{{ route('creator.dramas.episodes.create', $drama) }}" 
               class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white text-xs font-black shadow-xl shadow-red-950/80 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Muat Naik Episod Pertama
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($episodes as $episode)
                <div class="rounded-2xl bg-slate-900/80 border border-white/10 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:border-white/20 transition-all">
                    
                    <div class="flex items-center gap-4 min-w-0">
                        <!-- Episode Number Badge -->
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md">
                            {{ $episode->episode_number }}
                        </div>

                        <!-- Details -->
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-white truncate max-w-xs sm:max-w-md">
                                    {{ $episode->title }}
                                </h3>

                                <!-- Source Type Badge -->
                                @if ($episode->isComingSoon())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Akan Datang (Coming Soon)
                                    </span>
                                @elseif ($episode->video_source === 'youtube' || $episode->video_type === 'youtube')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/15 text-red-300 border border-red-500/30 flex items-center gap-1 font-mono">
                                        <i data-lucide="youtube" class="w-3 h-3 text-red-400"></i>
                                        {{ $episode->youtube_video_id ?? 'YouTube' }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 flex items-center gap-1">
                                        <i data-lucide="file-video" class="w-3 h-3 text-cyan-400"></i>
                                        MP4 Storan
                                    </span>
                                @endif

                                <!-- Free / VIP Badge -->
                                @if ($episode->is_free_preview)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        Pratonton Percuma
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                        <i data-lucide="crown" class="w-2.5 h-2.5 fill-current"></i> VIP Sahaja
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 text-[11px] text-slate-400">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    {{ $episode->duration ?? 60 }} saat
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3 h-3"></i>
                                    {{ number_format($episode->views_count) }} tontonan
                                </span>
                                <span>•</span>
                                <span>{{ $episode->created_at ? $episode->created_at->diffForHumans() : 'Baru' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Buttons -->
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        @if ($episode->isComingSoon())
                            <a href="{{ route('creator.dramas.episodes.edit', [$drama, $episode]) }}" 
                               class="px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-bold border border-amber-500/40 transition-colors flex items-center gap-1.5">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Pautkan Video</span>
                            </a>
                        @else
                            <a href="{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $episode->episode_number]) }}" 
                               target="_blank"
                               class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-xs font-bold border border-white/10 transition-colors flex items-center gap-1.5">
                                <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                                <span>Tonton</span>
                            </a>
                        @endif

                        <a href="{{ route('creator.dramas.episodes.edit', [$drama, $episode]) }}" 
                           class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-colors"
                           title="Kemaskini Maklumat Episod">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </a>

                        <!-- Delete Episode Form -->
                        <form action="{{ route('creator.dramas.episodes.destroy', [$drama, $episode]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Adakah anda pasti mahu memadam Episod {{ $episode->episode_number }} ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 transition-colors"
                                    title="Padam Episod">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

