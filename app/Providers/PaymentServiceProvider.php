<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\MercadoPagoService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            // Obtém o gateway padrão do arquivo de configuração (config/services.php)
            // Se não estiver definido, assume 'mercadopago' como fallback seguro
            $gateway = config('services.payment.default_gateway', 'mercadopago');

            if ($gateway === 'mercadopago') {
                return new MercadoPagoService();
            }

            // if ($gateway === 'stripe') {
            //     return new StripeService();
            // }

            // Se chegou aqui, a configuração aponta para um gateway que não existe no código
            throw new \Exception("Gateway de pagamento [{$gateway}] não é suportado ou não foi configurado corretamente.");
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}