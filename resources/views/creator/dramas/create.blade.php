@extends('layouts.app')

@section('title', 'Cipta Siri Drama Baharu - Filemai Studio')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{
        posterMode: 'file',
        posterPreview: null,
        bannerMode: 'file',
        bannerPreview: null,
        episodes: [
            { number: 1, title: 'Episod 1: Permulaan', video_type: 'coming_soon', youtube_url: '', video_url: '', is_free_preview: true, duration: 60, detected_yt: '' },
            { number: 2, title: 'Episod 2', video_type: 'coming_soon', youtube_url: '', video_url: '', is_free_preview: true, duration: 60, detected_yt: '' },
            { number: 3, title: 'Episod 3', video_type: 'coming_soon', youtube_url: '', video_url: '', is_free_preview: false, duration: 60, detected_yt: '' },
        ],
        addEpisode() {
            const nextNum = this.episodes.length + 1;
            this.episodes.push({
                number: nextNum,
                title: 'Episod ' + nextNum,
                video_type: 'coming_soon',
                youtube_url: '',
                video_url: '',
                is_free_preview: nextNum <= 2,
                duration: 60,
                detected_yt: ''
            });
        },
        removeEpisode(idx) {
            this.episodes.splice(idx, 1);
            this.episodes.forEach((ep, i) => {
                ep.number = i + 1;
                if (!ep.title || ep.title.startsWith('Episod ')) {
                    ep.title = 'Episod ' + (i + 1);
                }
            });
        },
        quickGenerate(count) {
            this.episodes = [];
            for (let i = 1; i <= count; i++) {
                this.episodes.push({
                    number: i,
                    title: i === 1 ? 'Episod 1: Permulaan' : 'Episod ' + i,
                    video_type: 'coming_soon',
                    youtube_url: '',
                    video_url: '',
                    is_free_preview: i <= 2,
                    duration: 60,
                    detected_yt: ''
                });
            }
        },
        previewImage(event, type) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (type === 'poster') this.posterPreview = e.target.result;
                    if (type === 'banner') this.bannerPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        parseYoutube(idx) {
            const ep = this.episodes[idx];
            if (!ep.youtube_url) {
                ep.detected_yt = '';
                return;
            }
            const regExp = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|v\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i;
            const match = ep.youtube_url.match(regExp);
            if (match && match[1]) {
                ep.detected_yt = match[1];
            } else if (/^[a-zA-Z0-9_-]{11}$/.test(ep.youtube_url.trim())) {
                ep.detected_yt = ep.youtube_url.trim();
            } else {
                ep.detected_yt = '';
            }
        }
     }">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('creator.dashboard') }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Creator Studio
        </a>
        <span>/</span>
        <span class="text-white font-semibold">Cipta Siri Drama Baharu</span>
    </div>

    <!-- Header Section -->
    <div class="border-b border-white/10 pb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-red-600/20 text-red-400 border border-red-500/30 uppercase tracking-widest">
                    PENGURUSAN KANDUNGAN
                </span>
                <span class="text-xs text-slate-400">Pendaftaran Siri & Episod Awal</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">
                Cipta Siri Drama Menegak Baharu
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl">
                Masukkan maklumat drama, poster, dan senarai episod awal anda. Episod yang tidak mempunyai pautan video akan secara automatik berstatus 
                <span class="text-amber-400 font-bold">Akan Datang (Coming Soon)</span> dan boleh dimuat naik bila-bila masa kemudian.
            </p>
        </div>
    </div>

    <!-- Flash Error Summary if Any -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1 shadow-xl">
            <div class="font-bold flex items-center gap-1.5 text-red-400">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                Sila betulkan ralat pendaftaran berikut:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Creation Form -->
    <form action="{{ route('creator.dramas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- SECTION 1: Maklumat Drama -->
        <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <div class="border-b border-white/10 pb-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-500 flex items-center justify-center font-bold">
                    <i data-lucide="film" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">1. Maklumat Asas Drama</h2>
                    <p class="text-xs text-slate-400">Tajuk, kategori genre, dan sinopsis plot cerita.</p>
                </div>
            </div>

            <!-- Title -->
            <div class="space-y-1.5">
                <label for="title" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    Tajuk Siri Drama <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}" 
                       required 
                       placeholder="cth: Dendam Pewaris Yang Dibuang, Jodoh Rahsia CEO..." 
                       class="w-full bg-slate-950 text-white text-sm px-4 py-3.5 rounded-2xl border {{ $errors->has('title') ? 'border-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                @error('title')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Genre & Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="genre" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Genre / Tema Utama <span class="text-red-500">*</span>
                    </label>
                    <select id="genre" name="genre" required 
                            class="w-full bg-slate-950 text-white text-sm px-4 py-3.5 rounded-2xl border border-white/10 focus:outline-none focus:border-[#E50914] transition-all">
                        @foreach ($genres as $genre)
                            <option value="{{ $genre }}" {{ old('genre', 'Romance') === $genre ? 'selected' : '' }}>
                                {{ $genre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="type" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Format Tayangan
                    </label>
                    <select id="type" name="type" 
                            class="w-full bg-slate-950 text-white text-sm px-4 py-3.5 rounded-2xl border border-white/10 focus:outline-none focus:border-[#E50914] transition-all">
                        <option value="series" {{ old('type') === 'series' ? 'selected' : '' }}>Siri Menegak 9:16 (Banyak Episod)</option>
                        <option value="movie" {{ old('type') === 'movie' ? 'selected' : '' }}>Telefilem Menegak / Tayangan Khas</option>
                    </select>
                </div>
            </div>

            <!-- Synopsis -->
            <div class="space-y-1.5">
                <label for="synopsis" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    Sinopsis Ringkas
                </label>
                <textarea id="synopsis" 
                          name="synopsis" 
                          rows="3" 
                          placeholder="Ceritakan latar belakang kisah, konflik watak utama, dan kenapa penonton wajib menonton drama ini..." 
                          class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-[#E50914] transition-all placeholder:text-slate-500">{{ old('synopsis') }}</textarea>
            </div>
        </div>

        <!-- SECTION 2: Poster & Banner -->
        <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <div class="border-b border-white/10 pb-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center font-bold">
                    <i data-lucide="image" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">2. Poster & Banner Siri</h2>
                    <p class="text-xs text-slate-400">Imej muka depan siri drama menegak (format 9:16) untuk menarik klik penonton.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Poster 9:16 -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Poster Menegak (Nisbah 9:16) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1 text-[11px]">
                            <button type="button" @click="posterMode = 'file'" 
                                    :class="posterMode === 'file' ? 'bg-white/20 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2 py-0.5 rounded transition-colors">Fail Imej</button>
                            <span class="text-slate-600">|</span>
                            <button type="button" @click="posterMode = 'url'" 
                                    :class="posterMode === 'url' ? 'bg-white/20 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2 py-0.5 rounded transition-colors">Pautan URL</button>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <!-- Poster Preview Thumbnail -->
                        <div class="w-20 h-28 rounded-2xl bg-slate-950 border border-white/10 overflow-hidden shrink-0 flex items-center justify-center text-slate-600 shadow-md">
                            <template x-if="posterPreview">
                                <img :src="posterPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!posterPreview">
                                <div class="text-center p-2">
                                    <i data-lucide="image" class="w-5 h-5 mx-auto opacity-50"></i>
                                    <span class="text-[9px] block mt-1">9:16</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2">
                            <!-- File input -->
                            <div x-show="posterMode === 'file'">
                                <input type="file" 
                                       name="poster_file" 
                                       accept="image/*" 
                                       :disabled="posterMode !== 'file'"
                                       @change="previewImage($event, 'poster')"
                                       class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#E50914]/20 file:text-red-400 hover:file:bg-[#E50914]/30 cursor-pointer bg-slate-950 p-2 rounded-2xl border border-white/10">
                            </div>

                            <!-- URL input -->
                            <div x-show="posterMode === 'url'">
                                <input type="url" 
                                       name="poster_url" 
                                       value="{{ old('poster_url') }}" 
                                       :disabled="posterMode !== 'url'"
                                       @input="posterPreview = $event.target.value"
                                       placeholder="https://.../poster.jpg" 
                                       class="w-full bg-slate-950 text-white text-xs px-3.5 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                            </div>

                            <p class="text-[11px] text-slate-400">
                                Disyorkan nisbah 9:16 (cth: 1080x1920 piksel). Jika dikosongkan, poster templat Filemai akan digunakan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Banner Horizontal (Backdrop) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Banner Billboard / Hero (Opsional)
                        </label>
                        <div class="flex items-center gap-1 text-[11px]">
                            <button type="button" @click="bannerMode = 'file'" 
                                    :class="bannerMode === 'file' ? 'bg-white/20 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2 py-0.5 rounded transition-colors">Fail Imej</button>
                            <span class="text-slate-600">|</span>
                            <button type="button" @click="bannerMode = 'url'" 
                                    :class="bannerMode === 'url' ? 'bg-white/20 text-white font-bold' : 'text-slate-400 hover:text-white'"
                                    class="px-2 py-0.5 rounded transition-colors">Pautan URL</button>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-28 h-20 rounded-2xl bg-slate-950 border border-white/10 overflow-hidden shrink-0 flex items-center justify-center text-slate-600 shadow-md">
                            <template x-if="bannerPreview">
                                <img :src="bannerPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!bannerPreview">
                                <div class="text-center p-2">
                                    <i data-lucide="image" class="w-5 h-5 mx-auto opacity-50"></i>
                                    <span class="text-[9px] block mt-1">16:9</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2">
                            <div x-show="bannerMode === 'file'">
                                <input type="file" 
                                       name="banner_file" 
                                       accept="image/*" 
                                       :disabled="bannerMode !== 'file'"
                                       @change="previewImage($event, 'banner')"
                                       class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white/10 file:text-slate-300 hover:file:bg-white/20 cursor-pointer bg-slate-950 p-2 rounded-2xl border border-white/10">
                            </div>

                            <div x-show="bannerMode === 'url'">
                                <input type="url" 
                                       name="banner_url" 
                                       value="{{ old('banner_url') }}" 
                                       :disabled="bannerMode !== 'url'"
                                       @input="bannerPreview = $event.target.value"
                                       placeholder="https://.../banner.jpg" 
                                       class="w-full bg-slate-950 text-white text-xs px-3.5 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                            </div>

                            <p class="text-[11px] text-slate-400">
                                Paparan latar belakang sinematik (16:9). Jika dikosongkan, sistem akan mengadaptasi poster utama secara automatik.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Senarai Episod Awal (Upfront Episode List) -->
        <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-white/10 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                        <i data-lucide="list-video" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">3. Senarai Episod Awal (List Episod)</h2>
                        <p class="text-xs text-slate-400">
                            Daftarkan senarai episod terlebih dahulu mengikut urutan.
                        </p>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jana Pantas:</span>
                    <button type="button" @click="quickGenerate(3)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-semibold border border-white/10 transition-colors">3 Ep</button>
                    <button type="button" @click="quickGenerate(5)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-semibold border border-white/10 transition-colors">5 Ep</button>
                    <button type="button" @click="quickGenerate(10)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-semibold border border-white/10 transition-colors">10 Ep</button>
                    <button type="button" @click="quickGenerate(20)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-semibold border border-white/10 transition-colors">20 Ep</button>
                </div>
            </div>

            <!-- Helpful Notice -->
            <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-500/30 text-amber-200 text-xs flex items-start gap-3">
                <i data-lucide="sparkles" class="w-5 h-5 text-amber-400 shrink-0 mt-0.5"></i>
                <div class="leading-relaxed">
                    <strong class="font-bold text-amber-300">Ciri Autotetapan Status:</strong>
                    Anda boleh mendaftar senarai episod secara berjadual awal sekarang. Mana-mana episod yang dibiarkan tanpa pautan YouTube atau fail video akan 
                    <strong class="underline decoration-amber-400">secara automatik berstatus Coming Soon (Akan Datang)</strong>. 
                    Anda boleh pautkan video bagi episod tersebut bila-bila masa kemudian di halaman Urus Drama.
                </div>
            </div>

            <!-- Dynamic Episode Rows Container -->
            <div class="space-y-4">
                <template x-for="(ep, idx) in episodes" :key="idx">
                    <div class="rounded-2xl bg-slate-950/80 border border-white/10 p-4 sm:p-5 space-y-4 relative hover:border-white/20 transition-all">
                        
                        <!-- Row Header -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center font-black text-xs shrink-0" 
                                      x-text="ep.number">
                                </span>
                                <span class="text-xs font-bold text-white uppercase tracking-wider">
                                    Episod #<span x-text="ep.number"></span>
                                </span>

                                <!-- Real-time Status Badge -->
                                <template x-if="ep.video_type === 'coming_soon' || (!ep.youtube_url && !ep.video_url)">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Akan Datang (Coming Soon)
                                    </span>
                                </template>
                                <template x-if="(ep.video_type === 'youtube' && ep.youtube_url) || (ep.video_type === 'storage' && ep.video_url)">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Ada Video Siap
                                    </span>
                                </template>
                            </div>

                            <button type="button" 
                                    @click="removeEpisode(idx)" 
                                    class="text-slate-500 hover:text-red-400 p-1 transition-colors"
                                    title="Buang baris episod ini">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <input type="hidden" :name="`episodes[${idx}][episode_number]`" :value="ep.number">

                        <!-- Episode Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                            
                            <!-- Episode Title (5 cols) -->
                            <div class="md:col-span-5 space-y-1">
                                <label class="block text-[11px] font-bold text-slate-300 uppercase">Tajuk Episod</label>
                                <input type="text" 
                                       :name="`episodes[${idx}][title]`" 
                                       x-model="ep.title" 
                                       required
                                       placeholder="Tajuk episod..." 
                                       class="w-full bg-slate-900 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                            </div>

                            <!-- Source Type Selector (3 cols) -->
                            <div class="md:col-span-3 space-y-1">
                                <label class="block text-[11px] font-bold text-slate-300 uppercase">Status Video</label>
                                <select :name="`episodes[${idx}][video_type]`" 
                                        x-model="ep.video_type"
                                        class="w-full bg-slate-900 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                                    <option value="coming_soon">⏳ Tiada Video (Coming Soon)</option>
                                    <option value="youtube">▶ Pautan YouTube</option>
                                    <option value="storage">📁 URL Video Langsung (MP4)</option>
                                </select>
                            </div>

                            <!-- Video Input or Coming Soon Note (4 cols) -->
                            <div class="md:col-span-4 space-y-1">
                                
                                <template x-if="ep.video_type === 'youtube'">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-300 uppercase flex items-center justify-between">
                                            <span>Pautan YouTube</span>
                                            <span x-show="ep.detected_yt" class="text-emerald-400 font-mono text-[10px]" x-text="`ID: ${ep.detected_yt}`"></span>
                                        </label>
                                        <input type="text" 
                                               :name="`episodes[${idx}][youtube_url]`" 
                                               x-model="ep.youtube_url" 
                                               @input="parseYoutube(idx)"
                                               placeholder="https://youtube.com/shorts/... atau ID" 
                                               class="w-full bg-slate-900 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-red-500">
                                    </div>
                                </template>

                                <template x-if="ep.video_type === 'storage'">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-300 uppercase">URL Fail Video (MP4)</label>
                                        <input type="url" 
                                               :name="`episodes[${idx}][video_url]`" 
                                               x-model="ep.video_url" 
                                               placeholder="https://.../video.mp4" 
                                               class="w-full bg-slate-900 text-white text-xs px-3.5 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500">
                                    </div>
                                </template>

                                <template x-if="ep.video_type === 'coming_soon'">
                                    <div class="pt-6">
                                        <span class="text-[11px] text-amber-300/80 italic block">
                                            Pautan video boleh diisi kemudian.
                                        </span>
                                    </div>
                                </template>

                            </div>

                        </div>

                        <!-- Episode Option Row: Duration & Free Preview -->
                        <div class="flex items-center justify-between pt-2 border-t border-white/5 text-xs text-slate-400">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" 
                                       :name="`episodes[${idx}][is_free_preview]`" 
                                       value="1" 
                                       x-model="ep.is_free_preview"
                                       class="w-3.5 h-3.5 rounded text-red-600 bg-slate-900 border-white/20">
                                <span>Pratonton Percuma (Boleh ditonton tanpa langganan VIP)</span>
                            </label>

                            <div class="flex items-center gap-1.5">
                                <span>Durasi:</span>
                                <input type="number" 
                                       :name="`episodes[${idx}][duration]`" 
                                       x-model="ep.duration" 
                                       min="5" 
                                       max="3600" 
                                       class="w-16 bg-slate-900 text-white text-xs px-2 py-1 rounded border border-white/10 text-center">
                                <span>saat</span>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- Add Row Button -->
            <button type="button" 
                    @click="addEpisode()" 
                    class="w-full py-3.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-dashed border-white/20 text-slate-300 hover:text-white text-xs font-bold transition-all flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-500"></i>
                <span>Tambah Satu Lagi Episod (+)</span>
            </button>

        </div>

        <!-- Form Submission Footer -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
            <a href="{{ route('creator.dashboard') }}" 
               class="px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/15 text-slate-300 hover:text-white text-xs font-bold transition-colors">
                Batal
            </a>

            <button type="submit" 
                    class="px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white text-xs font-black shadow-xl shadow-red-950/80 transition-all hover:scale-105 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Simpan Siri & Terbitkan Drama</span>
            </button>
        </div>

    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection

