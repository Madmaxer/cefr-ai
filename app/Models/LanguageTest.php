<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageTest extends Model
{
    protected $fillable = [
        'user_id',
        'language',
        'level',
        'description',
        'tested_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
