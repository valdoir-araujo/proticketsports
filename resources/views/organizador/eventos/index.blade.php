<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-T">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Eventos - Proticketsports</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Meus Eventos</h1>
    {{-- Bloco para exibir a mensagem de sucesso --}}
    @if (session('sucesso'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('sucesso') }}
        </div>
    @endif
    <a href="{{ route('organizador.eventos.create') }}" style="margin-bottom: 20px; display: inline-block;">
        + Adicionar Novo Evento
    </a>
    <table>
        <thead>
            <tr>
                <th>Nome do Evento</th>
                <th>Data</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($eventos as $evento)
                <tr>
                    <td>{{ $evento->nome }}</td>
                    <td>{{ $evento->data_evento }}</td>
                    <td>{{ $evento->status }}</td>
                    <td>
                    {{-- O link para a rota de edição que vamos criar --}}
                    <a href="{{ route('organizador.eventos.show', $evento) }}">Gerenciar</a>  |
                    <a href="{{ route('organizador.eventos.edit', $evento->id) }}">Editar</a> |
                    {{-- Formulário de Exclusão --}}
                    <form method="POST" action="{{ route('organizador.eventos.destroy', $evento->id) }}" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este evento?');">
                        @csrf
                        @method('DELETE')
                        <button type of="submit" style="color: red; border: none; background: none; cursor: pointer; padding: 0; font-size: inherit;">
                            Excluir
                        </button>
                    </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Você ainda não cadastrou nenhum evento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>