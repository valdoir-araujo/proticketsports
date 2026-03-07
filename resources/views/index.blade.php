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
    <table>
        <thead>
            <tr>
                <th>Nome do Evento</th>
                <th>Data</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($eventos as $evento)
                <tr>
                    <td>{{ $evento->nome }}</td>
                    <td>{{ $evento->data_evento }}</td>
                    <td>{{ $evento->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Você ainda não cadastrou nenhum evento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>