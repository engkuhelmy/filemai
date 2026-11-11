@extends('layouts.app')

@section('title', 'Tetapan Harga & Monetisasi Creator - Admin Filemai')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{
        monthlyPrice: {{ $monthlyPrice }},
        yearlyPrice: {{ $yearlyPrice }},
        creatorPackage: {{ $creatorPackage }},
        ratePerBlock: {{ $ratePerBlock }},
        blockSeconds: {{ $blockSeconds }},
        minPayout: {{ $minPayout }},
        
        // Interactive Live Simulator
        simSeconds: 119, // 1 minit 59 saat
        simViews: 10000,
        
        get simMinutesText() {
            const m = Math.floor(this.simSeconds / 60);
            const s = this.simSeconds % 60;
            return m + ' minit ' + s + ' saat';
        },
        get simBlocks() {
            if (this.blockSeconds <= 0) return 0;
            return Math.floor(this.simSeconds / this.blockSeconds);
        },
        get simEarningPerView() {
            return (this.simBlocks * this.ratePerBlock).toFixed(6);
        },
        get simTotalEarnings() {
            return (this.simBlocks * this.ratePerBlock * this.simViews).toFixed(4);
        }
     }">

    <!-- Top Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Kembali ke Admin Dashboard
                </a>
                <span>/</span>
                <span class="text-white font-semibold">Tetapan Monetisasi & Royalti</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-widest">
                    PENTADBIR SISTEM
                </span>
                <span class="text-xs text-slate-400">Kawalan Harga & Formula Royalti Creator</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">
                Tetapan Langganan Penonton & Pulangan Creator
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" 
               class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold border border-white/10 transition-colors flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-purple-400"></i>
                <span>Pusat Analitik</span>
            </a>
            <a href="{{ route('creator.dashboard') }}" 
               class="px-4 py-2.5 rounded-xl bg-red-600/20 hover:bg-red-600/30 text-red-300 text-xs font-bold border border-red-500/30 transition-colors flex items-center gap-2">
                <i data-lucide="clapperboard" class="w-4 h-4 text-red-500"></i>
                <span>Creator Studio</span>
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/50 border border-emerald-500/40 text-emerald-200 text-xs font-semibold flex items-center gap-3 shadow-lg">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-white text-sm">Berjaya Disimpan!</p>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-950/50 border border-red-500/40 text-red-200 text-xs font-semibold space-y-1 shadow-lg">
            <div class="flex items-center gap-2 font-bold text-white text-sm mb-1">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
                <span>Sila semak ralat berikut:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-red-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Monetization Overview Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Jumlah Royalti Terkumpul</span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-emerald-400">RM {{ number_format($totalWalletsEarned, 4) }}</h3>
            </div>
            <p class="text-[11px] text-slate-500">Jumlah keseluruhan dijana oleh semua creator</p>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Baki Semasa Dompet Creator</span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-white">RM {{ number_format($totalWalletsBalance, 4) }}</h3>
            </div>
            <p class="text-[11px] text-slate-500">Baki tersedia untuk dituntut oleh creator</p>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Telah Dikeluarkan (Payouts)</span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-amber-400">RM {{ number_format($totalWalletsWithdrawn, 2) }}</h3>
            </div>
            <p class="text-[11px] text-slate-500">Jumlah royalti yang telah ditunaikan</p>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Permohonan Pengeluaran</span>
                @if ($pendingPayoutsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        {{ $pendingPayoutsCount }} Perlu Tindakan
                    </span>
                @endif
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-black text-purple-400">{{ $pendingPayoutsCount }}</h3>
                <span class="text-xs text-slate-400">menunggu semakan</span>
            </div>
            <p class="text-[11px] text-slate-500">{{ number_format($totalBlocksProcessed) }} blok tontonan direkodkan</p>
        </div>

    </div>

    <!-- Main Settings Form & Live Interactive Simulator Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Form Column (7 Cols) -->
        <div class="lg:col-span-7">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- SEKSYEN 1: Harga Langganan Penonton VIP -->
                <div class="p-6 sm:p-7 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
                    <div class="border-b border-white/10 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center font-bold">
                                <i data-lucide="crown" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-white">1. Harga Langganan Penonton (Pas VIP Bebas Iklan)</h2>
                                <p class="text-xs text-slate-400">Kadar bayaran yang dikenakan kepada penonton untuk melanggan pas VIP tanpa pemasa iklan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Bulanan -->
                        <div class="space-y-2">
                            <label for="viewer_vip_monthly_price" class="block text-xs font-bold text-slate-300">
                                Pas Bulanan (30 Hari) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                                <input type="number" 
                                       step="0.10" 
                                       min="0.50" 
                                       id="viewer_vip_monthly_price" 
                                       name="viewer_vip_monthly_price" 
                                       x-model.number="monthlyPrice" 
                                       value="{{ old('viewer_vip_monthly_price', $monthlyPrice) }}" 
                                       required 
                                       class="w-full bg-slate-950 text-white font-bold text-sm pl-11 pr-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Lalai: RM 14.90 / 30 hari</span>
                        </div>

                        <!-- Tahunan -->
                        <div class="space-y-2">
                            <label for="viewer_vip_yearly_price" class="block text-xs font-bold text-slate-300">
                                Pas Tahunan (365 Hari) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                                <input type="number" 
                                       step="0.50" 
                                       min="1.00" 
                                       id="viewer_vip_yearly_price" 
                                       name="viewer_vip_yearly_price" 
                                       x-model.number="yearlyPrice" 
                                       value="{{ old('viewer_vip_yearly_price', $yearlyPrice) }}" 
                                       required 
                                       class="w-full bg-slate-950 text-white font-bold text-sm pl-11 pr-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-red-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Lalai: RM 129.90 / setahun</span>
                        </div>
                    </div>
                </div>

                <!-- SEKSYEN 2: Pakej Menjadi Video Creator -->
                <div class="p-6 sm:p-7 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
                    <div class="border-b border-white/10 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center font-bold">
                                <i data-lucide="video" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-white">2. Pakej Menjadi Video Creator</h2>
                                <p class="text-xs text-slate-400">Yuran pendaftaran dan pengesahan akaun pembikin siri drama di Filemai Studio.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="creator_package_price" class="block text-xs font-bold text-slate-300">
                            Harga Pakej Pendaftaran Video Creator <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                            <input type="number" 
                                   step="1.00" 
                                   min="0.00" 
                                   id="creator_package_price" 
                                   name="creator_package_price" 
                                   x-model.number="creatorPackage" 
                                   value="{{ old('creator_package_price', $creatorPackage) }}" 
                                   required 
                                   class="w-full bg-slate-950 text-white font-bold text-sm pl-11 pr-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-purple-500">
                        </div>
                        <p class="text-[11px] text-slate-400">
                            Pakej ini memberikan pembikin filem akses Creator Studio rasmi, muat naik siri tanpa had, pengedaran poster 9:16, dan kelayakan royalti penonton.
                        </p>
                    </div>
                </div>

                <!-- SEKSYEN 3: Kadar Pulangan Tontonan Creator (Royalti Blok 1-Minit) -->
                <div class="p-6 sm:p-7 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
                    <div class="border-b border-white/10 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold">
                                <i data-lucide="coins" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-white">3. Formula Royalti Tontonan Creator (Per Block)</h2>
                                <p class="text-xs text-slate-400">Setiap blok 1 minit penuh tontonan video melayakkan creator menerima kadar pulangan ini.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Kadar per Blok -->
                        <div class="space-y-2 sm:col-span-1">
                            <label for="creator_earnings_rate_per_block" class="block text-xs font-bold text-slate-300">
                                Kadar / Blok (RM) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                                <input type="number" 
                                       step="0.000001" 
                                       min="0.000001" 
                                       max="1.000000" 
                                       id="creator_earnings_rate_per_block" 
                                       name="creator_earnings_rate_per_block" 
                                       x-model.number="ratePerBlock" 
                                       value="{{ old('creator_earnings_rate_per_block', $ratePerBlock) }}" 
                                       required 
                                       class="w-full bg-slate-950 text-emerald-400 font-mono font-bold text-sm pl-9 pr-3 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Lalai: RM 0.000100</span>
                        </div>

                        <!-- Tempoh Blok (Saat) -->
                        <div class="space-y-2 sm:col-span-1">
                            <label for="block_duration_seconds" class="block text-xs font-bold text-slate-300">
                                Tempoh Blok (Saat) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       step="1" 
                                       min="10" 
                                       max="3600" 
                                       id="block_duration_seconds" 
                                       name="block_duration_seconds" 
                                       x-model.number="blockSeconds" 
                                       value="{{ old('block_duration_seconds', $blockSeconds) }}" 
                                       required 
                                       class="w-full bg-slate-950 text-white font-mono font-bold text-sm px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">saat</span>
                            </div>
                            <span class="text-[10px] text-slate-500 block">Lalai: 60 saat (1 minit)</span>
                        </div>

                        <!-- Minima Payout -->
                        <div class="space-y-2 sm:col-span-1">
                            <label for="min_payout_amount" class="block text-xs font-bold text-slate-300">
                                Minima Pengeluaran <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                                <input type="number" 
                                       step="1.00" 
                                       min="1.00" 
                                       max="1000.00" 
                                       id="min_payout_amount" 
                                       name="min_payout_amount" 
                                       x-model.number="minPayout" 
                                       value="{{ old('min_payout_amount', $minPayout) }}" 
                                       required 
                                       class="w-full bg-slate-950 text-white font-mono font-bold text-sm pl-9 pr-3 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Lalai: RM 10.00</span>
                        </div>
                    </div>

                    <!-- Syarat Peraturan Blok -->
                    <div class="p-3.5 rounded-2xl bg-slate-950 border border-white/5 space-y-1 text-slate-300 text-xs">
                        <div class="flex items-center gap-1.5 font-bold text-emerald-400">
                            <i data-lucide="info" class="w-4 h-4"></i>
                            <span>Syarat Pengiraan Blok (1 Minit Penuh):</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Pengiraan menggunakan formula pembundaran ke bawah (<code class="text-amber-300 font-mono">floor(durasi / 60 saat)</code>).
                            Jika sesuatu episod ditonton selama <strong>1 minit 59 saat (119 saat)</strong>, sistem hanya mengira <strong>1 blok</strong> (59 saat baki tidak mencukupi untuk blok ke-2). Jika ditonton bawah 60 saat, 0 blok dikira.
                        </p>
                    </div>
                </div>

                <!-- Simpan Butang -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" 
                            class="px-8 py-3.5 rounded-2xl bg-[#E50914] hover:bg-[#F40612] text-white font-black text-xs shadow-xl shadow-red-950/80 transition-all hover:scale-105 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Semua Tetapan Sistem</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Right Column: Interactive Live Simulator & Formula Explainer (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Interactive Block Simulator Card -->
            <div class="p-6 rounded-3xl bg-slate-900 border border-emerald-500/30 shadow-2xl space-y-5 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-emerald-400">Simulator Pengiraan Blok Langsung</h3>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Real-time Preview</span>
                </div>

                <div class="space-y-4">
                    <!-- Input Test Durasi -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <label class="font-bold text-slate-300">Tempoh Video Ditonton:</label>
                            <span class="font-bold text-emerald-400 font-mono" x-text="simMinutesText + ' (' + simSeconds + 's)'"></span>
                        </div>
                        <input type="range" 
                               min="10" 
                               max="600" 
                               step="1" 
                               x-model.number="simSeconds" 
                               class="w-full accent-emerald-500 cursor-pointer">
                        <div class="flex justify-between text-[10px] text-slate-500">
                            <button type="button" @click="simSeconds = 59" class="hover:text-white underline">59s (0 Blok)</button>
                            <button type="button" @click="simSeconds = 119" class="hover:text-emerald-300 underline font-bold">1m 59s (1 Blok)</button>
                            <button type="button" @click="simSeconds = 120" class="hover:text-white underline">2m 00s (2 Blok)</button>
                            <button type="button" @click="simSeconds = 300" class="hover:text-white underline">5m 00s (5 Blok)</button>
                        </div>
                    </div>

                    <!-- Input Test Tontonan -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <label class="font-bold text-slate-300">Anggaran Bilangan Tontonan:</label>
                            <span class="font-bold text-white font-mono" x-text="Number(simViews).toLocaleString() + ' tontonan'"></span>
                        </div>
                        <input type="range" 
                               min="100" 
                               max="500000" 
                               step="1000" 
                               x-model.number="simViews" 
                               class="w-full accent-red-500 cursor-pointer">
                    </div>

                    <!-- Live Calculation Breakdown Display -->
                    <div class="p-4 rounded-2xl bg-slate-950 border border-white/10 space-y-3 font-mono text-xs">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Formula Blok:</span>
                            <span class="text-white">floor(<span x-text="simSeconds"></span>s / <span x-text="blockSeconds"></span>s)</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/5 pt-2">
                            <span class="text-slate-400">Bilangan Blok Diperoleh:</span>
                            <span class="font-bold text-amber-400 text-sm" x-text="simBlocks + ' BLOK'"></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/5 pt-2">
                            <span class="text-slate-400">Pulangan 1 Tontonan:</span>
                            <span class="font-bold text-emerald-400">RM <span x-text="simEarningPerView"></span></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/10 pt-2 text-sm bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-500/20">
                            <span class="text-emerald-300 font-bold">Jumlah Royalti Creator:</span>
                            <span class="font-black text-emerald-400 text-base">RM <span x-text="simTotalEarnings"></span></span>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 space-y-1">
                    <p>💡 <em>Contoh:</em> 10,000 tontonan video bersaiz 1 minit 59 saat menghasilkan <strong>10,000 blok</strong> layak = <strong>RM 1.00</strong> royalti.</p>
                </div>
            </div>

            <!-- Quick Links to VIP Page & Creator Studio -->
            <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 space-y-3 text-xs">
                <span class="font-bold text-slate-300 uppercase tracking-wider block">Pautan Pantas Ujian</span>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('subscribe') }}" target="_blank" 
                       class="p-3 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/5 text-slate-300 hover:text-white transition-all flex items-center justify-between">
                        <span>Halaman Pas VIP</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                    </a>
                    <a href="{{ route('creator.dashboard') }}" 
                       class="p-3 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/5 text-slate-300 hover:text-white transition-all flex items-center justify-between">
                        <span>Creator Studio</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- SEKSYEN 4: Senarai Permohonan Pengeluaran Wang Creator (Creator Payouts) -->
    <div class="p-6 sm:p-7 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/10 pb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center font-bold">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-white">4. Pengurusan Permohonan Pengeluaran Wang Creator (Payouts)</h2>
                    <p class="text-xs text-slate-400">Semak dan sahkan pindahan wang royalti ke akaun bank creator.</p>
                </div>
            </div>

            <span class="text-xs text-slate-400">
                Jumlah permohonan: <strong class="text-white">{{ $recentPayouts->count() }}</strong>
            </span>
        </div>

        @if ($recentPayouts->isEmpty())
            <div class="text-center py-10 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-white/5 text-slate-500 flex items-center justify-center mx-auto">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-300">Tiada Permohonan Pengeluaran Pada Masa Ini</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Apabila video creator memohon pengeluaran baki royalti (minima RM {{ number_format($minPayout, 2) }}), ia akan dipaparkan di sini untuk kelulusan pentadbir.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-slate-400 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">ID / Tarikh</th>
                            <th class="py-3 px-3">Nama Creator</th>
                            <th class="py-3 px-3">Maklumat Bank</th>
                            <th class="py-3 px-3">Jumlah Dimohon</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($recentPayouts as $payout)
                            @php $badge = $payout->status_badge; @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-white block">#PAY-{{ $payout->id }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $payout->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-semibold text-slate-200 block">{{ $payout->creator->name ?? 'Creator #' . $payout->creator_id }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $payout->creator->email ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-3 space-y-0.5">
                                    <span class="font-bold text-slate-300 block">{{ $payout->bank_name }}</span>
                                    <span class="font-mono text-white block">{{ $payout->account_number }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $payout->account_holder }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="text-sm font-black text-emerald-400 font-mono">RM {{ number_format($payout->amount, 2) }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badge['class'] }} inline-flex items-center gap-1">
                                        <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"></i>
                                        {{ $badge['label'] }}
                                    </span>
                                    @if ($payout->reference_no)
                                        <span class="block text-[10px] text-slate-400 font-mono mt-0.5">Ref: {{ $payout->reference_no }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if ($payout->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Approve Form -->
                                            <form action="{{ route('admin.settings.payout.update', $payout) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                <input type="hidden" name="reference_no" value="TXN-{{ time() }}-{{ rand(100, 999) }}">
                                                <button type="submit" 
                                                        onclick="return confirm('Sahkan pindahan dana RM {{ $payout->amount }} telah dibuat kepada {{ $payout->account_holder }}?')"
                                                        class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition-all">
                                                    Sahkan Bayaran
                                                </button>
                                            </form>

                                            <!-- Reject Form -->
                                            <form action="{{ route('admin.settings.payout.update', $payout) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" 
                                                        onclick="return confirm('Tolak permohonan ini dan pulangkan baki RM {{ $payout->amount }} ke dompet creator?')"
                                                        class="px-2.5 py-1.5 rounded-lg bg-red-600/20 hover:bg-red-600/40 text-red-300 font-bold text-[10px] border border-red-500/30 transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-500">Telah Disemak</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

