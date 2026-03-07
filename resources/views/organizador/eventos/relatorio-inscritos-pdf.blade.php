<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Inscritos - {{ $evento->nome }}</title>
    <style>
        @page { 
            margin: 1.5cm; 
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9px; 
            color: #212529;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 18px; 
            font-weight: bold;
            color: #000;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
            color: #333;
        }
        .header p { 
            margin: 4px 0 0; 
            color: #666; 
            font-size: 10px; 
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        .table th, .table td { 
            padding: 7px; 
            text-align: left; 
            border: 1px solid #ccc; /* Bordas de planilha finas */
            vertical-align: top;
        }
        .table thead th { 
            background-color: #f0f0f0; /* Cinza muito claro, imprime bem */
            font-weight: bold; 
            font-size: 8px; 
            text-transform: uppercase; 
            border-bottom-width: 2px;
            border-bottom-color: #000;
        }
        .table .num { 
            width: 25px; 
            text-align: center; 
        }
        .percurso-row td {
            background-color: #e9ecef;
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            border-top: 2px solid #000; /* Separador forte para novo percurso */
        }
        .categoria-row td {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 10px;
            padding-left: 15px; /* Indentação para subgrupo */
        }
        .signature-col {
            width: 100px; /* Espaço para assinatura */
        }
        .atleta-num-col {
            width: 50px;
        }
        .footer { 
            position: fixed; 
            bottom: -40px; 
            left: 0; 
            right: 0; 
            height: 50px; 
            text-align: center; 
            font-size: 8px; 
            color: #94a3b8;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lista de Conferência de Inscritos</h1>
        <h2>{{ $evento->nome }}</h2>
        <p>
            Data de Emissão: {{ now()->format('d/m/Y H:i') }} | 
            Total de Inscritos Confirmados: <strong>{{ $inscricoesAgrupadas->flatten()->count() }}</strong>
        </p>
    </div>

    <div class="footer">
        <span class="page-number"></span> | Relatório gerado por Proticketsports
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="num">#</th>
                <th class="atleta-num-col">Nº Atleta</th>
                <th>Atleta</th>
                <th>Equipe</th>
                <th>Cidade/UF</th>
                <th class="signature-col">Assinatura</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscricoesAgrupadas as $nomePercurso => $categorias)
                {{-- Linha de Agrupamento por Percurso --}}
                <tr class="percurso-row">
                    <td colspan="6">{{ $nomePercurso }}</td>
                </tr>
                
                @foreach ($categorias as $nomeCategoria => $inscricoes)
                    {{-- Linha de Agrupamento por Categoria --}}
                    <tr class="categoria-row">
                        <td colspan="6">
                            {{ $nomeCategoria }}
                            <span style="font-weight: normal; font-size: 9px;">(Total: {{ count($inscricoes) }})</span>
                        </td>
                    </tr>

                    {{-- Lista de Atletas da Categoria --}}
                    @foreach ($inscricoes->sortBy('atleta.user.name') as $index => $inscricao)
                        <tr>
                            <td class="num">{{ $index + 1 }}</td>
                            <td class="atleta-num-col"></td> {{-- Espaço para o número do atleta --}}
                            <td>{{ $inscricao->atleta->user->name ?? 'N/A' }}</td>
                            <td>{{ $inscricao->equipe->nome ?? 'Individual' }}</td>
                            <td>{{ $inscricao->atleta->cidade ? $inscricao->atleta->cidade->nome . ' / ' . $inscricao->atleta->cidade->estado->uf : 'N/A' }}</td>
                            <td class="signature-col"></td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Nenhum inscrito confirmado para este evento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

