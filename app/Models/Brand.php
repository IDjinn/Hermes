<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 */
class Brand extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name'];

    public function products() : BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'brand', 'id');
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'brand', 'id');
    }
}
