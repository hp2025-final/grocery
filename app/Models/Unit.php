<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'abbreviation'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id');
    }
}
