<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method

        $user = User::with('merchant')->create([
                'name' => Str::random(8),
                'email' => $data['email'],
                'password' => $data['api_key'],
                'type' => User::TYPE_MERCHANT,
            ]);

        $user->merchant()->create([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);

        $user->load('merchant');

        return $user->merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        $user->update([
                'name' => Str::random(8),
                'email' => $data['email'],
                'password' => $data['api_key'],
            ]);

        $user->load('merchant');

        $user->merchant()->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);

    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        $user = User::where(['email' => $email])->with('merchant')->first();
        return $user?->merchant;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        $affiliate->load('orders');
        $unpaidOrders = $affiliate?->orders?->where('payout_status', Order::STATUS_UNPAID);

        if($unpaidOrders) {
            return ;
        }

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }

    public function getOrderByDate(string $from, string $to): Collection
    {
        return Order::whereBetween('created_at', [
            $this->getCarbon($from),
            $this->getCarbon($to)
        ])->get();
    }

    /**
     * @param string $dateTime
     * @return Carbon
     */
    public function getCarbon(string $dateTime): Carbon
    {
        $dateTime = Carbon::parse($dateTime);

        if (!$dateTime->format('H:i:s')) {
            $dateTime->setTime(0, 0, 0);
        }
        return $dateTime;
    }
}
