<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllotmentLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'product_info_id',
        'allotment_date',
        'remark',
        'careted_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product_info(): BelongsTo
    {
        return $this->belongsTo(ProductInfo::class);
    }
}
