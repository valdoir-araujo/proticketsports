<section class="space-y-5">
    <header class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center text-red-600 shrink-0">
            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Excluir Conta</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Todos os seus dados serão apagados de forma permanente. Baixe o que precisar antes de excluir.
            </p>
        </div>
    </header>

    <div class="pt-2">
        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center justify-center gap-2 min-h-[44px] px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl border border-transparent focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition touch-manipulation"
        >
            <i class="fa-solid fa-trash-can text-sm"></i> Excluir conta
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-800">
                Tem certeza que deseja excluir sua conta?
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                Todos os recursos e dados serão apagados permanentemente. Digite sua senha para confirmar.
            </p>

            <div class="mt-5">
                <x-input-label for="password" value="Senha" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full max-w-xs border-slate-300 rounded-lg"
                    placeholder="Sua senha"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap gap-3 justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')" class="rounded-xl font-bold">
                    Cancelar
                </x-secondary-button>
                <x-danger-button class="rounded-xl font-bold">
                    Excluir conta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>