<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'descricao',
        'price',
        // ...outros campos que já estavam aqui
        'status', // <- ADICIONE ESTA LINHA
    ];

/**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
  protected $casts = [
        'price' => 'float',
        'status' => ProdutoStatus::class, // <- USE O ENUM AQUI
    ];
}