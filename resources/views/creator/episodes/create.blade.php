@extends('layouts.app')

@section('title', 'Tambah Episod Siri - ' . $drama->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{
    videoType: '{{ old('video_type', 'youtube') }}',
    youtubeUrl: '{{ old('youtube_url', '') }}',
    detectedId: '',
    isValidYoutube: false,
    selectedFileName: '',

    parseYoutube() {
        const url = this.youtubeUrl.trim();
        this.detectedId = '';
        this.isValidYoutube = false;
        if (!url) return;

        // Standalone 11-character ID check
        if (/^[a-zA-Z0-9_-]{11}$/.test(url)) {
            this.detectedId = url;
            this.isValidYoutube = true;
            return;
        }

        // Standard, Shorts, Embed, youtu.be, Live regex
        const regExp = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
        const match = url.match(regExp);
        if (match && match[1]) {
            this.detectedId = match[1];
            this.isValidYoutube = true;
        }
    },

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFileName = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB)';
        } else {
            this.selectedFileName = '';
        }
    },

    init() {
        if (this.youtubeUrl) {
            this.parseYoutube();
        }
    }
}">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('creator.dramas.manage', $drama) }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pengurusan Siri
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-xs">{{ $drama->title }}</span>
        <span>/</span>
        <span class="text-slate-400">Tambah Episod Baharu</span>
    </div>

    <!-- Main Form Container -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 border-b border-white/10 bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 fill-red-500" viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm0 2v12h16V6H4zm2 2h3v3H6V8zm0 5h3v3H6v-3zm5-5h7v3h-7V8zm0 5h7v3h-7v-3z"/></svg>
                        Universal Episode Publisher
                    </span>
                    <span class="text-xs text-slate-400">• Siri: <strong>{{ $drama->title }}</strong></span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Tambah Episod Baharu</h1>
                <p class="text-xs text-slate-400">Pilih antara memuat naik fail storan terus (MP4/WebM) atau menggunakan pautan YouTube untuk menjimatkan kos storan.</p>
            </div>

            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 hidden sm:block shadow-lg">
                <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.episodes.store', $drama) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Flash Error Summary if Any -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-red-400">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Sila betulkan ralat berikut:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Media Source Selection (Radio Buttons / Cards) -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-300">
                    Jenis Sumber Media Video <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Option A: YouTube -->
                    <label class="relative flex items-center p-4 rounded-2xl border cursor-pointer transition-all"
                           :class="videoType === 'youtube' ? 'bg-red-950/30 border-red-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="youtube" x-model="videoType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 :class="videoType === 'youtube' ? 'bg-red-600 text-white shadow' : 'bg-white/10 text-slate-400'">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-black" :class="videoType === 'youtube' ? 'text-white' : 'text-slate-300'">Pautan Video YouTube</div>
                                <div class="text-[11px] text-slate-400">Jimat storan • Shorts / Unlisted / Standard</div>
                            </div>
                        </div>
                        <div class="ml-auto" x-show="videoType === 'youtube'">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shadow-sm"></span>
                        </div>
                    </label>

                    <!-- Option B: Direct Storage (MP4/WebM) -->
                    <label class="relative flex items-center p-4 rounded-2xl border cursor-pointer transition-all"
                           :class="videoType === 'storage' ? 'bg-cyan-950/30 border-cyan-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="storage" x-model="videoType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 :class="videoType === 'storage' ? 'bg-cyan-600 text-white shadow' : 'bg-white/10 text-slate-400'">
                                <svg class="w-5 h-5 fill-none stroke-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-black" :class="videoType === 'storage' ? 'text-white' : 'text-slate-300'">Muat Naik Fail Storan (MP4)</div>
                                <div class="text-[11px] text-slate-400">Kualiti langsung CDN • Kawalan penuh</div>
                            </div>
                        </div>
                        <div class="ml-auto" x-show="videoType === 'storage'">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 inline-block shadow-sm"></span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. Episode Number & Title -->
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

            <!-- 3A. DYNAMIC FIELD: YouTube URL Input -->
            <div x-show="videoType === 'youtube'" class="space-y-3" x-transition>
                <div class="space-y-2">
                    <label for="youtube_url" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Pautan Video YouTube <span class="text-red-500">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">Format: Watch, Shorts, youtu.be, atau ID 11-aksara</span>
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
                               class="w-full bg-slate-950 text-white text-sm pl-12 pr-4 py-3.5 rounded-2xl border @error('youtube_url') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                    @error('youtube_url')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Live YouTube ID & Thumbnail Preview Card -->
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
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-2xl">
                                <svg class="w-6 h-6 fill-white ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="youtubeUrl.trim() && !isValidYoutube" 
                     x-transition.opacity 
                     class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Sila pastikan URL mengandungi 11-aksara ID YouTube yang tepat.</span>
                </div>
            </div>

            <!-- 3B. DYNAMIC FIELD: Storage Video File Upload (MP4 / WebM) -->
            <div x-show="videoType === 'storage'" class="space-y-4" x-transition style="display: none;">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300">
                        Fail Video Storan (MP4 / WebM)
                    </label>

                    <div class="relative border-2 border-dashed border-white/20 hover:border-cyan-500/60 rounded-2xl p-6 text-center cursor-pointer bg-slate-950/40 transition-colors">
                        <input type="file" 
                               id="video_file" 
                               name="video_file" 
                               accept="video/mp4,video/webm,video/quicktime,video/x-m4v"
                               @change="handleFileSelect($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-full bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div class="text-xs font-bold text-white">
                                <span class="text-cyan-400 underline">Ketik untuk memilih fail</span> atau seret fail ke sini
                            </div>
                            <p class="text-[11px] text-slate-400">Sokongan: MP4, WebM (Saiz maks: 200MB, nisbah 9:16 disyorkan)</p>
                            
                            <div x-show="selectedFileName" class="pt-2 text-xs font-semibold text-emerald-400">
                                Fail Dipilih: <span x-text="selectedFileName" class="underline"></span>
                            </div>
                        </div>
                    </div>
                    @error('video_file')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Or External Direct CDN / Cloudflare R2 URL -->
                <div class="space-y-1.5 pt-1">
                    <label for="video_url" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Atau Masukkan URL Terus Video (CDN / Bunny / Cloudflare R2)</span>
                        <span class="text-[11px] text-slate-400 font-normal">Pilihan Tambahan</span>
                    </label>
                    <input type="url" 
                           id="video_url" 
                           name="video_url" 
                           value="{{ old('video_url', '') }}"
                           placeholder="https://cdn.example.com/videos/ep1.mp4" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('video_url') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-cyan-500 transition-colors">
                    @error('video_url')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 4. Duration & Free Preview Toggle -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
                <div class="space-y-1.5">
                    <label for="duration" class="block text-xs font-bold text-slate-300">
                        Anggaran Durasi Episod (Saat)
                    </label>
                    <input type="number" 
                           id="duration" 
                           name="duration" 
                           value="{{ old('duration', 60) }}" 
                           min="5" 
                           max="3600"
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 transition-colors">
                    <p class="text-[10px] text-slate-400">Standard mini drama: 60 - 120 saat.</p>
                </div>

                <div class="pt-2 sm:pt-4">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 cursor-pointer hover:bg-slate-950 transition-colors">
                        <input type="checkbox" 
                               name="is_free_preview" 
                               value="1" 
                               {{ old('is_free_preview', '1') ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-red-600 focus:ring-0 bg-slate-900 border-white/20">
                        <div class="text-xs">
                            <span class="font-bold text-white block">Episod Pratonton Percuma</span>
                            <span class="text-slate-400 text-[11px]">Boleh ditonton percuma oleh semua pengguna (dengan iklan jika Free).</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-white/10 flex items-center justify-end gap-3">
                <a href="{{ route('creator.dramas.manage', $drama) }}" 
                   class="px-6 py-3 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-xl shadow-red-950/80 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Terbitkan Episod</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
