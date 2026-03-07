@extends('layouts.public')

@section('title', 'Acessar Conta - Proticketsports')

@section('content')
<div class="min-h-screen bg-white flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    
    {{-- Elementos Decorativos de Fundo (Sutis) --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    {{-- Botão Voltar --}}
    <div class="absolute top-6 left-6 z-20">
        <a href="{{ url('/') }}" class="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao site
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        {{-- Logo / Branding --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                <span class="text-orange-600">Pro</span>ticketsports
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Acesse sua conta para gerenciar inscrições
            </p>
        </div>

        {{-- Card Principal (Agora com fundo Cinza Claro) --}}
        <div class="bg-slate-50 py-8 px-4 shadow-xl shadow-slate-200/60 sm:rounded-2xl sm:px-10 border border-slate-200">
            
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6" id="login-form">
                @csrf

                {{-- Input Login --}}
                <div>
                    <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                        CPF, CNPJ ou E-mail
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-envelope text-slate-400"></i>
                        </div>
                        {{-- Input Branco para destacar no fundo cinza --}}
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" 
                            class="block w-full pl-10 sm:text-sm bg-white border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 transition-colors h-11 shadow-sm" 
                            placeholder="seu@email.com ou documento">
                    </div>
                    <x-input-error :messages="$errors->get('login')" class="mt-1" />
                    <span id="login-error" class="text-xs text-red-500 mt-1 font-bold hidden"></span>
                </div>

                {{-- Input Senha --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                            Senha
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-orange-600 hover:text-orange-500 font-medium">
                                Esqueceu?
                            </a>
                        @endif
                    </div>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        {{-- Input Branco para destacar no fundo cinza --}}
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="block w-full pl-10 pr-10 sm:text-sm bg-white border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 transition-colors h-11 shadow-sm" 
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" id="togglePassword" class="text-slate-400 hover:text-slate-600 focus:outline-none" tabindex="-1">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                {{-- Lembrar --}}
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded cursor-pointer bg-white">
                    <label for="remember_me" class="ml-2 block text-sm text-slate-600 cursor-pointer">
                        Manter conectado
                    </label>
                </div>

                {{-- Botão Submit --}}
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-slate-900 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:-translate-y-0.5">
                        Entrar
                    </button>
                </div>
            </form>

            {{-- Separador --}}
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-300/70"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-slate-50 text-slate-500 font-medium">Novo por aqui?</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('register') }}" class="w-full flex justify-center py-3 px-4 border border-slate-300 rounded-lg shadow-sm bg-white text-sm font-bold text-slate-700 hover:bg-white hover:text-orange-600 hover:border-orange-300 transition-all">
                        Criar Conta Gratuita
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Rodapé --}}
        <p class="mt-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Proticketsports. Ambiente Seguro SSL.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loginInput = document.getElementById('login');
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    
    // Toggle Password Logic
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if(togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function (e) {
            e.preventDefault(); 
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }

    // CPF Validator
    function validateCpf(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
        let sum = 0, rest;
        for (let i = 1; i <= 9; i++) sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        rest = (sum * 10) % 11;
        if ((rest === 10) || (rest === 11)) rest = 0;
        if (rest !== parseInt(cpf.substring(9, 10))) return false;
        sum = 0;
        for (let i = 1; i <= 10; i++) sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        rest = (sum * 10) % 11;
        if ((rest === 10) || (rest === 11)) rest = 0;
        return rest === parseInt(cpf.substring(10, 11));
    }

    // CNPJ Validator
    function validateCnpj(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');
        if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;
        let size = cnpj.length - 2;
        let numbers = cnpj.substring(0, size);
        let digits = cnpj.substring(size);
        let sum = 0;
        let pos = size - 7;
        for (let i = size; i >= 1; i--) {
            sum += numbers.charAt(size - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(0)) return false;
        size = size + 1;
        numbers = cnpj.substring(0, size);
        sum = 0;
        pos = size - 7;
        for (let i = size; i >= 1; i--) {
            sum += numbers.charAt(size - i) * pos--;
            if (pos < 2) pos = 9;
        }
        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        return result == digits.charAt(1);
    }

    // Input Masking
    loginInput.addEventListener('input', function(e) {
        const originalValue = e.target.value;
        loginError.classList.add('hidden');
        loginError.textContent = ''; 

        if (originalValue.includes('@') || /[a-zA-Z]/.test(originalValue)) {
            return;
        }

        let value = originalValue.replace(/\D/g, ''); 

        if (value.length > 14) value = value.slice(0, 14);

        if (value.length <= 11) { // CPF
            e.target.value = value
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else { // CNPJ
            e.target.value = value
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        }
    });

    // Form Submission
    loginForm.addEventListener('submit', function(e) {
        const value = loginInput.value;
        if (value.includes('@')) return; 

        const pureDigits = value.replace(/\D/g, '');

        if (pureDigits.length === 11) {
            if (!validateCpf(pureDigits)) {
                e.preventDefault();
                loginError.textContent = 'CPF informado é inválido.';
                loginError.classList.remove('hidden');
                loginInput.focus();
            }
        } else if (pureDigits.length === 14) {
            if (!validateCnpj(pureDigits)) {
                e.preventDefault();
                loginError.textContent = 'CNPJ informado é inválido.';
                loginError.classList.remove('hidden');
                loginInput.focus();
            }
        } else if (pureDigits.length > 0) { 
             e.preventDefault();
             loginError.textContent = 'Documento inválido. Digite CPF, CNPJ ou E-mail.';
             loginError.classList.remove('hidden');
             loginInput.focus();
        }
    });
});
</script>
@endpush