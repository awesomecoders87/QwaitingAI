<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShrivraPackage extends Model
{
    // Scope for filtering by name
    public function scopeName(
        $query, $name)
    {
        return $query->where('name', 'like', "%$name%");
    }
    // Scope for filtering by price
    public function scopePrice($query, $price)
    {
        return $query->where('price', $price);
    }
    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    protected $table ="shrivra_package";

    protected $fillable = [
        'name',
        'price',
        'price_yearly',
        'type',
        'status',
        'currency',
        'show_page',
        'price_monthly_inr',
        'price_yearly_inr',
        'sorting',
    ];

    public function features()
    {
        return $this->hasMany(ShrivraPackageFeature::class, 'package_id');
    }
}
