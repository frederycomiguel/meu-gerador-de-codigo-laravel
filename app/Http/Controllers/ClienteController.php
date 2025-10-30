<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ClienteService;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function __construct(protected ClienteService $clienteService)
    {
        // Injeção de dependência do Service
    }

    public function index()
    {
        return $this->clienteService->listarTodos();
    }

    public function store(StoreClienteRequest $request)
    {
        $data = $request->validated();
        $model = $this->clienteService->criarNovo($data);
        return response()->json($model, 201);
    }

    public function show(Cliente $cliente)
    {
        return $this->clienteService->buscarPorId($cliente->id);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $data = $request->validated();
        $model = $this->clienteService->atualizar($cliente->id, $data);
        return response()->json($model);
    }

    public function destroy(Cliente $cliente)
    {
        $this->clienteService->deletar($cliente->id);
        return response()->json(null, 204);
    }


}