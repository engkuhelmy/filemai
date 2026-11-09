@extends('layouts.app')

@section('title', 'Kemaskini Siri: ' . $drama->title . ' - Filemai Studio')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
     x-data="{
        posterMode: 'file',
        posterPreview: '{{ $drama->poster_url }}',
        posterUrl: '{{ str_starts_with($drama->poster_url, "http") ? $drama->poster_url : "" }}',
        bannerMode: 'file',
        bannerPreview: '{{ $drama->banner_url ?? $drama->poster_url }}',
        bannerUrl: '{{ str_starts_with($drama->banner_url ?? "", "http") ? $drama->banner_url : "" }}',
        canEditIdentity: {{ $canEditIdentity ? 'true' : 'false' }},

        previewImage(event, target) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (target === 'poster') {
                        this.posterPreview = e.target.result;
                        this.posterUrl = '';
                    }
                    if (target === 'banner') {
                        this.bannerPreview = e.target.result;
                        this.bannerUrl = '';
                    }
                };
                reader.readAsDataURL(file);
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
        <a href="{{ route('creator.dramas.manage', $drama) }}" class="hover:text-white transition-colors truncate max-w-xs">
            {{ $drama->title }}
        </a>
        <span>/</span>
        <span class="text-white font-semibold">Kemaskini Siri</span>
    </div>

    <!-- 24-Hour Rule Status Card -->
    @if ($canEditIdentity)
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 flex items-start gap-3 text-emerald-200 text-xs shadow-lg">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
            <div class="space-y-1">
                <p class="font-bold text-sm text-white flex items-center gap-2">
                    <span>Peraturan 24 Jam: Boleh Kemaskini Tajuk & Poster</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/30 text-emerald-300 border border-emerald-500/40">
                        {{ $remainingTime ?? '24 jam lagi' }}
                    </span>
                </p>
                <p class="text-slate-300 leading-relaxed">
                    Tajuk dan poster siri drama ini dibenarkan untuk diubah dalam tempoh 24 jam selepas dicipta (Dicipta pada: {{ $drama->created_at ? $drama->created_at->format('d/m/Y, h:i A') : 'Hari ini' }}). Selepas tempoh ini tamat, tajuk dan poster akan dikunci secara kekal.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-950/40 border border-amber-500/40 flex items-start gap-3 text-amber-200 text-xs shadow-lg">
            <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <div class="space-y-1">
                <p class="font-bold text-sm text-white flex items-center gap-2">
                    <span>Peraturan 24 Jam: Tajuk & Poster Dikunci</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500/30 text-amber-300 border border-amber-500/40 uppercase">
                        Tamat Tempoh 24 Jam
                    </span>
                </p>
                <p class="text-slate-300 leading-relaxed">
                    Siri drama ini telah melebihi tempoh 24 jam sejak dicipta (Dicipta pada: {{ $drama->created_at ? $drama->created_at->format('d/m/Y, h:i A') : 'Sebelum ini' }}). Demi kestabilan pautan dan integriti penonton, <strong>tajuk dan poster tidak lagi boleh diubah</strong>. Anda masih boleh mengemaskini sinopsis, genre, status, dan senarai episod.
                </p>
            </div>
        </div>
    @endif

    <!-- Form Container -->
    <div class="rounded-3xl bg-slate-900 border border-white/10 shadow-2xl p-6 sm:p-8 space-y-8 backdrop-blur-xl">
        
        <div class="flex items-center justify-between border-b border-white/10 pb-5">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Kemaskini Siri Drama</h1>
                <p class="text-xs text-slate-400 mt-1">Urus maklumat asas, poster, dan sinopsis siri drama anda.</p>
            </div>
            <a href="{{ route('creator.dramas.manage', $drama) }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                Batal
            </a>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-red-400">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    Sila perbaiki ralat berikut:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-slate-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('creator.dramas.update', $drama) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- SECTION 1: Tajuk & Identiti Drama -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="clapperboard" class="w-4 h-4 text-red-500"></i>
                        <span>1. Tajuk Drama</span>
                    </h2>
                    @if ($canEditIdentity)
                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Boleh Diubah (Baki: {{ $remainingTime ?? '24j' }})
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> Dikunci (Lepas 24 Jam)
                        </span>
                    @endif
                </div>

                <div class="space-y-1.5">
                    @if ($canEditIdentity)
                        <label for="title" class="block text-xs font-bold text-slate-300">
                            Nama Siri Drama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $drama->title) }}" 
                               required 
                               class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                        <p class="text-[11px] text-slate-500">Tajuk akan dipaparkan di katalog utama dan poster promosi.</p>
                    @else
                        <label for="title_locked" class="block text-xs font-bold text-slate-400 flex items-center gap-1">
                            <span>Nama Siri Drama</span>
                            <i data-lucide="lock" class="w-3 h-3 text-amber-400"></i>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="title_locked" 
                                   value="{{ $drama->title }}" 
                                   readonly 
                                   class="w-full bg-slate-950/60 text-slate-400 text-sm px-4 py-3 pl-10 rounded-2xl border border-white/5 cursor-not-allowed select-none font-medium focus:outline-none">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5"></i>
                        </div>
                        <input type="hidden" name="title" value="{{ $drama->title }}">
                        <p class="text-[11px] text-slate-500 italic">
                            Nama siri telah dikunci kerana telah melebihi tempoh 24 jam selepas cipta.
                        </p>
                    @endif
                </div>
            </div>

            <!-- SECTION 2: Poster Drama (9:16) -->
            <div class="space-y-4 pt-4 border-t border-white/10">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="image" class="w-4 h-4 text-red-500"></i>
                        <span>2. Poster Menegak Siri (Nisbah 9:16)</span>
                    </h2>
                    @if ($canEditIdentity)
                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Boleh Diubah
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> Poster Dikunci
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <!-- Preview Card -->
                    <div class="w-full max-w-[200px] mx-auto md:mx-0 aspect-[9/16] rounded-2xl overflow-hidden bg-slate-950 border border-white/10 relative shadow-xl">
                        <template x-if="posterPreview">
                            <img :src="posterPreview" alt="Poster Preview" class="w-full h-full object-cover">
                        </template>
                        @if (!$canEditIdentity)
                            <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px] flex flex-col items-center justify-center p-3 text-center gap-1">
                                <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-300 flex items-center justify-center">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase text-amber-300">Poster Dikunci</span>
                                <span class="text-[9px] text-slate-300">Melebihi 24 jam</span>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Controls -->
                    <div class="md:col-span-2 space-y-4">
                        @if ($canEditIdentity)
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" 
                                        @click="posterMode = 'file'" 
                                        :class="posterMode === 'file' ? 'bg-red-600 text-white font-bold' : 'bg-slate-950 text-slate-400 border border-white/10'" 
                                        class="px-3.5 py-1.5 rounded-xl transition-all">
                                    Muat Naik Fail Poster Baharu
                                </button>
                                <button type="button" 
                                        @click="posterMode = 'url'" 
                                        :class="posterMode === 'url' ? 'bg-red-600 text-white font-bold' : 'bg-slate-950 text-slate-400 border border-white/10'" 
                                        class="px-3.5 py-1.5 rounded-xl transition-all">
                                    Pautan URL Poster
                                </button>
                            </div>

                            <div x-show="posterMode === 'file'" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-400">Pilih fail poster (JPG, PNG, WebP - Maks 5MB):</label>
                                <input type="file" 
                                       name="poster_file" 
                                       accept="image/jpeg,image/png,image/webp" 
                                       :disabled="posterMode !== 'file'"
                                       @change="previewImage($event, 'poster')" 
                                       class="w-full text-xs text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20 cursor-pointer bg-slate-950 p-2 rounded-2xl border border-white/10">
                            </div>

                            <div x-show="posterMode === 'url'" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-400">Masukkan pautan URL poster:</label>
                                <input type="url" 
                                       name="poster_url" 
                                       x-model="posterUrl" 
                                       :disabled="posterMode !== 'url'"
                                       @input="posterPreview = posterUrl" 
                                       placeholder="https://domain.com/poster.jpg" 
                                       class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            </div>

                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Tip: Format 9:16 menegak (cth: 1080x1920 piksel) disyorkan untuk paparan telefon pintar yang tajam dan sinematik.
                            </p>
                        @else
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/5 space-y-2">
                                <p class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i>
                                    Perubahan Poster Tidak Dibenarkan
                                </p>
                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Poster rasmi siri drama ini telah dikunci mengikut peraturan 24 jam. Jika anda memerlukan pertukaran poster rasmi atas sebab hak cipta atau isu teknikal mendesak, sila hubungi pentadbir Filemai melalui saluran sokongan rasmi.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SECTION 2.5: Banner Melintang Hero / Billboard (16:9) -->
            <div class="space-y-4 pt-4 border-t border-white/10">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="layout" class="w-4 h-4 text-amber-500"></i>
                        <span>2b. Banner Billboard / Hero Siri (Nisbah 16:9 - Opsional)</span>
                    </h2>
                    @if ($canEditIdentity)
                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Boleh Diubah
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> Banner Dikunci
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <!-- Preview Card -->
                    <div class="w-full max-w-[280px] mx-auto md:mx-0 aspect-[16/9] rounded-2xl overflow-hidden bg-slate-950 border border-white/10 relative shadow-xl">
                        <template x-if="bannerPreview">
                            <img :src="bannerPreview" alt="Banner Preview" class="w-full h-full object-cover">
                        </template>
                        @if (!$canEditIdentity)
                            <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px] flex flex-col items-center justify-center p-3 text-center gap-1">
                                <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-300 flex items-center justify-center">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <span class="text-[10px] font-black uppercase text-amber-300">Banner Dikunci</span>
                                <span class="text-[9px] text-slate-300">Melebihi 24 jam</span>
                            </div>
                        @endif
                    </div>

                    <!-- Upload Controls -->
                    <div class="md:col-span-2 space-y-4">
                        @if ($canEditIdentity)
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" 
                                        @click="bannerMode = 'file'" 
                                        :class="bannerMode === 'file' ? 'bg-red-600 text-white font-bold' : 'bg-slate-950 text-slate-400 border border-white/10'" 
                                        class="px-3.5 py-1.5 rounded-xl transition-all">
                                    Muat Naik Fail Banner Baharu
                                </button>
                                <button type="button" 
                                        @click="bannerMode = 'url'" 
                                        :class="bannerMode === 'url' ? 'bg-red-600 text-white font-bold' : 'bg-slate-950 text-slate-400 border border-white/10'" 
                                        class="px-3.5 py-1.5 rounded-xl transition-all">
                                    Pautan URL Banner
                                </button>
                            </div>

                            <div x-show="bannerMode === 'file'" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-400">Pilih fail banner (JPG, PNG, WebP - Maks 5MB):</label>
                                <input type="file" 
                                       name="banner_file" 
                                       accept="image/jpeg,image/png,image/webp" 
                                       :disabled="bannerMode !== 'file'"
                                       @change="previewImage($event, 'banner')" 
                                       class="w-full text-xs text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20 cursor-pointer bg-slate-950 p-2 rounded-2xl border border-white/10">
                            </div>

                            <div x-show="bannerMode === 'url'" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-400">Masukkan pautan URL banner:</label>
                                <input type="url" 
                                       name="banner_url" 
                                       x-model="bannerUrl" 
                                       :disabled="bannerMode !== 'url'"
                                       @input="bannerPreview = bannerUrl" 
                                       placeholder="https://domain.com/banner.jpg" 
                                       class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            </div>

                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Banner latar belakang nisbah 16:9 untuk paparan hero desktop, skrin lebar & smart TV.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Metadata Drama (Genre, Status, Sinopsis) -->
            <div class="space-y-4 pt-4 border-t border-white/10">
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                    <span>3. Maklumat Siri & Sinopsis</span>
                    <span class="text-[10px] font-normal text-slate-400">(Sentiasa Boleh Dikemaskini)</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Genre Selection -->
                    <div class="space-y-1.5">
                        <label for="genre" class="block text-xs font-bold text-slate-300">
                            Genre Siri <span class="text-red-500">*</span>
                        </label>
                        <select id="genre" 
                                name="genre" 
                                required 
                                class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            @foreach ($genres as $genre)
                                <option value="{{ $genre }}" {{ old('genre', $drama->genre) === $genre ? 'selected' : '' }}>
                                    {{ $genre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Selection -->
                    <div class="space-y-1.5">
                        <label for="status" class="block text-xs font-bold text-slate-300">
                            Status Penerbitan Siri <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status" 
                                class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            <option value="published" {{ old('status', $drama->status) === 'published' ? 'selected' : '' }}>
                                Diterbitkan (Published - Boleh Ditonton)
                            </option>
                            <option value="draft" {{ old('status', $drama->status) === 'draft' ? 'selected' : '' }}>
                                Draf / Tersembunyi (Hidden)
                            </option>
                            <option value="completed" {{ old('status', $drama->status) === 'completed' ? 'selected' : '' }}>
                                Siri Tamat (Completed)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Synopsis Textarea -->
                <div class="space-y-1.5">
                    <label for="synopsis" class="block text-xs font-bold text-slate-300">
                        Sinopsis / Ringkasan Jalan Cerita
                    </label>
                    <textarea id="synopsis" 
                              name="synopsis" 
                              rows="4" 
                              class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500 placeholder-slate-600 leading-relaxed">{{ old('synopsis', $drama->synopsis) }}</textarea>
                    <p class="text-[11px] text-slate-500">
                        Ceritakan plot siri drama anda yang memikat hati penonton dari minit pertama.
                    </p>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="flex items-center justify-between pt-6 border-t border-white/10">
                <a href="{{ route('creator.dramas.manage', $drama) }}" 
                   class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold transition-colors">
                    Batal
                </a>

                <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-white text-xs font-black shadow-xl shadow-red-950/80 transition-all hover:scale-105 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Kemaskini Drama</span>
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
