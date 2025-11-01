<?php

namespace App\Services;

use App\Models\Cliente;

class ClienteService
{
    public function __construct(protected Cliente $cliente)
    {
        // Injeção de dependência do Model
    }

    public function listarTodos()
    {
        return $this->cliente->all();
    }

    public function criarNovo(array $data)
    {
        return $this->cliente->create($data);
    }

    public function buscarPorId($id)
    {
        return $this->cliente->findOrFail($id);
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