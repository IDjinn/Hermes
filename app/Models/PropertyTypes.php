<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property Json|null $value
 */
class PropertyTypes extends Model
{
    use HasFactory;

    protected $table = "property_types";

    protected $fillable = [
      "id",
      "value",
    ];
}
