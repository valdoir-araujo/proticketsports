<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro - {{ $evento->nome }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .summary td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #888;
        }
        .summary .value {
            font-size: 18px;
            font-weight: bold;
        }
        .receita { color: #28a745; }
        .despesa { color: #dc3545; }
        .saldo { color: #007bff; }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Financeiro</h1>
        <p><strong>Evento:</strong> {{ $evento->nome }}</p>
        <p><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h3>Resumo Financeiro</h3>
    <table class="summary">
        <tr>
            <td>
                <div class="label">Total de Receitas</div>
                <div class="value receita">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Total de Despesas</div>
                <div class="value despesa">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Saldo Final</div>
                <div class="value saldo">R$ {{ number_format($saldoFinal, 2, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <h3>Histórico de Lançamentos</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lancamentosFinanceiros as $lancamento)
                <tr>
                    <td>{{ $lancamento->data->format('d/m/Y') }}</td>
                    <td>{{ $lancamento->descricao }}</td>
                    <td class="text-right {{ $lancamento->tipo == 'receita' ? 'receita' : 'despesa' }}">
                        {{ $lancamento->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Nenhum lançamento registado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Relatório gerado por Proticketsports
    </div>
</body>
</html>
