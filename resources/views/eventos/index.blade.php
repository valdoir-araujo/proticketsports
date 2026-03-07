@extends('layouts.public')

@section('title', 'Calendário de Eventos - Proticketsports')

@section('content')
    {{-- Cabeçalho da Página --}}
    <header class="bg-gray-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold">Calendário de Eventos</h1>
            <p class="text-lg mt-2 text-gray-300">Encontre a sua próxima prova e faça a sua inscrição!</p>
        </div>
    </header>

    <div class="container mx-auto p-4 md:p-8">
        {{-- Secção de Filtros --}}
        <div class="bg-white p-4 rounded-lg shadow-md mb-8">
            <form method="GET" action="{{ route('eventos.public.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="search" placeholder="Nome do evento..." value="{{ request('search') }}" class="border-gray-300 rounded-md shadow-sm">
                    <input type="text" name="cidade" placeholder="Cidade..." value="{{ request('cidade') }}" class="border-gray-300 rounded-md shadow-sm">
                    <input type="text" name="estado" placeholder="UF (ex: PR)" value="{{ request('estado') }}" class="border-gray-300 rounded-md shadow-sm">
                    <div class="flex space-x-2">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Filtrar</button>
                        <a href="{{ route('eventos.public.index') }}" class="w-full text-center bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Grelha de Eventos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($eventos as $evento)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-300 flex flex-col">
                    <a href="{{ route('eventos.public.show', $evento) }}">
                        <img class="w-full h-48 object-cover" src="{{ $evento->thumbnail_url ? asset('storage/' . $evento->thumbnail_url) : 'https://via.placeholder.com/400x300' }}" alt="Imagem do evento {{ $evento->nome }}">
                    </a>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-slate-800 leading-tight">
                            <a href="{{ route('eventos.public.show', $evento) }}" class="hover:text-blue-600">{{ $evento->nome }}</a>
                        </h3>
                        <p class="text-sm text-gray-600 mt-2">
                            <i class="fa-solid fa-calendar-alt mr-2 text-gray-400"></i>{{ \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y') }}
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            <i class="fa-solid fa-location-dot mr-2 text-gray-400"></i>{{ $evento->cidade }} - {{ $evento->estado }}
                        </p>
                        <div class="mt-auto pt-4">
                             <a href="{{ route('eventos.public.show', $evento) }}" class="w-full text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-semibold">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-600">Nenhum evento encontrado com os filtros aplicados.</p>
            @endforelse
        </div>

        {{-- Paginação --}}
        <div class="mt-8">
            {{ $eventos->links() }}
        </div>
    </div>
@endsection
