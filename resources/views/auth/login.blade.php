<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Login / Register</title>

    <!-- Tailwind CSS (via CDN) as requested -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js for interactive sliding forms -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Three.js for Animated Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #welcome-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 10;
        }

        /* Scrollbar styling for forms on mobile */
        .form-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .form-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .form-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .form-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-0 md:p-4">

    @php
        $isRegisterMode = isset($forceRegister) || old('name') || $errors->has('name') || ($errors->has('email') && old('name'));
    @endphp

    <!-- Toasts -->
    @if($errors->any())
        <div x-data="{ show: true }" x-show="show" x-cloak
            class="fixed top-5 right-5 z-[200] bg-red-900/80 border border-red-500/50 backdrop-blur-sm rounded-lg p-4 shadow-xl max-w-sm flex items-start gap-3">
            <svg class="w-6 h-6 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-100">Autentikasi Gagal</p>
                <p class="text-xs text-red-200 mt-1">{{ $errors->first() }}</p>
            </div>
            <button @click="show = false" class="text-red-400 hover:text-red-200 ml-auto"><svg class="w-4 h-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg></button>
        </div>
    @endif
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-cloak
            class="fixed top-5 right-5 z-[200] bg-green-900/80 border border-green-500/50 backdrop-blur-sm rounded-lg p-4 shadow-xl max-w-sm flex items-start gap-3">
            <svg class="w-6 h-6 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-100">Berhasil</p>
                <p class="text-xs text-green-200 mt-1">{{ session('status') }}</p>
            </div>
            <button @click="show = false" class="text-green-400 hover:text-green-200 ml-auto"><svg class="w-4 h-4"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg></button>
        </div>
    @endif

    <!-- Login Card -->
    <!-- h-[600px] on desktop (md), h-[100dvh] on mobile to ensure full viewport and fix form height -->
    <div x-data="{ isRegister: {{ $isRegisterMode ? 'true' : 'false' }} }"
        class="bg-transparent md:bg-gray-800/80 md:backdrop-blur-md md:shadow-2xl md:rounded-2xl overflow-hidden max-w-5xl w-full h-[100dvh] md:h-[600px] border-0 md:border border-gray-700/30 relative">

        <!-- Animated Background Canvas (Covers entire screen on mobile, left half on desktop) -->
        <div id="welcome-canvas" class="absolute inset-0 z-0"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 h-full w-full relative z-10">

            <!-- Left Column - Welcome Section for Desktop -->
            <div
                class="relative hidden md:flex flex-col justify-center items-center p-12 bg-transparent overflow-hidden">

                <!-- Welcome Content -->
                <div class="welcome-content text-center space-y-6 w-full">

                    <!-- Logo SAPMAR menggantikan icon kunci -->
                    <div
                        class="w-32 h-32 bg-blue-500/10 backdrop-blur-md rounded-full flex items-center justify-center mx-auto border border-blue-400/20 shadow-lg">
                        <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapmar"
                            class="w-24 h-24 object-contain drop-shadow-xl">
                    </div>

                    <div class="relative h-[120px] w-full">
                        <!-- Sign In Text -->
                        <div x-show="!isRegister" x-transition:enter="transition ease-out duration-500 delay-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-4" class="absolute inset-0">
                            <h1 class="text-4xl font-light text-white drop-shadow-lg mb-4">Welcome Back</h1>
                            <p class="text-gray-200 text-lg leading-relaxed max-w-sm mx-auto drop-shadow-md">
                                Sign in to your account to continue your journey with us.
                            </p>
                        </div>

                        <!-- Register Text -->
                        <div x-show="isRegister" x-cloak x-transition:enter="transition ease-out duration-500 delay-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-4" class="absolute inset-0">
                            <h1 class="text-4xl font-light text-white drop-shadow-lg mb-4">Halo, Sahabat!</h1>
                            <p class="text-gray-200 text-lg leading-relaxed max-w-sm mx-auto drop-shadow-md">
                                Daftarkan diri Anda untuk bergabung dengan platform SAPTA CBT.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-center space-x-2 text-sm text-gray-300 bg-black/20 backdrop-blur-sm rounded-full px-4 py-2 w-max mx-auto border border-gray-600/30">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span x-text="isRegister ? 'Join Us Today' : 'Secure Connection'">Secure Connection</span>
                    </div>
                </div>
            </div>

            <!-- Right Column - Forms Container with Sliding Logic -->
            <div
                class="relative flex flex-col justify-center bg-black/40 backdrop-blur-md md:backdrop-blur-none md:bg-slate-900 md:bg-gradient-to-br md:from-slate-900 md:to-gray-900 overflow-hidden h-full">

                <!-- LOGIN FORM PANEL -->
                <div class="absolute inset-0 flex flex-col justify-start md:justify-center p-6 md:p-12 form-scroll overflow-y-auto transition-transform duration-700 ease-in-out z-10"
                    :class="isRegister ? '-translate-x-full opacity-0 pointer-events-none' : 'translate-x-0 opacity-100'">

                    <div class="w-full max-w-sm mx-auto space-y-6 pt-8 pb-12 md:py-0">

                        <!-- Mobile Header & Logo -->
                        <div class="flex flex-col items-center mb-6 md:hidden">
                            <div
                                class="w-20 h-20 bg-blue-500/20 backdrop-blur-md rounded-full flex items-center justify-center border border-blue-400/30 shadow-lg mb-4">
                                <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapmar"
                                    class="w-14 h-14 object-contain drop-shadow-xl">
                            </div>
                            <h1 class="text-2xl font-semibold text-white drop-shadow-lg text-center">Login Sapta CBT
                            </h1>
                            <p class="text-gray-200 text-sm text-center drop-shadow-md mt-1">Masukkan kredensial untuk
                                melanjutkan</p>
                        </div>

                        <!-- Header (Desktop) -->
                        <div class="text-center space-y-2 hidden md:block">
                            <h2 class="text-2xl font-light text-gray-100">Sign In</h2>
                            <p class="text-gray-400 text-sm">Enter your credentials to access your account</p>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <!-- Email/Username Field -->
                            <div class="space-y-2">
                                <label class="text-sm text-gray-300 font-light">Email / Username</label>
                                <input type="text" name="login" value="{{ old('login') }}" required
                                    placeholder="Email atau Username"
                                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2 relative">
                                <label class="text-sm text-gray-300 font-light">Password</label>
                                <div class="relative">
                                    <input type="password" id="login-password" name="password" required
                                        placeholder="Enter your password"
                                        class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                                    <button type="button" onclick="togglePassword('login-password', this)"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between text-sm">
                                <label class="flex items-center space-x-2 text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="remember"
                                        class="w-4 h-4 bg-slate-800 border-slate-700 rounded focus:ring-blue-500/50 text-blue-500">
                                    <span class="font-light">Remember me</span>
                                </label>
                                <a href="{{ route('password.request') }}"
                                    class="text-blue-400 hover:text-blue-300 transition-colors font-light">Forgot
                                    password?</a>
                            </div>

                            <!-- Sign In Button -->
                            <button type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg transition-all font-light focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-[0_0_15px_rgba(59,130,246,0.3)] hover:shadow-[0_0_20px_rgba(59,130,246,0.5)]">
                                Sign In
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-slate-700/50"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-gray-900 text-gray-400 font-light rounded-full backdrop-blur-sm">Or
                                    continue with</span>
                            </div>
                        </div>

                        <!-- Social Login -->
                        <div class="flex justify-center mt-2">
                            <a href="{{ Route::has('google.login') ? route('google.login') : '#' }}"
                                class="flex w-full items-center justify-center px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg hover:bg-slate-700/80 transition-all text-gray-200">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z">
                                    </path>
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z">
                                    </path>
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z">
                                    </path>
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z">
                                    </path>
                                </svg>
                                <span class="font-light">Continue with Google</span>
                            </a>
                        </div>

                        <!-- Sign Up Link -->
                        <div class="text-center text-sm text-gray-400 pb-4 md:pb-0">
                            Don't have an account?
                            <button type="button" @click="isRegister = true"
                                class="text-blue-400 hover:text-blue-300 transition-colors font-light ml-1 focus:outline-none">Sign
                                up</button>
                        </div>
                    </div>
                </div>

                <!-- REGISTER FORM PANEL -->
                <div class="absolute inset-0 flex flex-col justify-start md:justify-center p-6 md:p-12 form-scroll overflow-y-auto transition-transform duration-700 ease-in-out z-10"
                    :class="isRegister ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0 pointer-events-none'">

                    <div class="w-full max-w-sm mx-auto space-y-4 pt-8 pb-12 md:py-0">

                        <!-- Mobile Header & Logo -->
                        <div class="flex flex-col items-center mb-4 md:hidden">
                            <div
                                class="w-16 h-16 bg-blue-500/20 backdrop-blur-md rounded-full flex items-center justify-center border border-blue-400/30 shadow-lg mb-3">
                                <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapmar"
                                    class="w-12 h-12 object-contain drop-shadow-xl">
                            </div>
                            <h1 class="text-xl font-semibold text-white drop-shadow-lg text-center">Pendaftaran Akun
                            </h1>
                            <p class="text-gray-200 text-xs text-center drop-shadow-md mt-1">Bergabung bersama sistem
                                CBT Sapmar</p>
                        </div>

                        <!-- Header (Desktop) -->
                        <div class="text-center space-y-1 hidden md:block">
                            <h2 class="text-2xl font-light text-gray-100">Sign Up</h2>
                            <p class="text-gray-400 text-sm">Create your new account</p>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('register') }}" class="space-y-3"
                            x-data="{ role: '{{ old('role', 'siswa') }}' }">
                            @csrf

                            <!-- Role Selection -->
                            <div class="space-y-1">
                                <label class="text-xs text-gray-300 font-light">Mendaftar Sebagai</label>
                                <select name="role" x-model="role"
                                    class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru</option>
                                    <option value="proktor">Proktor</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs text-gray-300 font-light">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    placeholder="Nama Lengkap"
                                    class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="space-y-1">
                                    <label class="text-xs text-gray-300 font-light">Username</label>
                                    <input type="text" name="username" value="{{ old('username') }}" required
                                        placeholder="Username"
                                        class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-gray-300 font-light">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                        placeholder="Email aktif"
                                        class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                                </div>
                            </div>

                            <!-- Dynamic Siswa Fields -->
                            <div x-show="role === 'siswa'" x-cloak class="grid grid-cols-2 gap-2">
                                <div class="space-y-1">
                                    <label class="text-xs text-gray-300 font-light">NISN <span
                                            class="text-gray-500">(Opsional)</span></label>
                                    <input type="text" name="nisn" value="{{ old('nisn') }}" placeholder="Nomor NISN"
                                        class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-gray-300 font-light">Kelas <span
                                            class="text-gray-500">(Opsional)</span></label>
                                    <select name="school_class_id"
                                        class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes ?? [] as $cls)
                                            <option value="{{ $cls->id }}" {{ old('school_class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Dynamic Guru Field -->
                            <div x-show="role === 'guru'" x-cloak class="space-y-1">
                                <label class="text-xs text-gray-300 font-light">NIP <span
                                        class="text-gray-500">(Opsional)</span></label>
                                <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai"
                                    class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="space-y-1 relative">
                                    <label class="text-xs text-gray-300 font-light">Password</label>
                                    <div class="relative">
                                        <input type="password" id="reg-password" name="password" required
                                            placeholder="Min. 8 Kar."
                                            class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                                        <button type="button" onclick="togglePassword('reg-password', this)"
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-1 relative">
                                    <label class="text-xs text-gray-300 font-light">Konfirmasi</label>
                                    <div class="relative">
                                        <input type="password" id="reg-password-conf" name="password_confirmation"
                                            required placeholder="Ulangi"
                                            class="w-full px-3 py-2 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all">
                                        <button type="button" onclick="togglePassword('reg-password-conf', this)"
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg transition-all font-light focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-[0_0_15px_rgba(59,130,246,0.3)] hover:shadow-[0_0_20px_rgba(59,130,246,0.5)] mt-2">
                                Sign Up
                            </button>
                        </form>

                        <!-- Sign In Link -->
                        <div class="text-center text-sm text-gray-400 pt-2 pb-6 md:pb-0">
                            Already have an account?
                            <button type="button" @click="isRegister = false"
                                class="text-blue-400 hover:text-blue-300 transition-colors font-light ml-1 focus:outline-none">Sign
                                in</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }

        const welcomeContainer = document.getElementById('welcome-canvas');
        if (welcomeContainer) {
            const scene = new THREE.Scene();
            const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
            const renderer = new THREE.WebGLRenderer({ alpha: true });

            function updateCanvasSize() {
                const container = welcomeContainer.parentElement;
                if (container) {
                    const rect = container.getBoundingClientRect();
                    renderer.setSize(rect.width, rect.height);
                    material.uniforms.iResolution.value.set(rect.width, rect.height);
                }
            }

            welcomeContainer.appendChild(renderer.domElement);

            const vertexShader = `
                void main() {
                    gl_Position = vec4(position, 1.0);
                }
            `;

            const fragmentShader = `
                uniform float iTime;
                uniform vec2 iResolution;

                vec4 mod289(vec4 x) { return x - floor(x / 289.0) * 289.0; }
                vec4 permute(vec4 x) { return mod289((x * 34.0 + 1.0) * x); }
                vec4 snoise(vec3 v) {
                    const vec2 C = vec2(1.0 / 6.0, 1.0 / 3.0);
                    vec3 i  = floor(v + dot(v, vec3(C.y)));
                    vec3 x0 = v   - i + dot(i, vec3(C.x));
                    vec3 g = step(x0.yzx, x0.xyz);
                    vec3 l = 1.0 - g;
                    vec3 i1 = min(g.xyz, l.zxy);
                    vec3 i2 = max(g.xyz, l.zxy);
                    vec3 x1 = x0 - i1 + C.x;
                    vec3 x2 = x0 - i2 + C.y;
                    vec3 x3 = x0 - 0.5;
                    vec4 p = permute(permute(permute(i.z + vec4(0.0, i1.z, i2.z, 1.0))
                                            + i.y + vec4(0.0, i1.y, i2.y, 1.0))
                                            + i.x + vec4(0.0, i1.x, i2.x, 1.0));
                    vec4 j = p - 49.0 * floor(p / 49.0);
                    vec4 x_ = floor(j / 7.0);
                    vec4 y_ = floor(j - 7.0 * x_); 
                    vec4 x = (x_ * 2.0 + 0.5) / 7.0 - 1.0;
                    vec4 y = (y_ * 2.0 + 0.5) / 7.0 - 1.0;
                    vec4 h = 1.0 - abs(x) - abs(y);
                    vec4 b0 = vec4(x.xy, y.xy);
                    vec4 b1 = vec4(x.zw, y.zw);
                    vec4 s0 = floor(b0) * 2.0 + 1.0;
                    vec4 s1 = floor(b1) * 2.0 + 1.0;
                    vec4 sh = -step(h, vec4(0.0));
                    vec4 a0 = b0.xzyw + s0.xzyw * sh.xxyy;
                    vec4 a1 = b1.xzyw + s1.xzyw * sh.zzww;
                    vec3 g0 = vec3(a0.xy, h.x);
                    vec3 g1 = vec3(a0.zw, h.y);
                    vec3 g2 = vec3(a1.xy, h.z);
                    vec3 g3 = vec3(a1.zw, h.w);
                    vec4 m = max(0.6 - vec4(dot(x0, x0), dot(x1, x1), dot(x2, x2), dot(x3, x3)), 0.0);
                    vec4 m2 = m * m;
                    vec4 m3 = m2 * m;
                    vec4 m4 = m2 * m2;
                    vec3 grad =
                    -6.0 * m3.x * x0 * dot(x0, g0) + m4.x * g0 +
                    -6.0 * m3.y * x1 * dot(x1, g1) + m4.y * g1 +
                    -6.0 * m3.z * x2 * dot(x2, g2) + m4.z * g2 +
                    -6.0 * m3.w * x3 * dot(x3, g3) + m4.w * g3;
                    vec4 px = vec4(dot(x0, g0), dot(x1, g1), dot(x2, g2), dot(x3, g3));
                    return 42.0 * vec4(grad, dot(m4, px));
                }

                void main() {
                    vec2 fragCoord = gl_FragCoord.xy;
                    vec2 p = (-iResolution.xy + 2.0*fragCoord) / iResolution.y;
                    vec3 ww = normalize(-vec3(0., 1., 1.));
                    vec3 uu = normalize(cross(ww, vec3(0., 1., 0.)));
                    vec3 vv = normalize(cross(uu,ww));
                    vec3 rd = p.x*uu + p.y*vv + 1.5*ww;
                    vec3 pos = -ww + rd*(ww.y/rd.y);
                    pos.y = iTime*0.3;
                    pos *= 3.;
                    vec4 n = snoise( pos );
                    pos -= 0.07*n.xyz;
                    n = snoise( pos );
                    pos -= 0.07*n.xyz;
                    n = snoise( pos );
                    float intensity = exp(n.w*3. - 1.5);
                    vec3 color = vec3(intensity * 0.4);
                    color.b += intensity * 0.6;
                    color.g += intensity * 0.2;
                    gl_FragColor = vec4(color, 0.7);
                }
            `;

            const material = new THREE.ShaderMaterial({
                vertexShader: vertexShader,
                fragmentShader: fragmentShader,
                uniforms: {
                    iTime: { value: 0 },
                    iResolution: { value: new THREE.Vector2(400, 700) }
                },
                transparent: true
            });

            const geometry = new THREE.PlaneGeometry(2, 2);
            const mesh = new THREE.Mesh(geometry, material);
            scene.add(mesh);

            updateCanvasSize();

            function animate() {
                requestAnimationFrame(animate);
                material.uniforms.iTime.value += 0.003;
                renderer.render(scene, camera);
            }

            window.addEventListener('resize', updateCanvasSize);
            animate();
        }
    </script>
</body>

</html>