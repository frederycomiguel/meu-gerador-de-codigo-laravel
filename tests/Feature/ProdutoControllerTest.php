<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Produto;
use PHPUnit\Framework\Attributes\Test;
// PASSO 6.1: Importa os models relacionados


class ProdutoControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_listagem_de_recursos_funciona()
    {
        Produto::factory(3)->create();
        $response = $this->getJson('/api/produtos');
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    #[Test]
    public function a_criacao_de_recurso_funciona()
    {
        // PASSO 6.1: Cria as dependências primeiro (ex: o User)
        

        $data = [
            'nome' => 'Nome de Teste',
            'preco' => '123.45'
        ];

        $response = $this->postJson('/api/produtos', $data);
                 
        $response->assertStatus(201)
                 ->assertJsonFragment([
            'nome' => 'Nome de Teste',
            'preco' => '123.45',
        ]);
                 
        $this->assertDatabaseHas('produtos', [
            [
            'nome' => 'Nome de Teste',
            'preco' => '123.45',
        ]
        ]);
    }
    
    #[Test]
    public function a_validacao_falha_sem_um_campo_obrigatorio()
    {
        // PASSO 6.1: Cria as dependências (necessárias para o 'user_id' não falhar)
        

        $data = [
            'nome' => 'Nome de Teste',
            'preco' => '123.45'
        ];
        
        // Remove um campo obrigatório para forçar o erro
        unset($data['nome']); 

        $response = $this->postJson('/api/produtos', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('nome');
    }
    
    #[Test]
    public function a_visualizacao_de_recurso_funciona()
    {
        $model = Produto::factory()->create();
        
        $response = $this->getJson('/api/produtos/' . $model->id);
                         
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $model->id]);
    }
}