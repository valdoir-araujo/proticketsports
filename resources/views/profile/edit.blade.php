<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Meu Perfil
                </h2>
                <p class="text-sm text-slate-500 font-medium">Atualize suas informações e preferências</p>
            </div>
        </div>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        {{-- Mesmo container do cabeçalho (max-w-7xl): formulário alinhado à esquerda, largura total --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
            {{-- Card: Informações do Perfil --}}
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Card: Atualizar Senha --}}
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Card: Zona de perigo --}}
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>