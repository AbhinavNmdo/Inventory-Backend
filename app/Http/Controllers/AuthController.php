<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        $token = Auth::attempt($request->only('username','password'));
        if (!$token) {
            return sendRes(401, 'Credentials does not match in our record.', null);
        }

        return sendRes(200, null, [
            'token' => $token,
            'user' => auth()->user(),
            'expiresIn' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function me()
    {
        return sendRes(200, null, ['user' => auth()->user()]);
    }

    public function refresh()
    {
        return sendRes(200, null, [
            'token'=> auth()->refresh(),
            'expiresIn' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return sendRes(200, 'You have been logged out successfully', null);
    }
}
