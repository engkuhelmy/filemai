<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12 pt-24 sm:pt-28">

    <!-- Header Section -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/30">
            <i data-lucide="crown" class="w-4 h-4 fill-[#E50914]"></i>
            PAS VIP EKSKLUSIF FILEMAI.MY
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight uppercase font-['Bebas_Neue','Inter',sans-serif]">
            Tonton Drama Pendek <span class="text-[#E50914]">Tanpa Gangguan Iklan</span>
        </h1>
        <p class="text-slate-400 text-xs sm:text-sm max-w-xl mx-auto">
            Nikmati penstriman lancar tanpa pemasa iklan 15 saat, buka semua episod VIP, dan sokong pembikin filem tempatan.
        </p>
    </div>

    <!-- Pricing Card -->
    <div class="relative max-w-lg mx-auto">
        <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-[#E50914] to-red-900 opacity-40 blur-xl"></div>
        
        <div class="relative rounded-3xl bg-[#141414] border-2 border-[#E50914]/50 p-8 shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#E50914]">Pelan VIP Bulanan</span>
                    <h3 class="text-2xl font-black text-white uppercase font-['Bebas_Neue','Inter',sans-serif]">Filemai VIP Pass</h3>
                </div>
                <div class="text-right">
                    <span class="text-4xl font-black text-[#E50914]">RM 5</span>
                    <span class="text-xs text-slate-400">/ 30 hari</span>
                </div>
            </div>

            <ul class="space-y-3.5 text-xs sm:text-sm text-slate-200 border-t border-b border-white/10 py-6">
                <li class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </div>
                    <span><strong>100% Bebas Iklan:</strong> Tiada iklan countdown 15 saat.</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </div>
                    <span><strong>Autoplay Pantas:</strong> Terus sambung ke episod seterusnya tanpa henti.</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </div>
                    <span><strong>Buka Episod VIP:</strong> Akses penuh semua episod terkini tanpa syarat.</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </div>
                    <span><strong>Sokong Creator Tempatan:</strong> Saluran royalti kepada pembikin karya.</span>
                </li>
            </ul>

            <?php if ($isPremium): ?>
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-center space-y-1">
                    <div class="flex items-center justify-center gap-2 text-amber-300 font-bold text-sm">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Akaun Anda Berstatus VIP Aktif
                    </div>
                    <p class="text-xs text-slate-400">
                        Sah sehingga: <?= date('d M Y, h:i A', strtotime($user['subscription_expires_at'] ?? '+30 days')) ?>
                    </p>
                    <div class="pt-2">
                        <a href="/" class="text-xs text-[#E50914] hover:underline font-bold">Mula Tonton Sekarang &rarr;</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/subscribe/checkout" 
                   class="w-full py-4 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-black text-sm shadow-xl shadow-red-950/80 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 fill-white"></i>
                    Langgan Sekarang (RM 5.00)
                </a>
                <p class="text-center text-[11px] text-slate-500">
                    Pembayaran selamat melalui ToyyibPay FPX, DuitNow QR & Kad. Boleh dibatalkan bila-bila masa.
                </p>
            <?php endif; ?>

        </div>
    </div>

</div>
