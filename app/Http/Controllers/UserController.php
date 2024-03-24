<?php

namespace App\Http\Controllers;

use App\Models\ProductInfo;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', 'Member')
            ->when($request->searchParam, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->searchParam}%")
                        ->orWhere('email', 'like', "%{$request->searchParam}%");
                });
            })
            ->when($request->orderBy, function ($query) use ($request) {
                !str_contains($request->orderBy['column'], '.') && $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
            })
            ->select('id', 'name', 'email');

        if ($request->isPaginate) {
            $users = $users->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $users = $users->get();
        }

        return sendRes(200, null, $users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'users.*.name' => 'required|max:150',
            'users.*.email' => 'required|email|distinct|max:250|unique:users,email,NULL,id,deleted_at,NULL'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            User::insert($request->users);

            DB::commit();
            return sendRes(200, 'User has been created successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }

    public function show($id)
    {
        return sendRes(200, null, User::find($id));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:150',
            'email' => "required|email|max:250|unique:users,email,{$id},id,deleted_at,NULL"
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            User::find($id)->update($request->only('name', 'email'));

            DB::commit();
            return sendRes(200, 'User has been updated successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return sendRes(200, 'User has been deleted successfully.', null);
    }

    public function dashboard()
    {
        return sendRes(200, null, [
            'users' => User::count(),
            'totalStocks' => ProductInfo::count(),
            'allotedStocks' => ProductInfo::whereNotNull('user_id')->count(),
            'damageStocks' => ProductInfo::where('is_damage', 1)->count()
        ]);
    }
}
