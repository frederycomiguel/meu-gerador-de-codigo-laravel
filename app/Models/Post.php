<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $fillable = [
        'titulo',
        'conteudo',
        'user_id',
    ];

    /**
     * Get the user that owns the Post.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
