<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FeatureGenerationService; // <-- 1. Importa o nosso service
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GeradorToolController extends Controller
{
    /**
     * Mostra a página do gerador (o formulário).
     */
    public function show(): View
    {
        // Apenas retorna a view. 
        // 'session('logs')' é usado para mostrar os logs da última execução.
        return view('gerador', [
            'logs' => session('logs', []),
            'last_prompt' => session('last_prompt', '')
        ]);
    }

    /**
     * Executa o gerador (o formulário aponta para cá).
     */
    public function run(Request $request, FeatureGenerationService $generator): RedirectResponse
    {
        // 1. Valida o formulário (garante que a descrição não está vazia)
        $request->validate([
            'descricao' => 'required|string|min:10',
        ]);

        $descricao = $request->input('descricao');

        // 2. Chama o mesmo service que o Artisan usa!
        $success = $generator->generate($descricao);
        
        // 3. Pega os logs gerados pelo service
        $logs = $generator->logs;

        // 4. Salva os logs e o prompt na sessão para que a página 
        // de 'show' possa exibi-los após o redirecionamento.
        return redirect()->route('gerador.show')
            ->with('logs', $logs)
            ->with('last_prompt', $descricao);
    }
}