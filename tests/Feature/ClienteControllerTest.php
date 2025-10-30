<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Cliente;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase; // Limpa o banco de dados a cada teste

    /**
     * Testa se a rota 'index' (listagem) está funcionando.
     * @test
     */
    public function a_listagem_de_recursos_funciona()
    {
        // Cria 3 instâncias do model usando a Factory
        Cliente::factory(3)->create();

        $response = $this->getJson('/api/clientes');

        $response->assertStatus(200)
                 ->assertJsonCount(3); // Verifica se os 3 foram retornados
    }

    /**
     * Testa se a criação de um novo recurso funciona.
     * @test
     */
    public function a_criacao_de_recurso_funciona()
    {
        $data = [
            'nome' => 'Nome de Teste',
            'email' => 'teste@email.com',
            'telefone' => '123456789',
        ];

        $response = $this->postJson('/api/clientes', $data);

        $response->assertStatus(201) // 201 Created
                 ->assertJsonFragment($data);
                 
        // Verifica se os dados realmente foram salvos no banco
        $this->assertDatabaseHas('clientes', $data);
    }
    
    /**
     * Testa se a validação falha ao enviar dados incompletos.
     * @test
     */
    public function a_validacao_falha_sem_um_campo_obrigatorio()
    {
        $data = [
            'nome' => 'Nome de Teste',
            'email' => 'teste@email.com',
            'telefone' => '123456789',
        ];
        
        // Remove um campo obrigatório para forçar o erro
        unset($data['nome']); 

        $response = $this->postJson('/api/clientes', $data);

        $response->assertStatus(422) // 422 Unprocessable Entity
                 ->assertJsonValidationErrors('nome');
    }
    
    /**
     * Testa se a visualização de um recurso funciona.
     * @test
     */
    public function a_visualizacao_de_recurso_funciona()
    {
        $model = Cliente::factory()->create();
        
        $response = $this->getJson('/api/clientes/' . $model->id);
                         
        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $model->id]);
    }
}