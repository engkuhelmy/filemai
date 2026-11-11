@extends('layouts.app')

@section('title', 'Studio Creator Hub - ' . $creator->name . ' | Filemai.my')

@section('content')
<div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    activeTab: 'wallet',
    payoutModal: false,
    payoutAmount: 50,
    notificationOpen: false
}">

    <!-- Top Alert / Flash Message -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs flex items-center justify-between shadow-xl">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-white">✕</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-950/80 border border-red-500/40 text-red-200 text-xs flex items-center gap-2 shadow-xl">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400 shrink-0"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <!-- Creator Profile Header Banner (Ciri 6, 12, 13) -->
    <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 sm:p-8 backdrop-blur-xl mb-8 relative overflow-hidden shadow-2xl">
        <div class="absolute -right-20 -bottom-20 w-96 h-96 rounded-full bg-red-600/10 blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <!-- Avatar & Bio Details -->
            <div class="flex items-start sm:items-center gap-4 sm:gap-6">
                <div class="relative shrink-0">
                    <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-slate-800 border-2 border-white/20 shadow-xl">
                        @if($creator->avatar_url)
                            <img src="{{ $creator->avatar_url }}" alt="{{ $creator->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl font-black text-white bg-gradient-to-tr from-red-600 to-amber-600">
                                {{ strtoupper(substr($creator->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <!-- Level Badge Icon -->
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-black border border-white/20 shadow"
                          :class="'{{ $creatorLevel['badge'] }}'">
                        ★
                    </span>
                </div>

                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-white truncate">{{ $creator->name }}</h1>
                        
                        <!-- Ciri 12: Creator Level Badge -->
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border flex items-center gap-1.5 {{ $creatorLevel['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $creatorLevel['dot'] }}"></span>
                            {{ $creatorLevel['name'] }}
                        </span>

                        @if($creator->is_bank_verified)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1" title="Akaun Bank Disahkan">
                                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-400"></i>
                                Bank Sah
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-400 max-w-xl truncate">
                        {{ $creator->headline ?: 'Pencipta Filem & Siri Drama Menegak Filemai.my' }}
                    </p>

                    <!-- Milestone Progress Bar to next level (Ciri 12) -->
                    @if($creatorLevel['next_threshold'])
                        <div class="pt-1 flex items-center gap-3 text-[11px] text-slate-400 max-w-md">
                            <span class="shrink-0 text-[10px] font-mono">{{ number_format($creatorLevel['views']) }} / {{ number_format($creatorLevel['next_threshold']) }} tontonan</span>
                            <div class="flex-1 h-1.5 bg-black/60 rounded-full overflow-hidden border border-white/10">
                                <div class="h-full bg-gradient-to-r from-red-600 to-amber-400 rounded-full" style="width: {{ $creatorLevel['progress'] }}%"></div>
                            </div>
                            <span class="shrink-0 font-bold text-amber-400 text-[10px]">{{ $creatorLevel['progress'] }}%</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Header Quick Actions (Ciri 6, 13) -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                
                <!-- Notification Bell (Ciri 13) -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" class="p-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-300 hover:text-white border border-white/10 transition-colors relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        @if($notifications->where('is_read', false)->count() > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-600 text-white text-[9px] font-bold flex items-center justify-center animate-pulse">
                                {{ $notifications->where('is_read', false)->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Notification Dropdown Modal -->
                    <div x-show="notifOpen" @click.outside="notifOpen = false" 
                         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-slate-900 border border-white/15 p-4 shadow-2xl z-50 space-y-3 backdrop-blur-2xl"
                         style="display: none;">
                        <div class="flex items-center justify-between border-b border-white/10 pb-2">
                            <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                <i data-lucide="bell" class="w-3.5 h-3.5 text-red-500"></i>
                                Notifikasi Kewangan &amp; Aktiviti
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $notifications->count() }} aktiviti</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto space-y-2 divide-y divide-white/5 scrollbar-thin">
                            @forelse($notifications as $n)
                                <div class="pt-2 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-white">{{ $n->title }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-slate-400 text-[11px]">{{ $n->message }}</p>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 text-center py-4">Tiada notifikasi baharu.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- View Public Profile (Ciri 6) -->
                <a href="{{ route('creator.public-profile', $creator->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 transition-colors flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                    <span>Profil Awam ↗</span>
                </a>

                <!-- Request Payout Quick Button (Ciri 1, 3) -->
                <button type="button" @click="payoutModal = true" class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-lg shadow-red-950/60 transition-all hover:scale-105 flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Tarik Pendapatan (RM)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (15 Features) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-3 mb-6 text-xs font-bold scrollbar-none border-b border-white/10">
        <button @click="activeTab = 'wallet'" 
                :class="activeTab === 'wallet' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="coins" class="w-4 h-4"></i>
            <span>Dompet &amp; Monitisasi</span>
        </button>

        <button @click="activeTab = 'analytics'" 
                :class="activeTab === 'analytics' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
            <span>Analitik &amp; Demografi</span>
        </button>

        <button @click="activeTab = 'dramas'" 
                :class="activeTab === 'dramas' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="film" class="w-4 h-4"></i>
            <span>Karya Drama ({{ $dramas->count() }})</span>
        </button>

        <button @click="activeTab = 'tips_promos'" 
                :class="activeTab === 'tips_promos' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="gift" class="w-4 h-4"></i>
            <span>Tips Peminat &amp; Promo</span>
        </button>

        <button @click="activeTab = 'community'" 
                :class="activeTab === 'community' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4"></i>
            <span>Pusat Komuniti</span>
        </button>

        <button @click="activeTab = 'settings'" 
                :class="activeTab === 'settings' ? 'bg-[#E50914] text-white shadow-lg' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/10'"
                class="px-4 py-2.5 rounded-xl transition-all shrink-0 flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4"></i>
            <span>Profil, Bank &amp; Pasukan</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: DOMPET & MONITISASI (Ciri 1, 2, 3, 5)             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'wallet'" class="space-y-8" x-transition>
        
        <!-- 4 Key Income Stat Cards (Ciri 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Current Balance -->
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-2 relative overflow-hidden shadow-xl">
                <span class="text-xs font-bold text-slate-400 block">Baki Semasa Dompet</span>
                <div class="text-2xl sm:text-3xl font-black text-white font-mono">
                    RM {{ number_format($wallet->current_balance, 2) }}
                </div>
                <div class="text-[11px] text-emerald-400 flex items-center gap-1 font-semibold">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    <span>Tersedia untuk pengeluaran</span>
                </div>
            </div>

            <!-- Available for Withdrawal -->
            <div class="rounded-3xl bg-slate-900/90 border border-emerald-500/30 p-6 space-y-2 relative overflow-hidden shadow-xl bg-gradient-to-br from-emerald-950/20 to-slate-900">
                <span class="text-xs font-bold text-slate-400 block">Sedia Untuk Dikeluarkan</span>
                <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono">
                    RM {{ number_format($availableForWithdrawal, 2) }}
                </div>
                <button type="button" @click="payoutModal = true" class="text-[11px] text-emerald-300 font-bold underline hover:text-white">
                    Mohon Pengeluaran Bank &rarr;
                </button>
            </div>

            <!-- Total Earned (Lifetime) -->
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-2 relative overflow-hidden shadow-xl">
                <span class="text-xs font-bold text-slate-400 block">Jumlah Keseluruhan Pendapatan</span>
                <div class="text-2xl sm:text-3xl font-black text-amber-400 font-mono">
                    RM {{ number_format($wallet->total_earned, 2) }}
                </div>
                <div class="text-[11px] text-slate-400">
                    Kombinasi Iklan, Paywall &amp; Tips
                </div>
            </div>

            <!-- Total Withdrawn -->
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-2 relative overflow-hidden shadow-xl">
                <span class="text-xs font-bold text-slate-400 block">Jumlah Telah Dikeluarkan</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-300 font-mono">
                    RM {{ number_format($wallet->total_withdrawn, 2) }}
                </div>
                <div class="text-[11px] text-slate-400">
                    Dipindahkan ke akaun bank
                </div>
            </div>

        </div>

        <!-- Ciri 2: Laporan Monitisasi Terperinci (Breakdown) -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-red-500"></i>
                        Laporan Monitisasi Terperinci (Model Perniagaan)
                    </h3>
                    <p class="text-xs text-slate-400">Analisis saluran pendapatan utama karya siri drama anda di Filemai.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">Platform Share: 85% Creator / 15% Server Host</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Ad Share -->
                <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-2">
                    <span class="text-xs font-bold text-slate-400 block">Kongsi Hasil Iklan (45%)</span>
                    <div class="text-xl font-bold text-white font-mono">RM {{ number_format($monetizationBreakdown['ad_share'], 2) }}</div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: 45%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500">Daripada tayangan iklan intersisial penonton percuma.</p>
                </div>

                <!-- Paywall Episodes -->
                <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-2">
                    <span class="text-xs font-bold text-slate-400 block">Jualan Episod Paywall (35%)</span>
                    <div class="text-xl font-bold text-emerald-400 font-mono">RM {{ number_format($monetizationBreakdown['paywall'], 2) }}</div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: 35%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500">Pembelian episod VIP berbayar.</p>
                </div>

                <!-- Season Bundle -->
                <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-2">
                    <span class="text-xs font-bold text-slate-400 block">Pakej Musim / Bundle (15%)</span>
                    <div class="text-xl font-bold text-amber-400 font-mono">RM {{ number_format($monetizationBreakdown['season_pass'], 2) }}</div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: 15%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500">Jualan terus satu musim penuh.</p>
                </div>

                <!-- Fan Tips -->
                <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-2">
                    <span class="text-xs font-bold text-slate-400 block">Tip &amp; Derma Peminat (5%)</span>
                    <div class="text-xl font-bold text-purple-400 font-mono">RM {{ number_format($monetizationBreakdown['tips'], 2) }}</div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full" style="width: 5%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500">Sumbangan ikhlas peminat setia.</p>
                </div>
            </div>
        </div>

        <!-- Ciri 5: Sejarah Transaksi & Payout (Penyata Kewangan) -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                        Sejarah Transaksi &amp; Penyata Pengeluaran
                    </h3>
                    <p class="text-xs text-slate-400">Rekod pengeluaran wang dan pemindahan ke akaun bank tempatan anda.</p>
                </div>

                <a href="{{ route('creator.wallet.export') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-white border border-white/10 transition-colors flex items-center gap-1.5 self-start sm:self-auto">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-cyan-400"></i>
                    <span>Muat Turun Penyata CSV (LHDN)</span>
                </a>
            </div>

            <!-- Table of Payout Requests -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-slate-400 font-bold uppercase text-[10px]">
                            <th class="py-3 px-3">No. Rujukan</th>
                            <th class="py-3 px-3">Tarikh</th>
                            <th class="py-3 px-3">Akaun Bank</th>
                            <th class="py-3 px-3 text-right">Jumlah (RM)</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($payouts as $p)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3 font-mono font-bold text-white">{{ $p->reference_no }}</td>
                                <td class="py-3 px-3 text-slate-400">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-3 text-slate-300">{{ $p->bank_name }} - {{ substr($p->account_number, 0, 4) }}****</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-400">RM {{ number_format($p->amount, 2) }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $p->status_badge['badge'] }}">
                                        {{ $p->status_badge['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">
                                    Belum ada rekod pengeluaran. Apabila baki melebihi RM 50.00, anda boleh memohon payout.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: ANALITIK & DEMOGRAFI (Ciri 4 & Ciri 8)             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'analytics'" class="space-y-8" x-transition style="display: none;">
        
        <!-- Content Analytics Overview (Ciri 4) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-1">
                <span class="text-xs font-bold text-slate-400">Jumlah Tontonan Unik</span>
                <div class="text-2xl font-black text-white font-mono">{{ number_format($totalViews) }}</div>
                <p class="text-[10px] text-slate-500">Sepanjang siri drama anda</p>
            </div>
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-1">
                <span class="text-xs font-bold text-slate-400">Anggaran Minit Ditonton</span>
                <div class="text-2xl font-black text-cyan-400 font-mono">{{ number_format($estimatedMinutesWatched) }} min</div>
                <p class="text-[10px] text-slate-500">Total masa audiens terpaku</p>
            </div>
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-1">
                <span class="text-xs font-bold text-slate-400">Purata Kadar Retensi</span>
                <div class="text-2xl font-black text-emerald-400 font-mono">{{ $averageRetention }}%</div>
                <p class="text-[10px] text-slate-500">Penonton tamat hingga saat akhir</p>
            </div>
            <div class="rounded-3xl bg-slate-900/90 border border-white/10 p-6 space-y-1">
                <span class="text-xs font-bold text-slate-400">Jumlah Episod Terbitan</span>
                <div class="text-2xl font-black text-amber-400 font-mono">{{ $totalEpisodes }}</div>
                <p class="text-[10px] text-slate-500">Format 9:16 sinematik</p>
            </div>
        </div>

        <!-- Ciri 8: Demografi Penonton Saluran -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-400"></i>
                    Demografi Penonton Saluran (*Audience Demographics*)
                </h3>
                <p class="text-xs text-slate-400">Ketahui siapa penonton anda untuk menyusun genre dan strategi penceritaan seterusnya.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Devices -->
                <div class="p-5 rounded-2xl bg-black/40 border border-white/5 space-y-3">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="smartphone" class="w-4 h-4 text-cyan-400"></i>
                        Peranti Digunakan
                    </span>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-300">
                            <span>Telefon Pintar (Mobile)</span>
                            <span class="font-bold text-white">{{ $demographics['devices']['Mobile'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-cyan-500 rounded-full" style="width: {{ $demographics['devices']['Mobile'] }}%"></div>
                        </div>

                        <div class="flex justify-between text-slate-300 pt-1">
                            <span>Komputer / Desktop</span>
                            <span class="font-bold text-white">{{ $demographics['devices']['Desktop'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-slate-500 rounded-full" style="width: {{ $demographics['devices']['Desktop'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Gender -->
                <div class="p-5 rounded-2xl bg-black/40 border border-white/5 space-y-3">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="user" class="w-4 h-4 text-pink-400"></i>
                        Jantina Penonton
                    </span>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-300">
                            <span>Wanita (Peminat Romantik/CEO)</span>
                            <span class="font-bold text-pink-400">{{ $demographics['gender']['Wanita'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-pink-500 rounded-full" style="width: {{ $demographics['gender']['Wanita'] }}%"></div>
                        </div>

                        <div class="flex justify-between text-slate-300 pt-1">
                            <span>Lelaki (Peminat Action/Dendam)</span>
                            <span class="font-bold text-blue-400">{{ $demographics['gender']['Lelaki'] }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $demographics['gender']['Lelaki'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Peak Hours -->
                <div class="p-5 rounded-2xl bg-black/40 border border-white/5 space-y-3">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                        Waktu Puncak Menonton
                    </span>
                    <div class="text-lg font-black text-amber-300 font-mono pt-1">
                        {{ $demographics['peak_hours'] }}
                    </div>
                    <p class="text-[11px] text-slate-400">
                        Waktu paling ideal untuk menerbitkan episod baharu agar mendapat tontonan tertinggi.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: KARYA DRAMA SAYA                                  -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'dramas'" class="space-y-6" x-transition style="display: none;">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <h3 class="text-base font-bold text-white">Senarai Siri Drama Milik Anda</h3>
                <p class="text-xs text-slate-400">Urus episod, tukar poster, atau sunting metadata 14 ciri siri drama anda.</p>
            </div>
            <a href="/creator/dramas/1/episodes/create" class="px-4 py-2 rounded-xl bg-[#E50914] text-xs font-bold text-white flex items-center gap-1.5 shadow">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Upload Episod Baharu</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($dramas as $d)
                <div class="rounded-3xl bg-slate-900 border border-white/10 overflow-hidden shadow-2xl flex flex-col justify-between group">
                    <div class="p-5 space-y-4">
                        <div class="flex gap-4">
                            <div class="w-20 h-28 rounded-xl overflow-hidden bg-slate-800 border border-white/10 shrink-0">
                                <img src="{{ $d->poster_url }}" alt="{{ $d->title }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase text-red-400 bg-red-600/20 border border-red-500/30">
                                        {{ $d->genre }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold border {{ $d->type_badge['badge'] }}">
                                        {{ $d->type_badge['label'] }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-white truncate">{{ $d->title }}</h4>
                                <div class="text-[11px] text-slate-400">
                                    <span>{{ $d->episodes_count }} Episod</span> • <span>{{ number_format($d->views_count) }} Views</span>
                                </div>
                                <span class="inline-block text-[10px] font-bold {{ $d->release_status_badge['badge'] }} px-2 py-0.5 rounded-full mt-1">
                                    {{ $d->release_status_badge['label'] }}
                                </span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400 line-clamp-2">{{ $d->synopsis }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="p-4 bg-black/40 border-t border-white/5 flex items-center justify-between gap-2 text-xs">
                        <a href="{{ route('creator.dramas.manage', $d) }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold transition-colors">
                            Urus Episod
                        </a>
                        <a href="{{ route('creator.dramas.edit', $d) }}" class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-amber-600/20 text-amber-300 font-bold transition-colors">
                            Sunting Butiran (14 Ciri)
                        </a>
                        <a href="{{ route('drama.show', $d->slug) }}" target="_blank" class="p-2 text-slate-400 hover:text-white" title="Lihat Halaman Awam">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: TIPS & KOD PROMOSI (Ciri 9 & Ciri 11)             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'tips_promos'" class="space-y-8" x-transition style="display: none;">
        
        <!-- Ciri 9: Sistem Tipping / Derma Peminat -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4 text-pink-500"></i>
                        Sumbangan Tip Peminat (*Fan Tipping*)
                    </h3>
                    <p class="text-xs text-slate-400">Penonton boleh menyumbang duit terus kepada anda sebagai tanda sokongan.</p>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-pink-500/10 border border-pink-500/30 text-pink-300 text-xs font-bold font-mono">
                    Total Tips: RM {{ number_format($totalTipsAmount, 2) }}
                </div>
            </div>

            <div class="space-y-3">
                @forelse($tips as $t)
                    <div class="p-4 rounded-2xl bg-black/40 border border-white/5 flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white">{{ $t->fan_name }}</span>
                                <span class="text-[10px] text-slate-500">• {{ $t->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-pink-300 italic font-serif">"{{ $t->message ?: 'Terima kasih atas karya drama yang hebat!' }}"</p>
                        </div>
                        <div class="text-sm font-black font-mono text-emerald-400 shrink-0">
                            +RM {{ number_format($t->amount, 2) }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">Belum ada tip diterima lagi. Tip akan muncul di sini bila peminat menekan butang 'Beri Tip'.</p>
                @endforelse
            </div>
        </div>

        <!-- Ciri 11: Penjanaan Kod Promosi (Promo Codes) -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-cyan-400"></i>
                    Penjanaan Kod Promosi (*Promo Codes*)
                </h3>
                <p class="text-xs text-slate-400">Cipta kod diskaun khas (contoh: TIKTOK20) untuk menarik peminat media sosial menonton siri anda.</p>
            </div>

            <!-- Create Promo Form -->
            <form action="{{ route('creator.promos.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-3 bg-black/40 p-4 rounded-2xl border border-white/5 items-end">
                @csrf
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Kod Promo</label>
                    <input type="text" name="code" placeholder="TIKTOK20" required class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10 uppercase font-mono">
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Jenis Diskaun</label>
                    <select name="discount_type" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                        <option value="percent">Peratusan (%)</option>
                        <option value="fixed">Jumlah Tetap (RM)</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Nilai (cth: 20)</label>
                    <input type="number" step="0.5" name="discount_value" placeholder="20" required class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Had Penggunaan</label>
                    <input type="number" name="usage_limit" placeholder="100" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-xs font-bold text-white transition-colors">
                    + Cipta Kod
                </button>
            </form>

            <!-- Active Promos Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-slate-400 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3">Kod</th>
                            <th class="py-2.5 px-3">Diskaun</th>
                            <th class="py-2.5 px-3">Penggunaan</th>
                            <th class="py-2.5 px-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($promoCodes as $pc)
                            <tr>
                                <td class="py-3 px-3 font-mono font-bold text-cyan-300">{{ $pc->code }}</td>
                                <td class="py-3 px-3 text-slate-300">
                                    {{ $pc->discount_type === 'percent' ? $pc->discount_value . '%' : 'RM ' . number_format($pc->discount_value, 2) }}
                                </td>
                                <td class="py-3 px-3 text-slate-400 font-mono">
                                    {{ $pc->times_used }} / {{ $pc->usage_limit ?: '∞' }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <form action="{{ route('creator.promos.destroy', $pc) }}" method="POST" onsubmit="return confirm('Padam kod ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-400">Padam</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">Belum ada kod promosi dicipta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: PUSAT KOMUNITI (Ciri 10)                           -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'community'" class="space-y-6" x-transition style="display: none;">
        <div class="border-b border-white/10 pb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4 text-cyan-400"></i>
                Pusat Pengurusan Komuniti (*Community Hub*)
            </h3>
            <p class="text-xs text-slate-400">Balas komen penonton, pin ulasan terbaik, atau padam komen spam merentasi semua siri drama anda.</p>
        </div>

        <div class="space-y-4">
            @forelse($recentComments as $comment)
                <div class="p-5 rounded-2xl bg-slate-900 border border-white/10 space-y-3" x-data="{ replyBox: false }">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white">{{ $comment->author_name }}</span>
                                <span class="text-[10px] text-slate-500">• Siri: <strong>{{ $comment->drama->title ?? '-' }}</strong> (Ep {{ $comment->episode->episode_number ?? '?' }})</span>
                                @if($comment->is_pinned)
                                    <span class="px-2 py-0.2 rounded text-[9px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-0.5">
                                        📌 Dipin
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">{{ $comment->content }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <form action="{{ route('creator.comments.reply', $comment) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="toggle_pin" value="1">
                                <button type="submit" class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-amber-400 text-xs" title="Pin / Unpin">
                                    📌
                                </button>
                            </form>

                            <button @click="replyBox = !replyBox" class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white text-xs">
                                💬 Balas
                            </button>

                            <form action="{{ route('creator.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Padam komen ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 text-xs">
                                    ✕
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Creator Reply Display -->
                    @if($comment->creator_reply)
                        <div class="pl-4 border-l-2 border-red-500 text-xs space-y-1 bg-white/[0.02] p-2.5 rounded-r-xl">
                            <span class="font-bold text-red-400 text-[10px] block">Balasan Anda:</span>
                            <p class="text-slate-300">{{ $comment->creator_reply }}</p>
                        </div>
                    @endif

                    <!-- Reply Form Drawer -->
                    <form x-show="replyBox" action="{{ route('creator.comments.reply', $comment) }}" method="POST" class="pt-2 flex gap-2">
                        @csrf
                        <input type="text" name="creator_reply" value="{{ $comment->creator_reply }}" placeholder="Tulis balasan kepada peminat..." required class="flex-1 bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10 focus:outline-none focus:border-red-500">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-xs font-bold text-white">Hantar</button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-500 text-center py-10">Belum ada komen penonton.</p>
            @endforelse
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 6: PROFIL, BANK & PASUKAN (Ciri 3, 6, 7, 14, 15)      -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'settings'" class="space-y-8" x-transition style="display: none;">
        
        <!-- Ciri 6 & 7: Profil Identiti Awam & Pautan Sosial -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i>
                    Profil Identiti Awam &amp; Ekosistem Sosial (Ciri 6 &amp; 7)
                </h3>
                <p class="text-xs text-slate-400">Maklumat ini akan dipaparkan pada halaman profil awam anda untuk penonton mengenali anda.</p>
            </div>

            <form action="{{ route('creator.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Gelaran / Tajuk Kepakaran (*Headline*)</label>
                        <input type="text" name="headline" value="{{ old('headline', $creator->headline) }}" placeholder="Contoh: Pengarah Siri Romantik No. 1 Filemai" class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Tag Kepakaran (Dipisahkan koma)</label>
                        @php
                            $specs = is_array($creator->specialties) ? implode(', ', $creator->specialties) : '';
                        @endphp
                        <input type="text" name="specialties" value="{{ old('specialties', $specs) }}" placeholder="Director, Screenwriter, Indie Producer" class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Bio Pengarah / Pencipta</label>
                    <textarea name="bio" rows="3" placeholder="Kongsi visi pembikinan filem anda..." class="w-full bg-slate-950 text-white text-xs p-4 rounded-2xl border border-white/10">{{ old('bio', $creator->bio) }}</textarea>
                </div>

                <!-- Avatar & Cover Upload -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Tukar Gambar Avatar Profil</label>
                        <input type="file" name="avatar_file" accept="image/*" class="w-full bg-slate-950 text-slate-300 text-xs p-2 rounded-xl border border-white/10 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-white/10 file:text-white file:text-xs">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Tukar Gambar Banner Profil</label>
                        <input type="file" name="cover_file" accept="image/*" class="w-full bg-slate-950 text-slate-300 text-xs p-2 rounded-xl border border-white/10 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-white/10 file:text-white file:text-xs">
                    </div>
                </div>

                <!-- Social Links (Ciri 7) -->
                @php
                    $soc = is_array($creator->social_links) ? $creator->social_links : [];
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300">TikTok</label>
                        <input type="text" name="tiktok" value="{{ old('tiktok', $soc['tiktok'] ?? '') }}" placeholder="@username" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300">Instagram</label>
                        <input type="text" name="instagram" value="{{ old('instagram', $soc['instagram'] ?? '') }}" placeholder="@username" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300">YouTube</label>
                        <input type="text" name="youtube" value="{{ old('youtube', $soc['youtube'] ?? '') }}" placeholder="@channel" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300">Website</label>
                        <input type="text" name="website" value="{{ old('website', $soc['website'] ?? '') }}" placeholder="https://..." class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-xs font-bold text-white transition-colors">
                        Simpan Profil Awam
                    </button>
                </div>
            </form>
        </div>

        <!-- Ciri 3: Tetapan Akaun Bank & Payout -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-emerald-400"></i>
                    Tetapan Akaun Bank &amp; Payout (Ciri 3)
                </h3>
                <p class="text-xs text-slate-400">Ruang bersekuriti tinggi untuk pendaftaran akaun bank tempatan bagi pemindahan hasil jualan siri.</p>
            </div>

            <form action="{{ route('creator.bank.update') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Nama Bank Tempatan <span class="text-red-500">*</span></label>
                        <select name="bank_name" required class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10">
                            @php
                                $banks = ['Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank', 'AmBank', 'Bank Islam', 'Bank Rakyat', 'OCBC Bank', 'Alliance Bank', 'Affin Bank'];
                            @endphp
                            <option value="">Pilih Bank</option>
                            @foreach($banks as $b)
                                <option value="{{ $b }}" {{ old('bank_name', $creator->bank_name) === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Nombor Akaun Bank <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $creator->bank_account_number) }}" placeholder="123456789012" required class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 font-mono">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Nama Pemegang Akaun (Seperti di IC/Bank) <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $creator->bank_account_holder) }}" placeholder="Nama Penuh" required class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">No. Kad Pengenalan / No. Pendaftaran SSM <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_ic_ssm" value="{{ old('bank_ic_ssm', $creator->bank_ic_ssm) }}" placeholder="900101-14-1234 atau 202301012345" required class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 font-mono">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition-colors">
                        Simpan Maklumat Bank
                    </button>
                </div>
            </form>
        </div>

        <!-- Ciri 14: Pengurusan Pasukan (Role Management) -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-blue-400"></i>
                    Pengurusan Pasukan &amp; Kru (*Role Management*) (Ciri 14)
                </h3>
                <p class="text-xs text-slate-400">Tambah kru (Editor / Uploader) dengan kebenaran terhad tanpa mendedahkan data kewangan atau maklumat bank anda.</p>
            </div>

            <!-- Add Member Form -->
            <form action="{{ route('creator.team.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-black/40 p-4 rounded-2xl border border-white/5 items-end">
                @csrf
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Nama Kru</label>
                    <input type="text" name="name" placeholder="Ahmad Editor" required class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Emel</label>
                    <input type="email" name="email" placeholder="kru@studio.my" required class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                </div>
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-300">Peranan (*Role*)</label>
                    <select name="role" class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10">
                        <option value="editor">Editor (Upload + Sunting Thumbnail)</option>
                        <option value="uploader">Uploader (Upload Episod Sahaja)</option>
                        <option value="moderator">Moderator (Balas Komen Komuniti)</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-xs font-bold text-white transition-colors">
                    + Tambah Kru
                </button>
            </form>

            <!-- Members List -->
            <div class="space-y-2">
                @forelse($teamMembers as $tm)
                    <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-white">{{ $tm->name }}</span>
                            <span class="text-slate-400 text-[11px]">({{ $tm->email }})</span>
                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-cyan-300 uppercase">{{ $tm->role }}</span>
                        </div>
                        <form action="{{ route('creator.team.destroy', $tm) }}" method="POST" onsubmit="return confirm('Padam akses ahli pasukan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-500 hover:text-red-400 text-xs">Batalkan Akses</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Belum ada ahli pasukan ditambah.</p>
                @endforelse
            </div>
        </div>

        <!-- Ciri 15: Tiket Sokongan & DMCA Takedown -->
        <div class="rounded-3xl bg-slate-900/80 border border-white/10 p-6 sm:p-8 space-y-6 shadow-xl">
            <div class="border-b border-white/10 pb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="help-circle" class="w-4 h-4 text-amber-400"></i>
                    Tiket Sokongan &amp; Laporan Pelanggaran Hak Cipta (*Takedown*) (Ciri 15)
                </h3>
                <p class="text-xs text-slate-400">Saluran bantuan pantas sekiranya video anda dicetak rompak atau menghadapi masalah teknikal/payout.</p>
            </div>

            <!-- New Ticket Form -->
            <form action="{{ route('creator.tickets.store') }}" method="POST" class="space-y-3 bg-black/40 p-5 rounded-2xl border border-white/5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-300">Kategori Isu</label>
                        <select name="type" class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10">
                            <option value="takedown">Pelanggaran Hak Cipta / Cetak Rompak (DMCA)</option>
                            <option value="payout">Kelewatan Payout / Isu Bank</option>
                            <option value="technical">Isu Teknikal Video / Encoding</option>
                            <option value="general">Pertanyaan Am</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-300">Tajuk Aduan</label>
                        <input type="text" name="subject" placeholder="Ringkasan isu anda..." required class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-300">Pautan Bukti Pelanggaran (Jika ada cetak rompak)</label>
                    <input type="url" name="proof_url" placeholder="https://tiktok.com/@pirate_account/video/..." class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-300">Keterangan Terperinci</label>
                    <textarea name="description" rows="2" placeholder="Jelaskan masalah secara terperinci..." required class="w-full bg-slate-950 text-white text-xs p-3 rounded-xl border border-white/10"></textarea>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs font-bold text-white transition-colors">
                        Hantar Tiket Sokongan
                    </button>
                </div>
            </form>

            <!-- Ticket List -->
            <div class="space-y-2">
                @forelse($supportTickets as $st)
                    <div class="p-3.5 rounded-2xl bg-black/40 border border-white/5 flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white">{{ $st->subject }}</span>
                                <span class="text-[10px] text-slate-500">• {{ $st->created_at->format('d/m/Y') }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 capitalize">{{ $st->type }}</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $st->status_badge['badge'] }}">
                            {{ $st->status_badge['label'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Tiada tiket sokongan aktif.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- PAYOUT REQUEST POP-UP MODAL (Ciri 1 & Ciri 3)            -->
    <!-- ======================================================== -->
    <div x-show="payoutModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" 
         style="display: none;">
        <div class="relative w-full max-w-md rounded-3xl bg-slate-900 border border-white/20 p-6 sm:p-8 shadow-2xl space-y-6"
             @click.outside="payoutModal = false">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <i data-lucide="wallet" class="w-5 h-5 text-emerald-400"></i>
                    Permohonan Pengeluaran Wang
                </h3>
                <button @click="payoutModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <!-- Current Bank Summary -->
            @if(empty($creator->bank_account_number))
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs space-y-2">
                    <span class="font-bold block">Akaun Bank Belum Didaftarkan</span>
                    <p class="text-[11px]">Sila ke tab "Profil, Bank &amp; Pasukan" untuk mendaftar akaun bank anda terlebih dahulu.</p>
                    <button type="button" @click="payoutModal = false; activeTab = 'settings'" class="text-xs font-bold text-white underline">
                        Daftar Bank Sekarang &rarr;
                    </button>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-black/40 border border-white/5 space-y-1 text-xs">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Akaun Bank Penerima</span>
                    <div class="font-bold text-white">{{ $creator->bank_name }} - {{ $creator->bank_account_number }}</div>
                    <div class="text-slate-400 text-[11px]">{{ $creator->bank_account_holder }}</div>
                </div>

                <form action="{{ route('creator.wallet.payout') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">
                            Jumlah Pengeluaran (RM)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 font-bold text-sm">RM</span>
                            <input type="number" 
                                   name="amount" 
                                   x-model="payoutAmount" 
                                   min="50" 
                                   max="{{ $availableForWithdrawal }}" 
                                   step="1"
                                   required 
                                   class="w-full bg-slate-950 text-white text-base pl-12 pr-4 py-3 rounded-2xl border border-white/10 font-mono font-bold">
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                            <span>Min: RM 50.00</span>
                            <span>Baki Sedia Ada: <strong class="text-emerald-400 font-mono">RM {{ number_format($availableForWithdrawal, 2) }}</strong></span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-[10px] text-slate-400 space-y-0.5">
                        <p>• Permohonan akan diproses dalam tempoh 1 hingga 3 hari bekerja.</p>
                        <p>• Duit akan dipindahkan terus ke akaun bank berdaftar anda.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="payoutModal = false" class="px-5 py-2.5 rounded-xl bg-white/10 text-xs font-bold text-slate-300">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#F40612] text-xs font-black text-white shadow-lg shadow-red-950/80">
                            Sahkan &amp; Tarik Duit
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

</div>
@endsection

