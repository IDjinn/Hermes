<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer $id
 * @property integer $product_id
 * @property integer $property_type
 * @property Json|null $value
 */
class ProductProperties extends Model
{
    use HasFactory;

    protected $table = "product_properties";
    protected $fillable = [
        "id",
        "product_id",
        "property_type",
        "value",
    ];

    public function propertyType() : HasOne
    {
        return $this->hasOne(PropertyTypes::class,'id', 'property_type');
    }

    public function product() : HasOne
    {
        return $this->hasOne(Product::class,'id', 'product_id');
    }
}
