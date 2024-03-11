<?php

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

function sendRes(int $status, string|null $msg, LengthAwarePaginator|Collection|array|null|Model $data) {
    return response()->json([
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ], $status);
}