<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Possibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'possibility',
        'description',
        'weblink',
        'assigned_quiz',
        'active_inactive'
    ];
}
