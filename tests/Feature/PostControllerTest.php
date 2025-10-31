<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;
use PHPUnit\Framework\Attributes\Test;
// PASSO 6.1: Importa os models relacionados
use \App\Models\User;


class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_listagem_de_recursos_funciona()
    {
        Post::factory(3)->create();
        $response = $this->getJson('/api/posts');
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
            'titulo' => 'Valor de Teste',
            'conteudo' => 'Valor de Teste'
        ];

        $response = $this->postJson('/api/posts', $data);
                 
        $response->assertStatus(201)
                 ->assertJsonFragment([
            'titulo' => 'Valor de Teste',
            'conteudo' => 'Valor de Teste',
        ]);
                 
        $this->assertDatabaseHas('posts', [
            [
            'titulo' => 'Valor de Teste',
            'conteudo' => 'Valor de Teste',
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
            'titulo' => 'Valor de Teste',
            'conteudo' => 'Valor de Teste'
        ];
        
        // Remove um campo obrigatório para forçar o erro
        unset($data['titulo']); 

        $response = $this->postJson('/api/posts', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('titulo');
    }
    
    #[Test]
    public function a_visualizacao_de_recurso_funciona()
    {
        $model = Post::factory()->create();
        
        $response = $this->getJson('/api/posts/' . $model->id);
                         
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $model->id]);
    }
}