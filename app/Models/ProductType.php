<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Product
 *
 * @package App\Models
 * @property int id
 * @property string name
 */
class ProductType extends Model
{
    use HasFactory;

    protected $table = 'product_types';
    protected $fillable = [
        'id',
        'name'
    ];

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_type', 'id');
    }
}
