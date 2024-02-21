<?php

use Illuminate\Http\Request;

function sendRes(int $status, string|null $msg, array|null $data) {
    return response()->json([
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    ], $status);
}