<?php

namespace App\Enums;

enum ProdutoStatus: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
    case PENDENTE = 'pendente';
}