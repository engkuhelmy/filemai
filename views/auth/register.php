<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/style.css?v=2">
</head>
<body class="bg-[#090d16] text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">
        
        <!-- Logo -->
        <div class="text-center space-y-2">
            <a href="/" class="inline-flex items-center space-x-2.5 group">
                <div class="w-12 h-12 rounded-2xl gradient-brand flex items-center justify-center shadow-xl shadow-cyan-500/20 group-hover:scale-105 transition-transform">
                    <i data-lucide="play" class="w-6 h-6 text-white fill-white ml-0.5"></i>
                </div>
                <div class="text-left">
                    <span class="text-2xl font-extrabold tracking-tight text-white block">
                        FILEMAI<span class="text-cyan-400">.MY</span>
                    </span>
                    <span class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase -mt-1 block">MiniDrama Hub</span>
                </div>
            </a>
            <h1 class="text-xl font-bold text-white">Daftar Akaun Baharu</h1>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span><?= $_SESSION['error'] ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-8 shadow-2xl space-y-5">
            
            <form action="/register" method="POST" class="space-y-4" x-data="{ selectedRole: '<?= htmlspecialchars($_GET['role'] ?? 'viewer') ?>' }">
                
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Nama Penuh</label>
                    <input type="text" name="name" required placeholder="Nama anda" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Alamat Emel</label>
                    <input type="email" name="email" required placeholder="nama@emel.com" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Kata Laluan (min 6 aksara)</label>
                    <input type="password" name="password" required minlength="6" placeholder="••••••••" 
                           class="w-full bg-slate-950 text-white text-sm px-4 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <!-- Account Role Selection -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300">Tujuan Akaun:</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-xl border cursor-pointer text-center transition-all"
                               :class="selectedRole === 'viewer' ? 'bg-cyan-500/20 border-cyan-500 text-white' : 'bg-slate-950 border-white/10 text-slate-400'">
                            <input type="radio" name="role" value="viewer" x-model="selectedRole" class="hidden">
                            <i data-lucide="tv" class="w-5 h-5 mx-auto mb-1 text-cyan-400"></i>
                            <span class="text-xs font-bold block">Penonton</span>
                            <span class="text-[10px] text-slate-400 block">Tonton Drama</span>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer text-center transition-all"
                               :class="selectedRole === 'creator' ? 'bg-cyan-500/20 border-cyan-500 text-white' : 'bg-slate-950 border-white/10 text-slate-400'">
                            <input type="radio" name="role" value="creator" x-model="selectedRole" class="hidden">
                            <i data-lucide="clapperboard" class="w-5 h-5 mx-auto mb-1 text-purple-400"></i>
                            <span class="text-xs font-bold block">Filmmaker</span>
                            <span class="text-[10px] text-slate-400 block">Muat Naik Drama</span>
                        </label>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 rounded-xl gradient-brand text-white font-bold text-sm shadow-xl shadow-cyan-500/25 hover:opacity-95 transition-all">
                    Cipta Akaun Percuma
                </button>

            </form>

            <div class="text-center text-xs text-slate-400 pt-2">
                Sudah mempunyai akaun? 
                <a href="/login" class="text-cyan-400 font-semibold hover:underline">Log masuk</a>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</body>
</html>
