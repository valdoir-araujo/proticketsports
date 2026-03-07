<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Atualizar Senha
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Garanta que sua conta esteja usando uma senha longa e aleatória para se manter segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        {{-- Senha Atual --}}
        <div>
            <x-input-label for="update_password_current_password" value="Senha Atual" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full pl-10" autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- Nova Senha --}}
        <div>
            <x-input-label for="update_password_password" value="Nova Senha" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full pl-10" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- Confirmar Nova Senha --}}
        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar Senha" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full pl-10" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >Salvo.</p>
            @endif
        </div>
    </form>
</section>