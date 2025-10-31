<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Exception;

class ModificarFeatureCommand extends Command
{
    protected $signature = 'feature:modificar {descricao}';
    protected $description = 'Modifica uma feature existente (Nível 8), adicionando colunas.';

    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-latest:generateContent?key=';

    public function handle()
    {
        $descricao = $this->argument('descricao');
        $this->info("Analisando pedido de modificação: '{$descricao}'");
        $this->info("Contatando IA para gerar plano de refatoração...");

        try {
            $planoJson = $this->obterPlanoDaIA($descricao);
            $planoJson = str_replace(['```json', '```'], '', $planoJson);
            $plano = json_decode(trim($planoJson), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Falha ao decodificar o JSON da IA. Resposta recebida:");
                $this->line($planoJson);
                return 1;
            }

            // O que vamos fazer?
            if ($plano['intent'] == 'add_column') {
                $this->adicionarColunas($plano);
            } else {
                $this->error("Não entendi a intenção. Por enquanto, só sei 'add_column'.");
                return 1;
            }

        } catch (Exception $e) {
            $this->error("Erro ao chamar a API do Gemini: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    // --- CÉREBRO (PROMPT NÍVEL 8) ---
    private function construirPrompt(string $descricaoUsuario)
    {
        return "
        Você é um arquiteto de software Laravel sênior focado em refatoração.
        Sua única tarefa é analisar um pedido de modificação e retornar **APENAS UM OBJETO JSON** válido, sem nenhum texto ou markdown (```json).
        
        O pedido do usuário é: \"{$descricaoUsuario}\"
        
        O JSON de saída deve seguir este schema:
        {
          \"intent\": \"add_column\",
          \"model\": \"NomeDoModel\",
          \"table\": \"nome_da_tabela\",
          \"fields\": [
            {
              \"name\": \"nome_campo\", 
              \"type\": \"string\", 
              \"default\": \"valor_padrao_opcional\",
              \"nullable\": false,
              \"validation\": \"required|string|max:50\"
            }
          ]
        }
        
        Analise o pedido \"{$descricaoUsuario}\" e gere APENAS o JSON.
        - 'model' deve ser o nome do Model (ex: 'Produto').
        - 'table' deve ser o nome da tabela no plural (ex: 'produtos').
        - 'type' deve ser um tipo de coluna do Laravel (string, integer, decimal, boolean, text, date).
        - 'default' e 'nullable' são opcionais.
        - Gere regras de validação para o campo.
        ";
    }

    private function obterPlanoDaIA(string $descricao)
    {
        $apiKey = Config::get('services.gemini.api_key');
        if (!$apiKey) { throw new Exception("Chave de API do Gemini não configurada."); }
        $url = $this->geminiApiUrl . $apiKey;
        $prompt = $this->construirPrompt($descricao);

        $response = Http::post($url, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [ 'responseMimeType' => 'application/json', 'temperature' => 0.1, ]
        ]);

        if ($response->failed()) { throw new Exception("Erro na API do Gemini: " . $response->body()); }
        return $response->json('candidates.0.content.parts.0.text');
    }
    
    // --- MÃOS (GERADOR NÍVEL 8) ---
    
    protected function adicionarColunas($plano)
    {
        $modelName = $plano['model'];
        $tableName = $plano['table'];
        $fields = $plano['fields'];
        
        // 1. Criar a Migration
        $migrationName = 'add_';
        foreach ($fields as $field) { $migrationName .= $field['name'] . '_'; }
        $migrationName .= "to_{$tableName}_table";
        
        $this->info("Criando migration: {$migrationName}");
        $this->call('make:migration', [
            'name' => $migrationName,
            '--table' => $tableName
        ]);
        
        sleep(1); // Dá tempo para o arquivo ser criado
        
        // 2. Editar a Migration
        $migrationPath = $this->encontrarMigration($migrationName);
        if (!$migrationPath) {
            $this->error("Não foi possível encontrar o arquivo da migration. Abortando.");
            return;
        }
        
        $this->preencherMigration($migrationPath, $fields);
        
        // 3. Gerar a "Lista de Tarefas" (TODO list)
        $this->gerarListaDeTarefas($modelName, $fields);
    }
    
    protected function preencherMigration(string $migrationPath, array $fields)
    {
        $migrationContents = File::get($migrationPath);

        // --- 1. Prepara o código para o método UP ---
        $fieldsStringUp = "";
        foreach ($fields as $field) {
            $line = "            \$table->{$field['type']}('{$field['name']}')";
            if (isset($field['default'])) $line .= "->default('{$field['default']}')";
            if (isset($field['nullable']) && $field['nullable']) $line .= "->nullable()";
            $fieldsStringUp .= $line . ";\n";
        }
        
        // --- 2. Prepara o código para o método DOWN ---
        $fieldsStringDown = "            \$table->dropColumn([\n";
        foreach ($fields as $field) {
            $fieldsStringDown .= "                '{$field['name']}',\n";
        }
        $fieldsStringDown .= "            ]);\n";

        // --- 3. Insere o código no método UP ---
        // O placeholder é o comentário "//" dentro do Schema::table() do método up()
        $placeholderUp = '//'; 
        // Usamos Str::replaceFirst para garantir que estamos editando o método 'up()' primeiro
        $migrationContents = Str::replaceFirst($placeholderUp, $fieldsStringUp, $migrationContents);
        
        // --- 4. Insere o código no método DOWN ---
        // Agora, o *próximo* placeholder "//" será o do método 'down()'
        $placeholderDown = '//'; 
        $migrationContents = Str::replaceFirst($placeholderDown, $fieldsStringDown, $migrationContents);

        // 5. Salva o arquivo de migration corrigido
        File::put($migrationPath, $migrationContents);
        $this->info("Migration [{$migrationPath}] atualizada (Nível 8.2 Corrigido).");
    }
    
    protected function gerarListaDeTarefas(string $modelName, array $fields)
    {
        $this->warn("\n--- AÇÃO MANUAL NECESSÁRIA ---");
        $this->info("A migration foi criada. Agora, adicione estes campos manualmente nos arquivos do seu Model:");

        // 1. Model
        $this->warn("\n👉 1. Em 'app/Models/{$modelName}.php', adicione ao \$fillable:");
        $fillable = "";
        foreach ($fields as $field) $fillable .= "        '{$field['name']}',\n";
        $this->line(rtrim($fillable));
        
        // 2. Factory
        $this->warn("\n👉 2. Em 'database/factories/{$modelName}Factory.php', adicione ao 'definition()':");
        $factory = "";
        foreach ($fields as $field) {
            $faker = $this->getFakerPropertyForField($field['name'], $field['type']);
            $factory .= "            '{$field['name']}' => {$faker},\n";
        }
        $this->line(rtrim($factory));
        
        // 3. Request
        $this->warn("\n👉 3. Em 'app/Http/Requests/Store{$modelName}Request.php', adicione às 'rules()':");
        $rules = "";
        foreach ($fields as $field) {
            $rules .= "            '{$field['name']}' => '{$field['validation']}',\n";
        }
        $this->line(rtrim($rules));
        $this->info("(Lembre-se de adicionar 'sometimes' no Update{$modelName}Request.php se necessário).");
        
        $this->warn("\n--- FIM DA AÇÃO MANUAL ---");
    }

    // --- Helpers (copiados do nosso Nível 6) ---
    protected function encontrarMigration(string $migrationName)
    {
        $migrationFiles = File::glob(database_path("migrations/*_{$migrationName}.php"));
        if (empty($migrationFiles)) {
            return null;
        }
        return $migrationFiles[0];
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