<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProdutoService;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function __construct(protected ProdutoService $produtoService)
    {
        // Injeção de dependência do Service
    }

    public function index()
    {
        return $this->produtoService->listarTodos();
    }

    public function store(StoreProdutoRequest $request)
    {
        $data = $request->validated();
        $model = $this->produtoService->criarNovo($data);
        return response()->json($model, 201);
    }

    public function show(Produto $produto)
    {
        return $this->produtoService->buscarPorId($produto->id);
    }

    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $data = $request->validated();
        $model = $this->produtoService->atualizar($produto->id, $data);
        return response()->json($model);
    }

    public function destroy(Produto $produto)
    {
        $this->produtoService->deletar($produto->id);
        return response()->json(null, 204);
    }


}