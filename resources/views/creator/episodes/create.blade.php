@extends('layouts.app')

@section('title', 'Tambah Episod YouTube - ' . $drama->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{
    youtubeUrl: '{{ old('youtube_url', '') }}',
    detectedId: '',
    isValidYoutube: false,
    parseYoutube() {
        const url = this.youtubeUrl.trim();
        this.detectedId = '';
        this.isValidYoutube = false;
        if (!url) return;

        // Direct 11 char ID check
        if (/^[a-zA-Z0-9_-]{11}$/.test(url)) {
            this.detectedId = url;
            this.isValidYoutube = true;
            return;
        }

        // Standard / Shorts / youtu.be / embed regex
        const regExp = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
        const match = url.match(regExp);
        if (match && match[1]) {
            this.detectedId = match[1];
            this.isValidYoutube = true;
        }
    },
    init() {
        if (this.youtubeUrl) {
            this.parseYoutube();
        }
    }
}">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('creator.dramas.manage', $drama) }}" class="hover:text-cyan-400 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pengurusan Siri
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-xs">{{ $drama->title }}</span>
        <span>/</span>
        <span class="text-slate-400">Tambah Episod YouTube</span>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 border-b border-white/10 bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 fill-red-500" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        YouTube Episode Module
                    </span>
                    <span class="text-xs text-slate-400">• Siri: <strong>{{ $drama->title }}</strong></span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Tambah Episod Baharu</h1>
                <p class="text-xs text-slate-400">Pautkan pautan video YouTube Shorts atau video menegak untuk episod ini.</p>
            </div>

            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 hidden sm:block">
                <img src="{{ $drama->poster_url }}" alt="" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.episodes.store', $drama) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Episode Number & Title -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1 space-y-1.5">
                    <label for="episode_number" class="block text-xs font-bold text-slate-300">
                        Nombor Episod <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="episode_number" 
                           name="episode_number" 
                           value="{{ old('episode_number', $nextEpNumber) }}" 
                           min="1" 
                           required
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('episode_number') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                    @error('episode_number')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 space-y-1.5">
                    <label for="title" class="block text-xs font-bold text-slate-300">
                        Tajuk Episod <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', 'Episod ' . $nextEpNumber) }}" 
                           placeholder="Contoh: Episod 1 - Pertemuan Tak Sengaja" 
                           required
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('title') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                    @error('title')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- YouTube URL Input -->
            <div class="space-y-2">
                <label for="youtube_url" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                    <span>Pautan Video YouTube <span class="text-red-500">*</span></span>
                    <span class="text-[11px] text-slate-400 font-normal">Sokongan: Watch URL, Shorts, youtu.be, atau ID 11-aksara</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-red-500">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </div>
                    <input type="text" 
                           id="youtube_url" 
                           name="youtube_url" 
                           x-model="youtubeUrl"
                           @input="parseYoutube"
                           placeholder="https://www.youtube.com/watch?v=... atau https://youtube.com/shorts/..." 
                           required
                           class="w-full bg-slate-950 text-white text-sm pl-12 pr-4 py-3.5 rounded-2xl border @error('youtube_url') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                </div>
                @error('youtube_url')
                    <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Real-Time YouTube ID & Thumbnail Preview Card -->
            <div x-show="isValidYoutube" 
                 x-transition.opacity.duration.300ms 
                 class="p-4 rounded-2xl bg-slate-950 border border-red-500/30 space-y-3"
                 style="display: none;">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        ID YouTube Sah Dikesan
                    </span>
                    <span class="px-2.5 py-0.5 rounded-md bg-red-500/20 text-red-300 font-mono font-bold text-xs" x-text="detectedId"></span>
                </div>

                <div class="relative w-full aspect-video max-w-sm rounded-xl overflow-hidden bg-black/80 border border-white/10 shadow-lg">
                    <img :src="`https://img.youtube.com/vi/${detectedId}/hqdefault.jpg`" 
                         alt="Thumbnail Preview" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-xl">
                            <svg class="w-6 h-6 fill-white ml-1" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duration & Preview Free Checkbox -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="space-y-1.5">
                    <label for="duration" class="block text-xs font-bold text-slate-300">
                        Anggaran Tempoh (Saat)
                    </label>
                    <input type="number" 
                           id="duration" 
                           name="duration" 
                           value="{{ old('duration', 60) }}" 
                           min="10" 
                           max="3600"
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 transition-colors">
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" 
                               name="is_free_preview" 
                               value="1" 
                               {{ old('is_free_preview', '1') ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-red-600 bg-slate-950 border-white/20 focus:ring-0 focus:ring-offset-0">
                        <div class="text-xs">
                            <span class="font-bold text-white block">Tontonan Percuma (Free Preview)</span>
                            <span class="text-slate-400">Episod boleh ditonton pengguna percuma bersama selingan iklan.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <a href="{{ route('creator.dramas.manage', $drama) }}" 
                   class="px-5 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-300 font-semibold text-xs border border-white/10 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-7 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-extrabold text-xs shadow-xl shadow-red-950/80 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Simpan & Terbitkan Episod
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
