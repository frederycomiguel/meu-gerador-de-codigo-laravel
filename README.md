# 🤖 Laravel AI CRUD Generator

Um assistente de código inteligente para Laravel que gera um CRUD completo (Nível 5) usando IA (Google Gemini) a partir de um único comando.

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php)

---

### 🧐 O que é isso?

Cansado de escrever o mesmo *boilerplate* (código repetitivo) para cada CRUD? Este projeto resolve isso.

Ele é um comando Artisan (`php artisan feature:gerar`) que usa o poder da API Google Gemini para interpretar um pedido em linguagem natural (como *"um CRUD de Clientes com nome e email"*) e gerar **toda** a estrutura de uma feature, economizando minutos (ou horas) de trabalho.

---

### 🚀 Funcionalidades (Nível 5)

Este bot não gera apenas arquivos vazios. Ele gera um código de produção completo e pronto para ser testado:

* ✅ **Model**: Cria o Model e preenche automaticamente o array `$fillable` para proteção contra *Mass Assignment*.
* ✅ **Migration**: Cria a Migration e já escreve os campos do banco de dados (ex: `$table->string('nome');`).
* ✅ **Factory**: Cria a Factory e preenche o método `definition()` com os dados do *Faker* (ex: `'nome' => $this->faker->name()`).
* ✅ **Service**: Gera uma classe de Serviço (Service Layer) com o Model injetado e os 5 métodos do CRUD (`listarTodos`, `criarNovo`, etc.) preenchidos com a lógica de banco de dados.
* ✅ **Controller**: Gera o Controller com a Service injetada via construtor (Injeção de Dependência) e os 5 métodos (`index`, `store`, `show`, etc.) chamando os métodos do serviço.
* ✅ **Form Requests**: Gera dois Form Requests (`StoreRequest` e `UpdateRequest`) e preenche o método `rules()` com as regras de validação extraídas do seu pedido (ex: `'email' => 'required|email|unique:clientes'`).
* ✅ **Rotas de API**: Adiciona automaticamente a rota `Route::apiResource(...)` ao final do seu arquivo `routes/api.php`.
* ✅ **Teste de Feature (PHPUnit)**: Gera um arquivo de teste (`...Test.php`) completo que testa as rotas `index`, `store` (sucesso e falha de validação) e `show`.

---

### 🛠️ Tecnologias Utilizadas

* **Laravel 10.x**
* **PHP 8.1+**
* **Google Gemini API** (o "cérebro" por trás da geração)
* **Laravel Stubs** (os "moldes" que nosso bot preenche)

---

### ⚙️ Instalação e Configuração

Para rodar este projeto, você precisa:

1.  Clonar o repositório:
    ```bash
    git clone https://[URL_DO_SEU_REPOSITORIO_GIT] meu-gerador
    cd meu-gerador
    ```

2.  Instalar as dependências do PHP:
    ```bash
    composer install
    ```

3.  Criar seu arquivo de ambiente:
    ```bash
    cp .env.example .env
    ```

4.  Gerar a chave do aplicativo:
    ```bash
    php artisan key:generate
    ```

5.  **Obter sua Chave de API do Gemini:**
    * Vá até o [Google AI Studio](https://aistudio.google.com/).
    * Crie uma nova chave de API.
    * Adicione a chave ao seu arquivo `.env`:

    ```env
    GEMINI_API_KEY="AIza..."
    ```

6.  **Configurar os Serviços do Laravel:**
    * Abra o arquivo `config/services.php`.
    * Adicione a configuração do Gemini para que o Laravel possa ler a chave do `.env`:

    ```php
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
    ```

7.  **Pronto!** Seu bot está configurado.

---

### 🎮 Como Usar

É simples. Apenas rode o comando Artisan `feature:gerar` e descreva o CRUD que você deseja construir em linguagem natural.

```bash
php artisan feature:gerar "SUA DESCRIÇÃO AQUI"
```
### ✨ Exemplo de Uso

Vamos gerar um CRUD completo para "Produtos".

1. Rode o comando:

```bash
php artisan feature:gerar "Preciso de um CRUD completo para Produto, com nome (string), preco (decimal) e estoque (integer)"
```


