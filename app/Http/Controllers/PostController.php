<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct(protected PostService $postService)
    {
        // Injeção de dependência do Service
    }

    public function index()
    {
        return $this->postService->listarTodos();
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $model = $this->postService->criarNovo($data);
        return response()->json($model, 201);
    }

    public function show(Post $post)
    {
        return $this->postService->buscarPorId($post->id);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $data = $request->validated();
        $model = $this->postService->atualizar($post->id, $data);
        return response()->json($model);
    }

    public function destroy(Post $post)
    {
        $this->postService->deletar($post->id);
        return response()->json(null, 204);
    }


}