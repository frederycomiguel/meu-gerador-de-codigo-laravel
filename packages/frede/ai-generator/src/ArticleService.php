<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
    public function __construct(protected Article $article)
    {
        // Injeção de dependência do Model
    }

    public function listarTodos()
    {
        return $this->article->all();
    }

    public function criarNovo(array $data)
    {
        return $this->article->create($data);
    }

    public function buscarPorId($id)
    {
        return $this->article->findOrFail($id);
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