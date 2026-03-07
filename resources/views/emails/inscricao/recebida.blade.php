<x-mail::message>
{{-- Cabeçalho Personalizado (Logotipo / Título) --}}
<div style="text-align: center; margin-bottom: 25px;">
    <h1 style="color: #ea580c; font-size: 24px; font-weight: bold; margin: 0;">ProticketSports</h1>
    <p style="color: #1e3a8a; font-size: 14px; margin-top: 5px; font-weight: 500;">Seu desafio começa aqui.</p>
</div>

# Olá, {{ $inscricao->atleta->user->name }}!

Sua pré-inscrição para o evento **{{ $inscricao->evento->nome }}** foi recebida com sucesso. Estamos aguardando a confirmação do pagamento para garantir sua vaga.

{{-- Aviso Destacado de Produtos --}}
@if($inscricao->produtosOpcionais->isNotEmpty())
<x-mail::panel>
**⚠️ Importante sobre Itens Adicionais:**
O não pagamento imediato desta inscrição **NÃO garante a reserva** dos produtos adicionais. O estoque é limitado e garantido apenas após a confirmação do pagamento.
</x-mail::panel>
@endif

## Resumo do Pedido

<div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #ea580c; margin-bottom: 20px;">
    <p style="margin: 5px 0;"><strong>Código:</strong> <span style="font-family: monospace; color: #4b5563;">{{ $inscricao->codigo_inscricao }}</span></p>
    <p style="margin: 5px 0;"><strong>Categoria:</strong> {{ $inscricao->categoria->nome }}</p>
    @if($inscricao->produtosOpcionais->isNotEmpty())
    <p style="margin: 10px 0 5px 0; font-weight: bold; color: #1e3a8a;">Itens Adicionais:</p>
    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #4b5563;">
        @foreach($inscricao->produtosOpcionais as $prod)
        <li>{{ $prod->nome }} (x{{ $prod->pivot->quantidade }})</li>
        @endforeach
    </ul>
    @endif
    <p style="margin-top: 15px; font-size: 18px; color: #ea580c;"><strong>Total a Pagar: R$ {{ number_format($inscricao->valor_pago, 2, ',', '.') }}</strong></p>
</div>

Para garantir sua vaga e seus itens, clique no botão abaixo e realize o pagamento (Pix ou Boleto):

<x-mail::button :url="route('pagamento.show', $inscricao)" color="primary">
Realizar Pagamento Agora
</x-mail::button>

<p style="font-size: 12px; color: #6b7280; text-align: center; margin-top: 20px;">
    Se você já realizou o pagamento, aguarde. A confirmação é automática e chegará em seu e-mail em instantes.
</p>

Obrigado,<br>
**Equipe {{ config('app.name') }}**
</x-mail::message>