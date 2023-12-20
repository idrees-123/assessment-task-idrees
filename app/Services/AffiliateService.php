<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    )
    {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param Merchant $merchant
     * @param string $email
     * @param string $name
     * @param float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        try {
            $this->associateMerchantWithUser($merchant, $name, $email);

            $affiliate = Affiliate::create([
                'user_id' => $merchant->user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $commissionRate,
                'discount_code' => $this->getCode($merchant),
            ]);

            $this->sendAffliateEmail($email, $affiliate);

            return $affiliate;
        } catch (Throwable $exception) {
            report($exception);
            throw new AffiliateCreateException($exception->getMessage());
        }

    }

    /**
     * @param string $name
     * @param string $email
     * @param Merchant $merchant
     * @return void
     */
    public function associateMerchantWithUser(Merchant $merchant, string $name, string $email): void
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(10),
            'type' => User::TYPE_MERCHANT,
        ]);

        $merchant->user()->associate($user);
    }

    /**
     * @param Merchant $merchant
     * @return string
     */
    public function getCode(Merchant $merchant): string
    {
        $data = $this->apiService->createDiscountCode($merchant);

        if (!isset($data['code'])) {
            return Str::uuid();
        }
        return $data['code'];
    }

    /**
     * @param $affiliate
     * @return void
     */
    public function sendAffliateEmail(string $email, Affiliate $affiliate): void
    {
        Mail::to($email)->send(new AffiliateCreated($affiliate));
    }
}
