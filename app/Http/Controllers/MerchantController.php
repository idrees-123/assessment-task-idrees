<?php

namespace App\Http\Controllers;

use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function __construct(
        protected MerchantService $merchantService
    )
    {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $orders = $this->merchantService->getOrderByDate(
            $request->input('from'),
            $request->input('to')
        );

        $noAffiliate = $orders->whereNull('affiliate_id');

        return response()->json([
            'count' => $orders->count(),
            'commissions_owed' => $orders->sum('commission_owed') - $noAffiliate->sum('commission_owed'),
            'revenue' => $orders->sum('subtotal'),
        ]);
    }
}
