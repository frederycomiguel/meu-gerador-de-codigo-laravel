<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    public function __construct(protected Post $post)
    {
        // Injeção de dependência do Model
    }

    public function listarTodos()
    {
        return $this->post->all();
    }

    public function criarNovo(array $data)
    {
        return $this->post->create($data);
    }

    public function buscarPorId($id)
    {
        return $this->post->findOrFail($id);
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