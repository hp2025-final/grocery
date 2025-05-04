<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_code', 'name', 'category_id', 'unit', 'buy_price', 'sale_price', 'opening_qty', 'notes', 'account_id'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($inventory) {
            $last = self::orderBy('id', 'desc')->first();
            $lastNumber = $last ? intval(substr($last->inventory_code, 4)) : 0;
            $inventory->inventory_code = 'PRD-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        });
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

}
