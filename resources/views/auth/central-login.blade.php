@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">
                    🔐 DesenvolveCity
                </h1>
                <p class="text-slate-300 text-sm">
                    Painel Central - Acesso Restrito
                </p>
                <p class="text-slate-400 text-xs mt-2">
                    Apenas proprietário do sistema
                </p>
            </div>

            <!-- Card de Login -->
            <div class="bg-white rounded-lg shadow-2xl p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800 font-semibold text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form action="{{ route('central.login.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                            E-mail
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition @error('email') border-red-500 @enderror"
                            placeholder="admin@desenvolve.city" required autofocus />
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            Senha
                        </label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition @error('password') border-red-500 @enderror"
                            placeholder="••••••••" required />
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500" />
                        <span class="ml-2 text-sm text-slate-600">Lembrar-me por 90 dias</span>
                    </label>

                    <x-turnstile />
                    @error('cf-turnstile-response')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        Entrar na Central
                    </button>
                </form>

                <!-- Aviso de Segurança -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-blue-900 text-xs leading-relaxed">
                        <strong>🔒 Segurança:</strong> Esta é uma área restrita apenas ao proprietário do sistema.
                        Se você é cliente, use o <a href="{{ route('tenant.login') }}" class="font-semibold underline">login
                            para clientes</a>.
                    </p>
                </div>

                <!-- Link para login cliente -->
                <div class="mt-6 text-center">
                    <p class="text-slate-600 text-sm">
                        É cliente?
                        <a href="{{ route('tenant.login') }}" class="text-blue-600 font-semibold hover:underline">
                            Clique aqui
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-slate-400 text-xs">
                    &copy; {{ date('Y') }} DesenvolveCity. Tutti i diritti riservati.
                </p>
            </div>
        </div>
    </div>
@endsection
