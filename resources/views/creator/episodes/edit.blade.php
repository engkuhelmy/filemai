@extends('layouts.app')

@section('title', 'Kemaskini Episod ' . $episode->episode_number . ' - ' . $drama->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{
    videoType: '{{ old('video_type', $episode->isComingSoon() ? 'coming_soon' : ($episode->isYouTube() ? 'youtube' : 'storage')) }}',
    youtubeUrl: '{{ old('youtube_url', $episode->video_url ?? '') }}',
    detectedId: '{{ $episode->effective_youtube_id ?? '' }}',
    isValidYoutube: {{ !empty($episode->effective_youtube_id) ? 'true' : 'false' }},
    selectedFileName: '',

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

    init() {
        if (this.youtubeUrl && !this.detectedId) {
            this.parseYoutube();
        }
    }
}">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="{{ route('creator.dramas.manage', $drama) }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Pengurusan Siri
        </a>
        <span>/</span>
        <span class="text-white font-semibold truncate max-w-xs">{{ $drama->title }}</span>
        <span>/</span>
        <span class="text-slate-400">Kemaskini Episod #{{ $episode->episode_number }}</span>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Header Banner -->
        <div class="p-6 sm:p-8 border-b border-white/10 bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30 flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        Kemaskini Episod #{{ $episode->episode_number }}
                    </span>
                    <span class="text-xs text-slate-400">• Siri: <strong>{{ $drama->title }}</strong></span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">{{ $episode->title }}</h1>
                <p class="text-xs text-slate-400">Pautkan video YouTube, muat naik fail MP4, atau simpan sebagai placeholder Coming Soon.</p>
            </div>

            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0 hidden sm:block shadow-lg">
                <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Status Notice -->
        @if ($episode->isComingSoon())
            <div class="m-6 sm:m-8 p-4 rounded-2xl bg-amber-950/40 border border-amber-500/40 text-amber-200 text-xs flex items-center gap-3">
                <i data-lucide="clock" class="w-5 h-5 text-amber-400 shrink-0"></i>
                <div class="leading-relaxed">
                    <strong>Status Semasa: Akan Datang (Coming Soon).</strong> Episod ini belum mempunyai video. Sila pilih pautan YouTube atau fail video di bawah untuk menerbitkan episod ini kepada penonton.
                </div>
            </div>
        @else
            <div class="m-6 sm:m-8 p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                <div class="leading-relaxed">
                    <strong>Status Semasa: Aktif & Diterbitkan.</strong> Episod ini boleh ditonton oleh pengguna di Filemai.
                </div>
            </div>
        @endif

        <!-- Form Body -->
        <form action="{{ route('creator.dramas.episodes.update', [$drama, $episode]) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 pt-0 space-y-6">
            @csrf
            @method('PUT')

            <!-- Error Alerts -->
            @if (isset($errors) && $errors->any())
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-red-400">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        Sila semak ralat berikut:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Episode Number & Title -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-1 space-y-1.5">
                    <label for="episode_number" class="block text-xs font-bold text-slate-300">
                        No. Episod <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="episode_number" 
                           name="episode_number" 
                           value="{{ old('episode_number', $episode->episode_number) }}" 
                           required 
                           min="1"
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 text-center font-bold">
                </div>

                <div class="sm:col-span-3 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="title" class="block text-xs font-bold text-slate-300">
                            Tajuk Episod <span class="text-red-500">*</span>
                        </label>
                        @if ($canEditTitle)
                            <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-md flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                Baki edit tajuk: {{ $remainingTime ?? '24 jam' }}
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 rounded-md flex items-center gap-1">
                                <i data-lucide="lock" class="w-3 h-3"></i>
                                Tajuk Dikunci (Peraturan 24 Jam)
                            </span>
                        @endif
                    </div>
                    @if ($canEditTitle)
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $episode->title) }}" 
                               required 
                               class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                    @else
                        <div class="relative">
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ $episode->title }}" 
                                   readonly 
                                   class="w-full bg-slate-900/60 text-slate-400 text-sm px-4 py-3 pl-10 rounded-2xl border border-white/5 cursor-not-allowed font-medium select-none focus:outline-none">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                        </div>
                        <p class="text-[11px] text-slate-500 italic">
                            Tajuk hanya boleh ditukar dalam tempoh 24 jam pertama. Anda masih boleh menukar video atau tetapan lain.
                        </p>
                    @endif
                </div>
            </div>

            <!-- 2. Source Selector Tabs -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    Pilih Status / Sumber Video
                </label>
                
                <div class="grid grid-cols-3 gap-3">
                    <!-- YouTube Option -->
                    <button type="button" 
                            @click="videoType = 'youtube'"
                            class="p-3.5 rounded-2xl border text-center transition-all flex flex-col items-center gap-1.5"
                            :class="videoType === 'youtube' ? 'bg-red-600/20 border-red-500 text-white shadow-lg shadow-red-950/40 ring-1 ring-red-500' : 'bg-slate-950 border-white/10 text-slate-400 hover:border-white/20'">
                        <i data-lucide="youtube" class="w-5 h-5 text-red-500"></i>
                        <span class="text-xs font-bold block">YouTube</span>
                        <span class="text-[10px] text-slate-400 block">Shorts / Unlisted</span>
                    </button>

                    <!-- Storage Option -->
                    <button type="button" 
                            @click="videoType = 'storage'"
                            class="p-3.5 rounded-2xl border text-center transition-all flex flex-col items-center gap-1.5"
                            :class="videoType === 'storage' ? 'bg-cyan-600/20 border-cyan-500 text-white shadow-lg shadow-cyan-950/40 ring-1 ring-cyan-500' : 'bg-slate-950 border-white/10 text-slate-400 hover:border-white/20'">
                        <i data-lucide="file-video" class="w-5 h-5 text-cyan-400"></i>
                        <span class="text-xs font-bold block">Fail Storan</span>
                        <span class="text-[10px] text-slate-400 block">MP4 / WebM</span>
                    </button>

                    <!-- Coming Soon Option -->
                    <button type="button" 
                            @click="videoType = 'coming_soon'"
                            class="p-3.5 rounded-2xl border text-center transition-all flex flex-col items-center gap-1.5"
                            :class="videoType === 'coming_soon' ? 'bg-amber-600/20 border-amber-500 text-white shadow-lg shadow-amber-950/40 ring-1 ring-amber-500' : 'bg-slate-950 border-white/10 text-slate-400 hover:border-white/20'">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-400"></i>
                        <span class="text-xs font-bold block">Akan Datang</span>
                        <span class="text-[10px] text-slate-400 block">Coming Soon</span>
                    </button>
                </div>

                <input type="hidden" name="video_type" :value="videoType">
            </div>

            <!-- YouTube Fields -->
            <div x-show="videoType === 'youtube'" x-transition class="space-y-4">
                <div class="space-y-1.5">
                    <label for="youtube_url" class="block text-xs font-bold text-slate-300">
                        Pautan Video YouTube (Shorts / Standard / Unlisted)
                    </label>
                    <input type="text" 
                           id="youtube_url" 
                           name="youtube_url" 
                           x-model="youtubeUrl"
                           @input="parseYoutube"
                           placeholder="https://www.youtube.com/watch?v=... atau https://youtube.com/shorts/..." 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3.5 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                </div>

                <div x-show="isValidYoutube" class="p-3.5 rounded-2xl bg-slate-950 border border-red-500/30 flex items-center gap-3">
                    <img :src="`https://img.youtube.com/vi/${detectedId}/hqdefault.jpg`" class="w-20 h-14 rounded-lg object-cover">
                    <div class="text-xs">
                        <span class="text-emerald-400 font-bold block">✓ Video YouTube Sah Dikesan</span>
                        <span class="text-slate-400 font-mono text-[11px]" x-text="`ID: ${detectedId}`"></span>
                    </div>
                </div>
            </div>

            <!-- Storage Fields -->
            <div x-show="videoType === 'storage'" x-transition class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">Muat Naik Fail Video Baharu (MP4 / WebM)</label>
                    <input type="file" 
                           name="video_file" 
                           accept="video/mp4,video/webm" 
                           @change="handleFileSelect"
                           class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/10 file:text-cyan-300 hover:file:bg-cyan-500/20 cursor-pointer bg-slate-950 p-2 rounded-2xl border border-white/10">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">Atau Pautan URL Video Langsung</label>
                    <input type="url" 
                           name="video_url" 
                           value="{{ old('video_url', $episode->isStorage() ? $episode->video_url : '') }}" 
                           placeholder="https://cdn.example.com/ep.mp4" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- Coming Soon Note -->
            <div x-show="videoType === 'coming_soon'" x-transition class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-200">
                Episod ini akan dipaparkan sebagai "Akan Datang (Coming Soon)" di laman web Filemai.
            </div>

            <!-- Duration & Free Preview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
                <div class="space-y-1.5">
                    <label for="duration" class="block text-xs font-bold text-slate-300">Durasi Episod (Saat)</label>
                    <input type="number" 
                           id="duration" 
                           name="duration" 
                           value="{{ old('duration', $episode->duration ?? 60) }}" 
                           min="5" 
                           max="7200" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                </div>

                <div class="pt-4">
                    <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 cursor-pointer hover:bg-slate-950 transition-colors">
                        <input type="checkbox" 
                               name="is_free_preview" 
                               value="1" 
                               {{ old('is_free_preview', $episode->is_free_preview) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-red-600 bg-slate-900 border-white/20">
                        <div class="text-xs">
                            <span class="font-bold text-white block">Episod Pratonton Percuma</span>
                            <span class="text-slate-400 text-[11px]">Boleh ditonton percuma oleh semua pengguna.</span>
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
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Episod</span>
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

