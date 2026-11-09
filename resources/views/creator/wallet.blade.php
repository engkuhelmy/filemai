@extends('layouts.app')

@section('title', 'Dompet Royalti & Penyata Kewangan - Creator Filemai')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{
        showWithdrawModal: false,
        balance: {{ (float)$wallet->current_balance }},
        minPayout: {{ (float)$minPayout }},
        withdrawAmount: {{ (float)$minPayout }},
        bankName: 'Maybank',
     }">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('creator.dashboard') }}" class="hover:text-red-400 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Kembali ke Creator Studio
                </a>
                <span>/</span>
                <span class="text-white font-semibold">Koleksi Royalti & Dompet</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase tracking-widest">
                    KOLEKSI PENDAPATAN CREATOR
                </span>
                <span class="text-xs text-slate-400">Penyata Rasmi Royalti Penstriman</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">
                Dompet & Hasil Tontonan Video
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="showWithdrawModal = true"
                    class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-xl shadow-emerald-950/80 transition-all hover:scale-105">
                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                <span>Tuntut Pengeluaran Wang</span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/50 border border-emerald-500/40 text-emerald-200 text-xs font-semibold flex items-center gap-3 shadow-lg">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="font-bold text-white text-sm">Permohonan Berjaya!</p>
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

    <!-- 3 MAIN COLLECTION CARDS: Terkumpul, Dikeluarkan, Baki -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <!-- KAD 1: DUIT TERKUMPUL -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/30 border border-emerald-500/30 shadow-2xl space-y-3 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
            
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                    Duit Terkumpul (Lifetime)
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    KESELURUHAN
                </span>
            </div>

            <div class="space-y-1">
                <h2 class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono tracking-tight">
                    RM {{ number_format($wallet->total_earned, 4) }}
                </h2>
                <p class="text-xs text-slate-400">
                    Hasil terkumpul daripada <strong class="text-white">{{ number_format($wallet->total_blocks) }}</strong> blok tontonan layak.
                </p>
            </div>

            <div class="pt-3 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-500">
                <span>Kadar Royalti:</span>
                <span class="font-bold text-emerald-300 font-mono">RM {{ number_format($ratePerBlock, 6) }} / blok</span>
            </div>
        </div>

        <!-- KAD 2: DIKELUARKAN -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-white/10 shadow-2xl space-y-3 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-amber-400"></i>
                    Dikeluarkan (Withdrawn)
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                    TELAH DITUNAIKAN
                </span>
            </div>

            <div class="space-y-1">
                <h2 class="text-3xl sm:text-4xl font-black text-amber-400 font-mono tracking-tight">
                    RM {{ number_format($wallet->total_withdrawn, 2) }}
                </h2>
                <p class="text-xs text-slate-400">
                    Jumlah dana yang telah berjaya dipindahkan ke akaun bank anda.
                </p>
            </div>

            <div class="pt-3 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-500">
                <span>Permohonan Selesai:</span>
                <span class="font-bold text-slate-300">{{ $payouts->where('status', 'completed')->count() }} transaksi</span>
            </div>
        </div>

        <!-- KAD 3: BAKI SEMASA -->
        <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 border-2 border-red-500/40 shadow-2xl space-y-3 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-red-600/15 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="piggy-bank" class="w-4 h-4 text-red-500"></i>
                    Baki Semasa (Tersedia)
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-red-500/20 text-red-400 border border-red-500/30">
                    BOLEH DITUNTUT
                </span>
            </div>

            <div class="space-y-1">
                <h2 class="text-3xl sm:text-4xl font-black text-white font-mono tracking-tight">
                    RM {{ number_format($wallet->current_balance, 4) }}
                </h2>
                <p class="text-xs text-slate-400">
                    Baki yang boleh dimohon keluar ke akaun bank tempatan pada bila-bila masa.
                </p>
            </div>

            <div class="pt-3 border-t border-white/10 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Minima Pengeluaran:</span>
                <span class="font-bold text-white font-mono">RM {{ number_format($minPayout, 2) }}</span>
            </div>
        </div>

    </div>

    <!-- Formula Blok Explanation Banner -->
    <div class="p-5 rounded-3xl bg-slate-900 border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs shadow-xl">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="space-y-0.5">
                <h4 class="font-bold text-white text-sm">Bagaimana Blok & Royalti Tontonan Dikira?</h4>
                <p class="text-slate-400 leading-relaxed">
                    Setiap <strong>1 minit ({{ $blockSeconds }} saat)</strong> tontonan penuh dikira sebagai <strong>1 Blok Layak</strong> dengan kadar <strong>RM {{ number_format($ratePerBlock, 6) }} / blok</strong>. 
                    Sekiranya video ditonton selama <em>1 minit 59 saat</em>, sistem mengira <em>1 blok</em> (baki 59 saat diabaikan mengikut peraturan blok penuh).
                </p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-slate-300 font-mono text-[11px]">
                floor(saat / 60) = Blok
            </span>
        </div>
    </div>

    <!-- 2 Column Section: Payout History & Earnings by Drama -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Payout History (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">
            <div class="p-6 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-white">Sejarah Pengeluaran Wang (Payouts)</h3>
                            <p class="text-xs text-slate-400">Rekod semua permohonan pengeluaran dan status pemindahan bank.</p>
                        </div>
                    </div>
                </div>

                @if ($payouts->isEmpty())
                    <div class="text-center py-8 space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-white/5 text-slate-500 flex items-center justify-center mx-auto">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-xs font-bold text-slate-300">Belum Ada Permohonan Pengeluaran</h4>
                        <p class="text-[11px] text-slate-500 max-w-sm mx-auto">
                            Bila baki anda mencecah minima RM {{ number_format($minPayout, 2) }}, anda boleh menekan butang 'Tuntut Pengeluaran Wang' di atas.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-white/10 text-slate-400 uppercase tracking-wider text-[10px]">
                                    <th class="py-2.5 px-3">Tarikh</th>
                                    <th class="py-2.5 px-3">Bank & Akaun</th>
                                    <th class="py-2.5 px-3">Jumlah (RM)</th>
                                    <th class="py-2.5 px-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ($payouts as $payout)
                                    @php $badge = $payout->status_badge; @endphp
                                    <tr class="hover:bg-white/[0.02]">
                                        <td class="py-3 px-3">
                                            <span class="font-bold text-white block">#PAY-{{ $payout->id }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $payout->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="py-3 px-3 space-y-0.5">
                                            <span class="font-semibold text-slate-200 block">{{ $payout->bank_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block">{{ $payout->account_number }}</span>
                                            <span class="text-[10px] text-slate-500 block">{{ $payout->account_holder }}</span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="font-bold text-white font-mono">RM {{ number_format($payout->amount, 2) }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }} inline-flex items-center gap-1">
                                                <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"></i>
                                                {{ $badge['label'] }}
                                            </span>
                                            @if ($payout->reference_no)
                                                <span class="block text-[9px] text-slate-500 font-mono mt-0.5">{{ $payout->reference_no }}</span>
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

        <!-- Right Column: Earnings by Drama (5 Cols) -->
        <div class="lg:col-span-5 space-y-5">
            <div class="p-6 rounded-3xl bg-slate-900 border border-white/10 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">
                            <i data-lucide="film" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-white">Pecahan Royalti Mengikut Siri</h3>
                            <p class="text-xs text-slate-400">Prestasi perolehan karya drama anda.</p>
                        </div>
                    </div>
                </div>

                @if ($dramaEarnings->isEmpty())
                    <p class="text-xs text-slate-500 py-4 text-center">Tiada data tontonan siri direkodkan lagi.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($dramaEarnings as $item)
                            <div class="p-3.5 rounded-2xl bg-slate-950 border border-white/5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 truncate">
                                    <img src="{{ $item->drama->poster_url ?? '/uploads/posters/default.jpg' }}" 
                                         alt="{{ $item->drama->title ?? 'Siri Drama' }}" 
                                         class="w-10 h-14 rounded-lg object-cover shrink-0">
                                    <div class="truncate">
                                        <h4 class="text-xs font-bold text-white truncate">{{ $item->drama->title ?? 'Drama #' . $item->drama_id }}</h4>
                                        <span class="text-[10px] text-slate-400 block">{{ number_format($item->total_blocks) }} blok tontonan</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-black text-emerald-400 font-mono block">RM {{ number_format($item->total_amount, 4) }}</span>
                                    <span class="text-[10px] text-slate-500">royalti</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Creator Guarantee Box -->
            <div class="p-5 rounded-3xl bg-slate-900/60 border border-white/10 space-y-2 text-xs">
                <div class="flex items-center gap-2 text-slate-300 font-bold">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Ketelusan Penuh Royalti Filemai</span>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Setiap saat tontonan dicatat secara tepat melalui sistem penjejakan langsung. Pindahan dana diproses terus ke akaun bank tempatan anda mengikut standard keselamatan perbankan Malaysia.
                </p>
            </div>
        </div>

    </div>

    <!-- WITHDRAWAL MODAL -->
    <div x-show="showWithdrawModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.outside="showWithdrawModal = false" 
             class="w-full max-w-lg rounded-3xl bg-slate-900 border border-white/15 p-6 sm:p-7 shadow-2xl space-y-5 relative">
            
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Mohon Pengeluaran Royalti</h3>
                        <p class="text-xs text-slate-400">Pindahan terus ke akaun bank peribadi atau syarikat anda.</p>
                    </div>
                </div>
                <button type="button" @click="showWithdrawModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Current Balance Info -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-white/10 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Baki Semasa Tersedia:</span>
                    <span class="text-lg font-black text-white font-mono">RM {{ number_format($wallet->current_balance, 4) }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[11px]">Minima Pengeluaran:</span>
                    <span class="text-xs font-bold text-emerald-400 font-mono">RM {{ number_format($minPayout, 2) }}</span>
                </div>
            </div>

            <form action="{{ route('creator.wallet.withdraw') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Jumlah Pengeluaran -->
                <div class="space-y-1.5">
                    <label for="amount" class="block text-xs font-bold text-slate-300">
                        Jumlah Pengeluaran (RM) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">RM</span>
                        <input type="number" 
                               step="0.01" 
                               min="{{ $minPayout }}" 
                               max="{{ $wallet->current_balance }}" 
                               id="amount" 
                               name="amount" 
                               x-model.number="withdrawAmount" 
                               required 
                               class="w-full bg-slate-950 text-white font-bold text-sm pl-12 pr-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Min: RM {{ number_format($minPayout, 2) }}</span>
                        <button type="button" 
                                @click="withdrawAmount = {{ (float)$wallet->current_balance }}" 
                                class="text-emerald-400 hover:underline font-bold">
                            Keluarkan Semua (Max)
                        </button>
                    </div>
                </div>

                <!-- Pilihan Bank -->
                <div class="space-y-1.5">
                    <label for="bank_name" class="block text-xs font-bold text-slate-300">
                        Nama Bank / Institusi Kewangan <span class="text-red-500">*</span>
                    </label>
                    <select id="bank_name" 
                            name="bank_name" 
                            x-model="bankName"
                            required 
                            class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                        <option value="Maybank">Malayan Banking Berhad (Maybank)</option>
                        <option value="CIMB Bank">CIMB Bank Berhad</option>
                        <option value="Bank Islam">Bank Islam Malaysia Berhad</option>
                        <option value="RHB Bank">RHB Bank Berhad</option>
                        <option value="Public Bank">Public Bank Berhad</option>
                        <option value="Hong Leong Bank">Hong Leong Bank</option>
                        <option value="AmBank">AmBank Berhad</option>
                        <option value="Bank Muamalat">Bank Muamalat</option>
                        <option value="Touch n Go eWallet">Touch 'n Go eWallet (DuitNow)</option>
                        <option value="Lain-lain">Lain-lain Institusi Kewangan</option>
                    </select>
                </div>

                <!-- Nombor Akaun -->
                <div class="space-y-1.5">
                    <label for="account_number" class="block text-xs font-bold text-slate-300">
                        Nombor Akaun Bank / DuitNow ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="account_number" 
                           name="account_number" 
                           placeholder="cth: 162263123456" 
                           required 
                           class="w-full bg-slate-950 text-white font-mono text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Nama Pemegang Akaun -->
                <div class="space-y-1.5">
                    <label for="account_holder" class="block text-xs font-bold text-slate-300">
                        Nama Pemegang Akaun (Seperti di Bank) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="account_holder" 
                           name="account_holder" 
                           value="{{ $user->name }}" 
                           placeholder="cth: AHMAD BIN ABDULLAH" 
                           required 
                           class="w-full bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl border border-white/10 focus:outline-none focus:border-emerald-500 uppercase">
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <button type="button" 
                            @click="showWithdrawModal = false" 
                            class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-950/80 transition-all">
                        Hantar Permohonan
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
