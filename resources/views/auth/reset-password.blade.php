@extends('layouts.public')

@section('title', 'Redefinir Senha - Proticketsports')

@push('styles')
<style>
    /* Fundo personalizado moderno com override forçado */
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
        <!-- Card Principal -->
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
            
            <!-- CABEÇALHO (Hero Image) -->
            <div class="relative h-48 bg-orange-600">
                <img class="w-full h-full object-cover" 
                     src="https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=2070&auto=format&fit=crop" 
                     alt="Esportes">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 z-20">
                    <h2 class="text-2xl font-extrabold text-white tracking-tight shadow-sm">Criar Nova Senha</h2>
                    <p class="mt-1 text-sm text-orange-100 font-medium text-shadow">
                        Segurança em primeiro lugar.
                    </p>
                </div>
            </div>

            <!-- Corpo -->
            <div class="py-8 px-6 sm:px-10">

                <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Email') }}
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-regular fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent sm:text-sm bg-gray-50 text-gray-500 cursor-not-allowed" 
                                readonly>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Password') }}
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                class="appearance-none block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent sm:text-sm transition duration-150 ease-in-out" 
                                placeholder="Nova senha">
                            
                            <button type="button" class="togglePassword absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-orange-600 cursor-pointer focus:outline-none" tabindex="-1">
                                <i class="fa-solid fa-eye transition-colors"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Confirm Password') }}
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent sm:text-sm transition duration-150 ease-in-out" 
                                placeholder="Confirme a nova senha">
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Botão -->
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all transform hover:scale-[1.02]">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-white/60">
            &copy; {{ date('Y') }} Proticketsports. Todos os direitos reservados.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script para alternar visibilidade da senha
    document.querySelectorAll('.togglePassword').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
</script>
@endpush