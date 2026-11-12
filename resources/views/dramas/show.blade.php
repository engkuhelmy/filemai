@extends('layouts.app')

@section('title', $drama->effective_meta_title)

@section('content')
<!-- Dynamic Accent & SEO Variables -->
<style>
    :root {
        --drama-accent: {{ $drama->effective_accent_color }};
    }
    .drama-glow {
        box-shadow: 0 10px 30px -10px var(--drama-accent);
    }
    .drama-border-accent {
        border-color: var(--drama-accent);
    }
    .drama-bg-accent {
        background-color: var(--drama-accent);
    }
</style>

<div class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 py-8 space-y-10" x-data="{ trailerModal: false }">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white flex items-center gap-1 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Utama
            </a>
            <span>/</span>
            <span class="text-slate-500">{{ $drama->genre }}</span>
            <span>/</span>
            <span class="text-white truncate max-w-xs font-semibold">{{ $drama->title }}</span>
        </div>

        @auth
            @if(auth()->user()->isAdmin() || (int)$drama->creator_id === (int)auth()->id())
                <a href="{{ route('creator.dramas.edit', $drama) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-bold text-amber-300 border border-white/10 transition-colors">
                    <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                    <span>Studio: Sunting Drama Ini</span>
                </a>
            @endif
        @endauth
    </div>

    <!-- 11. Creator Notice Board Alert Banner (If Any) -->
    @if(!empty($drama->announcement))
        <div class="rounded-2xl p-4 border border-amber-500/40 bg-gradient-to-r from-amber-950/40 via-slate-900 to-slate-900 flex items-center gap-3 text-amber-200 text-xs shadow-xl">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="megaphone" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <span class="font-bold text-amber-300 block">Pengumuman daripada Pencipta (Creator):</span>
                <span>{{ $drama->announcement }}</span>
            </div>
        </div>
    @endif

    <!-- Drama Hero Card (Netflix Backdrop Style) -->
    <div class="relative rounded-3xl overflow-hidden bg-[#141414] border border-white/10 p-6 sm:p-10 shadow-2xl">
        
        <!-- Backdrop Banner (Ciri 1) -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $drama->banner_url ?: $drama->poster_url }}" alt="{{ $drama->title }}" 
                 class="w-full h-full object-cover opacity-25 blur-xl scale-110">
            <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-[#0b0c0f] via-[#0b0c0f]/90 to-[#0b0c0f]/60"></div>
        </div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Vertical Poster (9:16 - Ciri 1) -->
            <div class="md:col-span-4 lg:col-span-3 flex justify-center">
                <div class="w-56 sm:w-64 aspect-[9/16] rounded-2xl overflow-hidden shadow-2xl border border-white/20 relative group">
                    <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Genre & Format Badge -->
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                        <span class="px-2.5 py-1 rounded text-xs font-black text-white drama-bg-accent shadow-lg">
                            {{ $drama->genre }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold border shadow-lg {{ $drama->type_badge['badge'] }}">
                            {{ $drama->type_badge['label'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-black/80 text-white border border-white/20">
                            {{ $drama->age_rating ?? '13+' }}
                        </span>
                    </div>

                    <!-- Series Status Pill (Ciri 7) -->
                    <div class="absolute bottom-3 inset-x-3 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold backdrop-blur-md {{ $drama->release_status_badge['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $drama->release_status_badge['dot'] }}"></span>
                            {{ $drama->release_status_badge['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Drama Info Details -->
            <div class="md:col-span-8 lg:col-span-9 space-y-5">
                <div>
                    <!-- Badges & Ratings -->
                    <div class="flex flex-wrap items-center gap-2.5 mb-2.5 text-xs font-semibold">
                        <span class="px-2.5 py-0.5 rounded-full text-white border border-white/20 font-bold"
                              style="background-color: {{ $drama->effective_accent_color }}30; color: {{ $drama->effective_accent_color }};">
                            9:16 Sinematik Menegak
                        </span>
                        
                        <!-- Format Kandungan Tag (Drama, Short Film, Filem) -->
                        <span class="px-2.5 py-0.5 rounded-full font-extrabold border flex items-center gap-1 {{ $drama->type_badge['badge'] }}">
                            <i data-lucide="{{ $drama->type_badge['icon'] }}" class="w-3.5 h-3.5"></i>
                            <span>{{ $drama->type_badge['full_label'] }}</span>
                        </span>

                        <!-- Release Status Badge (Ciri 7) -->
                        <span class="px-2.5 py-0.5 rounded-full font-bold {{ $drama->release_status_badge['badge'] }}">
                            {{ $drama->release_status_badge['label'] }}
                        </span>

                        <span class="text-slate-400">• <strong>{{ $episodes->count() }}</strong> Episod</span>
                        <span class="text-slate-400">• <strong>{{ number_format($drama->views_count) }}</strong> Tontonan</span>

                        <!-- Age Rating & Warnings (Ciri 10) -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-white/10 text-slate-300 border border-white/10">
                            Rating: {{ $drama->age_rating }}
                        </span>

                        @if($drama->social_hashtag)
                            <span class="text-xs font-mono font-bold text-red-400">{{ $drama->social_hashtag }}</span>
                        @endif
                    </div>

                    <!-- Title (Ciri 4) -->
                    <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight uppercase font-['Bebas_Neue','Inter',sans-serif]">
                        {{ $drama->title }}
                    </h1>

                    <!-- Multi-Genre Tags (Ciri 6) -->
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        @foreach($drama->genres_list as $g)
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-white/5 text-slate-300 border border-white/10">
                                {{ $g }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Rich Synopsis (Ciri 5) -->
                <div class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-3xl whitespace-pre-line">
                    {{ $drama->synopsis }}
                </div>

                <!-- Content Warnings (Ciri 10) -->
                @if(!empty($drama->content_warnings_list))
                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] text-slate-400">
                        <span class="font-bold text-slate-500">Amaran Kandungan:</span>
                        @foreach($drama->content_warnings_list as $cw)
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-300/80 border border-amber-500/20 text-[10px]">
                                {{ $cw }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <!-- Call To Actions (Watch, Trailer, Bundle) -->
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @php
                        $firstEp = $episodes->first();
                        $firstEpNum = $firstEp ? $firstEp->episode_number : 1;
                    @endphp

                    <!-- Big Watch Button -->
                    <a href="{{ route('episodes.show', [$drama->slug, $firstEpNum]) }}" 
                       class="px-8 py-3.5 rounded-full text-xs font-black text-white shadow-2xl transition-all flex items-center gap-2 hover:scale-105"
                       style="background-color: {{ $drama->effective_accent_color }}; box-shadow: 0 10px 25px -5px {{ $drama->effective_accent_color }}80;">
                        <i data-lucide="play" class="w-4 h-4 fill-white"></i>
                        <span>Mula Tonton Episod #{{ $firstEpNum }}</span>
                    </a>

                    <!-- TV Mode Button -->
                    <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => $firstEpNum]) }}" 
                       class="px-5 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-white border border-white/10 transition-colors flex items-center gap-2"
                       title="Tonton di Pelayar Smart TV / Android TV">
                        <i data-lucide="tv" class="w-4 h-4 text-red-500"></i>
                        <span>Strim TV</span>
                    </a>

                    <!-- Trailer Button (Ciri 8) -->
                    @if(!empty($drama->trailer_url))
                        <button type="button" @click="trailerModal = true" class="px-5 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-white border border-white/10 transition-colors flex items-center gap-2">
                            <i data-lucide="film" class="w-4 h-4 text-amber-400"></i>
                            <span>Tonton Trailer</span>
                        </button>
                    @endif

                    <!-- Season Bundle Purchase (Ciri 12) -->
                    @if($drama->is_bundle_enabled && $drama->season_price)
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-950/60 to-slate-900 border border-emerald-500/40 text-emerald-300 text-xs font-bold shadow-lg">
                            <i data-lucide="tag" class="w-4 h-4 text-emerald-400"></i>
                            <span>Pakej Musim Penuh: <strong>RM{{ number_format($drama->season_price, 2) }}</strong></span>
                            @if($drama->bundle_discount_note)
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/20 text-emerald-400">
                                    {{ $drama->bundle_discount_note }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Creator & Social Links Bar (Ciri 3 & Ciri 13) -->
                <div class="pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
                    <a href="{{ route('creator.public-profile', $drama->creator_id ?? 2) }}" class="flex items-center gap-3 group hover:opacity-90 transition">
                        <div class="w-10 h-10 rounded-full bg-white/10 border border-white/15 overflow-hidden flex items-center justify-center font-black text-white shrink-0 group-hover:border-red-500/50 transition">
                            @if($drama->creator && $drama->creator->avatar_url)
                                <img src="{{ $drama->creator->avatar_url }}" alt="{{ $drama->creator->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($drama->creator->name ?? 'C', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <span class="block text-slate-500 text-[10px]">Dicipta &amp; Diterbitkan Oleh</span>
                            <span class="text-white font-bold group-hover:text-red-400 transition-colors flex items-center gap-1">
                                {{ $drama->creator->name ?? 'Pencipta Filemai' }}
                                <i data-lucide="external-link" class="w-3 h-3 text-slate-500"></i>
                            </span>
                        </div>
                    </a>

                    <!-- Social Links (Ciri 13) -->
                    @php
                        $social = $drama->social_links_list;
                    @endphp
                    @if(!empty($social['tiktok']) || !empty($social['instagram']) || !empty($drama->social_hashtag))
                        <div class="flex items-center gap-3">
                            @if(!empty($social['tiktok']))
                                <a href="https://tiktok.com/{{ ltrim($social['tiktok'], '@') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-white text-[11px] font-bold border border-white/10 flex items-center gap-1.5 transition-colors">
                                    <span>TikTok: {{ $social['tiktok'] }}</span>
                                </a>
                            @endif
                            @if(!empty($social['instagram']))
                                <a href="https://instagram.com/{{ ltrim($social['instagram'], '@') }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-white text-[11px] font-bold border border-white/10 flex items-center gap-1.5 transition-colors">
                                    <span>IG: {{ $social['instagram'] }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

    <!-- SEKSYEN PELAKON & PROFIL CREATOR (Ciri 3 & Ciri 9) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Cast & Crew (Ciri 9) -->
        <div class="lg:col-span-2 rounded-3xl bg-slate-900/60 border border-white/10 p-6 sm:p-8 space-y-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-red-500"></i>
                Barisan Pelakon &amp; Watak Utama (*Cast &amp; Crew*)
            </h3>

            @if(!empty($drama->cast_list))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    @foreach($drama->cast_list as $c)
                        <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 text-slate-300 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($c['actor'] ?? 'P', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-white truncate">{{ $c['actor'] }}</h4>
                                <p class="text-[11px] text-slate-400 truncate">{{ $c['character'] ?: 'Pelakon' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500">Maklumat pelakon belum dimuat naik oleh pencipta.</p>
            @endif
        </div>

        <!-- Creator Bio & Director Note (Ciri 3) -->
        <div class="rounded-3xl bg-slate-900/60 border border-white/10 p-6 sm:p-8 space-y-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="clapperboard" class="w-4 h-4 text-amber-400"></i>
                Nota Pengarah &amp; Profil Pencipta
            </h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                {{ $drama->creator_bio ?: 'Pencipta berbakat tempatan yang menghasilkan karya mini drama berformat menegak di platform Filemai.my.' }}
            </p>
            <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                <span class="text-[11px] text-slate-500">Karya rasmi: <strong>{{ $drama->title }}</strong></span>
                <a href="{{ route('creator.public-profile', $drama->creator_id ?? 2) }}" class="text-xs font-bold text-red-400 hover:text-red-300 flex items-center gap-1 transition-colors">
                    <span>Lihat Profil</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- SEKSYEN SENARAI EPISOD -->
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <h3 class="text-xl font-bold text-white">Senarai Episod</h3>
                <p class="text-xs text-slate-400">Tonton mengikut urutan episod atau pilih babak yang anda minati.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-white/10 text-white text-xs font-bold">
                {{ $episodes->count() }} Episod
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($episodes as $ep)
                <a href="{{ route('episodes.show', [$drama->slug, $ep->episode_number]) }}" 
                   class="group relative rounded-2xl overflow-hidden bg-slate-900 border border-white/10 hover:border-red-500/50 transition-all hover:scale-[1.02] shadow-xl flex flex-col">
                    
                    <!-- Thumbnail with 9:16 Aspect -->
                    <div class="relative w-full aspect-[9/16] bg-black/60 overflow-hidden">
                        <img src="{{ $ep->thumbnail_url }}" alt="{{ $ep->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        
                        <!-- Overlay Badges -->
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-black/80 text-white border border-white/10">
                                #{{ $ep->episode_number }}
                            </span>
                        </div>

                        <div class="absolute top-2 right-2">
                            @if($ep->is_free_preview)
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-500/90 text-white shadow">
                                    FREE
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-500/90 text-black shadow flex items-center gap-0.5">
                                    <i data-lucide="lock" class="w-2.5 h-2.5"></i>
                                    VIP
                                </span>
                            @endif
                        </div>

                        <!-- Duration -->
                        <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/80 text-[9px] font-mono text-white">
                            {{ $ep->formatted_duration }}
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-3 flex-1 flex flex-col justify-between">
                        <h4 class="text-xs font-bold text-white group-hover:text-red-400 transition-colors line-clamp-2">
                            {{ $ep->title }}
                        </h4>
                        <div class="flex items-center gap-1 text-[10px] text-slate-500 mt-2">
                            <i data-lucide="eye" class="w-3 h-3"></i>
                            <span>{{ number_format($ep->views_count) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Trailer Modal (Ciri 8) -->
    @if(!empty($drama->trailer_url))
        <div x-show="trailerModal" 
             x-transition.opacity 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" 
             style="display: none;">
            <div class="relative w-full max-w-3xl rounded-3xl bg-slate-900 border border-white/20 p-4 shadow-2xl" 
                 @click.outside="trailerModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="film" class="w-4 h-4 text-amber-400"></i>
                        Trailer Rasmi: {{ $drama->title }}
                    </h3>
                    <button type="button" @click="trailerModal = false" class="text-slate-400 hover:text-white p-1">
                        ✕
                    </button>
                </div>
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black mt-3">
                    @php
                        $trailerId = \App\Helpers\MediaHelper::extractYouTubeId($drama->trailer_url);
                    @endphp
                    @if($trailerId)
                        <iframe src="https://www.youtube.com/embed/{{ $trailerId }}?autoplay=1" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @else
                        <video src="{{ $drama->trailer_url }}" controls autoplay class="w-full h-full object-contain"></video>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

