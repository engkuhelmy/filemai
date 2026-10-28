<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-10" x-data="{ openFaq: 1 }">
    <div class="space-y-3 text-center sm:text-left border-b border-white/10 pb-8">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#E50914]/15 text-[#E50914] border border-[#E50914]/30 inline-block">
            Pusat Bantuan & Soalan Lazim
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-white font-['Bebas_Neue','Inter',sans-serif] tracking-wide">
            SOALAN LAZIM (FAQ)
        </h1>
        <p class="text-xs sm:text-sm text-slate-400">
            Segala jawapan tentang cara tontonan, langganan VIP RM5, dan muat naik drama di Filemai.my
        </p>
    </div>

    <div class="space-y-4">
        
        <!-- FAQ 1 -->
        <div class="rounded-2xl bg-[#141414] border border-white/10 overflow-hidden transition-all">
            <button @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-[#E50914] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-[#E50914]/20 text-[#E50914] text-xs flex items-center justify-center font-bold">1</span>
                    Apa itu Filemai.my dan apakah beza dengan platform streaming biasa?
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 1 ? 'rotate-180 text-[#E50914]' : ''"></i>
            </button>
            <div x-show="openFaq === 1" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-white/5 leading-relaxed" x-transition>
                Filemai.my adalah platform penstriman mini drama format menegak (vertical 9:16) tempatan pertama di Malaysia. Episod kami direka khas berdurasi 1 hingga 2 minit setiap episod, padat dengan jalan cerita penuh suspens dan emosi yang sangat sesuai ditonton secara pantas di telefon pintar pada bila-bila masa.
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="rounded-2xl bg-[#141414] border border-white/10 overflow-hidden transition-all">
            <button @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-[#E50914] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-[#E50914]/20 text-[#E50914] text-xs flex items-center justify-center font-bold">2</span>
                    Adakah saya boleh menonton drama secara percuma?
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 2 ? 'rotate-180 text-[#E50914]' : ''"></i>
            </button>
            <div x-show="openFaq === 2" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-white/5 leading-relaxed" x-transition>
                Ya! Pengguna biasa boleh menonton episod pratonton secara percuma sepenuhnya. Untuk episod seterusnya, anda hanya perlu menonton selingan iklan ringkas (10-15 saat) sebelum episod bersambung tanpa sebarang caj dikenakan.
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="rounded-2xl bg-[#141414] border border-white/10 overflow-hidden transition-all">
            <button @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-[#E50914] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-[#E50914]/20 text-[#E50914] text-xs flex items-center justify-center font-bold">3</span>
                    Apakah kelebihan melanggan Pas VIP RM5 / Bulan?
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 3 ? 'rotate-180 text-[#E50914]' : ''"></i>
            </button>
            <div x-show="openFaq === 3" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-white/5 leading-relaxed" x-transition>
                Dengan hanya RM5 sebulan (sekitar 16 sen sehari), pemegang Pas VIP mendapat:
                <ul class="list-disc pl-5 mt-2 space-y-1 text-amber-300">
                    <li>Langkau 100% semua iklan selingan video.</li>
                    <li>Akses penuh ke semua episod terkunci dan tayangan eksklusif.</li>
                    <li>Sokongan kualiti video HD menegak ultra-pantas tanpa had.</li>
                </ul>
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="rounded-2xl bg-[#141414] border border-white/10 overflow-hidden transition-all">
            <button @click="openFaq = (openFaq === 4 ? null : 4)" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-[#E50914] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-[#E50914]/20 text-[#E50914] text-xs flex items-center justify-center font-bold">4</span>
                    Apakah kaedah pembayaran yang diterima?
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 4 ? 'rotate-180 text-[#E50914]' : ''"></i>
            </button>
            <div x-show="openFaq === 4" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-white/5 leading-relaxed" x-transition>
                Kami menyokong FPX Online Banking tempatan (Maybank2u, CIMB Clicks, Bank Islam, RHB, Public Bank, dll.) melalui gerbang pembayaran rasmi yang selamat, serta Kad Debit / Kredit Visa & Mastercard.
            </div>
        </div>

        <!-- FAQ 5 -->
        <div class="rounded-2xl bg-[#141414] border border-white/10 overflow-hidden transition-all">
            <button @click="openFaq = (openFaq === 5 ? null : 5)" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-white hover:text-[#E50914] transition-colors">
                <span class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-[#E50914]/20 text-[#E50914] text-xs flex items-center justify-center font-bold">5</span>
                    Saya seorang pembikin filem / content creator, bagaimana untuk menerbitkan drama saya?
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 5 ? 'rotate-180 text-[#E50914]' : ''"></i>
            </button>
            <div x-show="openFaq === 5" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-white/5 leading-relaxed" x-transition>
                Anda boleh mendaftar akaun sebagai <strong>Content Creator</strong> secara percuma melalui pautan <a href="/register?role=creator" class="text-[#E50914] underline font-bold">Daftar Pembikin Filem</a>. Portal Creator Studio kami menyediakan alat muat naik video episod, statistik tontonan penonton, dan sistem perkongsian hasil langganan / iklan.
            </div>
        </div>

    </div>

    <div class="p-6 rounded-2xl bg-gradient-to-r from-[#181818] to-red-950/30 border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-white">Ada soalan lain yang belum terjawab?</h3>
            <p class="text-xs text-slate-400">Pasukan sokongan kami sedia membantu anda.</p>
        </div>
        <a href="/contact" class="px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors shrink-0 flex items-center gap-2">
            <i data-lucide="mail" class="w-4 h-4"></i>
            Hubungi Khidmat Pelanggan
        </a>
    </div>
</div>
