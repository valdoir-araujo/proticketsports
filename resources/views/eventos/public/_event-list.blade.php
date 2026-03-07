{{-- Este ficheiro contém APENAS o loop que exibe os cards de evento --}}
@forelse($eventos as $evento)
    <x-event-card :evento="$evento" />
@empty
    {{-- Esta parte fica vazia de propósito para as requisições AJAX. A mensagem de "nenhum evento" já está na página principal. --}}
@endforelse
