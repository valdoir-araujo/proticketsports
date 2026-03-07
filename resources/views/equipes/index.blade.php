@extends('layouts.public')

@section('title', 'Equipes - Proticketsports')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-10 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-black text-slate-900">Minhas equipes</h1>
            <a href="{{ route('equipes.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 shadow-lg">
                <i class="fa-solid fa-plus"></i> Nova equipe
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                <h2 class="text-lg font-bold text-white">Lista de equipes</h2>
                <p class="text-orange-100 text-sm mt-0.5">Gerencie as equipes para usar em inscrições de eventos.</p>
            </div>

            <div class="p-4 sm:p-6">
                @if(session('sucesso'))
                    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-800">
                        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('sucesso') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b-2 border-slate-200 bg-slate-50">
                                <th class="py-3 px-3 text-left text-xs font-bold text-slate-700 uppercase">Logo</th>
                                <th class="py-3 px-3 text-left text-xs font-bold text-slate-700 uppercase">Nome</th>
                                <th class="py-3 px-3 text-left text-xs font-bold text-slate-700 uppercase">Coordenador</th>
                                <th class="py-3 px-3 text-left text-xs font-bold text-slate-700 uppercase">Fundação</th>
                                <th class="py-3 px-3 text-right text-xs font-bold text-slate-700 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipes as $equipe)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                    <td class="py-3 px-3">
                                        @if($equipe->logo_url)
                                            <img src="{{ asset('storage/' . $equipe->logo_url) }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-sm">{{ substr($equipe->nome, 0, 1) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-800">{{ $equipe->nome }}</td>
                                    <td class="py-3 px-3 text-slate-600">{{ $equipe->coordenador->user->name ?? '—' }}</td>
                                    <td class="py-3 px-3 text-slate-600">{{ $equipe->data_fundacao ? $equipe->data_fundacao->format('d/m/Y') : '—' }}</td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="{{ route('equipes.edit', $equipe) }}" class="inline-flex items-center gap-1 text-orange-600 hover:text-orange-700 font-medium text-sm">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <form action="{{ route('equipes.destroy', $equipe) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('Tem certeza que deseja excluir esta equipe?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                                <i class="fa-solid fa-trash-can"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 px-4 text-center text-slate-500">
                                        <i class="fa-solid fa-people-group text-3xl text-slate-300 mb-2 block"></i>
                                        Nenhuma equipe cadastrada. <a href="{{ route('equipes.create') }}" class="text-orange-600 font-bold hover:underline">Cadastrar primeira equipe</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($equipes->hasPages())
                    <div class="mt-4">
                        {{ $equipes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
