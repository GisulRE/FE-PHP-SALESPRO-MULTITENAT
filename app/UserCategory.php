<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model
{
    use HasFactory;

    protected $table = 'user_category';

    protected $fillable = [
        "user_id",
        "category_id"
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'id', 'category_id');
    }
}
