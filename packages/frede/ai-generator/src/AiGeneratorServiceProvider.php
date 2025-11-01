<?php

namespace Frede\AiGenerator;

use Illuminate\Support\ServiceProvider;

class AiGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Registra quaisquer serviços da aplicação.
     */
    public function register(): void
    {
        // 1. REGISTRA O SERVICE
        $this->app->singleton(FeatureGenerationService::class, function ($app) {
            return new FeatureGenerationService();
        });

        // 2. REGISTRA O ARQUIVO DE CONFIG
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai-generator.php', 'ai-generator'
        );
    }

    /**
     * Inicia quaisquer serviços da aplicação.
     */
    public function boot(): void
    {
        // --- 1. Registrar os Comandos ---
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Frede\AiGenerator\Commands\GerarFeatureCommand::class,
                \Frede\AiGenerator\Commands\ModificarFeatureCommand::class,
                \Frede\AiGenerator\Commands\RemoverFeatureCommand::class,
            ]);
        }

        // --- 2. Carregar as Rotas ---
        $this->loadRoutesFrom(__DIR__.'/../resources/routes/web.php');

        // --- 3. Carregar as Views ---
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai-generator');
        
        // --- 4. CONFIGURAR PUBLICAÇÃO ---
        $this->publishes([
            // Stubs
            __DIR__.'/../resources/stubs' => base_path('stubs/vendor/ai-generator'),
        ], 'stubs');
        
        $this->publishes([
            // Views (para o usuário poder editar)
            __DIR__.'/../resources/views' => resource_path('views/vendor/ai-generator'),
        ], 'views');
        
        $this->publishes([
            // Config (O MAIS IMPORTANTE)
            __DIR__.'/../config/ai-generator.php' => config_path('ai-generator.php'),
        ], 'config');
    }
}