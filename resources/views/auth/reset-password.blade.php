<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Buat Password Baru</title>
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
            <h1 class="font-bold text-2xl text-gray-800 mb-2">Buat Password Baru</h1>
            <p class="text-sm text-gray-500">
                Silakan buat kata sandi baru untuk akun Anda.
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="mb-4">
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       placeholder="Email"
                       class="custom-input @if($errors->has('email')) border-red-400 bg-red-50 @endif" readonly>
                @if($errors->has('email'))
                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           placeholder="Password Baru"
                           class="custom-input pr-10 @if($errors->has('password')) border-red-400 bg-red-50 @endif">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <!-- Confirm Password -->
            <div class="mb-8">
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           placeholder="Konfirmasi Password Baru"
                           class="custom-input pr-10 @if($errors->has('password_confirmation')) border-red-400 bg-red-50 @endif">
                    <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                @if($errors->has('password_confirmation'))
                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('password_confirmation') }}</p>
                @endif
            </div>

            <button type="submit" class="custom-btn">
                Simpan Password Baru
            </button>
        </form>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }
    </script>
</body>
</html>
