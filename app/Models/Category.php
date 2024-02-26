<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function sub_categories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }
}
