<?php

namespace App\Services;

use App\Models\Produto;

class ProdutoService
{
    public function __construct(protected Produto $produto)
    {
        // Injeção de dependência do Model
    }

    public function listarTodos()
    {
        return $this->produto->all();
    }

    public function criarNovo(array $data)
    {
        return $this->produto->create($data);
    }

    public function buscarPorId($id)
    {
        return $this->produto->findOrFail($id);
    }

    public function atualizar($id, array $data)
    {
        $model = $this->buscarPorId($id);
        $model->update($data);
        return $model;
    }

    public function deletar($id)
    {
        $model = $this->buscarPorId($id);
        return $model->delete();
    }


}