@extends('layouts.app')

@section('title', 'Tambah Episod Baharu - ' . $drama->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    videoType: '{{ old('video_type', 'youtube') }}',
    youtubeUrl: '{{ old('youtube_url', '') }}',
    detectedId: '',
    isValidYoutube: false,
    selectedFileName: '',
    selectedThumbName: '',
    thumbPreviewUrl: null,
    autoThumbnailUrl: '{{ old('thumbnail_url', '') }}',
    statusChoice: '{{ old('status', 'published') }}',
    title: '{{ addslashes(old('title', 'Episod ' . $nextEpNumber)) }}',
    description: '{{ addslashes(old('description', '')) }}',
    duration: {{ (int)old('duration', 60) }},

    // Data Extraction & Auto-Fill states
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
                // Auto-fill Title
                if (result.title) {
                    this.title = result.title;
                }

                // Auto-fill Description
                if (result.description) {
                    this.description = result.description;
                }

                // Auto-fill Duration (converted into total seconds)
                if (result.duration_seconds) {
                    this.duration = result.duration_seconds;
                }

                // Auto-fill Thumbnail (Highest quality available: maxresdefault / hqdefault)
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
    },

    init() {
        if (this.youtubeUrl) {
            this.parseYoutube();
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
        <span class="text-slate-400">Tambah Episod Baharu</span>
    </div>

    <!-- Main Form Container -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 border-b border-white/10 bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                        <i data-lucide="clapperboard" class="w-3.5 h-3.5 text-red-400"></i>
                        Universal Episode Publisher
                    </span>
                    <span class="text-xs text-slate-400">• Siri: <strong>{{ $drama->title }}</strong></span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Tambah Episod Baharu</h1>
                <p class="text-xs text-slate-400">Pilih antara memuat naik fail storan terus (MP4/WebM) atau menggunakan pautan YouTube untuk menjimatkan kos storan.</p>
            </div>

            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 hidden sm:block shadow-lg">
                <img src="{{ $drama->poster_url ?? '/uploads/posters/ceo-billboard.jpg' }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.episodes.store', $drama) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf

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
                                <div class="text-[11px] text-slate-400">Paling jimat kos &amp; muat cepat (Shorts/Unlisted/Public)</div>
                            </div>
                        </div>
                    </label>

                    <!-- Option B: Direct Storage Video File (MP4/WebM) -->
                    <label class="relative flex items-center p-4 rounded-2xl border cursor-pointer transition-all"
                           :class="videoType === 'storage' ? 'bg-cyan-950/30 border-cyan-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="video_type" value="storage" x-model="videoType" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 :class="videoType === 'storage' ? 'bg-cyan-600 text-white shadow' : 'bg-white/10 text-slate-400'">
                                <i data-lucide="hard-drive" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-xs font-black" :class="videoType === 'storage' ? 'text-white' : 'text-slate-300'">Muat Naik Fail Storan (MP4)</div>
                                <div class="text-[11px] text-slate-400">Kualiti natif HD tanpa logo luar (Maks 200MB)</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. Episode Metadata: Number & Title (Feature 2) -->
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
                               value="{{ old('episode_number', $nextEpNumber) }}" 
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
                           value="{{ old('title', 'Episod ' . $nextEpNumber) }}" 
                           placeholder="Contoh: Episod 1: Pertemuan Tak Diduga" 
                           required
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('title') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                    @error('title')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 3. Episode Description / Notes (Feature 2) -->
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                    <span>Keterangan / Sinopsis Episod</span>
                    <span class="text-[11px] text-slate-500 font-normal">Pilihan Tambahan (Bagus untuk SEO &amp; Nota Penonton)</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          x-model="description"
                          rows="3" 
                          placeholder="Masukkan ringkasan jalan cerita episod ini, nota penting untuk penonton, atau kata kunci carian..."
                          class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border @error('description') border-red-500 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 4A. DYNAMIC FIELD: YouTube Video Link -->
            <div x-show="videoType === 'youtube'" class="space-y-4" x-transition>
                <div class="space-y-1.5">
                    <label for="youtube_url" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Pautan URL Video YouTube <span class="text-red-500">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">Menyokong Format: Watch, Shorts, Embed, youtu.be</span>
                    </label>
                    
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-red-500 z-10">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </div>
                        <input type="text" 
                               id="youtube_url" 
                               name="youtube_url" 
                               x-model="youtubeUrl"
                               @input="parseYoutube"
                               @keydown.enter.prevent="extractData()"
                               value="{{ old('youtube_url', '') }}"
                               placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ atau https://youtu.be/..." 
                               class="w-full bg-slate-950 text-white text-sm pl-11 pr-36 py-3 rounded-2xl border @error('youtube_url') border-red-500 bg-red-950/20 @else border-white/10 @enderror focus:outline-none focus:border-red-500 transition-colors">
                        
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
                    @error('youtube_url')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
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

                <!-- Instant YouTube Preview -->
                <div x-show="isValidYoutube" x-transition.opacity class="p-4 rounded-2xl bg-black/60 border border-white/10 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-emerald-400 flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                            Pautan Sah Disahkan! ID: <span class="font-mono text-white" x-text="detectedId"></span>
                        </span>
                        <span class="text-slate-400 text-[11px]">Pratonton Gambar Kenit Auto</span>
                    </div>

                    <div class="relative w-full aspect-video max-w-sm rounded-xl overflow-hidden bg-black/80 border border-white/10 shadow-lg">
                        <img :src="`https://img.youtube.com/vi/${detectedId}/hqdefault.jpg`" 
                             alt="Thumbnail Preview" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-2xl">
                                <i data-lucide="play" class="w-6 h-6 fill-white ml-0.5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="youtubeUrl.trim() && !isValidYoutube" 
                     x-transition.opacity 
                     class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>Sila pastikan URL mengandungi 11-aksara ID YouTube yang tepat.</span>
                </div>
            </div>

            <!-- 4B. DYNAMIC FIELD: Storage Video File Upload (MP4 / WebM) -->
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
                                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
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

                <!-- External CDN URL -->
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

            <!-- 5. Gambar Kenit Tersuai (Custom Thumbnail - Feature 4) -->
            <div class="rounded-2xl bg-black/40 border border-white/10 p-5 space-y-4">
                <input type="hidden" name="thumbnail_url" x-model="autoThumbnailUrl">
                <div class="border-b border-white/5 pb-3">
                    <div class="text-xs font-bold text-white flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="image" class="w-4 h-4 text-amber-400"></i>
                            <span>Muat Naik Gambar Kenit Tersuai (Custom Thumbnail)</span>
                        </div>
                        <template x-if="autoThumbnailUrl">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                ✓ Auto-Extracted YouTube HD
                            </span>
                        </template>
                    </div>
                    <p class="text-[11px] text-slate-400">Jika tidak dimuat naik, sistem akan menggunakan gambar kenit berkualiti tinggi daripada YouTube atau poster siri.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="relative w-36 sm:w-44 aspect-video rounded-2xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 shadow-xl flex items-center justify-center">
                        <template x-if="thumbPreviewUrl">
                            <div class="relative w-full h-full">
                                <img :src="thumbPreviewUrl" alt="Thumbnail Preview" class="w-full h-full object-cover">
                                <template x-if="autoThumbnailUrl">
                                    <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded bg-black/80 text-[9px] font-mono text-amber-300 border border-amber-500/40">HD</span>
                                </template>
                            </div>
                        </template>
                        <template x-if="!thumbPreviewUrl">
                            <div class="text-center p-3 text-slate-500">
                                <i data-lucide="image" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                                <span class="text-[10px] block">Auto-Jana</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex-1 space-y-2">
                        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white cursor-pointer border border-white/10 transition-colors">
                            <i data-lucide="upload" class="w-4 h-4 text-amber-400"></i>
                            <span>Pilih Gambar Kenit Pengganti (JPG / PNG / WebP)</span>
                            <input type="file" 
                                   id="thumbnail_file" 
                                   name="thumbnail_file" 
                                   accept="image/jpeg,image/png,image/webp" 
                                   @change="handleThumbSelect($event)" 
                                   class="sr-only">
                        </label>
                        <span x-show="selectedThumbName" class="block text-xs text-emerald-400 font-semibold" x-text="`Dipilih: ${selectedThumbName}`"></span>
                        <p class="text-[11px] text-slate-400">Nisbah 9:16 atau 16:9 disyorkan. Saiz maks: 5MB.</p>
                    </div>
                </div>
            </div>

            <!-- 6. Status Visibiliti & Jadual Tayangan (Feature 5) -->
            <div class="rounded-2xl bg-black/40 border border-white/10 p-5 space-y-4">
                <div class="border-b border-white/5 pb-3">
                    <div class="text-xs font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-blue-400"></i>
                        Status Visibiliti &amp; Jadual Tayangan
                    </div>
                    <p class="text-[11px] text-slate-400">Pilih bila episod ini boleh ditonton oleh penonton awam.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Published -->
                    <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-start gap-3"
                           :class="statusChoice === 'published' ? 'bg-emerald-950/30 border-emerald-500 text-white' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                        <input type="radio" name="status" value="published" x-model="statusChoice" class="sr-only">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 mt-1 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-white block">Diterbitkan Segera</span>
                            <span class="text-[10px] text-slate-400">Boleh ditonton serta-merta selepas disimpan.</span>
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
                            <span class="text-[10px] text-slate-400">Automatik diterbitkan pada tarikh ditetapkan.</span>
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
                           value="{{ old('scheduled_at') }}" 
                           class="w-full max-w-sm bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-cyan-500">
                    @error('scheduled_at')
                        <p class="text-xs text-red-400 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 7. Duration & Free Preview Toggle (Feature 6) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div class="space-y-1.5">
                    <label for="duration" class="block text-xs font-bold text-slate-300">
                        Anggaran Durasi Episod (Saat)
                    </label>
                    <input type="number" 
                           id="duration" 
                           name="duration" 
                           x-model="duration"
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
                            <span class="font-bold text-white block">Episod Pratonton Percuma (Free Preview)</span>
                            <span class="text-slate-400 text-[11px]">Boleh ditonton percuma oleh semua pengguna (dengan iklan intersisial).</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-white/10 flex items-center justify-end gap-3">
                <a href="{{ route('creator.dramas.episodes.index', $drama) }}" 
                   class="px-6 py-3 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-xl shadow-red-950/80 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    <span>Simpan &amp; Terbitkan Episod</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
