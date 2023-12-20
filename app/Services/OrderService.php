<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService,
    )
    {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return bool
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $user = $this->findUserByDomain($data['merchant_domain']);

         $this->affiliateService->register(
            $user->merchant,
            $data['customer_email'],
            $data['customer_name'],
            (float)0.1,
        );

        $this->createOrUpdateOrder(
            $data,
            $user->merchant->id,
            $user->affiliate
        );
    }


    public function findUserByDomain(string $domain): User
    {
        $merchant = Merchant::where(['domain' => $domain])->with('user')->first();
        return $merchant->user;
    }

    public function createOrUpdateOrder(array $data, int $merchantId, Affiliate $affliate): void
    {
        Order::updateOrCreate([
            'external_order_id' => $data['order_id']
        ], [
            'external_order_id' => $data['order_id'],
            'merchant_id' => $merchantId,
            'affiliate_id' => $affliate->id,
            'subtotal' => round($data['subtotal_price'], 2),
            'commission_owed' => $data['subtotal_price'] * $affliate->commission_rate,
        ]);
    }
}
