<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-10">
    <div class="space-y-3 text-center sm:text-left border-b border-white/10 pb-8">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#E50914]/15 text-[#E50914] border border-[#E50914]/30 inline-block">
            Khidmat Pelanggan & Kerjasama
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-white font-['Bebas_Neue','Inter',sans-serif] tracking-wide">
            HUBUNGI KAMI
        </h1>
        <p class="text-xs sm:text-sm text-slate-400">
            Sebarang pertanyaan berkaitan platform, langganan VIP, atau kerjasama penerbitan siri drama boleh diajukan di sini.
        </p>
    </div>

    <?php if (!empty($submitted)): ?>
        <div class="p-6 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-sm flex items-center gap-3">
            <i data-lucide="check-circle" class="w-6 h-6 text-emerald-400 shrink-0"></i>
            <div>
                <strong class="font-bold">Mesej Anda Berjaya Dihantar!</strong>
                <p class="text-xs text-emerald-300 mt-0.5">Pasukan khidmat pelanggan Filemai.my akan membalas emel anda dalam tempoh 24 jam waktu bekerja.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Contact Info Side Cards -->
        <div class="space-y-4">
            
            <div class="p-5 rounded-2xl bg-[#141414] border border-white/10 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-[#E50914]/20 text-[#E50914] flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-white">Sokongan Penonton</h3>
                <p class="text-xs text-slate-400">Masalah tontonan atau isu bayaran VIP:</p>
                <a href="mailto:sokongan@filemai.my" class="text-xs text-[#E50914] font-bold block hover:underline">
                    sokongan@filemai.my
                </a>
            </div>

            <div class="p-5 rounded-2xl bg-[#141414] border border-white/10 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center">
                    <i data-lucide="clapperboard" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-white">Hubungan Penerbit</h3>
                <p class="text-xs text-slate-400">Pelesenan IP & tayangan siri baharu:</p>
                <a href="mailto:creator@filemai.my" class="text-xs text-amber-400 font-bold block hover:underline">
                    creator@filemai.my
                </a>
            </div>

            <div class="p-5 rounded-2xl bg-[#141414] border border-white/10 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-white">Ibu Pejabat</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Filemai Media Sdn. Bhd.<br>
                    Menara Sentral, Kuala Lumpur, Malaysia.
                </p>
            </div>

        </div>

        <!-- Contact Form -->
        <div class="md:col-span-2 p-6 sm:p-8 rounded-3xl bg-[#141414] border border-white/10 space-y-5">
            <h2 class="text-lg font-bold text-white">Hantar Mesej Kepada Kami</h2>
            
            <form action="/contact" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Nama Penuh</label>
                        <input type="text" name="name" required placeholder="Ahmad Zaki" 
                               class="w-full bg-[#1c1c1c] text-white text-xs px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Alamat Emel</label>
                        <input type="email" name="email" required placeholder="zaki@contoh.com" 
                               class="w-full bg-[#1c1c1c] text-white text-xs px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Tajuk / Kategori Pertanyaan</label>
                    <select name="subject" class="w-full bg-[#1c1c1c] text-white text-xs px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                        <option value="vip">Isu / Pertanyaan Langganan Pas VIP RM5</option>
                        <option value="creator">Pendaftaran Pembikin Filem / Creator Studio</option>
                        <option value="tech">Bantuan Teknikal / Pemain Video</option>
                        <option value="ads">Pengiklanan & Tajaan Komersil</option>
                        <option value="other">Pertanyaan Am</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Mesej Anda</label>
                    <textarea name="message" rows="4" required placeholder="Tuliskan butiran pertanyaan anda di sini..." 
                              class="w-full bg-[#1c1c1c] text-white text-xs px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]"></textarea>
                </div>

                <button type="submit" 
                        class="px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-xs shadow-xl shadow-red-950/80 transition-all flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Hantar Pertanyaan
                </button>
            </form>
        </div>

    </div>
</div>
