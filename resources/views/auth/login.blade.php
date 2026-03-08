@extends('layouts.public')

@section('title', 'Acessar Conta - Proticketsports')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    
    {{-- Fundo: padrão sutil + manchas de cor --}}
    <div class="absolute inset-0 overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-orange-500/5 to-transparent"></div>
        <div class="absolute top-[-15%] right-[-10%] w-[50%] h-[50%] bg-orange-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-15%] left-[-10%] w-[40%] h-[40%] bg-slate-400/10 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, #0f172a 1px, transparent 0); background-size: 24px 24px;"></div>
    </div>

    <div class="w-full sm:mx-auto sm:max-w-[420px] relative z-10">
        {{-- Cabeçalho --}}
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-block focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 rounded-xl">
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    <span class="text-orange-600">Pro</span>ticketsports
                </h1>
            </a>
            <p class="mt-3 text-slate-600 text-base font-medium">
                Acesse sua conta para gerenciar inscrições
            </p>
        </div>

        {{-- Card do formulário --}}
        <div class="relative bg-white/90 backdrop-blur-sm py-8 px-5 sm:px-10 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80">
            {{-- Faixa de destaque no topo do card --}}
            <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl bg-gradient-to-r from-orange-500 to-orange-600" aria-hidden="true"></div>

            <x-auth-session-status class="mb-5" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="login-form">
                @csrf

                {{-- Estrangeiro: opção discreta --}}
                <div class="flex justify-end">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-orange-600 cursor-pointer transition-colors select-none">
                        <input type="checkbox" name="login_estrangeiro" value="1" id="login_estrangeiro"
                            class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                        <span>É estrangeiro? Entrar com e-mail</span>
                    </label>
                </div>

                {{-- Login (e-mail ou CPF) --}}
                <div>
                    <label for="login" id="login-label" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        E-mail ou CPF/CNPJ
                    </label>
                    <div class="relative rounded-xl border-2 border-slate-200 bg-slate-50/50 focus-within:border-orange-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" 
                            class="block w-full pl-11 pr-4 py-3 bg-transparent text-slate-800 placeholder-slate-400 focus:outline-none sm:text-sm rounded-xl" 
                            placeholder="seu@email.com ou CPF">
                    </div>
                    <x-input-error :messages="$errors->get('login')" class="mt-1.5" />
                    <span id="login-error" class="text-sm text-red-600 mt-1 font-medium hidden"></span>
                    <p id="login-hint-estrangeiro" class="text-sm text-orange-600 mt-1.5 font-medium hidden">Use o mesmo e-mail cadastrado na plataforma.</p>
                </div>

                {{-- Senha --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-slate-700">
                            Senha
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                                Esqueceu a senha?
                            </a>
                        @endif
                    </div>
                    <div class="relative rounded-xl border-2 border-slate-200 bg-slate-50/50 focus-within:border-orange-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="block w-full pl-11 pr-12 py-3 bg-transparent text-slate-800 placeholder-slate-400 focus:outline-none sm:text-sm rounded-xl" 
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <button type="button" id="togglePassword" class="p-1 text-slate-400 hover:text-slate-600 focus:outline-none rounded" tabindex="-1" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                {{-- Manter conectado --}}
                <div class="flex items-center gap-2">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                    <label for="remember_me" class="text-sm text-slate-600 cursor-pointer select-none">
                        Manter conectado
                    </label>
                </div>

                {{-- Botão Entrar --}}
                <div class="pt-1">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-base font-bold text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 shadow-lg shadow-orange-500/25 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 active:scale-[0.99]">
                        <i class="fa-solid fa-right-to-bracket text-white/90"></i>
                        Entrar
                    </button>
                </div>
            </form>

            {{-- Separador "Novo por aqui?" --}}
            <div class="mt-8 pt-6 border-t border-slate-200">
                <p class="text-center text-sm text-slate-500 mb-4">
                    Ainda não tem conta?
                </p>
                <a href="{{ route('register') }}" class="block w-full text-center py-3 px-4 rounded-xl border-2 border-slate-200 text-slate-700 font-bold text-sm hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700 transition-all">
                    Criar conta gratuita
                </a>
            </div>
        </div>
        
        {{-- Rodapé --}}
        <p class="mt-8 text-center text-xs text-slate-400 flex items-center justify-center gap-1.5 flex-wrap">
            <i class="fa-solid fa-lock text-slate-300"></i>
            <span>&copy; {{ date('Y') }} Proticketsports. Conexão segura.</span>
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
    const loginLabel = document.getElementById('login-label');
    const hintEstrangeiro = document.getElementById('login-hint-estrangeiro');
    const checkEstrangeiro = document.getElementById('login_estrangeiro');

    function updateLoginUIForEstrangeiro() {
        const isEstrangeiro = checkEstrangeiro && checkEstrangeiro.checked;
        if (loginLabel) loginLabel.textContent = isEstrangeiro ? 'E-mail' : 'E-mail ou CPF/CNPJ';
        if (loginInput) loginInput.placeholder = isEstrangeiro ? 'seu@email.com' : 'seu@email.com ou CPF';
        if (hintEstrangeiro) {
            if (isEstrangeiro) hintEstrangeiro.classList.remove('hidden'); else hintEstrangeiro.classList.add('hidden');
        }
    }
    if (checkEstrangeiro) {
        checkEstrangeiro.addEventListener('change', updateLoginUIForEstrangeiro);
        updateLoginUIForEstrangeiro();
    }

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

    // Input Masking (apenas quando não for estrangeiro)
    loginInput.addEventListener('input', function(e) {
        if (checkEstrangeiro && checkEstrangeiro.checked) return;
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

    // Form Submission (estrangeiro: só e-mail, sem validar CPF/CNPJ)
    loginForm.addEventListener('submit', function(e) {
        if (checkEstrangeiro && checkEstrangeiro.checked) {
            if (!loginInput.value || !loginInput.value.includes('@')) {
                e.preventDefault();
                loginError.textContent = 'Estrangeiros devem informar o e-mail cadastrado.';
                loginError.classList.remove('hidden');
                loginInput.focus();
            }
            return;
        }
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