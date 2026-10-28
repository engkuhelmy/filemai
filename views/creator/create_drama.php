<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/creator" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Studio
        </a>
        <span>/</span>
        <span class="text-white">Cipta Siri Baru</span>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
        
        <div class="border-b border-white/10 pb-4">
            <h1 class="text-2xl font-extrabold text-white">Daftar Siri Mini Drama Menegak Baru</h1>
            <p class="text-xs text-slate-400 mt-1">Lengkapkan maklumat siri drama anda untuk mula memuat naik episod.</p>
        </div>

        <form action="/creator/drama/store" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <!-- Drama Title -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Tajuk Drama / Siri <span class="text-red-400">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Jodoh Rahsia Jutawan, Dendam Si Isteri..." 
                       class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50">
            </div>

            <!-- Genre & Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Genre / Kategori <span class="text-red-400">*</span></label>
                    <select name="genre" class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                        <option value="Romance">Romance (Romantik CEO/Kontrak)</option>
                        <option value="Action">Action & Dendam</option>
                        <option value="Mystery">Mystery & Thriller</option>
                        <option value="Drama">Drama Kekeluargaan</option>
                        <option value="Comedy">Komedi Santai</option>
                        <option value="Fantasy">Fantasi & Isekai</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Format Tayangan</label>
                    <select name="type" class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                        <option value="series">Siri Episod Berterusan (Series)</option>
                        <option value="movie">Telefilem Pendek Menegak (Movie)</option>
                    </select>
                </div>
            </div>

            <!-- Synopsis -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Sinopsis Ringkas</label>
                <textarea name="synopsis" rows="4" placeholder="Ceritakan plot asas drama pendek ini untuk menarik minat penonton..." 
                          class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50"></textarea>
            </div>

            <!-- Vertical Poster (9:16) -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Poster Menegak (Format 9:16) <span class="text-red-400">*</span></label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Muat Naik Fail Poster (JPG, PNG, WebP):</label>
                        <input type="file" name="poster" accept="image/*" 
                               class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/10 file:text-cyan-300 hover:file:bg-cyan-500/20 cursor-pointer bg-slate-950 p-2 rounded-xl border border-white/10">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Atau Masukkan URL Imej Poster:</label>
                        <input type="url" name="poster_url" placeholder="https://.../poster.jpg" 
                               class="w-full bg-slate-950 text-white text-xs px-3 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                </div>
                <p class="text-[11px] text-slate-500">
                    Nisbah disyorkan: 9:16 (contoh: 1080x1920 atau 720x1280 piksel) untuk paparan optimum pada skrin telefon pintar.
                </p>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-white/10 flex items-center justify-end gap-3">
                <a href="/creator" class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl gradient-brand text-white font-bold text-xs shadow-lg shadow-cyan-500/20 hover:opacity-95 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Siri & Tambah Episod
                </button>
            </div>

        </form>

    </div>

</div>
