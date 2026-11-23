@php
    $suites = \App\Models\RoomType::orderByDesc('base_price')->get()->map(fn ($t) => [
        'name' => $t->name,
        'price' => 'Rp ' . number_format($t->base_price, 0, ',', '.') . ' / malam',
        'image' => $t->cover_url,
    ])->values();

    if ($suites->isEmpty()) {
        $suites = collect([
            [
                'name' => 'Executive Svara Suite',
                'price' => 'Rp 1.650.000 / malam',
                'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1600&q=80',
            ],
            [
                'name' => 'Deluxe Garden View',
                'price' => 'Rp 950.000 / malam',
                'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1600&q=80',
            ],
        ]);
    }

    $hasRegisterError = $errors->has('name') || $errors->has('password_confirmation');
    $startMode = $hasRegisterError ? 'register' : ($initialMode ?? 'login');
@endphp

<div x-data="{
        mode: @js($startMode),
        ready: false,
        showLoginPass: false,
        showRegPass: false,
        currentSlide: 0,
        slides: @js($suites),
        switchMode(target) {
            this.mode = target;
            const targetUrl = target === 'register' ? @js(route('register')) : @js(route('login'));
            if (window.location.pathname !== new URL(targetUrl).pathname) {
                window.history.replaceState({}, '', targetUrl);
            }
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        },
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        }
     }"
     x-init="setTimeout(() => ready = true, 60)"
     class="relative min-h-screen lg:h-dvh flex items-center justify-center p-4 sm:p-6 overflow-x-hidden lg:overflow-hidden select-none">

    <!-- 1. LATAR BELAKANG LAYAR PENUH -->
    <div aria-hidden="true" class="fixed inset-0 z-0 bg-[#181512]">
        <template x-for="(slide, idx) in slides" :key="'bg-' + idx">
            <img :src="slide.image"
                 alt=""
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1600&q=80';"
                 :class="currentSlide === idx ? 'opacity-100 scale-100' : 'opacity-0 scale-105'"
                 class="absolute inset-0 w-full h-full object-cover transition-all duration-700">
        </template>
        <div class="absolute inset-0 bg-stone-950/40 backdrop-blur-[2px]"></div>
    </div>

    <!-- 2. KARTU UTAMA MENGAMBANG (PROPORSIONAL UNTUK LAYAR LAPTOP) -->
    <div class="relative z-10 w-full max-w-4xl rounded-2xl shadow-[0_28px_70px_-15px_rgba(0,0,0,0.75)] overflow-hidden bg-[#181512] lg:h-[510px]">

        <!-- Foto Interior di Dalam Kartu -->
        <template x-for="(slide, idx) in slides" :key="'card-' + idx">
            <img :src="slide.image"
                 :alt="slide.name"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1600&q=80';"
                 :class="currentSlide === idx ? 'opacity-100 scale-100' : 'opacity-0 scale-105'"
                 class="hidden lg:block absolute inset-0 w-full h-full object-cover transition-all duration-700">
        </template>
        <div class="hidden lg:block absolute inset-0 bg-gradient-to-t from-stone-950/85 via-stone-950/20 to-stone-950/45"></div>

        <!-- ============================================================= -->
        <!-- DISPLAY SISI KANAN (SAAT MODE LOGIN)                          -->
        <!-- ============================================================= -->
        <div :class="mode === 'login' ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-2 pointer-events-none'"
             class="hidden lg:flex flex-col justify-between absolute inset-y-0 right-0 w-[50%] p-7 z-10 text-white transition-all duration-500">
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('explore') }}"
                   class="px-3 py-1.5 rounded-full bg-black/35 hover:bg-black/50 border border-white/25 backdrop-blur-xs text-[11px] font-semibold tracking-wide text-white transition">
                    Katalog Suites &nearr;
                </a>
                <div class="flex items-center gap-1.5" x-show="slides.length > 1">
                    <button type="button" @click="prevSlide()" title="Foto Sebelumnya"
                            class="w-7 h-7 rounded-full bg-black/35 hover:bg-black/55 border border-white/25 backdrop-blur-xs flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button type="button" @click="nextSlide()" title="Foto Berikutnya"
                            class="w-7 h-7 rounded-full bg-black/35 hover:bg-black/55 border border-white/25 backdrop-blur-xs flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="text-right drop-shadow-[0_4px_14px_rgba(0,0,0,0.7)]">
                <span class="inline-block px-2.5 py-1 rounded-full bg-amber-800/85 border border-amber-400/30 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-100 mb-2.5"
                      x-text="slides[currentSlide]?.name"></span>
                <h2 class="text-2xl xl:text-3xl font-extrabold uppercase tracking-tight leading-none text-white">
                    SVARA HOTEL
                </h2>
                <p class="text-xs xl:text-sm font-semibold uppercase tracking-[0.2em] text-amber-100/95 mt-1">
                    SANCTUARY OF QUIET LUXURY
                </p>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- DISPLAY SISI KIRI (SAAT MODE REGISTER)                        -->
        <!-- ============================================================= -->
        <div :class="mode === 'register' ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-2 pointer-events-none'"
             class="hidden lg:flex flex-col justify-between absolute inset-y-0 left-0 w-[50%] p-7 z-10 text-white transition-all duration-500">
            <div class="flex items-center justify-start gap-2">
                <a href="{{ route('explore') }}"
                   class="px-3 py-1.5 rounded-full bg-black/35 hover:bg-black/50 border border-white/25 backdrop-blur-xs text-[11px] font-semibold tracking-wide text-white transition">
                    &larr; Katalog Suites
                </a>
                <div class="flex items-center gap-1.5" x-show="slides.length > 1">
                    <button type="button" @click="prevSlide()" title="Foto Sebelumnya"
                            class="w-7 h-7 rounded-full bg-black/35 hover:bg-black/55 border border-white/25 backdrop-blur-xs flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button type="button" @click="nextSlide()" title="Foto Berikutnya"
                            class="w-7 h-7 rounded-full bg-black/35 hover:bg-black/55 border border-white/25 backdrop-blur-xs flex items-center justify-center transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="text-left drop-shadow-[0_4px_14px_rgba(0,0,0,0.7)]">
                <span class="inline-block px-2.5 py-1 rounded-full bg-amber-800/85 border border-amber-400/30 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-100 mb-2.5"
                      x-text="slides[currentSlide]?.price"></span>
                <h2 class="text-2xl xl:text-3xl font-extrabold uppercase tracking-tight leading-none text-white">
                    RESERVASI MUDAH
                </h2>
                <p class="text-xs xl:text-sm font-semibold uppercase tracking-[0.2em] text-amber-100/95 mt-1">
                    PENGALAMAN MENGINAP EKSKLUSIF
                </p>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- PANEL KERTAS PUTIH BERGERIGI (BERGESER KIRI <-> KANAN)        -->
        <!-- ============================================================= -->
        <div :class="[
                 ready ? 'transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]' : '',
                 mode === 'login' ? 'lg:left-0' : 'lg:left-[55%]'
             ]"
             class="relative z-20 w-full lg:w-[45%] lg:h-full bg-white px-6 py-7 sm:px-9 sm:py-8 flex flex-col justify-center">

            <!-- EFEK SOBEKAN KERTAS SVG DI SISI DALAM -->
            <div aria-hidden="true"
                 :class="mode === 'login'
                    ? 'left-full -ml-px drop-shadow-[8px_0_12px_rgba(0,0,0,0.28)]'
                    : 'right-full -mr-px scale-x-[-1] drop-shadow-[-8px_0_12px_rgba(0,0,0,0.28)]'"
                 class="hidden lg:block absolute inset-y-0 w-10 xl:w-12 pointer-events-none">
                <svg viewBox="0 0 64 700" preserveAspectRatio="none" class="w-full h-full fill-white">
                    <path d="M0,0 L18,0 L25,15 L14,29 L29,45 L20,61 L36,77 L28,95 L42,112 L31,129 L45,147 L35,164 L49,182 L33,200 L46,218 L37,236 L53,255 L40,273 L50,291 L32,310 L44,329 L28,346 L39,365 L25,383 L37,402 L21,421 L34,439 L24,458 L42,477 L30,495 L47,514 L35,532 L52,551 L39,570 L48,588 L30,607 L37,625 L20,644 L26,662 L11,680 L15,692 L0,700 Z"/>
                </svg>
            </div>

            <!-- Brand Mini di Mobile -->
            <div class="flex items-center justify-between mb-5 lg:hidden">
                <a href="{{ route('explore') }}" class="font-editorial text-2xl text-stone-900">Svara Hotel</a>
                <a href="{{ route('explore') }}" class="text-xs font-semibold text-amber-800 hover:underline">Katalog &nearr;</a>
            </div>

            <!-- ========================================================= -->
            <!-- FORM 1: MASUK (LOGIN)                                     -->
            <!-- ========================================================= -->
            <div x-show="mode === 'login'"
                 x-transition:enter="transition ease-out duration-300 delay-150"
                 x-transition:enter-start="opacity-0 translate-y-1.5"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="w-full max-w-[275px] mx-auto">

                <div class="mb-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-800">Svara Hospitality</p>
                    <h1 class="text-2xl sm:text-[28px] font-extrabold uppercase tracking-tight text-stone-900 mt-0.5">
                        Masuk
                    </h1>
                    <p class="text-xs text-stone-500 mt-0.5">Akses portal tamu & manajemen reservasi</p>
                </div>

                @if (session('status'))
                    <div class="mb-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-3">
                    @csrf

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="username"
                                   placeholder="e-mail"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                        </div>
                        @if($startMode === 'login')
                            @error('email')
                                <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input :type="showLoginPass ? 'text' : 'password'"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="password"
                                   class="w-full pl-10 pr-10 py-2.5 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                            <button type="button"
                                    @click="showLoginPass = !showLoginPass"
                                    class="absolute inset-y-0 right-3.5 flex items-center text-stone-400 hover:text-stone-700">
                                <svg x-show="!showLoginPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="showLoginPass" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        @if($startMode === 'login')
                            @error('password')
                                <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="flex items-center justify-between px-1 text-[11px] text-stone-600">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-stone-300 text-amber-800 focus:ring-amber-800">
                            <span>Ingat sesi</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-amber-800 hover:underline font-medium">
                                Lupa sandi?
                            </a>
                        @endif
                    </div>

                    <div class="pt-1.5 flex items-center gap-2.5">
                        <button type="submit"
                                class="px-7 py-2.5 rounded-full bg-amber-800 hover:bg-amber-900 text-white text-xs font-bold uppercase tracking-wider shadow-md transition">
                            Masuk
                        </button>

                        @if (Route::has('register'))
                            <button type="button"
                                    @click="switchMode('register')"
                                    class="px-3 py-2.5 rounded-full text-xs font-semibold text-stone-600 hover:text-amber-900 transition">
                                Daftar Akun &rarr;
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- ========================================================= -->
            <!-- FORM 2: DAFTAR (REGISTER)                                 -->
            <!-- ========================================================= -->
            <div x-show="mode === 'register'"
                 style="display: none;"
                 x-transition:enter="transition ease-out duration-300 delay-150"
                 x-transition:enter-start="opacity-0 translate-y-1.5"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="w-full max-w-[275px] mx-auto">

                <div class="mb-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-800">Registrasi Tamu</p>
                    <h1 class="text-2xl sm:text-[28px] font-extrabold uppercase tracking-tight text-stone-900 mt-0.5">
                        Daftar
                    </h1>
                    <p class="text-xs text-stone-500 mt-0.5">Buat akun untuk memesan kamar pilihan Anda</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-2.5">
                    @csrf

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   placeholder="nama lengkap"
                                   class="w-full pl-10 pr-4 py-2 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                        </div>
                        @error('name')
                            <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="username"
                                   placeholder="e-mail"
                                   class="w-full pl-10 pr-4 py-2 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                        </div>
                        @if($startMode === 'register')
                            @error('email')
                                <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input :type="showRegPass ? 'text' : 'password'"
                                   name="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="password"
                                   class="w-full pl-10 pr-10 py-2 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                            <button type="button"
                                    @click="showRegPass = !showRegPass"
                                    class="absolute inset-y-0 right-3.5 flex items-center text-stone-400 hover:text-stone-700">
                                <svg x-show="!showRegPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="showRegPass" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        @if($startMode === 'register')
                            @error('password')
                                <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-amber-900/60 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <input :type="showRegPass ? 'text' : 'password'"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   placeholder="ulangi password"
                                   class="w-full pl-10 pr-4 py-2 rounded-full border border-amber-900/15 bg-amber-900/[0.06] text-xs sm:text-sm text-stone-900 placeholder:text-stone-500 focus:bg-white focus:border-amber-800 focus:ring-amber-800 transition">
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 pl-3.5 text-[11px] text-rose-700 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-1.5 flex items-center gap-2.5">
                        <button type="submit"
                                class="px-7 py-2.5 rounded-full bg-amber-800 hover:bg-amber-900 text-white text-xs font-bold uppercase tracking-wider shadow-md transition">
                            Daftar
                        </button>

                        <button type="button"
                                @click="switchMode('login')"
                                class="px-3 py-2.5 rounded-full text-xs font-semibold text-stone-600 hover:text-amber-900 transition">
                            &larr; Masuk
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>