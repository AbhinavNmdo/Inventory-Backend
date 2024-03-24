<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('sub_category:id,category_id,name', 'sub_category.category:id,name')
            ->when($request->searchParam, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->searchParam}%")
                        ->orWhereHas('sub_category', function($query) use ($request) {
                            $query->where('name', 'like', "%{$request->searchParam}%")
                                ->orWhereHas('category', fn($query) => $query->where('name', 'like', "%{$request->searchParam}%"));
                        });
                });
            })
            ->when($request->orderBy, function ($query) use ($request) {
                !str_contains($request->orderBy['column'], '.') && $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
            })
            ->when($request->filters, function ($query) use ($request) {
                foreach ($request->filters as $filter) {
                    if (!str_contains($filter['column'], '.')) {
                        $query->where($filter['column'], $filter['value']);
                    }
                }
            })
            ->select('id', 'sub_category_id', 'name', 'stock');

        if ($request->isPaginate) {
            $products = $products->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $products = $products->get();
        }

        if ($request->orderBy && str_contains($request->orderBy['column'], '.')) {
            $sortedResult = ($products instanceof Collection ? $products : $products->getCollection())->sortBy($request->orderBy['column'], $request->orderBy['order'] == 'desc');

            if (!$products instanceof Collection) {
                $products->setCollection($sortedResult->values());
            } else {
                $products = $sortedResult;
            }
        }

        return sendRes(200, null, $products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'required|exists:categories,id,deleted_at,NULL',
            'subCategoryId' => 'required|exists:sub_categories,id,deleted_at,NULL',
            'products.*.name' => 'required|max:150|distinct|unique:sub_categories,name,NULL,id,deleted_at,NULL',
            'products.*.stock' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            $productInfoArr = [];
            $productInfoCount = ProductInfo::count() + 1;
            foreach ($request->products as $products) {
                $product = Product::create([
                    'category_id' => $request->categoryId,
                    'sub_category_id' => $request->subCategoryId,
                    'name' => $products['name'],
                    'stock' => $products['stock']
                ]);

                for ($i=0; $i < $products['stock']; $i++) { 
                    $productInfoArr[] = [
                        'product_id' => $product->id,
                        'product_no' => $productInfoCount + $i,
                        'created_at' => now(),
                        'created_by' => auth()->id()
                    ];
                }
            }
            ProductInfo::insert($productInfoArr);

            DB::commit();
            return sendRes(200, 'Products has been created successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }

    public function show($id)
    {
        return sendRes(200, null, Product::with('sub_category:id,category_id,name', 'sub_category.category:id,name')->select('id', 'sub_category_id', 'name', 'stock')->find($id));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'required|exists:categories,id,deleted_at,NULL',
            'subCategoryId' => 'required|exists:sub_categories,id,deleted_at,NULL',
            'name' => "required|max:150|unique:sub_categories,name,{$id},id,deleted_at,NULL",
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            Product::find($id)->update([
                'sub_category_id' => $request->subCategoryId,
                'name' => $request->name
            ]);

            DB::commit();
            return sendRes(200, 'Product has been updated successfully.', null);
        } catch (Exception) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }

    public function destroy($id)
    {
        Product::find($id)->delete();

        return sendRes(200, 'Product has been deleted successfully.', null);
    }

    public function productInfoList()
    {
        return sendRes(200, null, ProductInfo::with('product:id,name')->where(['is_damage' => 0, 'user_id' => null])->get(['id', 'product_id', 'product_no']));
    }
}
