<?php

/**
 * Testes da aplicação de cupom na inscrição.
 * Teste de integração completo (com Evento, Inscricao, Cupom) pode ser ampliado com factories/seeders.
 */

it('rota de aplicar cupom exige autenticação ou retorna 404', function () {
    $response = $this->post(route('inscricao.cupom.aplicar', ['inscricao' => 99999]), [
        'codigo_cupom' => 'QUALQUER',
    ]);

    // Guest: redirect (302), 403, 404 ou 419 (CSRF/sessão)
    expect($response->status())->toBeIn([302, 403, 404, 419]);
});
