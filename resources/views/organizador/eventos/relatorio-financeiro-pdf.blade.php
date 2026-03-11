<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro - {{ $evento->nome }}</title>
    <style>
        @page {
            margin: 40px 40px 60px 40px;
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; }
        .page-header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .page-header .logo-wrapper {
            width: 60px;
            padding-right: 10px;
        }
        .page-header .logo-wrapper img {
            max-width: 60px;
            height: auto;
        }
        .page-header .main {
            flex: 1;
        }
        .page-header .brand-line { font-size: 13px; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; margin: 0 0 2px 0; }
        .page-header .tagline { font-size: 9px; color: #64748b; margin: 0 0 6px 0; }
        .page-header .report-title { font-size: 12px; font-weight: 700; margin: 0 0 2px 0; }
        .page-header .event-name { font-size: 11px; margin: 0 0 2px 0; }
        .page-header .meta { font-size: 9px; color: #6b7280; margin: 0; }

        .org-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            font-size: 10px;
        }
        .org-grid th, .org-grid td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
        }
        .org-grid th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #475569;
        }

        .section-title { font-size: 11px; font-weight: 700; color: #334155; margin: 0 0 6px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .table th, .table td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #475569;
        }
        .table .text-right { text-align: right; }
        .table tbody tr:nth-child(even) { background-color: #f9fafb; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 10px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #475569;
        }
        .receita { color: #15803d; }
        .despesa { color: #b91c1c; }
        .saldo { color: #1d4ed8; font-weight: 700; }

        .footer {
            position: fixed;
            bottom: -40px;
            left: 40px;
            right: 40px;
            font-size: 9px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
        .footer .page-number:after {
            content: "Página " counter(page) " de " counter(pages);
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="logo-wrapper">
            <img src="{{ public_path('favicon.svg') }}" alt="{{ config('app.name') }}">
        </div>
        <div class="main">
            <p class="brand-line">{{ strtoupper(config('app.name')) }}</p>
            <p class="tagline">{{ config('app.tagline') }}</p>
            <p class="report-title">Relatório Financeiro do Evento</p>
            <p class="event-name">{{ $evento->nome }}</p>
            <p class="meta">{{ $periodo ?? 'Período: Geral' }} · Emitido em {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <main>
    <p class="section-title">Dados do Organizador</p>
    <table class="org-grid">
        <tr>
            <th>Organização</th>
            <td>{{ $organizacao->nome_fantasia ?? '—' }}</td>
            <th>Documento</th>
            <td>{{ $organizacao->documento ?? '—' }}</td>
        </tr>
        <tr>
            <th>Telefone</th>
            <td>{{ $organizacao->telefone ?? '—' }}</td>
            <th>Cidade / UF</th>
            <td>
                @if(optional($organizacao->cidade)->nome)
                    {{ $organizacao->cidade->nome }}@if(optional($organizacao->cidade->estado)->uf)/{{ $organizacao->cidade->estado->uf }}@endif
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <th>Endereço</th>
            <td colspan="3">
                @php
                    $partes = [];
                    if (!empty($organizacao->endereco)) $partes[] = $organizacao->endereco;
                    if (!empty($organizacao->numero)) $partes[] = 'Nº ' . $organizacao->numero;
                    if (!empty($organizacao->bairro)) $partes[] = $organizacao->bairro;
                    if (!empty($organizacao->cep)) $partes[] = 'CEP ' . $organizacao->cep;
                @endphp
                {{ $partes ? implode(' - ', $partes) : '—' }}
            </td>
        </tr>
    </table>

    <p class="section-title">Lançamentos Financeiros</p>
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lancamentosFinanceiros as $lancamento)
                <tr>
                    <td>{{ $lancamento->data->format('d/m/Y') }}</td>
                    <td>{{ $lancamento->categoria ?? '—' }}</td>
                    <td>{{ $lancamento->descricao }}</td>
                    <td class="text-right {{ $lancamento->tipo == 'receita' ? 'receita' : 'despesa' }}">
                        {{ $lancamento->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhum lançamento registado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="section-title" style="margin-top: 14px;">Resumo Financeiro</p>
    <table class="summary-table">
        <tr>
            <th>Total de Receitas</th>
            <td class="receita">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total de Despesas</th>
            <td class="despesa">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Saldo Final</th>
            <td class="saldo">R$ {{ number_format($saldoFinal, 2, ',', '.') }}</td>
        </tr>
    </table>
    </main>

    <div class="footer">
        <div>
            @php
                $cidadeHeader = optional($organizacao->cidade)->nome;
                $ufHeader = optional(optional($organizacao->cidade)->estado)->uf;
            @endphp
            {{ $cidadeHeader ?? '' }}@if($cidadeHeader && $ufHeader)/{{ $ufHeader }}@elseif($ufHeader){{ $ufHeader }}@endif
            {{ $cidadeHeader || $ufHeader ? ' - ' : '' }}{{ now()->format('d/m/Y') }}
        </div>
        <div class="page-number"></div>
    </div>
</body>
</html>
