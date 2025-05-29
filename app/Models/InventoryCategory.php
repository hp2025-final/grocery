<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'description'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            $last = self::orderBy('id', 'desc')->first();
            $lastNumber = $last ? intval(substr($last->code, 4)) : 0;
            $category->code = 'INC-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        });
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'category_id');
    }
}
