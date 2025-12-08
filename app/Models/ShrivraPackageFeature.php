<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShrivraPackageFeature extends Model
{
    protected $fillable = [
        'package_id',
        'feature_id',
        'feature_value',
    ];

    public function package()
    {
        return $this->belongsTo(ShrivraPackage::class, 'package_id');
    }

    public function panelFeature()
    {
        return $this->belongsTo(ShrivraPanelFeature::class, 'feature_id');
    }
}