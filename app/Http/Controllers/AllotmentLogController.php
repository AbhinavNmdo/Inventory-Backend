<?php

namespace App\Http\Controllers;

use App\Models\AllotmentLog;
use App\Models\Product;
use App\Models\ProductInfo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllotmentLogController extends Controller
{
    public function index(Request $request)
    {
        $allotments = AllotmentLog::with('user:id,name,email', 'product_info:id,user_id,product_id,product_no,is_damage', 'product_info.product:id,sub_category_id,name,stock')
            ->when($request->searchParam, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhereHas('user', function ($query) use ($request) {
                        $query->where('name', 'like', "%{$request->searchParam}%");
                    })->orWhereHas('product_info', function ($query) use ($request) {
                        $query->where('name', 'like', "%{$request->searchParam}%");
                    });
                });
            })
            ->when($request->orderBy, function ($query) use ($request) {
                !str_contains($request->orderBy['column'], '.') && $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
            })->select('id', 'user_id', 'product_id', 'allotment_date', 'is_damage', 'remark');

        if ($request->isPaginate) {
            $allotments = $allotments->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $allotments = $allotments->get();
        }

        if ($request->orderBy && str_contains($request->orderBy['column'], '.')) {
            $sortedResult = ($allotments instanceof Collection ? $allotments : $allotments->getCollection())->sortBy($request->orderBy['column'], $request->orderBy['order'] == 'desc');

            if (!$allotments instanceof Collection) {
                $allotments->setCollection($sortedResult->values());
            } else {
                $allotments = $sortedResult;
            }
        }

        return sendRes(200, null, $allotments);
    }

    public function allotProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'productId' => 'required|exists:products,id',
            'productInfoId' => 'required|exists:product_infos,id',
            'allotmentDate' => 'required|date',
            'remark' => 'nullable|max:250'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            AllotmentLog::create([
                'user_id' => $request->userId,
                'product_info_id' => $request->productInfoId,
                'allotment_date' => Carbon::parse($request->allotmentDate)->format('Y-m-d'),
                'remark' => $request->remark,
                'created_by' => auth()->id()
            ]);

            Product::find($request->productId)->increment('stock');
            ProductInfo::find($request->productInfoId)->update([
                'user_id' => $request->userId
            ]);

            DB::commit();
            return sendRes(200, 'Product has been alloted successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }

    public function returnProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'allotmentLogId' => 'required|exists:allotment_logs,id',
            'returnDate' => 'required|date',
            'isDamage' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            $allotmentLog = AllotmentLog::find($request->allotmentLogId);

            if (!$request->isDamage) {
                Product::find($allotmentLog->product_info->product_id)->decrement('stock');
            }

            $allotmentLog->update([
                'return_date' => Carbon::parse($request->returnDate)->format('Y-m-d'),
                'updated_by' => auth()->id()
            ]);

            ProductInfo::find($allotmentLog->product_info_id)->update([
                'user_id' => null
            ]);

            DB::commit();
            return sendRes(200, 'Product has been returned successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }
}
