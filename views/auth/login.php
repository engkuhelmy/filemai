<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/style.css?v=3">
</head>
<body class="bg-[#0b0c0f] text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background movie collage subtle backdrop -->
    <div class="absolute inset-0 z-0 opacity-15">
        <img src="/uploads/posters/ceo-billboard.jpg" class="w-full h-full object-cover blur-md">
        <div class="absolute inset-0 bg-[#0b0c0f]/80"></div>
    </div>

    <div class="w-full max-w-md space-y-6 relative z-10">
        
        <!-- Logo -->
        <div class="text-center space-y-1">
            <a href="/" class="inline-block">
                <span class="font-['Bebas_Neue'] text-5xl tracking-wider text-[#E50914]">FILEMAI</span>
            </a>
            <p class="text-xs text-slate-400">Log masuk ke akaun anda untuk tontonan tanpa henti.</p>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500/40 text-red-300 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span><?= $_SESSION['error'] ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="rounded-3xl bg-[#141414]/90 backdrop-blur-xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-5">
            
            <form action="/login" method="POST" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Alamat Emel</label>
                    <input type="email" name="email" required placeholder="nama@emel.com" 
                           class="w-full bg-[#1c1c1c] text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Kata Laluan</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full bg-[#1c1c1c] text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914]">
                </div>

                <button type="submit" 
                        class="w-full py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-sm shadow-xl shadow-red-950/80 transition-all">
                    Log Masuk
                </button>

            </form>

            <!-- Quick 1-Click Test Demo Switcher -->
            <div class="pt-4 border-t border-white/10 space-y-2.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">
                    Uji Pantas (1-Klik Log Masuk Demo):
                </span>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                    <a href="/demo/switch/1" class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 hover:bg-purple-500/20 text-center transition-colors">
                        🛡️ Super Admin
                    </a>
                    <a href="/demo/switch/2" class="p-2.5 rounded-xl bg-[#E50914]/15 border border-[#E50914]/30 text-red-300 hover:bg-[#E50914]/25 text-center transition-colors">
                        🎬 Sir Helmy (Creator)
                    </a>
                    <a href="/demo/switch/4" class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 hover:bg-emerald-500/20 text-center transition-colors">
                        🍿 Ali (Free Viewer)
                    </a>
                    <a href="/demo/switch/5" class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 hover:bg-amber-500/20 text-center transition-colors">
                        👑 Sara (VIP Ad-Free)
                    </a>
                </div>
            </div>

            <div class="text-center text-xs text-slate-400 pt-2">
                Baru di Filemai? 
                <a href="/register" class="text-white font-bold hover:underline">Daftar sekarang</a>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</body>
</html>
