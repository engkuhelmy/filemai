<div class="max-w-xl mx-auto px-4 py-10 space-y-6">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/subscribe" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali
        </a>
        <span>/</span>
        <span class="text-white">Pengesahan Pembayaran</span>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
        
        <div class="border-b border-white/10 pb-4">
            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Ringkasan Pesanan</span>
            <div class="flex items-center justify-between mt-2">
                <div>
                    <h2 class="text-xl font-extrabold text-white">Pas VIP Filemai (30 Hari)</h2>
                    <p class="text-xs text-slate-400">Akaun: <?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black text-amber-400">RM 5.00</span>
                    <span class="block text-[10px] text-emerald-400">Sifar Cukai (0%)</span>
                </div>
            </div>
        </div>

        <form action="/subscribe/process" method="POST" class="space-y-6" x-data="{ channel: 'ToyyibPay FPX' }">
            
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Pilih Kaedah Pembayaran Tempatan:</label>
                
                <!-- ToyyibPay FPX -->
                <label class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition-all"
                       :class="channel === 'ToyyibPay FPX' ? 'bg-cyan-500/10 border-cyan-500/50 text-white' : 'bg-slate-950/60 border-white/10 text-slate-300 hover:border-white/20'">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="payment_channel" value="ToyyibPay FPX" x-model="channel" class="text-cyan-500 focus:ring-0">
                        <div>
                            <span class="text-sm font-bold block">Online Banking FPX (ToyyibPay)</span>
                            <span class="text-[11px] text-slate-400">Maybank2u, CIMB, Bank Islam, RHB, Public Bank</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-cyan-400 px-2 py-0.5 rounded bg-cyan-950/60 border border-cyan-500/30">FPX</span>
                </label>

                <!-- DuitNow QR -->
                <label class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition-all"
                       :class="channel === 'DuitNow QR' ? 'bg-cyan-500/10 border-cyan-500/50 text-white' : 'bg-slate-950/60 border-white/10 text-slate-300 hover:border-white/20'">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="payment_channel" value="DuitNow QR" x-model="channel" class="text-cyan-500 focus:ring-0">
                        <div>
                            <span class="text-sm font-bold block">DuitNow QR Pay</span>
                            <span class="text-[11px] text-slate-400">Touch 'n Go eWallet, GrabPay, MAE</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-pink-400 px-2 py-0.5 rounded bg-pink-950/60 border border-pink-500/30">QR Pay</span>
                </label>

                <!-- Kad Debit / Kredit -->
                <label class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition-all"
                       :class="channel === 'Stripe Card' ? 'bg-cyan-500/10 border-cyan-500/50 text-white' : 'bg-slate-950/60 border-white/10 text-slate-300 hover:border-white/20'">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="payment_channel" value="Stripe Card" x-model="channel" class="text-cyan-500 focus:ring-0">
                        <div>
                            <span class="text-sm font-bold block">Kad Debit / Kredit (Stripe)</span>
                            <span class="text-[11px] text-slate-400">Visa, Mastercard</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-indigo-400 px-2 py-0.5 rounded bg-indigo-950/60 border border-indigo-500/30">Card</span>
                </label>

            </div>

            <!-- Total and Submit -->
            <div class="pt-4 border-t border-white/10 space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Jumlah Perlu Dibayar:</span>
                    <span class="text-xl font-black text-amber-400">RM 5.00</span>
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-base shadow-xl shadow-amber-500/25 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    Bayar RM 5.00 Sekarang
                </button>
                
                <p class="text-center text-[10px] text-slate-500">
                    Mod Simulasi MVP Sandbox: Pembayaran akan disahkan serta-merta dan status VIP diaktifkan untuk akaun anda.
                </p>
            </div>

        </form>

    </div>

</div>
