<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Record;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'description',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
