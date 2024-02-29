<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $category = Category::when($request->searchParam, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->searchParam}%");
        })->when($request->orderBy, function ($query) use ($request) {
            $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
        })->select(['id', 'name']);

        if ($request->isPaginate) {
            $category = $category->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $category = $category->get();
        }

        return sendRes(200, null, $category);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories.*.name' => 'required|max:150|distinct|unique:categories,name,NULL,id,deleted_at,NULL'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            Category::insert(collect($request->categories)->map(function ($req) {
                return [
                    'name' => $req['name'],
                    'created_by' => auth()->id(),
                    'created_at' => now()->format('Y-m-d H:i:s')
                ];
            })->toArray());

            DB::commit();
            return sendRes(200, 'Categories has been saved successfully.', null);
        } catch (Exception $ex) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong', null);
        }
    }

    public function show($id)
    {
        return sendRes(200, null, Category::select(['id', 'name'])->find($id)->toArray());
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => "required|max:150|unique:categories,name,{$id},id,deleted_at,NULL"
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            Category::find($id)->update([
                'name' => $request->name,
                'updated_by' => auth()->id()
            ]);

            DB::commit();
            return sendRes(200, 'Category has been updated successfully.', null);
        } catch (Exception $ex) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong', null);
        }
    }

    public function destroy($id)
    {
        Category::find($id)->delete();

        return sendRes(200, 'Category has been deleted successfully.', null);
    }
}
