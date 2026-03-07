<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MercadoPago\SDK as MercadoPagoSDK;
use Exception;

class TestMpCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mp:test-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão com a API do Mercado Pago usando as credenciais do .env';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Tentando verificar as credenciais do Mercado Pago...');

        try {
            $accessToken = config('services.mercadopago.access_token');

            if (empty($accessToken)) {
                $this->error('ERRO: A variável MP_ACCESS_TOKEN não está definida no seu ficheiro .env.');
                return 1;
            }

            // Inicializa o SDK do Mercado Pago
            MercadoPagoSDK::setAccessToken($accessToken);

            // Tenta fazer uma chamada simples à API, como obter informações do utilizador
            // Esta é uma forma eficaz de validar se o Access Token é válido.
            $response = MercadoPagoSDK::get('/users/me');

            if ($response && isset($response['id'])) {
                $this->info('SUCESSO: Conexão com o Mercado Pago estabelecida com sucesso!');
                $this->line('ID do Utilizador: ' . $response['nickname']);
                return 0;
            }

            $this->error('ERRO: A resposta da API do Mercado Pago foi inválida, embora a conexão tenha sido estabelecida.');
            Log::error('Resposta inválida do Mercado Pago ao testar credenciais: ', (array) $response);
            return 1;

        } catch (Exception $e) {
            $this->error('FALHA: Não foi possível conectar-se à API do Mercado Pago.');
            $this->error('Mensagem de erro: ' . $e->getMessage());
            Log::error('Erro ao testar credenciais do Mercado Pago: ' . $e->getMessage());
            return 1;
        }
    }
}
