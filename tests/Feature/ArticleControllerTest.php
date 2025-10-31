<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Article;
use PHPUnit\Framework\Attributes\Test;
// PASSO 6.1: Importa os models relacionados
use \App\Models\User;


class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_listagem_de_recursos_funciona()
    {
        Article::factory(3)->create();
        $response = $this->getJson('/api/articles');
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    #[Test]
    public function a_criacao_de_recurso_funciona()
    {
        // PASSO 6.1: Cria as dependências primeiro (ex: o User)
                $user = \App\Models\User::factory()->create();


        $data = [
            'user_id' => $user->id,
            'title' => 'Valor de Teste',
            'body' => 'Valor de Teste'
        ];

        $response = $this->postJson('/api/articles', $data);
                 
        $response->assertStatus(201)
                 ->assertJsonFragment([
            'title' => 'Valor de Teste',
            'body' => 'Valor de Teste',
        ]);
                 
        $this->assertDatabaseHas('articles', [
            [
            'title' => 'Valor de Teste',
            'body' => 'Valor de Teste',
        ]
        ]);
    }
    
    #[Test]
    public function a_validacao_falha_sem_um_campo_obrigatorio()
    {
        // PASSO 6.1: Cria as dependências (necessárias para o 'user_id' não falhar)
                $user = \App\Models\User::factory()->create();


        $data = [
            'user_id' => $user->id,
            'title' => 'Valor de Teste',
            'body' => 'Valor de Teste'
        ];
        
        // Remove um campo obrigatório para forçar o erro
        unset($data['title']); 

        $response = $this->postJson('/api/articles', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('title');
    }
    
    #[Test]
    public function a_visualizacao_de_recurso_funciona()
    {
        $model = Article::factory()->create();
        
        $response = $this->getJson('/api/articles/' . $model->id);
                         
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $model->id]);
    }
}