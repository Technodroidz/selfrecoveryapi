<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'quiz_title',
        'title_font',
        'main_font'
    ];
}
