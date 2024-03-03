<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sub_category_id', 'name', 'stock', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at'
    ];

    public function sub_category(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
}
