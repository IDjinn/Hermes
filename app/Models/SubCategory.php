<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubCategory extends Model
{
    use HasFactory;


    protected $table = 'sub_categories';
    protected $fillable = [
        'id',
        'name',
        'parent_category_id'
    ];


    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'sub_category', 'id');
    }
}
