@extends('layouts.app')

@section('title', $creator->name . ' - Profil Pencipta & Siri Drama | Filemai.my')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10" x-data="{ tipAmount: 5 }">

    <!-- Flash message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs flex items-center justify-between shadow-xl">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-white">✕</button>
        </div>
    @endif

    <!-- Profile Cover & Header -->
    <div class="relative rounded-3xl overflow-hidden bg-slate-900 border border-white/10 shadow-2xl">
        <!-- Cover Banner Image -->
        <div class="h-48 sm:h-64 w-full bg-gradient-to-r from-red-950 via-slate-900 to-black relative">
            @if($creator->cover_photo_url)
                <img src="{{ $creator->cover_photo_url }}" alt="{{ $creator->name }}" class="w-full h-full object-cover opacity-60">
            @else
                <div class="w-full h-full bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-red-900/40 via-slate-900 to-black"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
        </div>

        <!-- Creator Profile Card Overlapping Banner -->
        <div class="relative px-6 sm:px-10 pb-8 -mt-20 sm:-mt-24 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                <!-- Avatar -->
                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl overflow-hidden bg-slate-800 border-4 border-slate-900 shadow-2xl shrink-0 relative">
                    @if($creator->avatar_url)
                        <img src="{{ $creator->avatar_url }}" alt="{{ $creator->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl font-black text-white bg-gradient-to-tr from-red-600 to-amber-600">
                            {{ strtoupper(substr($creator->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl font-black text-white">{{ $creator->name }}</h1>
                        
                        <!-- Level Badge -->
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border flex items-center gap-1.5 {{ $creatorLevel['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $creatorLevel['dot'] }}"></span>
                            {{ $creatorLevel['name'] }}
                        </span>

                        @if($creator->is_bank_verified)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3 text-emerald-400"></i>
                                Pengesahan Rasmi
                            </span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm text-slate-300 font-medium">
                        {{ $creator->headline ?: 'Pencipta Filem & Siri Drama Menegak Filemai.my' }}
                    </p>

                    <!-- Metrics Badges -->
                    <div class="flex items-center gap-4 text-xs text-slate-400 pt-1">
                        <span><strong>{{ $dramas->count() }}</strong> Siri Drama</span>
                        <span>•</span>
                        <span><strong>{{ number_format($totalViews) }}</strong> Tontonan Keseluruhan</span>
                    </div>
                </div>
            </div>

            <!-- Social Links Buttons -->
            @php
                $social = is_array($creator->social_links) ? $creator->social_links : [];
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                @if(!empty($social['tiktok']))
                    <a href="https://tiktok.com/{{ ltrim($social['tiktok'], '@') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 flex items-center gap-1.5 transition-colors">
                        <span>TikTok</span>
                    </a>
                @endif
                @if(!empty($social['instagram']))
                    <a href="https://instagram.com/{{ ltrim($social['instagram'], '@') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 flex items-center gap-1.5 transition-colors">
                        <span>Instagram</span>
                    </a>
                @endif
                @if(!empty($social['youtube']))
                    <a href="https://youtube.com/{{ ltrim($social['youtube'], '@') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 flex items-center gap-1.5 transition-colors">
                        <span>YouTube</span>
                    </a>
                @endif
                @if(!empty($social['website']))
                    <a href="{{ $social['website'] }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 flex items-center gap-1.5 transition-colors">
                        <span>Laman Web ↗</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Bio & Fan Tipping Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left: Bio & Specialties -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-3xl bg-slate-900/60 border border-white/10 p-6 sm:p-8 space-y-4 shadow-xl">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="clapperboard" class="w-4 h-4 text-red-500"></i>
                    Tentang Pencipta &amp; Visi Karya
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ $creator->bio ?: 'Pencipta kreatif tempatan yang menghasilkan karya mini drama berformat menegak di platform Filemai.my. Sokong karya beliau untuk menghasilkan lebih banyak episod menarik!' }}
                </p>

                @if(!empty($creator->specialties) && is_array($creator->specialties))
                    <div class="pt-2 flex flex-wrap gap-2">
                        @foreach($creator->specialties as $sp)
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/5 text-slate-300 border border-white/10">
                                {{ $sp }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Published Dramas Catalog -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <h3 class="text-lg font-bold text-white">Koleksi Siri Drama Dihasilkan ({{ $dramas->count() }})</h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($dramas as $drama)
                        <a href="{{ route('drama.show', $drama->slug) }}" class="group rounded-2xl overflow-hidden bg-slate-900 border border-white/10 hover:border-red-500/50 transition-all hover:scale-[1.02] shadow-xl flex flex-col">
                            <div class="w-full aspect-[9/16] bg-black/60 overflow-hidden relative">
                                <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase text-white bg-red-600/90 shadow">
                                        {{ $drama->genre }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold border shadow backdrop-blur-md {{ $drama->type_badge['badge'] }}">
                                        {{ $drama->type_badge['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 flex-1 flex flex-col justify-between">
                                <h4 class="text-xs font-bold text-white group-hover:text-red-400 transition-colors line-clamp-1">
                                    {{ $drama->title }}
                                </h4>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2">
                                    <span>{{ $drama->episodes_count }} Ep</span>
                                    <span>{{ number_format($drama->views_count) }} views</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Ciri 9 Fan Tipping Box (Beri Tip Peminat) -->
        <div class="rounded-3xl bg-gradient-to-b from-red-950/40 via-slate-900 to-slate-900 border border-red-500/30 p-6 sm:p-8 space-y-6 shadow-2xl">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-pink-500/20 text-pink-400 flex items-center justify-center">
                        <i data-lucide="heart" class="w-4 h-4 fill-current"></i>
                    </span>
                    <h3 class="text-base font-black text-white">Sokong Creator (Beri Tip)</h3>
                </div>
                <p class="text-xs text-slate-400">Sumbang tip secara terus untuk menyokong pembikinan episod baharu {{ $creator->name }}.</p>
            </div>

            <!-- Tipping Form -->
            <form action="{{ route('creator.tip.submit', $creator->id) }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Quick Amount Selector -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">Pilih Jumlah Tip (RM)</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" @click="tipAmount = 2" :class="tipAmount == 2 ? 'bg-red-600 text-white border-red-500' : 'bg-slate-950 text-slate-300 border-white/10'" class="py-2.5 rounded-xl border text-xs font-black transition-all">
                            RM 2
                        </button>
                        <button type="button" @click="tipAmount = 5" :class="tipAmount == 5 ? 'bg-red-600 text-white border-red-500' : 'bg-slate-950 text-slate-300 border-white/10'" class="py-2.5 rounded-xl border text-xs font-black transition-all">
                            RM 5
                        </button>
                        <button type="button" @click="tipAmount = 10" :class="tipAmount == 10 ? 'bg-red-600 text-white border-red-500' : 'bg-slate-950 text-slate-300 border-white/10'" class="py-2.5 rounded-xl border text-xs font-black transition-all">
                            RM 10
                        </button>
                        <button type="button" @click="tipAmount = 50" :class="tipAmount == 50 ? 'bg-red-600 text-white border-red-500' : 'bg-slate-950 text-slate-300 border-white/10'" class="py-2.5 rounded-xl border text-xs font-black transition-all">
                            RM 50
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-300">Atau Masukkan Jumlah Tersuai (RM)</label>
                    <input type="number" step="1" min="1" name="amount" x-model="tipAmount" required class="w-full bg-slate-950 text-white text-base px-4 py-3 rounded-2xl border border-white/10 font-mono font-bold focus:border-red-500">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-300">Nama Anda / Samaran</label>
                    <input type="text" name="fan_name" value="{{ auth()->check() ? auth()->user()->name : '' }}" placeholder="Peminat Setia" required class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:border-red-500">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-300">Mesej Sokongan (Pilihan)</label>
                    <textarea name="message" rows="2" placeholder="Tak sabar tunggu episod seterusnya! Jalan cerita mantap..." class="w-full bg-slate-950 text-white text-xs p-3.5 rounded-2xl border border-white/10 focus:border-red-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-xs font-black text-white shadow-xl shadow-red-950/80 transition-all flex items-center justify-center gap-2 hover:scale-[1.02]">
                    <i data-lucide="heart" class="w-4 h-4 fill-white"></i>
                    <span>Hantar Tip RM <span x-text="tipAmount"></span></span>
                </button>
            </form>

            @if(isset($recentTips) && $recentTips->isNotEmpty())
                <div class="pt-4 border-t border-white/10 space-y-3">
                    <h4 class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Sokongan Peminat Terkini</span>
                    </h4>
                    <div class="space-y-2">
                        @foreach($recentTips as $t)
                            <div class="p-3 rounded-xl bg-black/40 border border-white/5 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white">{{ $t->fan_name }}</span>
                                    <span class="font-mono font-extrabold text-pink-400">+RM {{ number_format($t->amount, 2) }}</span>
                                </div>
                                @if($t->message)
                                    <p class="text-[11px] text-slate-400 italic">"{{ $t->message }}"</p>
                                @endif
                                <span class="text-[9px] text-slate-500 block">{{ $t->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
