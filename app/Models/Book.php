<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books'; // this model maps to the books table

    protected $primaryKey = 'book_id'; // maps book_id as the primary key

    protected $fillable = [ 
        'title',
        'author',
        'status',
    ]; // can be mass-asigned
}
