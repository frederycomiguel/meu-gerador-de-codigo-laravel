<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Código AI</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; background-color: #f4f7f6; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { padding: 20px 30px; border-bottom: 1px solid #eef; }
        .content { padding: 30px; }
        textarea { width: 100%; min-height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; margin-bottom: 15px; box-sizing: border-box; }
        button { background-color: #FF2D20; color: white; border: none; padding: 12px 20px; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #c51605; }
        .logs { margin-top: 30px; background-color: #2d3748; color: #f7fafc; padding: 20px; border-radius: 5px; font-family: "Courier New", Courier, monospace; font-size: 14px; overflow-x: auto; }
        .logs pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; }
        .log-ERRO { color: #f56565; }
        .log-SUCESSO { color: #48bb78; }
        .log-AVISO { color: #ecc94b; }
        
        /* --- NOVO CSS ADICIONADO --- */
        .examples {
            font-size: 0.9em;
            color: #555;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .examples ul { padding-left: 20px; margin: 5px 0 0 0; }
        .examples li { margin-bottom: 5px; }
        /* --- FIM DO NOVO CSS --- */
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>🤖 Gerador de Código AI</h1>
            <p>Descreva o CRUD que você quer gerar. Inclua os campos e relacionamentos.</p>
        </div>

        <div class="content">
            <form action="{{ route('gerador.run') }}" method="POST">
                @csrf <label for="descricao"><b>Pedido:</b></label>
                <textarea id="descricao" name="descricao" placeholder="Ex: Preciso de um CRUD para 'Post', com 'titulo' e 'conteudo', que pertence a um 'User'.">{{ $last_prompt ?? '' }}</textarea>
                
                <div class="examples">
                    <b>💡 Exemplos de Geração:</b>
                    <ul>
                        <li>Preciso de um CRUD completo para Cliente, que terá nome, email e telefone</li>
                        <li>Quero um CRUD para Post, com titulo e conteudo (text), que pertence a um User</li>
                        <li>CRUD para Produto, com nome (string), preco (decimal) e estoque (integer)</li>
                    </ul>
                    <small><b>Nota:</b> O comando de Modificação (`feature:modificar`) só está disponível via terminal (`php artisan`) por enquanto.</small>
                </div>
                @if ($errors->any())
                    <div style="color: red; margin-bottom: 15px;">
                        <strong>Ops!</strong> {{ $errors->first() }}
                    </div>
                @endif
                
                <button type="submit">Gerar Código</button>
            </form>

            @if (count($logs) > 0)
            <div class="logs">
                <pre>
@foreach ($logs as $log)
<span class="log-{{ explode(':', $log)[0] }}">{{ $log }}</span>
@endforeach
                </pre>
            </div>
            @endif
        </div>
    </div>

</body>
</html>