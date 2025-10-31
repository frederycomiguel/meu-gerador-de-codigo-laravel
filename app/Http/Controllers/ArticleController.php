<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;

class ArticleController extends Controller
{
    public function __construct(protected ArticleService $articleService)
    {
        // Injeção de dependência do Service
    }

    public function index()
    {
        return $this->articleService->listarTodos();
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $model = $this->articleService->criarNovo($data);
        return response()->json($model, 201);
    }

    public function show(Article $article)
    {
        return $this->articleService->buscarPorId($article->id);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();
        $model = $this->articleService->atualizar($article->id, $data);
        return response()->json($model);
    }

    public function destroy(Article $article)
    {
        $this->articleService->deletar($article->id);
        return response()->json(null, 204);
    }


}