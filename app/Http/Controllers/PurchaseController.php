<?php

namespace App\Http\Controllers;

use App\Models\ProductInfo;
use App\Models\Purchase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with('product:id,name,stock')
            ->when($request->searchParam, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('vendor', 'like', "%{$request->searchParam}%")
                        ->orWhere('bill_no', 'like', "%{$request->searchParam}%")
                        ->orWhere('amount', 'like', "%{$request->searchParam}%")
                        ->orWhereHas('product', function ($query) use ($request) {
                            $query->where('name', 'liek', "%{$request->searchParam}%");
                        });
                });
            })
            ->when($request->orderBy, function ($query) use ($request) {
                !str_contains($request->orderBy['column'], '.') && $query->orderBy($request->orderBy['column'], $request->orderBy['order']);
            })
            ->select('id', 'product_id', 'vendor', 'bill_no', 'amount');

        if ($request->isPaginate) {
            $purchases = $purchases->paginate(($request->perPage ?? 10), ['*'], 'page', ($request->page ?? 1));
        } else {
            $purchases = $purchases->get();
        }

        if ($request->orderBy && str_contains($request->orderBy['column'], '.')) {
            $sortedResult = ($purchases instanceof Collection ? $purchases : $purchases->getCollection())->sortBy($request->orderBy['column'], $request->orderBy['order'] == 'desc');

            if (!$purchases instanceof Collection) {
                $purchases->setCollection($sortedResult->values());
            } else {
                $purchases = $sortedResult;
            }
        }

        return sendRes(200, null, $purchases);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productId' => 'required|exists:products,id',
            'vendor' => 'required|max:150',
            'billNo' => 'required|max:50',
            'amount' => 'required|numeric',
            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            Purchase::create([
                'product_id' => $request->productId,
                'vendor' => $request->vendor,
                'bill_no' => $request->billNo,
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'created_by' => auth()->id()
            ]);

            $productInfoArr = [];
            $productInfoCount = ProductInfo::count() + 1;
            for ($i=0; $i < $request->quantity; $i++) { 
                $productInfoArr[] = [
                    'product_id' => $request->productId,
                    'product_no' => $productInfoCount + $i,
                    'created_at' => now(),
                    'created_by' => auth()->id()
                ];
            }
            ProductInfo::insert($productInfoArr);

            DB::commit();
            return sendRes(200, 'Products has been created successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }
}
