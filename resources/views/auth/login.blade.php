<form method="POST" action="{{ route('login') }}" class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
    @csrf
    
    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16">
    </div>
    
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">{{ __('Login to Your Account') }}</h2>

    <!-- Email Address -->
    <div class="mb-4">
        <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
        <x-text-input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div class="mb-4">
        <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
        <x-text-input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    type="password"
                    name="password"
                    required autocomplete="current-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Remember Me -->
    <div class="mb-4">
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
        </label>
    </div>

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        @if (Route::has('password.request'))
            <a class="text-sm text-indigo-600 hover:text-indigo-800 font-medium" href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif

        <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
            {{ __('Log in') }}
        </x-primary-button>
    </div>
    
    
</form>