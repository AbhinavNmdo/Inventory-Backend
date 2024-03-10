<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductInfo;
use App\Models\Purchase;
use App\Models\PurchaseInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::when($request->searchParam, function ($query) use ($request) {
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
            ->select('id', 'vendor', 'bill_no', 'total_amt');

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
            'vendor' => 'required|max:150',
            'billNo' => 'required|max:50',
            'purchases.*.productId' => 'required|exists:products,id',
            'purchases.*.quantity' => 'required|numeric',
            'purchases.*.amount' => 'required|numeric',
            'totalAmt' => 'required'
        ]);

        if ($validator->fails()) {
            return sendRes(403, $validator->errors()->first(), null);
        }

        try {
            DB::beginTransaction();

            $purchase = Purchase::create([
                'vendor' => $request->vendor,
                'bill_no' => $request->billNo,
                'total_amt' => $request->totalAmt,
                'created_by' => auth()->id()
            ]);

            $productInfoArr = [];
            $purchaseInfoArr = [];
            $productInfoCount = ProductInfo::count() + 1;
            foreach ($request->purchases as $purchases) {
                $purchaseInfoArr[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $purchases['productId'],
                    'quantity' => $purchases['quantity'],
                    'amount' => $purchases['amount'],
                    'created_at' => now(),
                    'created_by' => auth()->id()
                ];

                for ($i = 0; $i < $purchases['quantity']; $i++) {
                    $productInfoArr[] = [
                        'product_id' => $purchases['productId'],
                        'product_no' => $productInfoCount + $i,
                        'created_at' => now(),
                        'created_by' => auth()->id()
                    ];
                }
                Product::find($purchases['productId'])->increment('stock', $purchases['quantity']);
            }
            ProductInfo::insert($productInfoArr);
            PurchaseInfo::insert($purchaseInfoArr);

            DB::commit();
            return sendRes(200, 'Products has been created successfully.', null);
        } catch (Exception $th) {
            DB::rollBack();
            return sendRes(500, 'Something went wrong.', null);
        }
    }
}
