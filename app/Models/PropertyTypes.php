<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property string|null $name
 * @property integer|null $product_type_id
 * @property integer|null $category_id
 * @property integer|null $sub_category_id
 * @property Json|null $value
 */
class PropertyTypes extends Model
{
    use HasFactory;

    protected $table = "property_types";

    protected $fillable = [
        "id",
        "product_type_id",
        "category_id",
        "sub_category_id",
        "name",
        "value",
    ];

    public function product_type() : BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }
    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function sub_category() : BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
}
