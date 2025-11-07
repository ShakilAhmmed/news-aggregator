<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';

    protected $primaryKey = 'id';

    protected $fillable = [
        'source',
        'external_id',
        'url',
        'title',
        'summary',
        'authors',
        'category',
        'published_at',
        'raw',
    ];

    protected $casts = [
        'authors' => 'array',
        'raw' => 'array',
        'published_at' => 'datetime',
    ];
}
