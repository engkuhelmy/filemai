@extends('layouts.app')

@section('title', 'Kemaskini Siri: ' . $drama->title . ' - Studio Creator')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    title: '{{ addslashes(old('title', $drama->title)) }}',
    slug: '{{ addslashes(old('slug', $drama->slug)) }}',
    synopsis: '{{ addslashes(old('synopsis', $drama->synopsis ?? '')) }}',
    accentColor: '{{ old('accent_color', $drama->effective_accent_color) }}',
    contentFormat: '{{ old('type', $drama->effective_type ?? 'drama') }}',
    releaseStatus: '{{ old('release_status', $drama->release_status ?? 'ongoing') }}',
    status: '{{ old('status', $drama->status ?? ($drama->release_status === 'completed' ? 'draft' : 'published')) }}',
    ageRating: '{{ old('age_rating', $drama->age_rating ?? '13+') }}',
    trailerUrl: '{{ old('trailer_url', $drama->trailer_url ?? '') }}',
    trailerYoutubeId: '',
    posterPreview: '{{ $drama->poster_url }}',
    bannerPreview: '{{ $drama->banner_url }}',
    isBundleEnabled: {{ old('is_bundle_enabled', $drama->is_bundle_enabled) ? 'true' : 'false' }},

    // Delete Drama Modal State
    deleteModalOpen: false,
    isDeleting: false,

    // Data Extraction states
    isExtracting: false,
    toastMessage: '',
    toastType: '',
    toastTimeout: null,

    handleReleaseStatusChange(newStatus) {
        this.releaseStatus = newStatus;
        if (newStatus === 'completed') {
            this.status = 'draft';
        } else {
            this.status = 'published';
        }
    },

    async confirmDeleteDrama() {
        this.isDeleting = true;
        try {
            const response = await fetch('{{ route('creator.dramas.destroy', $drama) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            });

            const result = await response.json().catch(() => null);

            if (response.ok) {
                this.showToast('Siri drama berjaya dipadam. Mengalihkan ke papan pemuka...', 'success');
                setTimeout(() => {
                    window.location.href = (result && result.redirect_url) ? result.redirect_url : '{{ route('creator.dashboard') }}';
                }, 800);
            } else {
                this.showToast((result && result.message) ? result.message : 'Gagal memadam drama. Sila cuba lagi.', 'error');
                this.isDeleting = false;
            }
        } catch (error) {
            // Fallback to native form POST with _method=DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('creator.dramas.destroy', $drama) }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    },

    showToast(message, type = 'success') {
        this.toastMessage = message;
        this.toastType = type;
        if (this.toastTimeout) clearTimeout(this.toastTimeout);
        this.toastTimeout = setTimeout(() => {
            this.toastMessage = '';
        }, 5000);
    },

    async extractTrailerData() {
        const url = this.trailerUrl.trim();
        if (!url) {
            this.showToast('Sila masukkan pautan URL YouTube trailer terlebih dahulu.', 'error');
            return;
        }

        this.isExtracting = true;
        this.parseTrailer();

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
                if (result.title) {
                    this.title = result.title;
                    if (!this.slug) {
                        this.slug = result.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    }
                }
                if (result.description) {
                    this.synopsis = result.description;
                }
                this.parseTrailer();
                this.showToast('Data trailer video berjaya diekstrak & diisi secara automatik!', 'success');
            } else {
                this.showToast(result.message || 'Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.', 'error');
            }
        } catch (error) {
            this.showToast('Gagal memuat turun data video. Pastikan pautan sah dan video adalah Public/Unlisted.', 'error');
        } finally {
            this.isExtracting = false;
        }
    },

    setAccent(color) {
        this.accentColor = color;
    },

    parseTrailer() {
        const url = this.trailerUrl.trim();
        this.trailerYoutubeId = '';
        if (!url) return;
        const regExp = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
        const match = url.match(regExp);
        if (match && match[1]) {
            this.trailerYoutubeId = match[1];
        }
    },

    handlePoster(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.posterPreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    },

    handleBanner(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.bannerPreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    },

    init() {
        if (this.trailerUrl) {
            this.parseTrailer();
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
        <a href="{{ route('creator.dramas.manage', $drama) }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Pengurusan Siri (Episod)
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-xs">{{ $drama->title }}</span>
        <span>/</span>
        <span class="text-slate-400">Kemaskini Halaman Utama Drama</span>
    </div>

    <!-- Main Container Card -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner with Dynamic Accent -->
        <div class="p-6 sm:p-8 border-b border-white/10 relative overflow-hidden" 
             :style="`background: linear-gradient(135deg, ${accentColor}25 0%, #0f172a 70%)`">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider text-white border border-white/20"
                              :style="`background-color: ${accentColor}40`">
                            Studio Creator • 14 Ciri Pengurusan Drama
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border transition-colors"
                              :class="contentFormat === 'short_film' ? 'bg-purple-500/30 text-purple-300 border-purple-400/50' : (contentFormat === 'film' ? 'bg-amber-500/30 text-amber-300 border-amber-400/50' : 'bg-red-500/30 text-red-300 border-red-400/50')"
                              x-text="contentFormat === 'short_film' ? '🎬 Short Film' : (contentFormat === 'film' ? '🎥 Filem' : '📺 Drama (Siri)')">
                        </span>
                        <span class="text-xs text-slate-400">• ID Siri: #{{ $drama->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black text-white">Kemaskini Halaman Utama Drama</h1>
                    <p class="text-xs text-slate-300 max-w-2xl">Optimumkan paparan penonton, penjenamaan peribadi creator, tetapan harga bundle, dan kebolehjumpaan SEO di Google &amp; Filemai.</p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Padam Drama Button (Danger / Red Aesthetic) -->
                    <button type="button" 
                            @click="deleteModalOpen = true" 
                            class="px-4 py-2 rounded-xl bg-red-600/15 hover:bg-red-600 text-xs font-bold text-red-400 hover:text-white border border-red-500/30 hover:border-red-600 transition-all flex items-center gap-1.5 shadow-sm group"
                            title="Padam siri drama ini secara kekal">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-400 group-hover:text-white transition-colors"></i>
                        <span>Padam Drama</span>
                    </button>

                    <a href="{{ route('drama.show', $drama->slug) }}" target="_blank" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-white border border-white/10 transition-colors flex items-center gap-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Lihat Paparan Awam</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.update', $drama) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Error Summary -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-red-400">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                        Sila semak ralat berikut:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- SEKSYEN A: Maklumat Asas & Tajuk (Ciri 4, 7, 14) -->
            <div class="space-y-4">
                <div class="border-b border-white/10 pb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-black">A</span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Identiti Siri &amp; Penjenamaan Utama</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- 4. Tajuk Drama -->
                    <div class="space-y-1.5">
                        <label for="title" class="block text-xs font-bold text-slate-300">
                            Tajuk Rasmi Drama / Siri <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               x-model="title" 
                               value="{{ old('title', $drama->title) }}" 
                               required
                               placeholder="Contoh: Jodoh Kontrak CEO Dingin" 
                               class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 transition-colors">
                    </div>

                    <!-- 4. Custom Slug URL -->
                    <div class="space-y-1.5">
                        <label for="slug" class="block text-xs font-bold text-slate-300">
                            Pautan URL Tersuai (Slug)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs font-mono">filemai.my/drama/</span>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   x-model="slug" 
                                   value="{{ old('slug', $drama->slug) }}" 
                                   placeholder="jodoh-kontrak-ceo-dingin" 
                                   class="w-full bg-slate-950 text-white text-sm pl-36 pr-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 font-mono text-xs">
                        </div>
                    </div>
                </div>

                <!-- Format Kandungan (Tagging: Drama, Short Film, Filem) -->
                <div class="space-y-3 pt-2">
                    <label for="type" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-4 h-4 text-red-500"></i>
                            Format Kandungan (*Content Format / Tagging*) <span class="text-red-500">*</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-normal">Pilih format rasmi karya untuk katalog &amp; penstriman</span>
                    </label>

                    <!-- Interactive Format Cards Selector -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                               :class="contentFormat === 'drama' ? 'bg-red-950/40 border-red-500 text-white shadow-lg ring-1 ring-red-500/50' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                            <input type="radio" name="type_radio" value="drama" x-model="contentFormat" class="sr-only">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                 :class="contentFormat === 'drama' ? 'bg-red-600 text-white' : 'bg-white/5 text-slate-400'">
                                <i data-lucide="tv" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold flex items-center gap-1.5">
                                    <span>Drama</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] bg-red-500/20 text-red-300 font-black">Siri</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Siri berbilang episod menegak (9:16)</p>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                               :class="contentFormat === 'short_film' ? 'bg-purple-950/40 border-purple-500 text-white shadow-lg ring-1 ring-purple-500/50' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                            <input type="radio" name="type_radio" value="short_film" x-model="contentFormat" class="sr-only">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                 :class="contentFormat === 'short_film' ? 'bg-purple-600 text-white' : 'bg-white/5 text-slate-400'">
                                <i data-lucide="film" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold flex items-center gap-1.5">
                                    <span>Short Film</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] bg-purple-500/20 text-purple-300 font-black">Pendek</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Filem pendek episod tunggal / ringkas</p>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-start gap-3"
                               :class="contentFormat === 'film' ? 'bg-amber-950/40 border-amber-500 text-white shadow-lg ring-1 ring-amber-500/50' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'">
                            <input type="radio" name="type_radio" value="film" x-model="contentFormat" class="sr-only">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                 :class="contentFormat === 'film' ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'">
                                <i data-lucide="clapperboard" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold flex items-center gap-1.5">
                                    <span>Filem</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] bg-amber-500/20 text-amber-300 font-black">Penuh</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Karya filem berdurasi penuh (*Feature Film*)</p>
                            </div>
                        </label>
                    </div>

                    <!-- Dropdown Pilihan Format Kandungan (Official Dropdown Input) -->
                    <div class="space-y-1">
                        <select id="type" 
                                name="type" 
                                x-model="contentFormat"
                                class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 font-semibold cursor-pointer">
                            <option value="drama" {{ old('type', $drama->effective_type ?? $drama->type) === 'drama' || old('type', $drama->type) === 'series' ? 'selected' : '' }}>📺 Drama (Siri Episod Menegak)</option>
                            <option value="short_film" {{ old('type', $drama->type) === 'short_film' ? 'selected' : '' }}>🎬 Short Film (Filem Pendek)</option>
                            <option value="film" {{ old('type', $drama->type) === 'film' || old('type', $drama->type) === 'movie' ? 'selected' : '' }}>🎥 Filem (Berdurasi Penuh / Film)</option>
                        </select>
                        <p class="text-[11px] text-slate-500">Tag ini akan dipaparkan secara rasmi pada halaman katalog, poster utama, dan laman profil pencipta.</p>
                    </div>
                </div>

                <!-- 7. Status Tayangan Siri (Ongoing / Completed / Hiatus / Upcoming) -->
                <div class="space-y-3 pt-2">
                    <!-- Hidden Public Visibility Status Input (Auto-switched by Alpine) -->
                    <input type="hidden" name="status" :value="status">

                    <label class="block text-xs font-bold text-slate-300">
                        Status Tayangan Siri (*Series Release Status*) <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-2.5"
                               :class="releaseStatus === 'ongoing' ? 'bg-red-950/30 border-red-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'"
                               @click="handleReleaseStatusChange('ongoing')">
                            <input type="radio" name="release_status" value="ongoing" x-model="releaseStatus" @change="handleReleaseStatusChange('ongoing')" class="sr-only">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            <div class="text-xs font-bold">Sedang Ditayangkan</div>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-2.5"
                               :class="releaseStatus === 'completed' ? 'bg-emerald-950/30 border-emerald-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'"
                               @click="handleReleaseStatusChange('completed')">
                            <input type="radio" name="release_status" value="completed" x-model="releaseStatus" @change="handleReleaseStatusChange('completed')" class="sr-only">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <div class="text-xs font-bold">Tamat (Completed)</div>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-2.5"
                               :class="releaseStatus === 'hiatus' ? 'bg-amber-950/30 border-amber-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'"
                               @click="handleReleaseStatusChange('hiatus')">
                            <input type="radio" name="release_status" value="hiatus" x-model="releaseStatus" @change="handleReleaseStatusChange('hiatus')" class="sr-only">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <div class="text-xs font-bold">Berehat (Hiatus)</div>
                        </label>

                        <label class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center gap-2.5"
                               :class="releaseStatus === 'upcoming' ? 'bg-cyan-950/30 border-cyan-500 text-white shadow-lg' : 'bg-slate-950/60 border-white/10 text-slate-400 hover:border-white/20'"
                               @click="handleReleaseStatusChange('upcoming')">
                            <input type="radio" name="release_status" value="upcoming" x-model="releaseStatus" @change="handleReleaseStatusChange('upcoming')" class="sr-only">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                            <div class="text-xs font-bold">Akan Datang</div>
                        </label>
                    </div>

                    <!-- Contextual Feedback / Warning: Auto-Hide Drama on Tamat (Completed) -->
                    <div x-show="releaseStatus === 'completed'" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                         class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-200 text-xs flex items-start gap-3 shadow-xl backdrop-blur-md">
                        <div class="p-1.5 rounded-xl bg-amber-500/20 text-amber-400 shrink-0 mt-0.5">
                            <i data-lucide="eye-off" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="font-extrabold text-amber-300 flex items-center gap-2">
                                <span>Makluman: Drama ini akan disembunyikan daripada paparan awam secara automatik.</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[9px] font-black uppercase tracking-wider">Auto-Hidden</span>
                            </div>
                            <p class="text-[11px] text-amber-200/90 leading-relaxed">
                                Karya yang berstatus <strong>Tamat (Completed)</strong> akan ditukar status visibiliti awamnya kepada <em>Draf / Tersembunyi</em> supaya tidak lagi dipaparkan dalam katalog awam aktif Filemai bagi penonton umum.
                            </p>
                        </div>
                    </div>

                    <!-- Reassurance feedback when Ongoing -->
                    <div x-show="releaseStatus === 'ongoing'" 
                         x-cloak
                         x-transition
                         class="p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-500/20 text-emerald-300 text-[11px] flex items-center gap-2">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Drama ini berstatus aktif dan dipaparkan dalam katalog awam Filemai.</span>
                    </div>
                </div>

                <!-- 14. Tetapan Warna Tema (Accent Color) -->
                <div class="space-y-2 pt-2">
                    <label class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Warna Tema &amp; Aksen Halaman Drama (*Accent Theme Color*)</span>
                        <span class="text-[11px] font-mono text-slate-400" x-text="accentColor"></span>
                    </label>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="setAccent('#E50914')" class="w-8 h-8 rounded-full bg-[#E50914] border-2 transition-transform hover:scale-110" :class="accentColor === '#E50914' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        <button type="button" @click="setAccent('#06B6D4')" class="w-8 h-8 rounded-full bg-[#06B6D4] border-2 transition-transform hover:scale-110" :class="accentColor === '#06B6D4' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        <button type="button" @click="setAccent('#8B5CF6')" class="w-8 h-8 rounded-full bg-[#8B5CF6] border-2 transition-transform hover:scale-110" :class="accentColor === '#8B5CF6' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        <button type="button" @click="setAccent('#10B981')" class="w-8 h-8 rounded-full bg-[#10B981] border-2 transition-transform hover:scale-110" :class="accentColor === '#10B981' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        <button type="button" @click="setAccent('#F59E0B')" class="w-8 h-8 rounded-full bg-[#F59E0B] border-2 transition-transform hover:scale-110" :class="accentColor === '#F59E0B' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        <button type="button" @click="setAccent('#EC4899')" class="w-8 h-8 rounded-full bg-[#EC4899] border-2 transition-transform hover:scale-110" :class="accentColor === '#EC4899' ? 'border-white scale-110 shadow-lg' : 'border-transparent'"></button>
                        
                        <div class="flex items-center gap-2 pl-3 border-l border-white/10">
                            <input type="color" name="accent_color" x-model="accentColor" class="w-9 h-9 rounded-xl bg-transparent cursor-pointer border border-white/20">
                            <span class="text-xs text-slate-400">Pilih Warna Bebas</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEKSYEN B: Visual Media (Ciri 1 & Ciri 8) -->
            <div class="space-y-4">
                <div class="border-b border-white/10 pb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-black">B</span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Visual Poster, Banner &amp; Trailer</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Vertical Poster (9:16) -->
                    <div class="rounded-2xl bg-black/40 border border-white/10 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="image" class="w-4 h-4 text-red-500"></i>
                                Poster Menegak Utama (9:16)
                            </label>
                            <span class="text-[10px] text-slate-400">Kadar Klik CTR Utama</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-24 h-36 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 shadow-lg">
                                <img :src="posterPreview" alt="Poster Preview" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 space-y-2">
                                <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white cursor-pointer border border-white/10 transition-colors">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Pilih Fail Poster Baru</span>
                                    <input type="file" name="poster_file" accept="image/*" @change="handlePoster($event)" class="sr-only">
                                </label>
                                <input type="text" name="poster_url" value="{{ old('poster_url', $drama->poster_url) }}" placeholder="Atau pautan URL gambar..." class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                                <p class="text-[10px] text-slate-500">Format: JPG, PNG, WEBP. Maks 5MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Horizontal Banner / Backdrop -->
                    <div class="rounded-2xl bg-black/40 border border-white/10 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="monitor" class="w-4 h-4 text-cyan-400"></i>
                                Banner Melintang / Backdrop (16:9)
                            </label>
                            <span class="text-[10px] text-slate-400">Latar Belakang Hero</span>
                        </div>

                        <div class="space-y-3">
                            <div class="w-full h-24 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shadow-lg">
                                <img :src="bannerPreview || posterPreview" alt="Banner Preview" class="w-full h-full object-cover">
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white cursor-pointer border border-white/10 transition-colors shrink-0">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    <span>Muat Naik Banner</span>
                                    <input type="file" name="banner_file" accept="image/*" @change="handleBanner($event)" class="sr-only">
                                </label>
                                <input type="text" name="banner_url" value="{{ old('banner_url', $drama->banner_url) }}" placeholder="Atau URL banner melintang..." class="flex-1 bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. Trailer Utama Drama -->
                <div class="rounded-2xl bg-black/40 border border-white/10 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="trailer_url" class="text-xs font-bold text-white flex items-center gap-1.5">
                            <i data-lucide="video" class="w-4 h-4 text-amber-400"></i>
                            Pautan Video Trailer / Teaser Utama
                        </label>
                        <span class="text-[10px] text-slate-400">Meningkatkan Kadar Pembelian Episod</span>
                    </div>

                    <div class="relative flex items-center">
                        <input type="text" 
                               id="trailer_url" 
                               name="trailer_url" 
                               x-model="trailerUrl" 
                               @input="parseTrailer" 
                               @keydown.enter.prevent="extractTrailerData()"
                               value="{{ old('trailer_url', $drama->trailer_url) }}" 
                               placeholder="https://www.youtube.com/watch?v=... atau pautan MP4 trailer" 
                               class="w-full bg-slate-950 text-white text-xs pl-4 pr-36 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-amber-500">
                        
                        <!-- Auto-Extract Button -->
                        <button type="button" 
                                @click="extractTrailerData()" 
                                :disabled="isExtracting || !trailerUrl.trim()" 
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
                    <div x-show="isExtracting" x-transition class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-3 shadow-lg" style="display: none;">
                        <svg class="animate-spin w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <div class="text-[11px]">
                            <strong class="block text-amber-200 font-bold">Sedang mengekstrak data video YouTube (*Data Extraction*)...</strong>
                            <span class="text-slate-300">Mengambil tajuk drama, deskripsi sinopsis, dan gambar resolusi tinggi (*maxres*).</span>
                        </div>
                    </div>

                    <div x-show="trailerYoutubeId" class="pt-2 flex items-center gap-3" style="display: none;">
                        <div class="w-28 aspect-video rounded-lg overflow-hidden border border-white/10 shadow-lg">
                            <img :src="`https://img.youtube.com/vi/${trailerYoutubeId}/hqdefault.jpg`" class="w-full h-full object-cover">
                        </div>
                        <span class="text-xs text-emerald-400 font-semibold">✓ Trailer YouTube sah disahkan!</span>
                    </div>
                </div>
            </div>

            <!-- SEKSYEN C: Sinopsis, Genre & Pelakon (Ciri 5, 6, 9) -->
            <div class="space-y-4">
                <div class="border-b border-white/10 pb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-black">C</span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Jalan Cerita, Kategori &amp; Barisan Pelakon</h2>
                </div>

                <!-- 5. Editor Sinopsis Penuh -->
                <div class="space-y-1.5">
                    <label for="synopsis" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Sinopsis Penuh Drama (Rich Text)</span>
                        <span class="text-[11px] text-slate-500 font-normal">Gunakan perenggan kemas untuk memikat emosi penonton</span>
                    </label>
                    <textarea id="synopsis" 
                              name="synopsis" 
                              x-model="synopsis"
                              rows="5" 
                              placeholder="Ceritakan ringkasan kisah dramatik, konflik utama, dan tarikan siri drama anda..." 
                              class="w-full bg-slate-950 text-white text-sm p-4 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 leading-relaxed">{{ old('synopsis', $drama->synopsis) }}</textarea>
                </div>

                <!-- 6. Kategori & Multi-Genre Tags -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300">
                        Kategori &amp; Tag Genre (Pilih satu atau beberapa)
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $currentGenres = $drama->genres_list;
                        @endphp
                        @foreach($availableGenres as $g)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border cursor-pointer transition-all"
                                   x-data="{ checked: {{ in_array($g, $currentGenres) ? 'true' : 'false' }} }"
                                   :class="checked ? 'bg-red-600 text-white border-red-500 shadow-md' : 'bg-slate-950/80 text-slate-400 border-white/10 hover:text-white'">
                                <input type="checkbox" name="genres[]" value="{{ $g }}" x-model="checked" class="sr-only">
                                <span>{{ $g }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 9. Senarai Pelakon Utama (Cast & Crew) -->
                <div class="space-y-1.5">
                    <label for="cast_raw" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Senarai Pelakon Utama (*Cast &amp; Crew*)</span>
                        <span class="text-[11px] text-slate-500 font-normal">Format: Nama Pelakon - Nama Watak (Satu baris setiap pelakon)</span>
                    </label>
                    @php
                        $castLines = '';
                        if (is_array($drama->cast_list)) {
                            foreach($drama->cast_list as $c) {
                                $castLines .= ($c['actor'] ?? '') . ($c['character'] ? ' - ' . $c['character'] : '') . "\n";
                            }
                        }
                    @endphp
                    <textarea id="cast_raw" 
                              name="cast_raw" 
                              rows="3" 
                              placeholder="Aiman Hakim - Megat Rayyan (CEO)&#10;Emma Maembong - Sofia Ariana&#10;Fattah Amin - Adam Harris" 
                              class="w-full bg-slate-950 text-white text-xs p-3.5 rounded-2xl border border-white/10 font-mono focus:outline-none focus:border-red-500">{{ old('cast_raw', trim($castLines)) }}</textarea>
                </div>
            </div>

            <!-- SEKSYEN D: Komunikasi, Keselamatan & Pengumuman (Ciri 3, 10, 11, 13) -->
            <div class="space-y-4">
                <div class="border-b border-white/10 pb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-black">D</span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Komunikasi, Penilaian &amp; Media Sosial</h2>
                </div>

                <!-- 11. Papan Pengumuman Creator (Notice Board) -->
                <div class="space-y-1.5">
                    <label for="announcement" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-amber-300">
                            <i data-lucide="megaphone" class="w-4 h-4 text-amber-400"></i>
                            Papan Pengumuman Creator (*Notice Board*)
                        </span>
                        <span class="text-[11px] text-slate-500">Terpapar terus pada halaman penonton &amp; video player</span>
                    </label>
                    <input type="text" 
                           id="announcement" 
                           name="announcement" 
                           value="{{ old('announcement', $drama->announcement) }}" 
                           placeholder="Contoh: Episod 4 &amp; 5 akan ditayangkan pada Jumaat ini jam 8:00 malam!" 
                           class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-amber-500/30 focus:outline-none focus:border-amber-400">
                </div>

                <!-- 10. Rating Umur & Amaran Kandungan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">Rating Umur Penonton <span class="text-red-500">*</span></label>
                        <select name="age_rating" class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            <option value="U" {{ old('age_rating', $drama->age_rating) === 'U' ? 'selected' : '' }}>U - Tontonan Umum (Semua Umur)</option>
                            <option value="13+" {{ old('age_rating', $drama->age_rating ?? '13+') === '13+' ? 'selected' : '' }}>13+ - Bimbingan Ibu Bapa</option>
                            <option value="16+" {{ old('age_rating', $drama->age_rating) === '16+' ? 'selected' : '' }}>16+ - Remaja Matang</option>
                            <option value="18+" {{ old('age_rating', $drama->age_rating) === '18+' ? 'selected' : '' }}>18+ - Dewasa Sahaja</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">Amaran Kandungan (*Content Warnings*)</label>
                        <div class="flex flex-wrap gap-2 pt-1">
                            @php
                                $currentWarnings = $drama->content_warnings_list;
                            @endphp
                            @foreach($commonWarnings as $w)
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold border cursor-pointer transition-all"
                                       x-data="{ checked: {{ in_array($w, $currentWarnings) ? 'true' : 'false' }} }"
                                       :class="checked ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-slate-950/80 text-slate-400 border-white/10'">
                                    <input type="checkbox" name="content_warnings[]" value="{{ $w }}" x-model="checked" class="sr-only">
                                    <span>{{ $w }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 3. Maklumat & Profil Creator -->
                <div class="space-y-1.5">
                    <label for="creator_bio" class="block text-xs font-bold text-slate-300 flex items-center justify-between">
                        <span>Nota Pengarah &amp; Profil Creator</span>
                        <span class="text-[11px] text-slate-500">Bina kredibiliti bersama peminat setia</span>
                    </label>
                    <textarea id="creator_bio" 
                              name="creator_bio" 
                              rows="2" 
                              placeholder="Kongsi pengalaman pembikinan, anugerah lepas, atau mesej peribadi dari anda untuk penonton..." 
                              class="w-full bg-slate-950 text-white text-xs p-3.5 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">{{ old('creator_bio', $drama->creator_bio) }}</textarea>
                </div>

                <!-- 13. Pautan Sosial Media & Hashtag -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label for="social_hashtag" class="block text-[11px] font-bold text-slate-300">Hashtag Rasmi</label>
                        <input type="text" id="social_hashtag" name="social_hashtag" value="{{ old('social_hashtag', $drama->social_hashtag) }}" placeholder="#JodohKontrakCEO" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                    </div>
                    @php
                        $social = $drama->social_links_list;
                    @endphp
                    <div class="space-y-1">
                        <label for="social_tiktok" class="block text-[11px] font-bold text-slate-300">Akaun TikTok Rasmi</label>
                        <input type="text" id="social_tiktok" name="social_tiktok" value="{{ old('social_tiktok', $social['tiktok'] ?? '') }}" placeholder="@filemai.my" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                    </div>
                    <div class="space-y-1">
                        <label for="social_instagram" class="block text-[11px] font-bold text-slate-300">Akaun Instagram Rasmi</label>
                        <input type="text" id="social_instagram" name="social_instagram" value="{{ old('social_instagram', $social['instagram'] ?? '') }}" placeholder="@filemai.my" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                    </div>
                </div>
            </div>

            <!-- SEKSYEN E: SEO & Monetisasi Bundle (Ciri 2 & Ciri 12) -->
            <div class="space-y-4">
                <div class="border-b border-white/10 pb-2 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-black">E</span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">SEO Carian &amp; Harga Pakej Musim (*Bundle*)</h2>
                </div>

                <!-- 12. Harga Pakej (Bundle/Season Pricing) -->
                <div class="rounded-2xl bg-black/40 border border-white/10 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_bundle_enabled" value="1" x-model="isBundleEnabled" class="w-4 h-4 rounded text-red-600 bg-slate-900 border-white/20">
                            <span class="text-xs font-bold text-white">Aktifkan Pembelian Pakej Penuh Satu Musim (*Season Pass*)</span>
                        </label>
                        <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">Peningkatan Aliran Tunai</span>
                    </div>

                    <div x-show="isBundleEnabled" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="space-y-1">
                            <label for="season_price" class="block text-[11px] font-bold text-slate-300">Harga Pakej Penuh (RM)</label>
                            <input type="number" step="0.50" id="season_price" name="season_price" value="{{ old('season_price', $drama->season_price) }}" placeholder="15.00" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                        </div>
                        <div class="space-y-1">
                            <label for="bundle_discount_note" class="block text-[11px] font-bold text-slate-300">Nota Promosi Diskaun</label>
                            <input type="text" id="bundle_discount_note" name="bundle_discount_note" value="{{ old('bundle_discount_note', $drama->bundle_discount_note) }}" placeholder="Jimat 40% berbanding bayar setiap episod!" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                        </div>
                    </div>
                </div>

                <!-- 2. Tetapan SEO & Keywords -->
                <div class="rounded-2xl bg-black/40 border border-white/10 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white flex items-center gap-1.5">
                            <i data-lucide="search" class="w-4 h-4 text-cyan-400"></i>
                            Tetapan Enjin Carian (Google &amp; Filemai SEO)
                        </span>
                        <span class="text-[10px] text-slate-400">Kebolehjumpaan Penonton</span>
                    </div>

                    <div class="space-y-2">
                        <div class="space-y-1">
                            <label for="meta_title" class="block text-[11px] font-bold text-slate-300">Meta Title (Tajuk Carian)</label>
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $drama->meta_title) }}" placeholder="{{ $drama->title }} - Tonton Siri Drama Menegak | Filemai" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                        </div>

                        <div class="space-y-1">
                            <label for="meta_description" class="block text-[11px] font-bold text-slate-300">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2" placeholder="Ringkasan pendek yang akan terpapar di hasil carian Google..." class="w-full bg-slate-950 text-white text-xs p-3 rounded-xl border border-white/10">{{ old('meta_description', $drama->meta_description) }}</textarea>
                        </div>

                        <div class="space-y-1">
                            <label for="keywords" class="block text-[11px] font-bold text-slate-300">Kata Kunci (*Keywords* Dipisahkan dengan koma)</label>
                            <input type="text" id="keywords" name="keywords" value="{{ old('keywords', $drama->keywords) }}" placeholder="drama melayu, ceo dingin, kahwin kontrak, filemai, mini drama" class="w-full bg-slate-950 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action Bar -->
            <div class="pt-6 border-t border-white/10 flex items-center justify-between gap-4">
                <a href="{{ route('creator.dramas.manage', $drama) }}" class="px-6 py-3 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Kembali ke Episod
                </a>

                <button type="submit" class="px-8 py-3.5 rounded-full text-xs font-black text-white shadow-xl transition-all flex items-center gap-2 hover:scale-[1.02]"
                        :style="`background-color: ${accentColor}; box-shadow: 0 10px 25px -5px ${accentColor}80`">
                    <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    <span>Simpan &amp; Kemaskini Halaman Utama Drama</span>
                </button>
            </div>

        </form>

    </div>

    <!-- Confirmation Modal: Padam Drama (Delete Drama Confirmation Dialog) -->
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="if (!isDeleting) deleteModalOpen = false"
         style="display: none;">
        
        <div class="max-w-md w-full rounded-3xl bg-slate-900 border border-red-500/40 p-6 sm:p-7 shadow-2xl shadow-red-950/60 space-y-5"
             @click.outside="if (!isDeleting) deleteModalOpen = false">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-600/20 border border-red-500/40 text-red-500 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base sm:text-lg font-black text-white">Padam Drama "{{ $drama->title }}"?</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Adakah anda pasti mahu memadamkan drama ini? Semua episod dan data analitik akan terhapus. Tindakan ini tidak boleh dipulihkan.
                    </p>
                </div>
            </div>

            <div class="p-3 rounded-2xl bg-red-950/40 border border-red-500/20 text-[11px] text-red-300 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-red-400"></i>
                <span>Tindakan ini adalah kekal dan tidak dapat diundur.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        @click="deleteModalOpen = false" 
                        :disabled="isDeleting"
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-slate-300 hover:text-white transition-colors">
                    Batal
                </button>

                <button type="button" 
                        @click="confirmDeleteDrama()" 
                        :disabled="isDeleting"
                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-xs font-black text-white shadow-lg shadow-red-950/60 flex items-center gap-2 transition-all hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="isDeleting" class="inline-block animate-spin mr-1">⏳</span>
                    <i data-lucide="trash-2" class="w-4 h-4" x-show="!isDeleting"></i>
                    <span x-text="isDeleting ? 'Memadamkan...' : 'Ya, Padam Drama'">Ya, Padam Drama</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

