<?php

namespace Frede\AiGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Exception;
use Frede\AiGenerator\FeatureGenerationService;

class GerarFeatureCommand extends Command
{
    protected $signature = 'feature:gerar {descricao}';
    protected $description = 'Gera uma feature completa com relacionamentos (Nível 6).';

    // Use o modelo que funcionou para você
    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-latest:generateContent?key=';

    private $modelName = '';
    private $serviceName = '';
    private $controllerName = '';

    public function handle()
    {
        $descricao = $this->argument('descricao');

        $this->info("Iniciando a geração da feature...");
        $this->line("Descrição recebida: " . $descricao);
        $this->info("Contatando IA para gerar o plano de arquitetura...");

        try {
            $planoJson = $this->obterPlanoDaIA($descricao);
            $planoJson = str_replace(['```json', '```'], '', $planoJson);
            $plano = json_decode(trim($planoJson), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Falha ao decodificar o JSON da IA. Resposta recebida:");
                $this->line($planoJson);
                return 1;
            }
        } catch (Exception $e) {
            $this->error("Erro ao chamar a API do Gemini: " . $e->getMessage());
            return 1;
        }

        $this->info("Plano recebido. Iniciando geração dos arquivos...");

        if (isset($plano['model'])) {
            $this->gerarModelEMigration($plano['model']); // Passamos toda a config do model
        }
        if (isset($plano['requests']) && isset($plano['model']['fields'])) {
            $this->gerarRequests($plano['requests'], $plano['model']);
        }
        if (isset($plano['service'])) {
            $this->gerarService($plano['service'], $plano['model']);
        }
        if (isset($plano['controller'])) {
            $this->gerarController($plano['controller'], $plano['service'], $plano['requests'], $plano['model']);
        }
        if (isset($plano['routes'])) {
            $this->gerarRota($plano['routes']);
        }
        if (isset($plano['routes']) && isset($plano['model'])) {
            $this->gerarTesteFeature($plano);
        }

        $this->info("Feature gerada com sucesso! (Nível 6)");
        return 0;
    }

    // --- CÉREBRO (PROMPT) ATUALIZADO (PASSO 6) ---
    private function construirPrompt(string $descricaoUsuario)
    {
        // ATUALIZAÇÃO NÍVEL 6: Adicionamos o array "relationships" ao schema.
        return "
        Você é um arquiteto de software Laravel sênior.
        Sua única tarefa é analisar o pedido do usuário e retornar **APENAS UM OBJETO JSON** válido, sem nenhum texto ou markdown (```json).
        
        O pedido do usuário é: \"{$descricaoUsuario}\"
        
        O objeto JSON de saída deve seguir estritamente este schema:
        {
          \"model\": { 
            \"name\": \"NomeDoModelSingular\", 
            \"fields\": [ 
              {\"name\": \"nome_campo\", \"type\": \"string\", \"validation\": \"required|string|max:255\"}
            ],
            \"relationships\": [
              {\"type\": \"belongsTo\", \"model\": \"ModelRelacionado\"}
            ]
          },
          \"controller\": { 
            \"name\": \"NomeDoModelSingularController\", 
            \"actions\": [\"index\", \"store\", \"show\", \"update\", \"destroy\"] 
          },
          \"service\": { 
            \"name\": \"NomeDoModelSingularService\", 
            \"methods\": [\"listarTodos\", \"criarNovo\", \"buscarPorId\", \"atualizar\", \"deletar\"]
          },
          \"requests\": [
             { \"name\": \"StoreNomeDoModelRequest\" },
             { \"name\": \"UpdateNomeDoModelRequest\" }
          ],
          \"routes\": {
            \"type\": \"apiResource\", 
            \"name\": \"nome-do-recurso-plural\" 
          }
        }
        
        Analise o pedido \"{$descricaoUsuario}\" e gere APENAS o JSON.
        - Os nomes do Model, Controller, Service e Requests devem ser em Singular e CamelCase.
        - O nome da rota deve ser em plural e kebab-case.
        - Gere regras de validação inteligentes para os campos.
        - Se o usuário mencionar 'pertence a um...', gere um relacionamento 'belongsTo'.
        - O 'model' no relacionamento deve ser o nome do Model (ex: 'User', 'Categoria').
        - Se houver um relacionamento 'belongsTo', adicione o campo '_id' (ex: 'user_id') à lista de 'fields' com o tipo 'integer' e a validação 'required|exists:tabela,id'.
        ";
    }

    // ... (obterPlanoDaIA permanece igual) ...
    private function obterPlanoDaIA(string $descricao)
    {
        $apiKey = Config::get('services.gemini.api_key');
        if (!$apiKey) { throw new Exception("Chave de API do Gemini não configurada em config/services.php"); }
        $url = $this->geminiApiUrl . $apiKey;
        $prompt = $this->construirPrompt($descricao);

        $response = Http::post($url, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [ 'responseMimeType' => 'application/json', 'temperature' => 0.2, ]
        ]);

        if ($response->failed()) { throw new Exception("Erro na API do Gemini: " . $response->body()); }
        return $response->json('candidates.0.content.parts.0.text');
    }

    // --- MÃOS (GERADORES) ATUALIZADOS (PASSO 6) ---

    protected function gerarModelEMigration($modelConfig) // $config agora é $modelConfig
    {
        $this->modelName = $modelConfig['name'];
        $this->info("Gerando Model [{$this->modelName}], Migration e Factory...");

        try {
            $this->call('make:model', [
                'name' => $this->modelName,
                '-m' => true,
                '-f' => true,
            ]);
        } catch (Exception $e) {
            $this->error("Erro ao rodar make:model: " . $e->getMessage());
            $this->warn("Verifique se o model '{$this->modelName}.php' já existe em app/Models/");
            return;
        }

        sleep(1); 
        
        $fieldNames = collect($modelConfig['fields'])->pluck('name')->toArray();
        $relationships = $modelConfig['relationships'] ?? []; // Pega os relacionamentos

        // 2. Preenche a MIGRATION
        $this->preencherMigration($modelConfig); // Passa a config inteira
        
        // 3. Preenche o MODEL (com $fillable e métodos de relacionamento)
        $this->preencherModel($fieldNames, $relationships);
        
        // 4. Preenche a FACTORY (com definition() e foreign keys)
        $this->preencherFactory($modelConfig['fields'], $relationships);
    }
    
    protected function preencherMigration($modelConfig)
    {
        $tableName = Str::snake(Str::plural($this->modelName));
        $migrationNamePart = "create_{$tableName}_table";
        $migrationFiles = File::glob(database_path("migrations/*_{$migrationNamePart}.php"));
        
        if (empty($migrationFiles)) {
            $this->warn("Não foi possível encontrar a migration para '{$migrationNamePart}'. Pulando preenchimento.");
            return;
        }
        $migrationPath = $migrationFiles[0];
        $migrationContents = File::get($migrationPath);

        $fieldsString = "";
        
        // Adiciona campos normais
        foreach ($modelConfig['fields'] as $field) {
            // Não adiciona o '_id' manualmente, pois o 'foreignId' vai cuidar disso
            if (Str::endsWith($field['name'], '_id')) continue; 
            
            $type = $field['type'];
            $name = $field['name'];
            $method = 'string';
            if (in_array($type, ['integer', 'int'])) $method = 'integer';
            if ($type == 'decimal') $method = 'decimal';
            if ($type == 'boolean') $method = 'boolean';
            if ($type == 'text') $method = 'text';
            if ($type == 'date') $method = 'date';
            $fieldsString .= "            \$table->{$method}('{$name}');\n";
        }
        
        // ATUALIZAÇÃO NÍVEL 6: Adiciona Foreign Keys
        foreach ($modelConfig['relationships'] ?? [] as $rel) {
            if ($rel['type'] == 'belongsTo') {
                $relatedModel = $rel['model'];
                $foreignKey = Str::snake($relatedModel) . '_id';
                // ex: $table->foreignId('user_id')->constrained();
                // (constrained() automaticamente acha a tabela 'users' e a coluna 'id')
                $fieldsString .= "            \$table->foreignId('{$foreignKey}')->constrained();\n";
            }
        }

        $placeholder = '$table->timestamps();';
        $replacement = $fieldsString . "            " . $placeholder; 
        
        if (str_contains($migrationContents, $placeholder)) {
             $migrationContents = Str::replaceFirst($placeholder, $replacement, $migrationContents);
             File::put($migrationPath, $migrationContents);
             $this->info("Migration [{$migrationPath}] atualizada com campos e relacionamentos.");
        } else {
             $this->warn("Não foi possível encontrar o placeholder '\$table->timestamps();' na migration. Pulando preenchimento.");
        }
    }
    
    // ATUALIZADO (PASSO 6)
    protected function preencherModel(array $fieldNames, array $relationships)
    {
        $modelPath = app_path("Models/{$this->modelName}.php");
        if (!File::exists($modelPath)) {
            $this->warn("Arquivo do Model [{$modelPath}] não encontrado. Pulando preenchimento.");
            return;
        }
        
        $modelContents = File::get($modelPath);
        
        // Cria a string do array $fillable
        $fillableString = "    protected \$fillable = [\n";
        foreach ($fieldNames as $field) {
            $fillableString .= "        '{$field}',\n";
        }
        $fillableString .= "    ];";
        
        // ATUALIZAÇÃO NÍVEL 6: Adiciona métodos de relacionamento
        $relationshipMethods = "";
        foreach ($relationships as $rel) {
            if ($rel['type'] == 'belongsTo') {
                $relatedModel = $rel['model'];
                $methodName = Str::camel($relatedModel);
                // Usamos o namespace completo para não precisar injetar 'use' statements
                $relatedClass = "\\App\\Models\\{$relatedModel}"; 
                
                $relationshipMethods .= "\n    /**\n     * Get the {$methodName} that owns the {$this->modelName}.\n     */\n";
                $relationshipMethods .= "    public function {$methodName}()\n";
                $relationshipMethods .= "    {\n";
                $relationshipMethods .= "        return \$this->belongsTo({$relatedClass}::class);\n";
                $relationshipMethods .= "    }\n";
            }
            // (Futuro: adicionar lógica para 'hasMany')
        }

        // Encontra o placeholder para inserir o $fillable
        $placeholder = 'use HasFactory;';
        // Insere o $fillable E os novos métodos
        $replacement = $placeholder . "\n\n" . $fillableString . "\n" . $relationshipMethods;
        
        if (str_contains($modelContents, $placeholder)) {
             $modelContents = Str::replaceFirst($placeholder, $replacement, $modelContents);
             File::put($modelPath, $modelContents);
             $this->info("Model [{$modelPath}] atualizado com \$fillable e relacionamentos.");
        } else {
             $this->warn("Não foi possível encontrar 'use HasFactory;' no Model. Pulando preenchimento.");
        }
    }
    
    // ATUALIZADO (PASSO 6)
    protected function preencherFactory(array $fields, array $relationships)
    {
        $factoryPath = database_path("factories/{$this->modelName}Factory.php");
        if (!File::exists($factoryPath)) {
            $this->warn("Arquivo da Factory [{$factoryPath}] não encontrado. Pulando preenchimento.");
            return;
        }
        
        $factoryContents = File::get($factoryPath);
        
        $definitionString = "";
        
        // Adiciona campos normais
        foreach ($fields as $field) {
             // Não adiciona o '_id', pois o relacionamento cuidará disso
             if (Str::endsWith($field['name'], '_id')) continue;
             
             $fakerProperty = $this->getFakerPropertyForField($field['name'], $field['type']);
             $definitionString .= "            '{$field['name']}' => {$fakerProperty},\n";
        }
        
        // ATUALIZAÇÃO NÍVEL 6: Adiciona foreign keys da factory
        foreach ($relationships as $rel) {
            if ($rel['type'] == 'belongsTo') {
                $relatedModel = $rel['model'];
                $foreignKey = Str::snake($relatedModel) . '_id';
                $relatedClass = "\\App\\Models\\{$relatedModel}";
                
                // ex: 'user_id' => \App\Models\User::factory()
                $definitionString .= "            '{$foreignKey}' => {$relatedClass}::factory(),\n";
            }
        }

        // Encontra o placeholder
        $placeholder = 'return [';
        $replacement = $placeholder . "\n" . $definitionString . "        ";
        
        if (str_contains($factoryContents, $placeholder)) {
             $factoryContents = Str::replaceFirst($placeholder, $replacement, $factoryContents);
             File::put($factoryPath, $factoryContents);
             $this->info("Factory [{$factoryPath}] atualizada com definition() e relacionamentos.");
        } else {
             $this->warn("Não foi possível encontrar 'return [' na Factory. Pulando preenchimento.");
        }
    }
    
    // ... (gerarRequests permanece igual) ...
    protected function gerarRequests($configs, $modelConfig)
    {
        $this->info("Gerando Form Requests...");
        $stubPath = base_path('stubs/custom.request.stub');
        if (!File::exists($stubPath)) { $this->error("Stub de request não encontrado."); return; }
        
        $rulesString = "";
        foreach ($modelConfig['fields'] as $field) {
            $rulesString .= "            '{$field['name']}' => '{$field['validation']}',\n";
        }

        foreach ($configs as $config) {
            $nomeClasse = $config['name'];
            
            try { $this->call('make:request', ['name' => $nomeClasse]); } 
            catch (Exception $e) { $this->warn("make:request falhou (arquivo '{$nomeClasse}' provavelmente já existe). Sobrescrevendo..."); }
            
            $targetPath = app_path("Http/Requests/{$nomeClasse}.php");
            $stub = File::get($stubPath);
            $stub = str_replace('{{Namespace}}', 'App\Http\Requests', $stub);
            $stub = str_replace('{{ClassName}}', $nomeClasse, $stub);
            $stub = str_replace('{{Rules}}', $rulesString, $stub);
            if (Str::startsWith($nomeClasse, 'Update')) {
                 $stub = Str::replaceFirst("'required|", "'sometimes|", $stub);
            }
            File::put($targetPath, $stub);
            $this->info("Request [{$targetPath}] criado e preenchido.");
        }
    }

    // ... (gerarService permanece igual) ...
    protected function gerarService($config, $modelConfig)
    {
        $this->serviceName = $config['name'];
        $modelName = $modelConfig['name'];
        $modelVarName = Str::camel($modelName);
        File::ensureDirectoryExists(app_path('Services'));
        $stubPath = base_path('stubs/custom.service.stub');
        if (!File::exists($stubPath)) { $this->error("Stub de service não encontrado."); return; }
        $stub = File::get($stubPath);
        $stub = str_replace('{{Namespace}}', 'App\Services', $stub);
        $stub = str_replace('{{ClassName}}', $this->serviceName, $stub);
        $stub = str_replace('{{UseStatements}}', "use App\\Models\\{$modelName};", $stub);
        $stub = str_replace('{{ModelName}}', $modelName, $stub);
        $stub = str_replace('{{ModelVarName}}', $modelVarName, $stub);
        
        $metodos = "";
        $metodos .= "    public function listarTodos()\n    {\n        return \$this->{$modelVarName}->all();\n    }\n\n";
        $metodos .= "    public function criarNovo(array \$data)\n    {\n        return \$this->{$modelVarName}->create(\$data);\n    }\n\n";
        $metodos .= "    public function buscarPorId(\$id)\n    {\n        return \$this->{$modelVarName}->findOrFail(\$id);\n    }\n\n";
        $metodos .= "    public function atualizar(\$id, array \$data)\n    {\n        \$model = \$this->buscarPorId(\$id);\n        \$model->update(\$data);\n        return \$model;\n    }\n\n";
        $metodos .= "    public function deletar(\$id)\n    {\n        \$model = \$this->buscarPorId(\$id);\n        return \$model->delete();\n    }\n\n";
        $stub = str_replace('{{Methods}}', $metodos, $stub);
        $targetPath = app_path("Services/{$this->serviceName}.php");
        File::put($targetPath, $stub);
        $this->info("Service [{$targetPath}] criado.");
    }

    // ... (gerarController permanece igual) ...
    protected function gerarController($config, $serviceConfig, $requestConfigs, $modelConfig)
    {
        $this->controllerName = $config['name'];
        $serviceName = $serviceConfig['name'];
        $serviceVarName = Str::camel($serviceName);
        $modelVarName = Str::camel($modelConfig['name']);
        $storeRequest = $requestConfigs[0]['name'];
        $updateRequest = $requestConfigs[1]['name'];
        $stubPath = base_path('stubs/custom.controller.stub');
        if (!File::exists($stubPath)) { $this->error("Stub de controller não encontrado."); return; }
        $stub = File::get($stubPath);
        $stub = str_replace('{{Namespace}}', 'App\Http\Controllers', $stub);
        $stub = str_replace('{{ClassName}}', $this->controllerName, $stub);
        $use = "use App\\Services\\{$serviceName};\n";
        $use .= "use App\\Http\\Requests\\{$storeRequest};\n";
        $use .= "use App\\Http\\Requests\\{$updateRequest};\n";
        $use .= "use App\\Models\\{$this->modelName};";
        $stub = str_replace('{{UseStatements}}', $use, $stub);
        $stub = str_replace('{{ServiceName}}', $serviceName, $stub);
        $stub = str_replace('{{ServiceVarName}}', $serviceVarName, $stub);
        
        $metodos = "";
        $metodos .= "    public function index()\n    {\n        return \$this->{$serviceVarName}->listarTodos();\n    }\n\n";
        $metodos .= "    public function store({$storeRequest} \$request)\n    {\n        \$data = \$request->validated();\n        \$model = \$this->{$serviceVarName}->criarNovo(\$data);\n        return response()->json(\$model, 201);\n    }\n\n";
        $metodos .= "    public function show({$this->modelName} \${$modelVarName})\n    {\n        return \$this->{$serviceVarName}->buscarPorId(\${$modelVarName}->id);\n    }\n\n";
        $metodos .= "    public function update({$updateRequest} \$request, {$this->modelName} \${$modelVarName})\n    {\n        \$data = \$request->validated();\n        \$model = \$this->{$serviceVarName}->atualizar(\${$modelVarName}->id, \$data);\n        return response()->json(\$model);\n    }\n\n";
        $metodos .= "    public function destroy({$this->modelName} \${$modelVarName})\n    {\n        \$this->{$serviceVarName}->deletar(\${$modelVarName}->id);\n        return response()->json(null, 204);\n    }\n\n";
        $stub = str_replace('{{Methods}}', $metodos, $stub);
        $targetPath = app_path("Http/Controllers/{$this->controllerName}.php");
        File::put($targetPath, $stub);
        $this->info("Controller [{$targetPath}] criado.");
    }
    
    // ... (gerarRota permanece igual) ...
    protected function gerarRota($config)
    {
        $routeName = $config['name'];
        $controllerClass = "App\\Http\\Controllers\\{$this->controllerName}";
        $routeString = "\nRoute::apiResource('{$routeName}', {$controllerClass}::class);";
        $apiRoutesPath = base_path('routes/api.php');
        $apiRoutesContents = File::get($apiRoutesPath);
        if (Str::contains($apiRoutesContents, $routeString)) {
            $this->warn("Rota [{$routeName}] já existe em routes/api.php. Pulando.");
            return;
        }
        try {
            File::append($apiRoutesPath, $routeString);
            $this->info("Rota [{$routeName}] adicionada em routes/api.php.");
        } catch (Exception $e) {
            $this->error("Não foi possível adicionar a rota em routes/api.php: " . $e->getMessage());
        }
    }
    
    // ... (gerarTesteFeature permanece igual) ...
   // --- MÉTODO ATUALIZADO (PASSO 6.1) ---
    protected function gerarTesteFeature($plano)
    {
        $modelName = $plano['model']['name'];
        $testClassName = $modelName . 'ControllerTest';
        $apiRouteName = $plano['routes']['name'];
        $tableName = Str::snake(Str::plural($modelName));
        $this->info("Gerando Teste de Feature [{$testClassName}]...");

        try {
            $this->call('make:test', [ 'name' => $testClassName, ]);
        } catch (Exception $e) {
            $this->error("Erro ao rodar make:test: " . $e->getMessage());
            $this->warn("O teste '{$testClassName}.php' provavelmente já existe. Sobrescrevendo...");
        }
        
        $targetPath = base_path("tests/Feature/{$testClassName}.php");
        
        if (!File::exists($targetPath)) {
            sleep(1);
            if (!File::exists($targetPath)) {
                $this->error("Não foi possível encontrar o arquivo de teste em: {$targetPath}");
                return;
            }
        }

        $stubPath = base_path('stubs/custom.test.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub de teste não encontrado.");
            return;
        }
        $stub = File::get($stubPath);
        
        // --- Lógica de Geração de Dados (Nível 6.1) ---
        $postData = "";           // O conteúdo para o array $data
        $assertData = "[\n";       // O conteúdo para o assertJsonFragment
        $validationColumn = '';
        $dependentFactories = ''; // Código para criar models (ex: User)
        $relatedModelsUse = '';   // 'use' statements para os models
        $dataForAssert = [];      // Array PHP para assert

        // 1. Lida com Relacionamentos (Cria Factories)
        foreach ($plano['model']['relationships'] ?? [] as $rel) {
            if ($rel['type'] == 'belongsTo') {
                $relatedModel = $rel['model']; // "User"
                $varName = Str::camel($relatedModel); // "user"
                $keyName = Str::snake($relatedModel) . '_id'; // "user_id"
                $className = "\\App\\Models\\{$relatedModel}";

                $relatedModelsUse .= "use {$className};\n";
                $dependentFactories .= "        \${$varName} = {$className}::factory()->create();\n";
                
                $postData .= "            '{$keyName}' => \${$varName}->id,\n";
                // Não adicionamos ao assertData, pois o ID é dinâmico
            }
        }
        
        // 2. Lida com Campos Normais
        foreach ($plano['model']['fields'] as $field) {
            $fieldName = $field['name'];
            if (Str::endsWith($fieldName, '_id')) continue; // Já cuidamos disso acima

            if (empty($validationColumn) && str_contains($field['validation'], 'required')) {
                $validationColumn = $fieldName;
            }
            
            $fakeValue = $this->getFakerValueForFieldTest($field);
            $postData .= "            '{$fieldName}' => '{$fakeValue}',\n";
            $assertData .= "            '{$fieldName}' => '{$fakeValue}',\n"; // Adiciona ao assert
        }
        $postData = rtrim(trim($postData), ',');
        $assertData .= "        ]";
        
        if (empty($validationColumn) && !empty($plano['model']['fields'])) {
            $validationColumn = $plano['model']['fields'][0]['name'];
        }

        // 3. Substitui os placeholders
        $stub = str_replace('{{Namespace}}', 'Tests\Feature', $stub);
        $stub = str_replace('{{ClassName}}', $testClassName, $stub);
        $stub = str_replace('{{ModelNamespace}}', 'App\\Models\\' . $modelName, $stub);
        $stub = str_replace('{{ModelName}}', $modelName, $stub);
        $stub = str_replace('{{ApiRouteName}}', $apiRouteName, $stub);
        $stub = str_replace('{{DatabaseTableName}}', $tableName, $stub);
        
        // Placeholders Nível 6.1
        $stub = str_replace('{{RelatedModelsUse}}', $relatedModelsUse, $stub);
        $stub = str_replace('{{DependentFactories}}', $dependentFactories, $stub);
        $stub = str_replace('{{PostData}}', $postData, $stub);
        $stub = str_replace('{{AssertData}}', $assertData, $stub);
        $stub = str_replace('{{ValidationColumn}}', $validationColumn, $stub);

        File::put($targetPath, $stub);
        $this->info("Teste [{$targetPath}] criado e preenchido (Nível 6.1).");
    }

    // ... (os dois helpers de Faker permanecem iguais) ...
    private function getFakerValueForFieldTest($field)
    {
        $name = $field['name'];
        if (str_contains($name, 'email')) return 'teste@email.com';
        if (str_contains($name, 'nome') || str_contains($name, 'name')) return 'Nome de Teste';
        if (str_contains($name, 'phone') || str_contains($name, 'telefone')) return '123456789';
        $type = $field['type'];
        if (in_array($type, ['integer', 'int'])) return 10;
        if ($type == 'boolean') return 'true';
        if ($type == 'decimal') return '123.45';
        return 'Valor de Teste';
    }
    
    private function getFakerPropertyForField(string $name, string $type)
    {
        if (str_contains($name, 'email')) return '$this->faker->unique()->safeEmail()';
        if (str_contains($name, 'nome') || str_contains($name, 'name')) return '$this->faker->name()';
        if (str_contains($name, 'phone') || str_contains($name, 'telefone')) return '$this->faker->phoneNumber()';
        if (str_contains($name, 'address') || str_contains($name, 'endereco')) return '$this->faker->address()';
        if (str_contains($name, 'city') || str_contains($name, 'cidade')) return '$this->faker->city()';
        if (str_contains($name, 'password') || str_contains($name, 'senha')) return 'Hash::make("password")';
        if (in_array($type, ['integer', 'int'])) return '$this->faker->numberBetween(1, 1000)';
        if ($type == 'boolean') return '$this->faker->boolean()';
        if ($type == 'decimal') return '$this->faker->randomFloat(2, 10, 1000)';
        if ($type == 'text') return '$this->faker->paragraph()';
        return '$this->faker->word()';
    }
}