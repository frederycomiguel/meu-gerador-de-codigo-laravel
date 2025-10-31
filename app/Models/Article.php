<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
    ];

    /**
     * Get the user that owns the Article.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
