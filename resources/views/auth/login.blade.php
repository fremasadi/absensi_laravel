<x-guest-layout>
    <div class="w-full max-w-sm mx-auto">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16">
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <x-input-label for="email" :value="'Email'" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" :value="'Kata Sandi'" />
                <x-text-input id="password" class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me + Actions dalam satu baris -->
            <div style="display: flex; align-items: center; justify-content: space-between;" class="mb-4">
                <div style="display: flex; align-items: center;">
                    <input id="remember_me" type="checkbox" name="remember" style="margin-right: 8px;">
                    <label for="remember_me" style="font-size: 14px; color: #4B5563;">Ingat saya</label>
                </div>

                <button type="submit" style="
                    padding: 8px 16px;
                    background-color: #2563eb;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-weight: 600;
                    font-size: 14px;
                    cursor: pointer;
                ">
                    Masuk
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
