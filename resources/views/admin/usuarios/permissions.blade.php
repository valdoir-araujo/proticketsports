<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gerenciar Acessos: {{ $usuario->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.usuarios.permissions.update', $usuario->id) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="mb-8 border-b pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Papel Principal (Role)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $usuario->role === 'atleta' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="role" value="atleta" class="text-orange-600 focus:ring-orange-500" {{ $usuario->role === 'atleta' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-900">Atleta</span>
                                    <span class="block text-xs text-gray-500">Acesso padrão de utilizador.</span>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $usuario->role === 'organizador' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="role" value="organizador" class="text-orange-600 focus:ring-orange-500" {{ $usuario->role === 'organizador' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-900">Organizador</span>
                                    <span class="block text-xs text-gray-500">Cria eventos e campeonatos.</span>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $usuario->role === 'staff' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="role" value="staff" class="text-orange-600 focus:ring-orange-500" {{ $usuario->role === 'staff' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-900">Staff / Apoio</span>
                                    <span class="block text-xs text-gray-500">Acesso administrativo limitado.</span>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-red-50 {{ $usuario->role === 'admin' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                                <input type="radio" name="role" value="admin" class="text-red-600 focus:ring-red-500" {{ $usuario->role === 'admin' ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-red-700">Super Admin</span>
                                    <span class="block text-xs text-red-500">Acesso total ao sistema.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Permissões Específicas</h3>
                    <p class="text-sm text-gray-500 mb-6">Selecione quais áreas do painel administrativo este utilizador pode acessar (útil para Staff).</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($permissionsGrouped as $group => $permissions)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2 uppercase text-xs tracking-wider">{{ $group }}</h4>
                                <div class="space-y-3">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-start">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}" 
                                                   class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                   {{ $usuario->permissions->contains($permission->id) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">{{ $permission->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-end gap-4 border-t pt-6">
                        <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Salvar Permissões
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>