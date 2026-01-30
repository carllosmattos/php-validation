<?php

namespace App\Providers;

use App\Models\Client;
use App\Observers\ClientObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        // Registra o Observer para logs de auditoria
        Client::observe(ClientObserver::class);

        // Rate limit para API geral
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Muitas requisições. Tente novamente em alguns instantes.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // Rate limit para login (mais restritivo)
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Muitas tentativas de login. Tente novamente em 1 minuto.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // Rate limit para criação de clientes (anti-spam)
        RateLimiter::for('create-client', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Limite de criação excedido. Aguarde um momento.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });
    }
}
