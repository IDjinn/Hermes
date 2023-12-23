<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class Product
 *
 * @package App\Models
 *
 * @property int $id
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
        "model",
        "part_number",
        "brand",
        "datasheet",
        "extra"
    ];
}
