# 🤖 Laravel AI CRUD Generator

Um assistente de código inteligente para Laravel que gera um CRUD completo, refatora e remove features (Nível 9) usando IA (Google Gemini).

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php)

---

### 🧐 O que é isso?

Cansado de escrever o mesmo *boilerplate* (código repetitivo) para cada CRUD? Este projeto resolve isso.

Esta é uma suíte de ferramentas que usa o poder da API Google Gemini para interpretar pedidos em linguagem natural e gerar, modificar ou destruir uma feature inteira, economizando minutos (ou horas) de trabalho.

---

### 🚀 Funcionalidades (Nível 9)

Este bot não gera apenas arquivos vazios. Ele gerencia o ciclo de vida completo da sua feature:

#### Geração (Nível 6)
* ✅ **Model**: Cria o Model e preenche o `$fillable` e os métodos de **relacionamento** (ex: `belongsTo`).
* ✅ **Migration**: Cria a Migration e escreve os campos normais e as **chaves estrangeiras** (ex: `foreignId('user_id')`).
* ✅ **Factory**: Cria a Factory e preenche a `definition()` com dados do *Faker* e **factories de relacionamento** (ex: `User::factory()`).
* ✅ **Service**: Gera uma classe de Serviço (Service Layer) com o Model injetado e os 5 métodos do CRUD preenchidos.
* ✅ **Controller**: Gera o Controller com a Service injetada (Injeção de Dependência) e os 5 métodos chamando o serviço.
* ✅ **Form Requests**: Gera `StoreRequest` e `UpdateRequest` e preenche as `rules()` com validações, incluindo `exists` para chaves estrangeiras.
* ✅ **Rotas de API**: Adiciona automaticamente a rota `Route::apiResource(...)` ao `routes/api.php`.
* ✅ **Teste de Feature (PHPUnit)**: Gera um teste `...Test.php` completo que valida as rotas `index`, `show`, `store` (sucesso e falha) e lida com a criação de **dependências** (como criar um `User` antes de criar um `Post`).

#### Outras Ferramentas
* ✅ **Nível 7 (Interface Gráfica):** Uma página web amigável em `/gerador` para rodar o comando de geração.
* ✅ **Nível 8 (Refatoração Segura):** Um comando `php artisan feature:modificar` que cria e preenche uma *nova* migration para adicionar colunas e fornece uma "lista de tarefas" (TODO list) para o dev atualizar o Model/Factory.
* ✅ **Nível 9 (Destruição):** Um comando `php artisan feature:remover` que reverte a migration, deleta todos os arquivos da feature e limpa a rota da API.

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

Você tem duas formas de usar a ferramenta:

#### 1. Interface Gráfica (Recomendado)

Rode o servidor local (`php artisan serve`) e acesse a UI no seu navegador:
**[http://127.0.0.1:8000/gerador](http://127.0.0.1:8000/gerador)**

A UI é focada na **criação** de *features* (Nível 6).

#### 2. Comandos Artisan (Controle Total)

Para ter acesso a todas as ferramentas (gerar, modificar e remover), use o terminal:

* **Para CRIAR uma feature (Nível 6):**
    ```bash
    php artisan feature:gerar "CRUD para Post, com titulo e conteudo, que pertence a um User"
    ```

* **Para MODIFICAR uma feature (Nível 8):**
    ```bash
    php artisan feature:modificar "Adicione o campo 'status' (string, default 'ativo') ao model 'Post'"
    ```

* **Para REMOVER uma feature (Nível 9):**
    ```bash
    php artisan feature:remover Post
    ```

---

### ✨ Exemplo de Uso (Nível 6)

Vamos gerar um CRUD para "Posts" que pertencem a "Usuários".

**1. Rode o comando:**
```bash
php artisan feature:gerar "Quero um CRUD para Post, com titulo e conteudo (text), que pertence a um User"