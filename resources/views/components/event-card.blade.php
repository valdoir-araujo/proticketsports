@props(['evento'])

{{-- O link agora envolve todo o card, mantendo os efeitos de grupo --}}
<a href="{{ route('eventos.public.show', $evento) }}"
   class="group flex flex-col h-full bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">

    {{-- Secção da Imagem: Altura fixa, esperando uma imagem na proporção 4:3 --}}
    <div class="h-60 overflow-hidden">
        <img class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
             {{-- LINHA CORRIGIDA --}}
             src="{{ $evento->thumbnail_url ? asset('storage/' . $evento->thumbnail_url) : 'https://via.placeholder.com/800x600.png?text=Proticketsports' }}"
             alt="Imagem do evento {{ $evento->nome }}">
    </div>

    {{-- Secção de Informações --}}
    <div class="p-4 flex flex-col flex-grow">
        <div class="flex-grow">
            <h3 class="text-sm font-bold text-gray-800 leading-tight h-15 line-clamp-2 transition-colors group-hover:text-orange-600">
                {{ $evento->nome }}
            </h3>
            <hr class="my-2">

            {{-- Detalhes do Evento --}}
            <div class="space-y-1 text-xs text-gray-500">
                <p class="truncate" title="{{ $evento->local }}">
                    <i class="fa-solid fa-map-pin w-4 mr-1 text-gray-400"></i>
                    {{ $evento->local }}
                </p>
                <p class="truncate">
                    <i class="fa-solid fa-location-dot w-4 mr-1 text-gray-400"></i>
                    @if($evento->cidade)
                        {{ $evento->cidade->nome }} - {{ $evento->cidade->estado->uf }}
                    @else
                        Local a definir
                    @endif
                </p>
                <p class="truncate">
                    <i class="fa-solid fa-calendar-day w-4 mr-1 text-gray-400"></i>
                    {{ $evento->data_evento->format('d/m/Y') }}
                </p>
            </div>

            {{-- Badge de Campeonato --}}
            @if($evento->campeonato)
                <div class="mt-3">
                    <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        <i class="fa-solid fa-trophy mr-1"></i>
                        {{ $evento->campeonato->nome }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Botão de Ação --}}
        <div class="mt-4 pt-4 border-t">
            <div class="w-full text-center flex items-center justify-center space-x-2 bg-orange-500 text-white font-bold py-2 px-4 rounded-md transition-colors group-hover:bg-orange-600">
                <span>Inscreva-se</span>
                <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
            </div>
        </div>
    </div>
</a>