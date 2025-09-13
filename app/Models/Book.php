<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'authors', 'genre', // many too many ???
        'title', 'description', 'edition', 'publisher',
        'year', 'format', 'pages', 'country', 'isbn'
    ];
}
