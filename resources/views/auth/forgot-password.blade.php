@extends('layouts.public')

@section('title', 'Recuperar Senha - Proticketsports')

@push('styles')
<style>
    /* Fundo personalizado moderno com override forçado (Mesmo do Login) */
    .login-background-forced {
        background-color: #0a192f;
        background-image: 
            url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3Ccircle cx='13' cy='13' r='1'/%3E%3C/g%3E%3C/svg%3E"),
            linear-gradient(135deg, #020c1b 0%, #0a192f 50%, #172a46 100%) !important;
        background-size: auto, cover;
        background-attachment: fixed;
        background-repeat: repeat, no-repeat;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen login-background-forced flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
            
            <div class="relative h-48 bg-orange-600">
                <img class="w-full h-full object-cover" 
                     src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=2070&auto=format&fit=crop" 
                     alt="Esportes">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 z-20">
                    <h2 class="text-2xl font-extrabold text-white tracking-tight shadow-sm">Recuperar Acesso</h2>
                    <p class="mt-1 text-sm text-orange-100 font-medium text-shadow">
                        Vamos ajudar você a voltar.
                    </p>
                </div>
            </div>

            <div class="py-8 px-6 sm:px-10">
                
                <div class="mb-6 text-sm text-gray-600 text-center leading-relaxed">
                    {{ __('Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail abaixo e enviaremos um link para você redefinir sua senha.') }}
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            E-mail cadastrado
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-regular fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus 
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent sm:text-sm transition duration-150 ease-in-out" 
                                placeholder="exemplo@email.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all transform hover:scale-[1.02]">
                            {{ __('Enviar Link de Recuperação') }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center border-t border-gray-100 pt-6">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-orange-600 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Voltar para o Login
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-white/60">
            &copy; {{ date('Y') }} Proticketsports.
        </div>
    </div>
</div>
@endsection