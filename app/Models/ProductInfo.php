<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductInfo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'product_no',
        'is_damage',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    protected $casts = [
        'is_damage' => 'bool'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allotment_log(): HasMany
    {
        return $this->hasMany(AllotmentLog::class);
    }
}
