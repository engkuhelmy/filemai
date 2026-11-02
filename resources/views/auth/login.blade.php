<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Log Masuk - Filemai.my' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#E50914',
                            redHover: '#F40612',
                            dark: '#0b0c0f',
                            card: '#141414',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        title: ['Bebas Neue', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/style.css?v=5">
</head>
<body class="bg-[#0b0c0f] text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden font-sans selection:bg-red-600 selection:text-white"
      x-data="{ 
          showPassword: false,
          fillDemo(email, pass) {
              document.getElementById('email').value = email;
              document.getElementById('password').value = pass;
          }
      }">

    <!-- Cinematic Backdrop Blur -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="/uploads/posters/ceo-billboard.jpg" alt="Backdrop" class="w-full h-full object-cover blur-2xl scale-110 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0c0f] via-[#0b0c0f]/80 to-[#0b0c0f]/90"></div>
    </div>

    <div class="w-full max-w-md space-y-6 relative z-10 my-8">
        
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group transition-transform hover:scale-105">
                <span class="font-['Bebas_Neue'] text-5xl sm:text-6xl tracking-wider text-[#E50914] drop-shadow-lg">FILEMAI</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/40 uppercase tracking-widest">MY</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Log Masuk ke Akaun Anda</h1>
            <p class="text-xs sm:text-sm text-slate-400">Tonton drama siri menegak kegemaran anda tanpa henti.</p>
        </div>

        <!-- Flash & Error Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3 backdrop-blur-md shadow-lg">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-emerald-400"></i>
                <span class="font-medium">{!! session('success') !!}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 rounded-2xl bg-blue-950/80 border border-blue-500/40 text-blue-300 text-xs flex items-center gap-3 backdrop-blur-md shadow-lg">
                <i data-lucide="info" class="w-5 h-5 shrink-0 text-blue-400"></i>
                <span class="font-medium">{!! session('info') !!}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/40 text-red-300 text-xs flex items-center gap-3 backdrop-blur-md shadow-lg">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-red-400"></i>
                <span class="font-medium">{!! session('error') !!}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/40 text-red-300 text-xs flex items-start gap-3 backdrop-blur-md shadow-lg">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-red-400 mt-0.5"></i>
                <div class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <p class="font-medium leading-relaxed">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="rounded-3xl bg-[#141414]/90 backdrop-blur-2xl border border-white/10 p-6 sm:p-8 shadow-2xl shadow-black/80 space-y-6">
            
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Email Field -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Alamat Emel
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email"
                               autofocus
                               placeholder="nama@emel.com" 
                               class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-red-500 ring-1 ring-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Kata Laluan
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" 
                               id="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="••••••••" 
                               class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-11 py-3 rounded-xl border {{ $errors->has('password') ? 'border-red-500 ring-1 ring-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" 
                               name="remember" 
                               value="1" 
                               {{ old('remember') ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-[#1c1c1c] border-white/20 text-[#E50914] focus:ring-[#E50914] focus:ring-offset-0 focus:ring-offset-transparent cursor-pointer">
                        <span class="text-xs text-slate-300">Ingat saya pada peranti ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-6 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-sm shadow-xl shadow-red-950/80 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Log Masuk</span>
                </button>

            </form>

            <!-- Quick 1-Click Test Demo Switcher (Staging / Debug Mode) -->
            <div class="pt-4 border-t border-white/10 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        Log Masuk Pantas (Akaun Ujian):
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-white/5 text-slate-400 border border-white/10 font-mono">
                        Password: password
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                    <a href="{{ route('demo.switch', 1) }}" 
                       class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 hover:bg-purple-500/20 text-center transition-colors flex items-center justify-center gap-1.5"
                       title="Super Admin">
                        <span>🛡️</span>
                        <span class="truncate">Super Admin</span>
                    </a>
                    
                    <a href="{{ route('demo.switch', 2) }}" 
                       class="p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 hover:bg-red-500/20 text-center transition-colors flex items-center justify-center gap-1.5"
                       title="Sir Helmy (Creator)">
                        <span>🎬</span>
                        <span class="truncate">Creator Studio</span>
                    </a>

                    <a href="{{ route('demo.switch', 5) }}" 
                       class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 hover:bg-amber-500/20 text-center transition-colors flex items-center justify-center gap-1.5"
                       title="Sara VIP (Tanpa Iklan)">
                        <span>👑</span>
                        <span class="truncate">Sara VIP Pass</span>
                    </a>

                    <a href="{{ route('demo.switch', 4) }}" 
                       class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 hover:bg-emerald-500/20 text-center transition-colors flex items-center justify-center gap-1.5"
                       title="Ali Penonton Biasa">
                        <span>🍿</span>
                        <span class="truncate">Ali (Penonton)</span>
                    </a>
                </div>
            </div>

            <!-- Footer Link to Register -->
            <div class="text-center text-xs text-slate-400 pt-2 border-t border-white/5">
                Belum mempunyai akaun Filemai? 
                <a href="{{ route('register') }}" class="text-[#E50914] font-bold hover:underline hover:text-red-400 ml-1">
                    Daftar akaun baharu sekarang
                </a>
            </div>

        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-300 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Halaman Utama Filemai</span>
            </a>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
