<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategory = SubCategory::with('category:id,name')
            ->when($request->searchParam, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->searchParam}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$request->searchParam}%"));
                });
            })
            ->when($request->orderBy, function ($query) use ($request) {
                !str_contains($request->orderBy['column'], '.') && $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
            })
            ->select('id', 'category_id', 'name');
        
        if ($request->isPaginate) {
            $subCategory = $subCategory->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $subCategory = $subCategory->get();
        }

        if ($request->orderBy && str_contains($request->orderBy['column'], '.')) {
            $sortedResult = $subCategory instanceof Collection ? $subCategory : $subCategory->getCollection();
            $sortedResult->sortBy($request->orderBy['column'], $request->orderBy['order'] == 'asc');
            if (!$subCategory instanceof Collection) {
                $subCategory->setCollection($sortedResult->values());
            }
        }

        return sendRes(200, null, $subCategory);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'required|exists:categories,id,deleted_at,NULL',
            'subCategories.*.name' => 'required|max:150|distinct|unique:sub_categories,name,NULL,id,deleted_at,NULL'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            SubCategory::insert(collect($request->subCategories)->map(function ($req) use ($request) {
                return [
                    'category_id' => $request->categoryId,
                    'name' => $req['name'],
                    'created_by' => auth()->id(),
                    'created_at' => now()->format('Y-m-d H:i:s')
                ];
            })->toArray());

            DB::commit();
            return sendRes(200, 'Sub Categories has been saved successfully.', null);
        } catch (Exception $ex) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong', null);
        }
    }

    public function show($id)
    {
        return sendRes(200, null, SubCategory::select(['id', 'name'])->find($id)->toArray());
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id,deleted_at,NULL',
            'name' => "required|max:150|unique:sub_categories,name,{$id},id,deleted_at,NULL"
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            SubCategory::find($id)->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'updated_by' => auth()->id()
            ]);

            DB::commit();
            return sendRes(200, 'Sub Category has been updated successfully.', null);
        } catch (Exception $ex) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong', null);
        }
    }

    public function destroy($id)
    {
        SubCategory::find($id)->delete();

        return sendRes(200, 'Sub Category has been deleted successfully.', null);
    }
}
