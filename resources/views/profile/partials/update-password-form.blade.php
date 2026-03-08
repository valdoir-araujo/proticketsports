<section>
    <header class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
            <i class="fa-solid fa-key text-sm"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Atualizar Senha</h2>
            <p class="text-sm text-slate-500">Use uma senha longa e aleatória para manter sua conta segura.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5 w-full max-w-md">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Senha Atual" class="font-medium text-slate-700" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                </div>
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nova Senha" class="font-medium text-slate-700" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                </div>
                <x-text-input id="update_password_password" name="password" type="password" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar Senha" class="font-medium text-slate-700" />
            <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                </div>
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full pl-10 border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <x-primary-button class="rounded-xl font-bold px-5 py-2.5 min-h-[44px] touch-manipulation">Salvar senha</x-primary-button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i> Salvo.
                </p>
            @endif
        </div>
    </form>
</section>