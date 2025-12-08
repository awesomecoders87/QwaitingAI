<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShrivraPackage extends Model
{
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

