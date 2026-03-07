@extends('layouts.public')

@section('title', 'Identificação - ' . $evento->nome)

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    
    {{-- Elementos Decorativos --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    {{-- Botão Voltar --}}
    <div class="absolute top-6 left-6 z-20">
        <a href="{{ route('carrinho.index') }}" class="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao Carrinho
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        {{-- Branding --}}
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fa-solid fa-user-check text-xl"></i>
                </div>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Identificação Rápida</h2>
            <p class="mt-2 text-sm text-slate-500">
                Informe seus dados para vincularmos sua compra.
            </p>
        </div>

        {{-- Card Principal --}}
        <div class="bg-white py-8 px-4 shadow-xl shadow-slate-200/60 sm:rounded-2xl sm:px-10 border border-slate-200">
            
            <form method="POST" action="{{ route('loja.identificacao.verificar') }}" id="identificacao-form" class="space-y-5">
                @csrf
                <input type="hidden" name="evento_id" value="{{ $evento->id }}">
                
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded-r">
                        <p class="text-xs text-red-700 font-bold">{{ $errors->first() }}</p>
                    </div>
                @endif

                {{-- Campo Identificação --}}
                <div>
                    <label for="identificacao" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                        CPF, CNPJ ou E-mail
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-id-card text-slate-400"></i>
                        </div>
                        <input id="identificacao" name="identificacao" type="text" required autofocus
                            class="block w-full pl-10 sm:text-sm bg-white border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 transition-colors h-12 shadow-sm text-lg" 
                            placeholder="Seu documento ou email" value="{{ old('identificacao') }}">
                    </div>
                </div>

                {{-- Campo Data de Nascimento (Otimizado Mobile) --}}
                <div>
                    <label for="nascimento_visual" class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                        Data de Nascimento
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                        </div>
                        
                        {{-- Input Visual (Para digitação fácil) --}}
                        <input id="nascimento_visual" type="text" inputmode="numeric" required
                            class="block w-full pl-10 sm:text-sm bg-white border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 transition-colors h-12 shadow-sm text-lg text-slate-600 placeholder-slate-300" 
                            placeholder="DD/MM/AAAA" 
                            maxlength="10"
                            value="{{ old('nascimento') ? \Carbon\Carbon::parse(old('nascimento'))->format('d/m/Y') : '' }}">
                            
                        {{-- Input Oculto (Envia o formato correto YYYY-MM-DD para o backend) --}}
                        <input type="hidden" name="nascimento" id="nascimento" value="{{ old('nascimento') }}">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Digite apenas os números. Ex: 25051990</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-orange-500/20 text-sm font-bold text-white bg-slate-900 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all transform hover:-translate-y-0.5 gap-2 items-center">
                        <span>Continuar</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-500 mb-2">Novo por aqui?</p>
                <a href="{{ route('register') }}" class="text-sm font-bold text-orange-600 hover:text-orange-800 hover:underline">
                    Fazer cadastro completo
                </a>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-slate-400">
            <i class="fa-solid fa-lock mr-1"></i> Ambiente seguro. Seus dados estão protegidos.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- MÁSCARA INTELIGENTE CPF/CNPJ/EMAIL ---
    const inputIdent = document.getElementById('identificacao');
    
    inputIdent.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // Se tiver @ é email, não aplica máscara
        if (value.includes('@') || /[a-zA-Z]/.test(value)) {
            return;
        }

        // Remove não dígitos
        value = value.replace(/\D/g, '');
        if (value.length > 14) value = value.slice(0, 14);

        // Aplica máscara CPF ou CNPJ
        if (value.length <= 11) {
            e.target.value = value
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            e.target.value = value
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        }
    });

    // --- MÁSCARA DATA DE NASCIMENTO (DD/MM/AAAA) ---
    const inputNascVisual = document.getElementById('nascimento_visual');
    const inputNascHidden = document.getElementById('nascimento');

    inputNascVisual.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
        
        if (value.length > 8) value = value.slice(0, 8); // Limita a 8 dígitos

        // Aplica máscara DD/MM/AAAA
        if (value.length > 4) {
            e.target.value = value.replace(/^(\d{2})(\d{2})(\d{0,4})/, '$1/$2/$3');
        } else if (value.length > 2) {
            e.target.value = value.replace(/^(\d{2})(\d{0,2})/, '$1/$2');
        } else {
            e.target.value = value;
        }

        // Atualiza o input hidden (formato YYYY-MM-DD) se a data estiver completa
        if (value.length === 8) {
            const dia = value.substring(0, 2);
            const mes = value.substring(2, 4);
            const ano = value.substring(4, 8);
            
            // Validação básica de data
            if(mes > 0 && mes <= 12 && dia > 0 && dia <= 31) {
                inputNascHidden.value = `${ano}-${mes}-${dia}`;
            } else {
                inputNascHidden.value = ''; // Data inválida
            }
        } else {
            inputNascHidden.value = '';
        }
    });
});
</script>
@endpush