@extends('layouts.app')

@section('title', 'Studio Creator - Pengurusan Episod: ' . $drama->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    activeFilter: 'all',
    toastMessage: '',
    toastVisible: false,
    showToast(msg) {
        this.toastMessage = msg;
        this.toastVisible = true;
        setTimeout(() => { this.toastVisible = false; }, 3500);
    },
    async toggleFree(episodeId, buttonEl) {
        try {
            buttonEl.classList.add('opacity-50', 'pointer-events-none');
            const res = await fetch(`/creator/dramas/{{ $drama->id }}/episodes/${episodeId}/toggle-free`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                if (data.is_free_preview) {
                    buttonEl.innerHTML = `<span class='flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20 transition-all'>
                        <span class='w-2 h-2 rounded-full bg-emerald-400 animate-pulse'></span>
                        Percuma (Iklan)
                    </span>`;
                } else {
                    buttonEl.innerHTML = `<span class='flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30 hover:bg-amber-500/20 transition-all'>
                        <i data-lucide='lock' class='w-3.5 h-3.5 text-amber-400'></i>
                        VIP Sahaja
                    </span>`;
                }
                if (window.lucide) window.lucide.createIcons();
                this.showToast(data.message || 'Status akses berjaya ditukar!');
            }
        } catch (e) {
            this.showToast('Gagal menukar status akses. Sila cuba lagi.');
        } finally {
            buttonEl.classList.remove('opacity-50', 'pointer-events-none');
        }
    },
    async toggleStatus(episodeId, newStatus, buttonEl) {
        try {
            buttonEl.classList.add('opacity-50', 'pointer-events-none');
            const res = await fetch(`/creator/dramas/{{ $drama->id }}/episodes/${episodeId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message || 'Status visibiliti dikemaskini!');
                setTimeout(() => window.location.reload(), 600);
            }
        } catch (e) {
            this.showToast('Ralat mengemaskini status visibiliti.');
        } finally {
            buttonEl.classList.remove('opacity-50', 'pointer-events-none');
        }
    }
}">

    <!-- Toast Notification -->
    <div x-show="toastVisible" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900 border border-red-500/50 text-white px-5 py-3.5 rounded-2xl shadow-2xl backdrop-blur-xl"
         style="display: none;">
        <div class="w-6 h-6 rounded-full bg-red-600/20 text-red-400 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="text-xs font-semibold" x-text="toastMessage"></span>
    </div>

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                Utama
            </a>
            <span>/</span>
            <span class="text-slate-300 font-semibold">Studio Creator</span>
            <span>/</span>
            <span class="text-white font-bold truncate max-w-xs">{{ $drama->title }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('drama.show', $drama->slug) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-bold text-slate-300 border border-white/10 transition-colors">
                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Tonton (Awam)</span>
            </a>
            <a href="{{ route('creator.dramas.edit', $drama) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 transition-colors hover:border-amber-400/50">
                <i data-lucide="settings-2" class="w-3.5 h-3.5 text-amber-400"></i>
                <span>Kemaskini Butiran Drama</span>
            </a>
            <a href="{{ route('creator.dramas.episodes.create', $drama) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-lg shadow-red-950/60 transition-all hover:scale-[1.02]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Episod Baharu</span>
            </a>
        </div>
    </div>

    <!-- Drama Profile Header Card -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 sm:p-8 backdrop-blur-xl mb-8 relative overflow-hidden shadow-2xl">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-red-600/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Poster -->
            <div class="w-20 h-28 sm:w-24 sm:h-36 rounded-2xl overflow-hidden bg-slate-800 border border-white/15 shrink-0 shadow-xl">
                <img src="{{ $drama->poster_url ?? '/uploads/posters/ceo-billboard.jpg' }}" alt="{{ $drama->title }}" class="w-full h-full object-cover">
            </div>

            <!-- Series Info -->
            <div class="flex-1 space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600/20 text-red-400 border border-red-500/30">
                        {{ $drama->genre ?? 'Drama' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $drama->release_status_badge['badge'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $drama->release_status_badge['dot'] }} inline-block mr-1"></span>
                        {{ $drama->release_status_badge['label'] }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-white/10 text-white border border-white/15">
                        {{ $drama->age_rating ?? '13+' }}
                    </span>
                    @if($drama->social_hashtag)
                        <span class="text-xs text-red-400 font-bold font-mono">{{ $drama->social_hashtag }}</span>
                    @endif
                    <span class="text-xs text-slate-400">• Dicipta oleh: <strong>{{ $drama->creator->name ?? 'Anda' }}</strong></span>
                </div>

                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $drama->title }}</h1>
                    <a href="{{ route('creator.dramas.edit', $drama) }}" class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-amber-400 transition-colors" title="Kemaskini Butiran Siri (14 Ciri)">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </a>
                </div>

                <p class="text-xs text-slate-400 line-clamp-2 max-w-3xl leading-relaxed">{{ $drama->synopsis }}</p>

                @if(!empty($drama->announcement))
                    <div class="mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2 max-w-3xl">
                        <i data-lucide="megaphone" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span><strong>Pengumuman Creator:</strong> {{ $drama->announcement }}</span>
                    </div>
                @endif

                <!-- Quick Stats Badges -->
                <div class="pt-2 flex flex-wrap items-center gap-3 sm:gap-6 text-xs text-slate-300">
                    <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                        <i data-lucide="layers" class="w-4 h-4 text-red-400"></i>
                        <span><strong>{{ $stats['total'] }}</strong> Episod</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                        <span><strong>{{ $stats['published'] }}</strong> Diterbitkan</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                        <i data-lucide="file-edit" class="w-4 h-4 text-amber-400"></i>
                        <span><strong>{{ $stats['draft'] }}</strong> Draf</span>
                    </div>
                    @if($stats['scheduled'] > 0)
                    <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                        <i data-lucide="calendar" class="w-4 h-4 text-cyan-400"></i>
                        <span><strong>{{ $stats['scheduled'] }}</strong> Dijadual</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-1.5 bg-black/40 px-3 py-1.5 rounded-xl border border-white/5">
                        <i data-lucide="eye" class="w-4 h-4 text-blue-400"></i>
                        <span><strong>{{ number_format($stats['views']) }}</strong> Tontonan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder & Tip Banner (Feature 7) -->
    <div class="rounded-2xl bg-gradient-to-r from-red-950/40 via-slate-900 to-slate-900 border border-red-500/20 p-4 mb-6 flex items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center shrink-0">
                <i data-lucide="move" class="w-5 h-5"></i>
            </div>
            <div class="text-xs">
                <span class="font-bold text-white block">Susun Semula Episod (Drag & Drop)</span>
                <span class="text-slate-400">Pegang dan seret pemegang <span class="text-white font-mono font-bold">:::</span> untuk mengubah urutan episod secara automatik tanpa perlu ubah nombor satu per satu.</span>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-red-400 font-semibold bg-red-500/10 px-2.5 py-1 rounded-lg border border-red-500/20">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            Auto-Save Aktif
        </span>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-2 text-xs font-bold scrollbar-none">
        <button @click="activeFilter = 'all'" 
                :class="activeFilter === 'all' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0">
            Semua ({{ $stats['total'] }})
        </button>
        <button @click="activeFilter = 'published'" 
                :class="activeFilter === 'published' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            Diterbitkan ({{ $stats['published'] }})
        </button>
        <button @click="activeFilter = 'draft'" 
                :class="activeFilter === 'draft' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            Draf ({{ $stats['draft'] }})
        </button>
        @if($stats['scheduled'] > 0)
        <button @click="activeFilter = 'scheduled'" 
                :class="activeFilter === 'scheduled' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            Dijadual ({{ $stats['scheduled'] }})
        </button>
        @endif
        <button @click="activeFilter = 'free'" 
                :class="activeFilter === 'free' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0">
            Percuma ({{ $stats['free'] }})
        </button>
        <button @click="activeFilter = 'vip'" 
                :class="activeFilter === 'vip' ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/60' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2 rounded-xl transition-all shrink-0">
            VIP ({{ $stats['vip'] }})
        </button>
    </div>

    <!-- Episodes List Container (Feature 1: Episode Dashboard) -->
    <div class="rounded-3xl bg-slate-900/80 border border-white/10 overflow-hidden shadow-2xl backdrop-blur-xl">
        
        @if($episodes->isEmpty())
            <!-- Empty State -->
            <div class="py-16 px-6 text-center space-y-4">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-red-600/10 border border-red-500/20 text-red-500 flex items-center justify-center shadow-xl">
                    <i data-lucide="clapperboard" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-white">Belum Ada Episod Dimuat Naik</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Mulakan siri anda dengan memuat naik episod pertama melalui pautan YouTube atau fail MP4 terus.</p>
                </div>
                <a href="{{ route('creator.dramas.episodes.create', $drama) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-lg shadow-red-950/80 transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Muat Naik Episod #1</span>
                </a>
            </div>
        @else
            <!-- Episodes Table / Rows -->
            <div class="divide-y divide-white/[0.06]" id="episodes-sortable">
                @foreach($episodes as $episode)
                    <div class="episode-row group p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors hover:bg-white/[0.02]"
                         data-id="{{ $episode->id }}"
                         data-status="{{ $episode->status }}"
                         data-free="{{ $episode->is_free_preview ? '1' : '0' }}"
                         x-show="activeFilter === 'all' || 
                                 (activeFilter === 'published' && '{{ $episode->status }}' === 'published') ||
                                 (activeFilter === 'draft' && '{{ $episode->status }}' === 'draft') ||
                                 (activeFilter === 'scheduled' && '{{ $episode->status }}' === 'scheduled') ||
                                 (activeFilter === 'free' && '{{ $episode->is_free_preview ? '1' : '0' }}' === '1') ||
                                 (activeFilter === 'vip' && '{{ $episode->is_free_preview ? '1' : '0' }}' === '0')">
                        
                        <!-- Left Details: Drag Handle + Ep Badge + Thumb + Meta -->
                        <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                            
                            <!-- Drag Handle (Feature 7) -->
                            <div class="drag-handle cursor-grab active:cursor-grabbing p-1.5 text-slate-600 group-hover:text-slate-300 hover:text-white rounded-lg hover:bg-white/5 transition-colors" title="Seret untuk ubah urutan">
                                <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                            </div>

                            <!-- Episode Number Badge -->
                            <div class="w-9 h-9 rounded-xl bg-slate-950 border border-white/10 flex items-center justify-center shrink-0">
                                <span class="ep-number text-xs font-black text-white">#{{ $episode->episode_number }}</span>
                            </div>

                            <!-- Thumbnail Preview (Feature 4) -->
                            <div class="relative w-20 sm:w-24 aspect-[9/16] sm:aspect-video rounded-xl overflow-hidden bg-black/60 border border-white/10 shrink-0">
                                <img src="{{ $episode->thumbnail_url }}" alt="{{ $episode->title }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/80 text-[9px] font-mono font-bold text-white">
                                    {{ $episode->formatted_duration }}
                                </span>
                            </div>

                            <!-- Title & Metadata -->
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-bold text-white truncate max-w-md">{{ $episode->title }}</h4>
                                    
                                    <!-- Media Source Badge (Feature 3) -->
                                    @if($episode->isYouTube())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-600/15 text-red-400 border border-red-500/20">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            YouTube
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/20">
                                            <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                            Storan Fail (MP4)
                                        </span>
                                    @endif
                                </div>

                                <!-- Episode Description Snippet (Feature 2) -->
                                <p class="text-xs text-slate-400 line-clamp-1">
                                    {{ $episode->description ?: 'Tiada keterangan episod.' }}
                                </p>

                                <div class="flex items-center gap-4 text-[11px] text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                        {{ number_format($episode->views_count) }} tontonan
                                    </span>
                                    <span>•</span>
                                    <span>Dikemaskini: {{ $episode->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Controls: Status + Access Toggle + Actions -->
                        <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 sm:gap-4 shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-white/5">
                            
                            <!-- Visibility Status Badge & Quick Toggle (Feature 5) -->
                            <div class="relative" x-data="{ open: false }">
                                @if($episode->status === 'published')
                                    <button @click="open = !open" class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 hover:bg-emerald-500/20 transition-all">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Diterbitkan
                                        <i data-lucide="chevron-down" class="w-3 h-3 text-emerald-400"></i>
                                    </button>
                                @elseif($episode->status === 'scheduled')
                                    <button @click="open = !open" class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 flex items-center gap-1.5 hover:bg-cyan-500/20 transition-all" title="Dijadualkan pada: {{ $episode->scheduled_at ? $episode->scheduled_at->format('d/m/Y h:i A') : '-' }}">
                                        <i data-lucide="calendar" class="w-3 h-3 text-cyan-400"></i>
                                        Dijadual ({{ $episode->scheduled_at ? $episode->scheduled_at->format('d M') : '-' }})
                                        <i data-lucide="chevron-down" class="w-3 h-3 text-cyan-400"></i>
                                    </button>
                                @else
                                    <button @click="open = !open" class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center gap-1.5 hover:bg-amber-500/20 transition-all">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Draf
                                        <i data-lucide="chevron-down" class="w-3 h-3 text-amber-400"></i>
                                    </button>
                                @endif

                                <!-- Dropdown menu for fast status change -->
                                <div x-show="open" @click.outside="open = false" 
                                     class="absolute right-0 mt-2 w-44 rounded-2xl bg-slate-900 border border-white/15 p-1.5 shadow-2xl z-30 space-y-1 backdrop-blur-2xl"
                                     style="display: none;">
                                    <button type="button" @click="toggleStatus({{ $episode->id }}, 'published', $el); open = false;" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        Terbitkan Serta-merta
                                    </button>
                                    <button type="button" @click="toggleStatus({{ $episode->id }}, 'draft', $el); open = false;" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-amber-400 hover:bg-amber-500/10 transition-colors">
                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                        Simpan Sebagai Draf
                                    </button>
                                </div>
                            </div>

                            <!-- Paywall / Free Access Toggle (Feature 6) -->
                            <button type="button" 
                                    @click="toggleFree({{ $episode->id }}, $el)" 
                                    class="transition-transform active:scale-95" 
                                    title="Klik untuk togol antara Percuma dan VIP">
                                @if($episode->is_free_preview)
                                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20 transition-all">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Percuma (Iklan)
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30 hover:bg-amber-500/20 transition-all">
                                        <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-400"></i>
                                        VIP Sahaja
                                    </span>
                                @endif
                            </button>

                            <!-- Actions: Preview, Edit, Delete -->
                            <div class="flex items-center gap-1.5">
                                <!-- Watch Preview -->
                                <a href="{{ route('episodes.show', [$drama->slug, $episode->episode_number]) }}" target="_blank" class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors" title="Pratonton Video">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                </a>

                                <!-- Edit Metadata & Swap Source (Feature 2 & 3) -->
                                <a href="{{ route('creator.dramas.episodes.edit', [$drama, $episode]) }}" class="p-2 rounded-xl bg-white/5 hover:bg-red-600/20 text-slate-300 hover:text-red-400 border border-white/5 transition-colors" title="Kemaskini Episod">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('creator.dramas.episodes.destroy', [$drama, $episode]) }}" method="POST" onsubmit="return confirm('Adakah anda pasti ingin memadam Episod #{{ $episode->episode_number }} ({{ addslashes($episode->title) }})? Tindakan ini tidak boleh diundur.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors" title="Padam Episod">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

</div>

<!-- SortableJS for Drag-and-Drop Reordering (Feature 7) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sortableContainer = document.getElementById('episodes-sortable');
        if (!sortableContainer) return;

        new Sortable(sortableContainer, {
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'bg-red-950/40',
            chosenClass: 'scale-[1.01]',
            onEnd: async function () {
                // Collect ordered episode IDs
                const rows = sortableContainer.querySelectorAll('.episode-row');
                const orderedIds = [];
                rows.forEach((row, index) => {
                    const id = row.getAttribute('data-id');
                    if (id) orderedIds.push(id);
                    // Update frontend badge immediately
                    const badge = row.querySelector('.ep-number');
                    if (badge) badge.innerText = '#' + (index + 1);
                });

                try {
                    const res = await fetch('{{ route('creator.dramas.episodes.reorder', $drama) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ order: orderedIds })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const event = new CustomEvent('toast', { detail: '✓ ' + (data.message || 'Susunan episod berjaya disimpan!') });
                        window.dispatchEvent(event);
                    }
                } catch (err) {
                    console.error('Reorder failed', err);
                    alert('Gagal menyimpan susunan episod. Sila muat semula halaman.');
                }
            }
        });

        // Bridge custom event to Alpine toast
        window.addEventListener('toast', (e) => {
            const root = document.querySelector('[x-data]');
            if (root && root._x_dataStack && root._x_dataStack[0]) {
                root._x_dataStack[0].showToast(e.detail);
            }
        });
    });
</script>
@endsection

