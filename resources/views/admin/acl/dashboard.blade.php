<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestão de Direitos de Acesso (ACL)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Barra de Busca --}}
                    <form method="GET" action="{{ route('admin.acl.dashboard') }}" class="mb-6">
                        <div class="flex gap-4">
                            <input type="text" name="search" placeholder="Buscar usuário por nome ou email..." value="{{ request('search') }}" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Buscar</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Papel (Role)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões Extras</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($usuarios as $usuario)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $usuario->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $usuario->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                                  ($usuario->role === 'organizador' ? 'bg-purple-100 text-purple-800' : 
                                                  ($usuario->role === 'staff' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')) }}">
                                                {{ ucfirst($usuario->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($usuario->permissions as $permission)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $permission->label }}
                                                    </span>
                                                @endforeach
                                                @if($usuario->permissions->isEmpty())
                                                    <span class="text-xs text-gray-400 italic">Nenhuma específica</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- AQUI ESTÁ O LINK PARA A PÁGINA QUE VOCÊ MOSTROU --}}
                                            <a href="{{ route('admin.usuarios.permissions.edit', $usuario->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold border border-indigo-200 px-3 py-1 rounded hover:bg-indigo-50">
                                                <i class="fa-solid fa-key mr-1"></i> Editar Acessos
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum usuário encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $usuarios->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>