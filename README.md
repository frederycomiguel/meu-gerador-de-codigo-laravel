# 🤖 Laravel AI CRUD Generator

Um assistente de código inteligente para Laravel que gera um CRUD completo, refatora e remove features (Nível 9) usando IA (Google Gemini). Este projeto está estruturado como um **Pacote Laravel (Nível 10)**.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)

---

### 🧐 O que é isso?

Cansado de escrever o mesmo *boilerplate* (código repetitivo) para cada CRUD? Este projeto resolve isso.

Esta é uma suíte de ferramentas que usa o poder da API Google Gemini para interpretar pedidos em linguagem natural e gerar, modificar ou destruir uma feature inteira, economizando minutos (ou horas) de trabalho.

O projeto principal (`meu-gerador-de-codigo`) serve como um "host" ou "demo", e a ferramenta em si vive dentro da pasta `packages/frede/ai-generator` como um pacote Composer local.

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
* ✅ **Nível 7 (Interface Gráfica):** Uma página web amigável em `/gerador` para rodar o comando de geração, carregada de dentro do pacote.
* ✅ **Nível 8 (Refatoração Segura):** Um comando `php artisan feature:modificar` que cria e preenche uma *nova* migration para adicionar colunas e fornece uma "lista de tarefas" (TODO list) para o dev atualizar o Model/Factory.
* ✅ **Nível 9 (Destruição):** Um comando `php artisan feature:remover` que reverte a migration, deleta todos os arquivos da feature e limpa a rota da API.

---

### ⚙️ Instalação e Configuração (Nível 10)

Este projeto já está configurado para carregar a ferramenta como um pacote local. Para rodá-lo:

1.  Clonar o repositório:
    ```bash
    git clone https://[URL_DO_SEU_REPOSITORIO_GIT] meu-gerador
    cd meu-gerador
    ```

2.  Instalar as dependências (isso irá "linkar" o pacote local):
    ```bash
    composer install
    ```
    *(O `composer.json` principal já está configurado para encontrar e carregar o pacote de `packages/frede/ai-generator`).*

3.  Criar seu arquivo de ambiente:
    ```bash
    cp .env.example .env
    ```

4.  Gerar a chave do aplicativo:
    ```bash
    php artisan key:generate
    ```

5.  **Publicar a Configuração do Pacote:**
    Para que o bot encontre sua chave de API, você precisa publicar o arquivo de configuração dele:
    ```bash
    php artisan vendor:publish --tag=config
    ```
    *(Isso copiará o arquivo de config do pacote para `config/ai-generator.php`)*.

6.  **Adicionar sua Chave de API do Gemini:**
    * Vá até o [Google AI Studio](https://aistudio.google.com/).
    * Crie uma nova chave de API.
    * Adicione a chave ao seu arquivo `.env`:

    ```env
    GEMINI_API_KEY="AIza..."
    ```

7.  **Pronto!** O arquivo `config/ai-generator.php` lerá esta chave automaticamente.

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

**1. Rode o comando (ou use a UI):**
```bash
php artisan feature:gerar "Quero um CRUD para Post, com titulo e conteudo (text), que pertence a um User"