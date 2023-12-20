<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\AffiliateService;
use App\Services\MerchantService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService     $orderService,
        protected MerchantService  $merchantService,
        protected AffiliateService $affiliateService,
    )
    {
    }

    /**
     * Pass the necessary data to the process order method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method

        try {
            $merchant = $this->merchantService->findMerchantByEmail(
                (string)$request->get('customer_email')
            );

            if (!$merchant) {
                /** @var Merchant $merchant */
                $merchant = $this->merchantService->register([
                    'domain' => (string)$request->get('merchant_domain'),
                    'name' => (string)$request->get('customer_name'),
                    'email' => (string)$request->get('customer_email'),
                    'api_key' => Str::random(6),
                ]);
            }
            $this->orderService
                ->processOrder([
                    'order_id' => (string)$request->get('order_id'),
                    'subtotal_price' => (float)$request->get('subtotal_price'),
                    'merchant_domain' => (string)$merchant->domain,
                    'discount_code' => (string)$request->get('discount_code'),
                    'customer_email' => (string)$merchant->user->email,
                    'customer_name' => (string)$merchant->user->name,
                ]);

        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Some thing went wrong'], 500);
        }
        return response()->json(['message' => 'Order Processed Successfully'], 200);
    }
}
