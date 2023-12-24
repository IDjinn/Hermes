<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Product
 *
 * @package App\Models
 *
 * @property int $id
 *
 * @property string|null $product_type
 * @property string|null $category
 * @property string|null $sub_category
 *
 * @property string $model
 * @property string|null $part_number
 * @property string $brand
 * @property string|null $datasheet
 * @property string|null $extra
 */
class Product extends Model
{
    use HasFactory;

    public $table = "products";

    protected $fillable = [
        "id",

        "product_type",
        "category",
        "sub_category",

        "model",
        "part_number",
        "brand",
        "datasheet",
        "extra"
    ];
    public function productType(): HasOne
    {
        return $this->hasOne(ProductType::class, 'id', 'product_type');
    }
    public function _category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category');
    }
    public function subCategory(): HasOne
    {
        return $this->hasOne(SubCategory::class, 'id', 'sub_category');
    }
    public function _brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand');
    }
}
