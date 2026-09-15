<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Lupa Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f0f4f8;
            background-image: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            background-color: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            padding: 40px;
        }

        .custom-input {
            width: 100%;
            background-color: #f3f4f6;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            outline: none;
            background-color: #fff;
            border-color: #1e3a5f;
            box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.1);
        }

        .custom-btn {
            background-color: #1e3a5f;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 32px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .custom-btn:hover {
            background-color: #2a466a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 px-4">
    <div class="auth-card">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapta Marga" class="w-20 h-20 object-contain mx-auto mb-4 drop-shadow-md">
            <h1 class="font-bold text-2xl text-gray-800 mb-2">Lupa Password?</h1>
            <p class="text-sm text-gray-500">
                Tidak masalah. Masukkan alamat email yang Anda gunakan, dan kami akan mengirimkan tautan untuk membuat kata sandi baru.
            </p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-sm text-green-600 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-6">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="Masukkan Alamat Email"
                       class="custom-input @if($errors->has('email')) border-red-400 bg-red-50 @endif">
                @if($errors->has('email'))
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <button type="submit" class="custom-btn">
                Kirim Tautan Reset Password
            </button>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-sapta-600 font-medium hover:text-sapta-800 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke halaman Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>
