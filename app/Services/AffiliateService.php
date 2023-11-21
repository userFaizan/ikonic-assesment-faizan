<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        $user = User::firstOrCreate(['email' => $email], ['name' => $name]);

        // Check if the user is already an affiliate for the given merchant
        if ($user->affiliates()->where('merchant_id', $merchant->id)->exists()) {
            throw new AffiliateCreateException("User is already an affiliate for this merchant.");
        }

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->generateDiscountCode(),
        ]);

        // Send an email to the affiliate
        Mail::to($user->email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
    /**
     * Generate a unique discount code for the affiliate.
     *
     * @return string
     */
    protected function generateDiscountCode(): string
    {
        $code = strtoupper(str_random(8)); // Use Laravel helper function str_random to generate a random string

        // Check if the generated code already exists, generate a new one if it does
        while (Affiliate::where('discount_code', $code)->exists()) {
            $code = strtoupper(str_random(8));
        }

        return $code;
    }
}
