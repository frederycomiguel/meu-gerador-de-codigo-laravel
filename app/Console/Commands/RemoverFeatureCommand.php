<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File; // <-- Importar File
use Illuminate\Support\Facades\Artisan; // <-- Importar Artisan
use Illuminate\Support\Str; // <-- Importar Str

class RemoverFeatureCommand extends Command
{
    /**
     * A assinatura do comando.
     * {modelName} -> Define um argumento obrigatório
     */
    protected $signature = 'feature:remover {modelName}';

    /**
     * A descrição do comando.
     */
    protected $description = 'Remove todos os arquivos e a migration de um CRUD gerado (Passo 9)';

    // Propriedades para guardar os nomes
    private $modelName;
    private $controllerName;
    private $serviceName;
    private $factoryName;
    private $storeRequestName;
    private $updateRequestName;
    private $testName;
    private $tableName;
    private $routeName;

    /**
     * Executa o comando.
     */
    public function handle()
    {
        // 1. Pega o nome e normaliza
        $this->modelName = Str::studly($this->argument('modelName')); // ex: "produto" -> "Produto"
        
        // 2. Inferir todos os outros nomes
        $this->inferirNomes();

        // 3. CONFIRMAÇÃO (CRUCIAL!)
        $this->warn("Atenção: Esta ação é irreversível.");
        $confirm = $this->confirm("Tem certeza que deseja DELETAR a tabela '{$this->tableName}' e todos estes arquivos?\n - {$this->modelName}.php\n - {$this->controllerName}.php\n - {$this->serviceName}.php\n - {$this->factoryName}.php\n - {$this->storeRequestName}.php\n - {$this->updateRequestName}.php\n - {$this->testName}.php");
        
        if (!$confirm) {
            $this->info("Remoção cancelada.");
            return Command::SUCCESS;
        }

        $this->info("Iniciando remoção da feature '{$this->modelName}'...");

        // 4. Executar as ações de remoção
        $this->removerMigration();
        $this->removerRota();
        $this->removerArquivos();

        $this->info("Limpeza concluída com sucesso!");
        $this->warn("Recomendado rodar 'php artisan optimize:clear' para limpar o cache de rotas.");

        return Command::SUCCESS;
    }

    /**
     * Preenche todas as variáveis de nome com base no Model
     */
    private function inferirNomes()
    {
        $this->controllerName   = $this->modelName . 'Controller';
        $this->serviceName      = $this->modelName . 'Service';
        $this->factoryName      = $this->modelName . 'Factory';
        $this->storeRequestName = 'Store' . $this->modelName . 'Request';
        $this->updateRequestName = 'Update' . $this->modelName . 'Request';
        $this->testName         = $this->modelName . 'ControllerTest';
        $this->tableName        = Str::snake(Str::plural($this->modelName)); // "Produto" -> "produtos"
        $this->routeName        = $this->tableName;
    }

    /**
     * Encontra a migration, reverte (rollback) e deleta o arquivo.
     */
    private function removerMigration()
    {
        $migrationNamePart = "create_{$this->tableName}_table";
        $migrationFiles = File::glob(database_path("migrations/*_{$migrationNamePart}.php"));

        if (empty($migrationFiles)) {
            $this->warn("Migration '{$migrationNamePart}' não encontrada. Pulando.");
            return;
        }

        $migrationPath = $migrationFiles[0];
        // O Artisan precisa do caminho relativo a partir da raiz do projeto
        $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationPath);

        try {
            $this->info("Revertendo migration: {$relativePath}");
            // Roda o rollback APENAS para esta migration
            Artisan::call('migrate:rollback', [
                '--path' => $relativePath,
                '--force' => true // Força a execução sem perguntar em 'produção'
            ]);

            // Deleta o arquivo da migration
            File::delete($migrationPath);
            $this->info("Arquivo de migration deletado: {$relativePath}");

        } catch (Exception $e) {
            $this->error("Erro ao reverter ou deletar migration: " . $e->getMessage());
        }
    }

    /**
     * Remove a linha da rota do arquivo routes/api.php
     */
    private function removerRota()
    {
        $apiRoutesPath = base_path('routes/api.php');
        $controllerClass = "App\\Http\\Controllers\\{$this->controllerName}";
        
        // Esta é a linha exata que nosso gerador (Nível 6) cria
        $routeString = "Route::apiResource('{$this->routeName}', {$controllerClass}::class);";

        try {
            $contents = File::get($apiRoutesPath);

            if (Str::contains($contents, $routeString)) {
                // Remove a linha e limpa linhas em branco extras
                $contents = str_replace($routeString, '', $contents);
                $contents = preg_replace("/\n\s*\n/", "\n", $contents); // Limpa linhas em branco
                
                File::put($apiRoutesPath, $contents);
                $this->info("Rota '{$this->routeName}' removida de routes/api.php.");
            } else {
                $this->warn("Rota '{$this->routeName}' não encontrada em routes/api.php. Pulando.");
            }
        } catch (Exception $e) {
            $this->error("Erro ao remover rota: " . $e->getMessage());
        }
    }

    /**
     * Deleta todos os 7 arquivos gerados
     */
    private function removerArquivos()
    {
        $filesToDelete = [
            app_path("Models/{$this->modelName}.php"),
            app_path("Http/Controllers/{$this->controllerName}.php"),
            app_path("Services/{$this->serviceName}.php"),
            database_path("factories/{$this->factoryName}.php"),
            app_path("Http/Requests/{$this->storeRequestName}.php"),
            app_path("Http/Requests/{$this->updateRequestName}.php"),
            base_path("tests/Feature/{$this->testName}.php")
        ];

        $this->info("Deletando arquivos da feature...");
        foreach ($filesToDelete as $path) {
            if (File::exists($path)) {
                File::delete($path);
                $this->line(" - Deletado: {$path}");
            } else {
                $this.this->warn(" - Não encontrado, pulando: {$path}");
            }
        }
    }
}