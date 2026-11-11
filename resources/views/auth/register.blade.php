<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Daftar Akaun Baharu - Filemai.my' }}</title>
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
<body class="bg-[#0b0c0f] text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden font-sans selection:bg-red-600 selection:text-white"
      x-data="{ 
          selectedRole: '{{ old('role', $initialRole ?? 'viewer') }}',
          showPassword: false,
          showPasswordConfirm: false
      }">

    <!-- Cinematic Backdrop Blur -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="/uploads/posters/dendam-billboard.jpg" alt="Backdrop" class="w-full h-full object-cover blur-2xl scale-110 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0c0f] via-[#0b0c0f]/80 to-[#0b0c0f]/90"></div>
    </div>

    <div class="w-full max-w-lg space-y-6 relative z-10 my-8">
        
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group transition-transform hover:scale-105">
                <span class="font-['Bebas_Neue'] text-5xl sm:text-6xl tracking-wider text-[#E50914] drop-shadow-lg">FILEMAI</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/40 uppercase tracking-widest">MY</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Cipta Akaun Baharu Filemai</h1>
            <p class="text-xs sm:text-sm text-slate-400">Sertai komuniti penstriman drama mini menegak pertama Malaysia.</p>
        </div>

        <!-- Flash & Error Alerts -->
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/40 text-red-300 text-xs flex items-center gap-3 backdrop-blur-md shadow-lg">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-red-400"></i>
                <span class="font-medium">{{ session('error') }}</span>
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
            
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Account Role Selection Cards -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Pilih Jenis Akaun:
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        
                        <!-- Penonton Option -->
                        <div @click="selectedRole = 'viewer'" 
                             class="p-3.5 rounded-2xl border cursor-pointer transition-all flex flex-col items-center text-center gap-2"
                             :class="selectedRole === 'viewer' 
                                 ? 'bg-[#E50914]/15 border-[#E50914] text-white shadow-lg shadow-red-950/40 ring-1 ring-[#E50914]' 
                                 : 'bg-[#1c1c1c] border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-300'">
                            <input type="radio" name="role" value="viewer" x-model="selectedRole" class="hidden">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                                 :class="selectedRole === 'viewer' ? 'bg-[#E50914] text-white' : 'bg-white/10 text-slate-400'">
                                <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold block" :class="selectedRole === 'viewer' ? 'text-white' : 'text-slate-200'">Penonton</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Tonton Drama</span>
                            </div>
                        </div>

                        <!-- Filmmaker / Creator Option -->
                        <div @click="selectedRole = 'creator'" 
                             class="p-3.5 rounded-2xl border cursor-pointer transition-all flex flex-col items-center text-center gap-2"
                             :class="selectedRole === 'creator' 
                                 ? 'bg-[#E50914]/15 border-[#E50914] text-white shadow-lg shadow-red-950/40 ring-1 ring-[#E50914]' 
                                 : 'bg-[#1c1c1c] border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-300'">
                            <input type="radio" name="role" value="creator" x-model="selectedRole" class="hidden">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                                 :class="selectedRole === 'creator' ? 'bg-[#E50914] text-white' : 'bg-white/10 text-slate-400'">
                                <i data-lucide="clapperboard" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold block" :class="selectedRole === 'creator' ? 'text-white' : 'text-slate-200'">Filmmaker</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Muat Naik Drama</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Full Name Field -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Nama Penuh
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autocomplete="name"
                               placeholder="cth: Helmy Azhar" 
                               class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-4 py-3 rounded-xl border {{ $errors->has('name') ? 'border-red-500 ring-1 ring-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                    </div>
                </div>

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
                               placeholder="nama@emel.com" 
                               class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-red-500 ring-1 ring-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                    </div>
                </div>

                <!-- Password Fields Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    
                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Kata Laluan
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="6"
                                   placeholder="Min 6 aksara" 
                                   class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-10 py-3 rounded-xl border {{ $errors->has('password') ? 'border-red-500 ring-1 ring-red-500' : 'border-white/10' }} focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200">
                                <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Sahkan Kata Laluan
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <input :type="showPasswordConfirm ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="6"
                                   placeholder="Ulang semula" 
                                   class="w-full bg-[#1c1c1c] text-white text-sm pl-10 pr-10 py-3 rounded-xl border border-white/10 focus:outline-none focus:border-[#E50914] focus:ring-1 focus:ring-[#E50914] transition-all placeholder:text-slate-500">
                            <button type="button" 
                                    @click="showPasswordConfirm = !showPasswordConfirm" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200">
                                <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Terms and Privacy Notice -->
                <p class="text-[11px] text-slate-400 pt-1 leading-relaxed">
                    Dengan mendaftar, anda bersetuju dengan 
                    <a href="/terms" class="text-slate-300 hover:text-white underline">Syarat Perkhidmatan</a> 
                    serta 
                    <a href="/privacy" class="text-slate-300 hover:text-white underline">Dasar Privasi</a> 
                    Filemai.my.
                </p>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-6 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-sm shadow-xl shadow-red-950/80 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Cipta Akaun Percuma</span>
                </button>

            </form>

            <!-- Footer Link to Login -->
            <div class="text-center text-xs text-slate-400 pt-2 border-t border-white/5">
                Sudah mempunyai akaun Filemai? 
                <a href="{{ route('login') }}" class="text-[#E50914] font-bold hover:underline hover:text-red-400 ml-1">
                    Log masuk di sini
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

