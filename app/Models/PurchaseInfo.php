<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInfo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'quantity',
        'amount',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
