@extends('layouts.app')

@section('title', 'Kemaskini Episod #' . $episode->episode_number . ' - ' . $drama->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    videoTypeChoice: '{{ old('video_type', 'current') }}',
    youtubeUrl: '{{ old('youtube_url', $episode->isYouTube() ? ($episode->video_url ?: 'https://www.youtube.com/watch?v=' . $episode->youtube_video_id) : '') }}',
    detectedId: '{{ $episode->effective_youtube_id }}',
    isValidYoutube: {{ !empty($episode->effective_youtube_id) ? 'true' : 'false' }},
    statusChoice: '{{ old('status', $episode->status) }}',
    title: '{{ addslashes(old('title', $episode->title)) }}',
    description: '{{ addslashes(old('description', $episode->description ?? '')) }}',
    duration: {{ (int)old('duration', $episode->duration ?? 60) }},
    selectedFileName: '',
    selectedThumbName: '',
    thumbPreviewUrl: null,
    autoThumbnailUrl: '{{ old('thumbnail_url', '') }}',

    // Data Extraction states
    isExtracting: false,
    toastMessage: '',
    toastType: '',
    toastTimeout: null,

    showToast(message, type = 'success') {
        this.toastMessage = message;
        this.toastType = type;
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => {
            this.toastMessage = '';
        }, 5000);
    },

    async extractData() {
        const url = this.youtubeUrl.trim();
        if (!url) {
            this.showToast('Sila masukkan pautan URL YouTube terlebih dahulu.', 'error');
            return;
        }

        this.isExtracting = true;
        this.parseYoutube();

        try {
            const response = await fetch('{{ route('creator.youtube.extract') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ url: url })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                if (result.title) this.title = result.title;
                if (result.description) this.description = result.description;
                if (result.duration_seconds) this.duration = result.duration_seconds;
                if (result.thumbnail_url) {
                    this.thumbPreviewUrl = result.thumbnail_url;
                    this.autoThumbnailUrl = result.thumbnail_url;
                    this.selectedThumbName = 'YouTube High-Res (' + (result.thumbnail_quality || 'maxres') + ')';
                }
                this.parseYoutube();
                this.showToast('Data video berjaya diekstrak & diisi secara automatik!', 'success');
            } else {
                this.showToast(result.message || 'Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.', 'error');
            }
        } catch (error) {
            this.showToast('Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.', 'error');
        } finally {
            this.isExtracting = false;
        }
    },

    parseYoutube() {
        const url = this.youtubeUrl.trim();
        this.detectedId = '';
        this.isValidYoutube = false;
        if (!url) return;

        if (/^[a-zA-Z0-9_-]{11}$/.test(url)) {
            this.detectedId = url;
            this.isValidYoutube = true;
            return;
        }

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

    handleThumbSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.selectedThumbName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { 
                this.thumbPreviewUrl = e.target.result;
                this.autoThumbnailUrl = '';
            };
            reader.readAsDataURL(file);
        } else {
            this.selectedThumbName = '';
            this.thumbPreviewUrl = null;
        }
    }
}">

    <!-- Toast Notification Banner -->
    <div x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-20 right-4 sm:right-8 z-50 max-w-md p-4 rounded-2xl border shadow-2xl flex items-start gap-3 backdrop-blur-xl"
         :class="toastType === 'success' ? 'bg-emerald-950/95 border-emerald-500/60 text-emerald-200 shadow-emerald-950/50' : 'bg-red-950/95 border-red-500/60 text-red-200 shadow-red-950/50'"
         style="display: none;">
        <div class="mt-0.5 shrink-0">
            <template x-if="toastType === 'success'">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            </template>
            <template x-if="toastType === 'error'">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400"></i>
            </template>
        </div>
        <div class="flex-1 text-xs">
            <span class="font-bold block" x-text="toastType === 'success' ? 'Ekstrak Berjaya! ✨' : 'Ralat Ekstrak Video ⚠️'"></span>
            <p class="text-[11px] mt-0.5" x-text="toastMessage"></p>
        </div>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white text-xs p-1">✕</button>
    </div>

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('creator.dramas.episodes.index', $drama) }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Senarai Episod
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-xs">{{ $drama->title }}</span>
        <span>/</span>
        <span class="text-slate-400">Kemaskini Episod #{{ $episode->episode_number }}</span>
    </div>

    <!-- Main Container -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 border-b border-white/10 bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                        <i data-lucide="pencil" class="w-3 h-3 text-red-400"></i>
                        Kemaskini Episod
                    </span>
                    <span class="text-xs text-slate-400">• Siri: <strong>{{ $drama->title }}</strong></span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Episod #{{ $episode->episode_number }}: {{ $episode->title }}</h1>
                <p class="text-xs text-slate-400">Sunting tajuk, keterangan, tukar sumber video, atau jadualkan tayangan tanpa memadam tontonan sedia ada.</p>
            </div>

            <!-- Mini Poster -->
            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 hidden sm:block shadow-lg">
                <img src="{{ $drama->poster_url ?? '/uploads/posters/ceo-billboard.jpg' }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.episodes.update', [$drama, $episode]) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Flash Error Summary if Any -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-red-400">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                        Sila betulkan ralat berikut:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Metadata: Episode Number & Title (Feature 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-1 space-y-1.5">
                    <label for="episode_number" class="block text-xs font-bold text-slate-300">
                        No. Episod <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">#</span>
                        <input type="number" 
                               id="episode_number" 
                               name="episode_number" 
                               value="{{ old('episode_number', $episode->episode_number) }}" 
                               min="1" 
                               required
                               class="w-full bg-slate-950 text-white text-sm pl-8 pr-4 py-3 rounded-2xl border @error('episode_number') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors font-bold">
                    </div>
                    @error('episode_number')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3 space-y-1.5">
                    <label for="title" class="block text-xs font-bold text-slate-300">
                        Tajuk Episod <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           x-model="title"
                           value="{{ old('title', $episode->title) }}" 
                           placeholder="Contoh: Episod 1: Pertemuan Tak Diduga" 
                           required
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('title') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                    @error('title')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 2. Episode Description / Notes (Feature 2) -->
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                    <span>Keterangan / Sinopsis Episod</span>
                    <span class="text-[11px] text-slate-500 font-normal">Pilihan Tambahan (Bagus untuk SEO & Nota Penonton)</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          x-model="description"
                          rows="3" 
                          placeholder="Masukkan ringkasan jalan cerita episod ini, nota penting untuk penonton, atau kata kunci carian..."
                          class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('description') border-red-500 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">{{ old('description', $episode->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 3. Tukar Sumber Media (Swap Media Source - Feature 3) -->
            <div class="rounded-2xl bg-black/40 border border-white/10 p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/5 pb-3">
                    <div>
                        <div class="text-xs font-bold text-white flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-cyan-400"></i>
                            Tukar Sumber Media Video
                        </div>
                        <p class="text-[11px] text-slate-400">Tukar video tanpa memadamkan rekod tontonan ({{ number_format($episode->views_count) }} views) atau ulasan.</p>
                    </div>

                    <!-- Current Source Status Badge -->
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-slate-400">Sumber Semasa:</span>
                        @if($episode->isYouTube())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-600/20 text-red-400 border border-red-500/30">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube (ID: {{ $episode->effective_youtube_id ?: 'Tiada' }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                                <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                Fail Storan (MP4)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Choice: Keep current OR Swap -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-center gap-3"
                           :class="videoTypeChoice === 'current' ? 'bg-red-950/30 border-red-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="current" x-model="videoTypeChoice" class="sr-only">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0" x-show="videoTypeChoice === 'current'"></i>
                        <span class="text-xs font-bold">Kekalkan Sumber Semasa</span>
                    </label>

                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-center gap-3"
                           :class="videoTypeChoice === 'youtube' ? 'bg-red-950/30 border-red-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="youtube" x-model="videoTypeChoice" class="sr-only">
                        <svg class="w-4 h-4 fill-red-500 shrink-0" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        <span class="text-xs font-bold">Tukar ke YouTube</span>
                    </label>

                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-center gap-3"
                           :class="videoTypeChoice === 'storage' ? 'bg-cyan-950/30 border-cyan-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="storage" x-model="videoTypeChoice" class="sr-only">
                        <i data-lucide="upload-cloud" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                        <span class="text-xs font-bold">Tukar ke Storan (MP4)</span>
                    </label>
                </div>

                <!-- Input for YouTube when selected -->
                <div x-show="videoTypeChoice === 'youtube'" x-transition class="space-y-3 pt-2" style="display: none;">
                    <label for="youtube_url" class="block text-xs font-bold text-slate-300">Pautan Video YouTube Baru</label>
                    
                    <div class="relative flex items-center">
                        <input type="text" 
                               id="youtube_url" 
                               name="youtube_url" 
                               x-model="youtubeUrl" 
                               @input="parseYoutube" 
                               @keydown.enter.prevent="extractData()"
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-slate-950 text-white text-sm pl-4 pr-36 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                        
                        <!-- Auto-Extract Button -->
                        <button type="button" 
                                @click="extractData()" 
                                :disabled="isExtracting || !youtubeUrl.trim()" 
                                class="absolute right-1.5 top-1.5 bottom-1.5 px-3.5 rounded-xl bg-gradient-to-r from-red-600 via-pink-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white text-xs font-black flex items-center gap-1.5 transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-lg">
                            <template x-if="!isExtracting">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span>Ambil Data</span>
                                </span>
                            </template>
                            <template x-if="isExtracting">
                                <span class="flex items-center gap-1.5">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span class="text-[11px]">Memuat...</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <!-- Extraction Loading Banner -->
                    <div x-show="isExtracting" x-transition class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-3 shadow-lg" style="display: none;">
                        <svg class="animate-spin w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <div class="text-[11px]">
                            <strong class="block text-amber-200 font-bold">Sedang mengekstrak data video YouTube (*Data Extraction*)...</strong>
                            <span class="text-slate-300">Mengambil tajuk rasmi, deskripsi sinopsis, durasi video (saat), dan gambar kenit resolusi tertinggi (*maxres*).</span>
                        </div>
                    </div>
                    
                    <div x-show="isValidYoutube" class="pt-2">
                        <div class="relative w-48 aspect-video rounded-xl overflow-hidden bg-black/80 border border-white/10 shadow-lg">
                            <img :src="`https://img.youtube.com/vi/${detectedId}/hqdefault.jpg`" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
                    
                    <div x-show="isValidYoutube" class="pt-2">
                        <div class="relative w-48 aspect-video rounded-xl overflow-hidden bg-black/80 border border-white/10 shadow-lg">
                            <img :src="`https://img.youtube.com/vi/${detectedId}/hqdefault.jpg`" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                <!-- Input for Storage when selected -->
                <div x-show="videoTypeChoice === 'storage'" x-transition class="space-y-3 pt-2" style="display: none;">
                    <label class="block text-xs font-bold text-slate-300">Muat Naik Fail MP4 Baru atau Masukkan URL Terus</label>
                    <div class="relative border-2 border-dashed border-white/20 hover:border-cyan-500/60 rounded-2xl p-5 text-center cursor-pointer bg-slate-950/40">
                        <input type="file" 
                               name="video_file" 
                               accept="video/mp4,video/webm,video/quicktime,video/x-m4v"
                               @change="handleFileSelect($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-1">
                            <i data-lucide="upload" class="w-6 h-6 mx-auto text-cyan-400"></i>
                            <div class="text-xs font-bold text-white">Ketik untuk memilih fail video MP4 baharu</div>
                            <div x-show="selectedFileName" class="text-xs text-emerald-400 font-semibold pt-1">
                                Fail Dipilih: <span x-text="selectedFileName"></span>
                            </div>
                        </div>
                    </div>
                    <input type="url" 
                           name="video_url" 
                           value="{{ old('video_url', $episode->isStorage() ? $episode->video_url : '') }}" 
                           placeholder="Atau URL terus video (CDN / Cloudflare R2)..." 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- 4. Muat Naik Gambar Kenit Tersuai (Custom Thumbnail - Feature 4) -->
            <div class="rounded-2xl bg-black/40 border border-white/10 p-5 space-y-4">
                <input type="hidden" name="thumbnail_url" x-model="autoThumbnailUrl">
                <div class="border-b border-white/5 pb-3">
                    <div class="text-xs font-bold text-white flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="image" class="w-4 h-4 text-amber-400"></i>
                            <span>Gambar Kenit Episod (Custom Thumbnail)</span>
                        </div>
                        <template x-if="autoThumbnailUrl">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                ✓ Auto-Extracted YouTube HD
                            </span>
                        </template>
                    </div>
                    <p class="text-[11px] text-slate-400">Tingkatkan kadar tontonan (CTR) dengan memuat naik gambar kenit khas atau gunakan gambar kenit resolusi tinggi dari YouTube.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <!-- Thumbnail Preview Container -->
                    <div class="relative w-36 sm:w-44 aspect-video rounded-2xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 shadow-xl">
                        <!-- If new selected thumb preview available -->
                        <template x-if="thumbPreviewUrl">
                            <div class="relative w-full h-full">
                                <img :src="thumbPreviewUrl" alt="Thumbnail Preview" class="w-full h-full object-cover">
                                <template x-if="autoThumbnailUrl">
                                    <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/80 text-[9px] font-mono text-amber-300 border border-amber-500/40">HD</span>
                                </template>
                            </div>
                        </template>
                        <!-- Else current thumbnail -->
                        <template x-if="!thumbPreviewUrl">
                            <img src="{{ $episode->thumbnail_url }}" alt="{{ $episode->title }}" class="w-full h-full object-cover">
                        </template>
                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-black/80 text-[9px] font-bold text-white">Pratonton</span>
                    </div>

                    <!-- Upload Controls -->
                    <div class="flex-1 space-y-3">
                        <div class="relative">
                            <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white cursor-pointer border border-white/10 transition-colors">
                                <i data-lucide="upload" class="w-4 h-4 text-amber-400"></i>
                                <span>Pilih Gambar Kenit Baru (JPG / PNG / WebP)</span>
                                <input type="file" 
                                       id="thumbnail_file" 
                                       name="thumbnail_file" 
                                       accept="image/jpeg,image/png,image/webp" 
                                       @change="handleThumbSelect($event)" 
                                       class="sr-only">
                            </label>
                            <span x-show="selectedThumbName" class="block text-xs text-emerald-400 font-semibold pt-1.5" x-text="`Dipilih: ${selectedThumbName}`"></span>
                        </div>
                        <p class="text-[11px] text-slate-400">Nisbah 9:16 atau 16:9 disyorkan. Maksimum saiz: 5MB.</p>

                        @if(!empty($episode->getRawOriginal('thumbnail_url')))
                            <label class="flex items-center gap-2 pt-1 text-xs text-slate-400 cursor-pointer">
                                <input type="checkbox" name="remove_custom_thumbnail" value="1" class="w-4 h-4 rounded text-red-600 bg-slate-900 border-white/20">
                                <span>Padam gambar kenit tersuai (kembali ke auto-jana dari YouTube/poster)</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 5. Status Visibiliti & Jadual Tayangan (Feature 5) -->
            <div class="rounded-2xl bg-black/40 border border-white/10 p-5 space-y-4">
                <div class="border-b border-white/5 pb-3">
                    <div class="text-xs font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-blue-400"></i>
                        Status Visibiliti &amp; Jadual Tayangan
                    </div>
                    <p class="text-[11px] text-slate-400">Kawal bilakah episod ini boleh dilihat oleh penonton awam.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Published -->
                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-start gap-3"
                           :class="statusChoice === 'published' ? 'bg-emerald-950/30 border-emerald-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="status" value="published" x-model="statusChoice" class="sr-only">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 mt-1 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-white block">Diterbitkan Segera</span>
                            <span class="text-[10px] text-slate-400">Boleh ditonton serta-merta oleh orang awam.</span>
                        </div>
                    </label>

                    <!-- Draft -->
                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-start gap-3"
                           :class="statusChoice === 'draft' ? 'bg-amber-950/30 border-amber-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="status" value="draft" x-model="statusChoice" class="sr-only">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 mt-1 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-white block">Simpan Sebagai Draf</span>
                            <span class="text-[10px] text-slate-400">Hanya anda dan admin boleh lihat episod ini.</span>
                        </div>
                    </label>

                    <!-- Scheduled -->
                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-start gap-3"
                           :class="statusChoice === 'scheduled' ? 'bg-cyan-950/30 border-cyan-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="status" value="scheduled" x-model="statusChoice" class="sr-only">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 mt-1 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-white block">Jadualkan Tayangan</span>
                            <span class="text-[10px] text-slate-400">Lepaskan siaran secara automatik pada waktu ditetapkan.</span>
                        </div>
                    </label>
                </div>

                <!-- Datetime Picker when Scheduled -->
                <div x-show="statusChoice === 'scheduled'" x-transition class="space-y-1.5 pt-2" style="display: none;">
                    <label for="scheduled_at" class="block text-xs font-bold text-slate-300">
                        Tarikh &amp; Masa Tayangan <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="scheduled_at" 
                           name="scheduled_at" 
                           value="{{ old('scheduled_at', $episode->scheduled_at ? $episode->scheduled_at->format('Y-m-d\TH:i') : '') }}" 
                           class="w-full max-w-sm bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-cyan-500">
                    @error('scheduled_at')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 6. Duration & Paywall Toggle (Feature 6) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div class="space-y-1.5">
                    <label for="duration" class="block text-xs font-bold text-slate-300">
                        Durasi Episod (Saat)
                    </label>
                    <input type="number" 
                           id="duration" 
                           name="duration" 
                           x-model="duration"
                           value="{{ old('duration', $episode->duration) }}" 
                           min="5" 
                           max="3600"
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 transition-colors">
                </div>

                <div class="pt-2 sm:pt-4">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 cursor-pointer hover:bg-slate-950 transition-colors">
                        <input type="checkbox" 
                               name="is_free_preview" 
                               value="1" 
                               {{ old('is_free_preview', $episode->is_free_preview) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-red-600 focus:ring-0 bg-slate-900 border-white/20">
                        <div class="text-xs">
                            <span class="font-bold text-white block">Episod Pratonton Percuma (Free Preview)</span>
                            <span class="text-slate-400 text-[11px]">Boleh ditonton percuma oleh semua pengguna (dengan iklan intersisial).</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Action Bar -->
            <div class="pt-4 border-t border-white/10 flex items-center justify-end gap-3">
                <a href="{{ route('creator.dramas.episodes.index', $drama) }}" 
                   class="px-6 py-3 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-xl shadow-red-950/80 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    <span>Simpan Perubahan Episod</span>
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

